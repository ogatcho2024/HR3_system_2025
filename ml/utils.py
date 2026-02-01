import os
from datetime import datetime, timedelta

import numpy as np
import pandas as pd
from sqlalchemy import create_engine, text

from config import MlConfig


def get_engine(cfg: MlConfig):
    return create_engine(
        f"mysql+pymysql://{cfg.db_user}:{cfg.db_password}@{cfg.db_host}:{cfg.db_port}/{cfg.db_name}"
    )


def fetch_leave_requests(cfg: MlConfig) -> pd.DataFrame:
    engine = get_engine(cfg)
    query = f"""
        SELECT
            lr.{cfg.col_leave_id} AS leave_id,
            lr.{cfg.col_leave_user_id} AS user_id,
            lr.{cfg.col_leave_type} AS leave_type,
            lr.{cfg.col_leave_start_date} AS start_date,
            lr.{cfg.col_leave_end_date} AS end_date,
            lr.{cfg.col_leave_days} AS days_requested,
            lr.{cfg.col_leave_status} AS status,
            lr.{cfg.col_leave_created_at} AS created_at,
            e.{cfg.col_emp_department} AS department,
            e.{cfg.col_emp_position} AS position,
            e.{cfg.col_emp_hire_date} AS hire_date
        FROM {cfg.leave_requests_table} lr
        LEFT JOIN {cfg.employees_table} e
            ON lr.{cfg.col_leave_user_id} = e.{cfg.col_emp_user_id}
    """
    return pd.read_sql(text(query), engine)


def add_derived_features(df: pd.DataFrame) -> pd.DataFrame:
    df = df.copy()
    df["start_date"] = pd.to_datetime(df["start_date"], errors="coerce")
    df["end_date"] = pd.to_datetime(df["end_date"], errors="coerce")
    df["created_at"] = pd.to_datetime(df["created_at"], errors="coerce")
    df["hire_date"] = pd.to_datetime(df["hire_date"], errors="coerce")

    df["start_month"] = df["start_date"].dt.month
    df["start_dow"] = df["start_date"].dt.dayofweek
    df["lead_time_days"] = (df["start_date"] - df["created_at"]).dt.days
    df["duration_days"] = (df["end_date"] - df["start_date"]).dt.days + 1
    df["employee_tenure_days"] = (df["created_at"] - df["hire_date"]).dt.days

    return df


def build_daily_demand(df: pd.DataFrame) -> pd.DataFrame:
    df = df.copy()
    df["start_date"] = pd.to_datetime(df["start_date"], errors="coerce")
    daily = df.groupby(df["start_date"].dt.date).size().reset_index(name="leave_count")
    daily.rename(columns={"start_date": "date"}, inplace=True)
    daily["date"] = pd.to_datetime(daily["date"])
    return daily.sort_values("date")


def make_demand_features(daily: pd.DataFrame) -> pd.DataFrame:
    daily = daily.copy().sort_values("date")
    daily["dow"] = daily["date"].dt.dayofweek
    daily["month"] = daily["date"].dt.month
    daily["is_weekend"] = daily["dow"].isin([5, 6]).astype(int)
    daily["lag_1"] = daily["leave_count"].shift(1)
    daily["lag_7"] = daily["leave_count"].shift(7)
    daily["rolling_7"] = daily["leave_count"].rolling(7).mean()
    return daily


def forecast_next_7_days(model, history: pd.DataFrame) -> pd.DataFrame:
    history = history.copy().sort_values("date")
    last_date = history["date"].max()
    future_rows = []

    history_series = history.set_index("date")["leave_count"].copy()

    for i in range(1, 8):
        next_date = last_date + timedelta(days=i)
        dow = next_date.weekday()
        month = next_date.month
        is_weekend = 1 if dow in (5, 6) else 0

        lag_1_date = next_date - timedelta(days=1)
        lag_7_date = next_date - timedelta(days=7)
        lag_1 = history_series.get(lag_1_date, np.nan)
        lag_7 = history_series.get(lag_7_date, np.nan)

        window_start = next_date - timedelta(days=7)
        rolling_7 = history_series.loc[window_start:lag_1_date].mean()

        row = {
            "date": next_date,
            "dow": dow,
            "month": month,
            "is_weekend": is_weekend,
            "lag_1": lag_1,
            "lag_7": lag_7,
            "rolling_7": rolling_7,
        }

        X = pd.DataFrame([row]).fillna(0)
        if "date" in X.columns:
            X = X.drop(columns=["date"])
        prediction = float(model.predict(X)[0])
        prediction = max(0.0, prediction)
        history_series.loc[next_date] = prediction
        future_rows.append({"date": next_date, "predicted_count": prediction})

    return pd.DataFrame(future_rows)
