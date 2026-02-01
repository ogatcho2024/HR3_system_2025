import argparse
import os
from datetime import datetime, timedelta

import pandas as pd
from joblib import load
from sqlalchemy import text

from config import MlConfig
from utils import (
    add_derived_features,
    build_daily_demand,
    forecast_next_7_days,
    get_engine,
)


def load_model(path: str):
    if not os.path.exists(path):
        raise FileNotFoundError(f"Model not found: {path}")
    return load(path)


def generate_approval_predictions(cfg: MlConfig, model_path: str):
    engine = get_engine(cfg)
    model_data = load_model(model_path)
    model = model_data["model"]
    feature_cols = model_data["feature_cols"]
    model_version = model_data["model_version"]

    status_filter = ""
    if cfg.approval_predict_statuses:
        placeholders = ", ".join([f"'{s}'" for s in cfg.approval_predict_statuses])
        status_filter = f"AND lr.{cfg.col_leave_status} IN ({placeholders})"

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
        LEFT JOIN leave_approval_predictions lap
            ON lap.leave_request_id = lr.{cfg.col_leave_id}
        WHERE lap.id IS NULL
        {status_filter}
    """

    df = pd.read_sql(text(query), engine)
    if df.empty:
        print("No leave requests pending ML approval prediction.")
        return

    df = add_derived_features(df)
    X = df[feature_cols].copy()

    proba = model.predict_proba(X)
    class_index = list(model.classes_).index(1) if hasattr(model, "classes_") else 1
    approve_prob = proba[:, class_index]
    predicted_label = ["APPROVE" if p >= 0.5 else "REVIEW" for p in approve_prob]

    now = datetime.utcnow()
    output = pd.DataFrame(
        {
            "leave_request_id": df["leave_id"],
            "predicted_label": predicted_label,
            "predicted_probability": approve_prob,
            "model_version": model_version,
            "predicted_at": now,
            "created_at": now,
            "updated_at": now,
        }
    )

    output.to_sql("leave_approval_predictions", engine, if_exists="append", index=False)
    print(f"Inserted {len(output)} approval predictions.")


def generate_demand_prediction(cfg: MlConfig, model_path: str):
    engine = get_engine(cfg)
    model_data = load_model(model_path)
    model = model_data["model"]
    model_version = model_data["model_version"]

    query = f"""
        SELECT
            lr.{cfg.col_leave_start_date} AS start_date
        FROM {cfg.leave_requests_table} lr
    """
    df = pd.read_sql(text(query), engine)
    if df.empty:
        print("No leave requests available for demand forecasting.")
        return

    daily = build_daily_demand(df)
    if daily.empty or len(daily) < 8:
        print("Not enough historical data to forecast demand (need at least 8 days).")
        return

    forecast = forecast_next_7_days(model, daily)
    predicted_count = int(round(forecast["predicted_count"].sum()))

    start_date = (datetime.utcnow().date() + timedelta(days=1))
    end_date = start_date + timedelta(days=6)
    now = datetime.utcnow()

    output = pd.DataFrame(
        [
            {
                "forecast_start_date": start_date,
                "forecast_end_date": end_date,
                "predicted_count": predicted_count,
                "model_version": model_version,
                "predicted_at": now,
                "created_at": now,
                "updated_at": now,
            }
        ]
    )

    output.to_sql("leave_demand_predictions", engine, if_exists="append", index=False)
    print("Inserted demand forecast.")


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--approval-model", default=os.path.join(os.path.dirname(__file__), "models", "approval_model.pkl"))
    parser.add_argument("--demand-model", default=os.path.join(os.path.dirname(__file__), "models", "demand_model.pkl"))
    parser.add_argument("--approval-only", action="store_true")
    parser.add_argument("--demand-only", action="store_true")
    args = parser.parse_args()

    cfg = MlConfig.from_env()

    if not args.demand_only:
        generate_approval_predictions(cfg, args.approval_model)

    if not args.approval_only:
        generate_demand_prediction(cfg, args.demand_model)


if __name__ == "__main__":
    main()
