import argparse
import json
import os
from datetime import datetime

import numpy as np
import pandas as pd
from joblib import dump
from sklearn.ensemble import RandomForestRegressor
from sklearn.linear_model import LinearRegression
from sklearn.metrics import mean_absolute_error, mean_squared_error
from sklearn.model_selection import train_test_split

from config import MlConfig
from utils import make_demand_features


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--input-csv", default=os.path.join(os.path.dirname(__file__), "data", "daily_leave_demand.csv"))
    parser.add_argument("--output-model", default=os.path.join(os.path.dirname(__file__), "models", "demand_model.pkl"))
    parser.add_argument("--metrics-output", default=os.path.join(os.path.dirname(__file__), "metrics", "demand_metrics.json"))
    parser.add_argument("--model-type", choices=["linear", "rf"], default="rf")
    args = parser.parse_args()

    cfg = MlConfig.from_env()
    os.makedirs(os.path.dirname(args.output_model), exist_ok=True)
    os.makedirs(os.path.dirname(args.metrics_output), exist_ok=True)

    daily = pd.read_csv(args.input_csv)
    daily["date"] = pd.to_datetime(daily["date"])

    daily = make_demand_features(daily)
    daily = daily.dropna()

    feature_cols = ["dow", "month", "is_weekend", "lag_1", "lag_7", "rolling_7"]
    X = daily[feature_cols]
    y = daily["leave_count"]

    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=cfg.random_state
    )

    if args.model_type == "linear":
        model = LinearRegression()
        model_type = "LinearRegression"
    else:
        model = RandomForestRegressor(
            n_estimators=200, random_state=cfg.random_state, max_depth=8
        )
        model_type = "RandomForestRegressor"

    model.fit(X_train, y_train)
    y_pred = model.predict(X_test)

    mae = mean_absolute_error(y_test, y_pred)
    rmse = mean_squared_error(y_test, y_pred, squared=False)

    metrics = {
        "model_type": model_type,
        "mae": float(mae),
        "rmse": float(rmse),
        "train_rows": int(len(X_train)),
        "test_rows": int(len(X_test)),
        "generated_at": datetime.utcnow().isoformat() + "Z",
    }

    with open(args.metrics_output, "w", encoding="utf-8") as f:
        json.dump(metrics, f, indent=2)

    dump(
        {
            "model": model,
            "feature_cols": feature_cols,
            "model_version": datetime.utcnow().strftime("%Y%m%d_%H%M%S"),
        },
        args.output_model,
    )

    print("Demand model training complete.")
    print(json.dumps(metrics, indent=2))


if __name__ == "__main__":
    main()
