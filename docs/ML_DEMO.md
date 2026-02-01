# ML Demo (Offline Pipeline, Option 1)

This project uses **offline ML**: models are trained outside the web request, predictions are written to MySQL, and Laravel only **reads** predictions.

## Proposed Schema Assumptions (placeholders)
- Leave requests table: `leave_requests`
  - `id`, `user_id`, `leave_type`, `start_date`, `end_date`, `days_requested`, `status`, `created_at`
- Employees table: `employees`
  - `user_id`, `department`, `position`, `hire_date`

If your schema differs, update the `.env` mappings (see below).

## Configuration (env-driven mapping)
Set these in `.env` (or `.env.example` as reference):

- DB connection (ML only):
  - `ML_DB_HOST`, `ML_DB_PORT`, `ML_DB_DATABASE`, `ML_DB_USERNAME`, `ML_DB_PASSWORD`
- Table names:
  - `ML_LEAVE_REQUESTS_TABLE`, `ML_EMPLOYEES_TABLE`
- Column mappings:
  - `ML_COL_LEAVE_ID`, `ML_COL_LEAVE_USER_ID`, `ML_COL_LEAVE_TYPE`, `ML_COL_LEAVE_START_DATE`,
    `ML_COL_LEAVE_END_DATE`, `ML_COL_LEAVE_DAYS`, `ML_COL_LEAVE_STATUS`, `ML_COL_LEAVE_CREATED_AT`
  - `ML_COL_EMP_USER_ID`, `ML_COL_EMP_DEPARTMENT`, `ML_COL_EMP_POSITION`, `ML_COL_EMP_HIRE_DATE`
- Status mapping:
  - `ML_APPROVAL_POSITIVE_STATUSES` (default: approved)
  - `ML_APPROVAL_NEGATIVE_STATUSES` (default: rejected)
  - `ML_APPROVAL_IGNORE_STATUSES` (default: pending)
  - `ML_APPROVAL_PREDICT_STATUSES` (default: pending)

## Folder Structure (ML Evidence)
```
ml/
  data/
    leave_requests_dataset.csv
    daily_leave_demand.csv
    synthetic_employees.csv
    synthetic_leave_requests.csv
  metrics/
    approval_metrics.json
    demand_metrics.json
  models/
    approval_model.pkl
    demand_model.pkl
  *.py (scripts)
docs/
  ML_DEMO.md
```

## Requirements (Python)
```
pip install -r ml/requirements.txt
```

## Offline Workflow (Option 1)
1) Export dataset
```
python ml/export_dataset.py
```

2) Train approval model (classification)
```
python ml/train_approval_model.py
```

3) Train demand model (regression)
```
python ml/train_demand_model.py
```

4) Generate predictions and insert into MySQL
```
python ml/generate_predictions.py
```

Optional via Artisan (still offline, run on server/CLI only):
```
php artisan ml:generate-predictions
```

## One-Command Pipeline
```
py -3.12 ml/run_pipeline.py
```

You can skip steps:
```
py -3.12 ml/run_pipeline.py --skip-export
py -3.12 ml/run_pipeline.py --skip-train-demand
py -3.12 ml/run_pipeline.py --skip-predict
```

## Laravel Scheduler (Subdomain-friendly)
The scheduler runs offline via CLI, not during web requests. It works on a subdomain as long as the server runs the scheduler.

Example schedule (already added in `routes/console.php`):
- Approval predictions every 5 minutes
- Demand forecast daily at 1:00 AM

To enable it on the server:
- Add a cron entry that runs Laravel scheduler every minute:
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

If you use Windows Task Scheduler on the server:
- Action: `php`
- Arguments: `artisan schedule:run`
- Start in: `C:\path\to\project`

## Evaluation Metrics
- Approval (classification): accuracy + precision/recall + confusion matrix
  - Output: `ml/metrics/approval_metrics.json`
- Demand (regression): MAE (+ RMSE)
  - Output: `ml/metrics/demand_metrics.json`

## Synthetic Data (if real data is limited)
Generate synthetic data for demos:
```
python ml/generate_synthetic_leave_data.py
```

Ethical demo guidance:
- Clearly label synthetic data in your screenshots and oral defense.
- Explain that models are trained on synthetic data for the demo only.
- Show how the same pipeline will run on real data in production.

## 1-Minute Defense Script (Option 1)
“Our capstone uses **offline ML** to avoid real-time training inside the app. We train two simple, explainable models in Python:  
1) a **Logistic Regression** to label leave requests as APPROVE vs REVIEW with a probability score, and  
2) a **Random Forest Regressor** to forecast total leave demand for the next 7 days.  
After training, we save the models to disk and generate predictions in a scheduled offline job. Those predictions are written to MySQL tables, and the Laravel UI only **reads** them.  
This keeps the app fast, safe, and auditable while still delivering ML insights.”
