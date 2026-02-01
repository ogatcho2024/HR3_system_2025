import argparse
import os
from datetime import datetime, timedelta

import numpy as np
import pandas as pd


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--output-dir", default=os.path.join(os.path.dirname(__file__), "data"))
    parser.add_argument("--employees", type=int, default=120)
    parser.add_argument("--days", type=int, default=365)
    parser.add_argument("--seed", type=int, default=42)
    args = parser.parse_args()

    rng = np.random.default_rng(args.seed)
    os.makedirs(args.output_dir, exist_ok=True)

    departments = ["IT", "HR", "Finance", "Operations", "Sales", "Marketing"]
    positions = ["Staff", "Senior", "Lead", "Manager"]
    leave_types = ["sick", "vacation", "personal", "emergency", "annual", "unpaid"]

    employees = []
    for i in range(1, args.employees + 1):
        hire_date = datetime.utcnow().date() - timedelta(days=int(rng.integers(60, 3650)))
        employees.append(
            {
                "user_id": i,
                "department": rng.choice(departments),
                "position": rng.choice(positions),
                "hire_date": hire_date,
            }
        )

    employees_df = pd.DataFrame(employees)
    employees_df.to_csv(os.path.join(args.output_dir, "synthetic_employees.csv"), index=False)

    start_date = datetime.utcnow().date() - timedelta(days=args.days)
    leave_rows = []
    leave_id = 1

    for day_offset in range(args.days):
        day = start_date + timedelta(days=day_offset)
        base_requests = rng.poisson(2)
        if day.weekday() in (0, 4):
            base_requests += rng.integers(1, 3)

        for _ in range(base_requests):
            user_id = int(rng.integers(1, args.employees + 1))
            leave_type = rng.choice(leave_types, p=[0.22, 0.28, 0.16, 0.1, 0.2, 0.04])
            duration = int(rng.integers(1, 6))
            end_date = day + timedelta(days=duration - 1)
            status = rng.choice(["approved", "rejected", "pending"], p=[0.7, 0.15, 0.15])
            created_at = day - timedelta(days=int(rng.integers(1, 20)))

            leave_rows.append(
                {
                    "id": leave_id,
                    "user_id": user_id,
                    "leave_type": leave_type,
                    "start_date": day,
                    "end_date": end_date,
                    "days_requested": duration,
                    "reason": "Synthetic data for demo",
                    "status": status,
                    "created_at": created_at,
                }
            )
            leave_id += 1

    leaves_df = pd.DataFrame(leave_rows)
    leaves_df.to_csv(os.path.join(args.output_dir, "synthetic_leave_requests.csv"), index=False)

    print("Synthetic data generated.")
    print(f"Employees: {len(employees_df)}")
    print(f"Leave requests: {len(leaves_df)}")


if __name__ == "__main__":
    main()
