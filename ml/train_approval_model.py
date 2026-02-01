import argparse
import json
import os
from datetime import datetime

import numpy as np
import pandas as pd
from joblib import dump
from sklearn.compose import ColumnTransformer
from sklearn.impute import SimpleImputer
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import accuracy_score, classification_report, confusion_matrix
from sklearn.model_selection import train_test_split
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import OneHotEncoder

from config import MlConfig


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--input-csv", default=os.path.join(os.path.dirname(__file__), "data", "leave_requests_dataset.csv"))
    parser.add_argument("--output-model", default=os.path.join(os.path.dirname(__file__), "models", "approval_model.pkl"))
    parser.add_argument("--metrics-output", default=os.path.join(os.path.dirname(__file__), "metrics", "approval_metrics.json"))
    args = parser.parse_args()

    cfg = MlConfig.from_env()
    os.makedirs(os.path.dirname(args.output_model), exist_ok=True)
    os.makedirs(os.path.dirname(args.metrics_output), exist_ok=True)

    df = pd.read_csv(args.input_csv)

    df["status"] = df["status"].astype(str).str.lower()
    df = df[~df["status"].isin(cfg.approval_ignore_statuses)]
    df = df[df["status"].isin(cfg.approval_positive_statuses + cfg.approval_negative_statuses)]

    df["label"] = np.where(df["status"].isin(cfg.approval_positive_statuses), 1, 0)

    feature_cols = [
        "leave_type",
        "days_requested",
        "start_month",
        "start_dow",
        "lead_time_days",
        "duration_days",
        "department",
        "position",
        "employee_tenure_days",
    ]

    X = df[feature_cols].copy()
    y = df["label"].astype(int)

    categorical_cols = ["leave_type", "department", "position"]
    numeric_cols = [c for c in feature_cols if c not in categorical_cols]

    numeric_transformer = Pipeline(
        steps=[
            ("imputer", SimpleImputer(strategy="median")),
        ]
    )
    categorical_transformer = Pipeline(
        steps=[
            ("imputer", SimpleImputer(strategy="most_frequent")),
            ("onehot", OneHotEncoder(handle_unknown="ignore")),
        ]
    )

    preprocessor = ColumnTransformer(
        transformers=[
            ("num", numeric_transformer, numeric_cols),
            ("cat", categorical_transformer, categorical_cols),
        ]
    )

    model = LogisticRegression(max_iter=500, class_weight="balanced", random_state=cfg.random_state)

    clf = Pipeline(steps=[("preprocessor", preprocessor), ("model", model)])

    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=cfg.random_state, stratify=y
    )

    clf.fit(X_train, y_train)
    y_pred = clf.predict(X_test)

    metrics = {
        "model_type": "LogisticRegression",
        "accuracy": float(accuracy_score(y_test, y_pred)),
        "classification_report": classification_report(y_test, y_pred, output_dict=True),
        "confusion_matrix": confusion_matrix(y_test, y_pred).tolist(),
        "train_rows": int(len(X_train)),
        "test_rows": int(len(X_test)),
        "generated_at": datetime.utcnow().isoformat() + "Z",
    }

    with open(args.metrics_output, "w", encoding="utf-8") as f:
        json.dump(metrics, f, indent=2)

    dump(
        {
            "model": clf,
            "feature_cols": feature_cols,
            "model_version": datetime.utcnow().strftime("%Y%m%d_%H%M%S"),
        },
        args.output_model,
    )

    print("Approval model training complete.")
    print(json.dumps(metrics, indent=2))


if __name__ == "__main__":
    main()
