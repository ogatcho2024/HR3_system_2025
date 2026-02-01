import argparse
import os

import pandas as pd

from config import MlConfig
from utils import fetch_leave_requests, add_derived_features, build_daily_demand


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--output-dir", default=os.path.join(os.path.dirname(__file__), "data"))
    args = parser.parse_args()

    cfg = MlConfig.from_env()
    os.makedirs(args.output_dir, exist_ok=True)

    df = fetch_leave_requests(cfg)
    df = add_derived_features(df)
    df.to_csv(os.path.join(args.output_dir, "leave_requests_dataset.csv"), index=False)

    daily = build_daily_demand(df)
    daily.to_csv(os.path.join(args.output_dir, "daily_leave_demand.csv"), index=False)

    print("Export completed.")
    print(f"leave_requests_dataset.csv rows: {len(df)}")
    print(f"daily_leave_demand.csv rows: {len(daily)}")


if __name__ == "__main__":
    main()
