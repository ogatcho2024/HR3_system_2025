import os
from dataclasses import dataclass
from dotenv import load_dotenv


def load_env():
    load_dotenv(dotenv_path=os.path.join(os.path.dirname(__file__), "..", ".env"), override=False)


@dataclass
class MlConfig:
    db_host: str
    db_port: str
    db_name: str
    db_user: str
    db_password: str

    leave_requests_table: str
    employees_table: str

    col_leave_id: str
    col_leave_user_id: str
    col_leave_type: str
    col_leave_start_date: str
    col_leave_end_date: str
    col_leave_days: str
    col_leave_status: str
    col_leave_created_at: str

    col_emp_user_id: str
    col_emp_department: str
    col_emp_position: str
    col_emp_hire_date: str

    approval_positive_statuses: list
    approval_negative_statuses: list
    approval_ignore_statuses: list
    approval_predict_statuses: list

    random_state: int

    @staticmethod
    def from_env() -> "MlConfig":
        load_env()

        def env(name, default=None):
            return os.getenv(name, default)

        return MlConfig(
            db_host=env("ML_DB_HOST", env("DB_HOST", "127.0.0.1")),
            db_port=str(env("ML_DB_PORT", env("DB_PORT", "3306"))),
            db_name=env("ML_DB_DATABASE", env("DB_DATABASE", "")),
            db_user=env("ML_DB_USERNAME", env("DB_USERNAME", "")),
            db_password=env("ML_DB_PASSWORD", env("DB_PASSWORD", "")),
            leave_requests_table=env("ML_LEAVE_REQUESTS_TABLE", "leave_requests"),
            employees_table=env("ML_EMPLOYEES_TABLE", "employees"),
            col_leave_id=env("ML_COL_LEAVE_ID", "id"),
            col_leave_user_id=env("ML_COL_LEAVE_USER_ID", "user_id"),
            col_leave_type=env("ML_COL_LEAVE_TYPE", "leave_type"),
            col_leave_start_date=env("ML_COL_LEAVE_START_DATE", "start_date"),
            col_leave_end_date=env("ML_COL_LEAVE_END_DATE", "end_date"),
            col_leave_days=env("ML_COL_LEAVE_DAYS", "days_requested"),
            col_leave_status=env("ML_COL_LEAVE_STATUS", "status"),
            col_leave_created_at=env("ML_COL_LEAVE_CREATED_AT", "created_at"),
            col_emp_user_id=env("ML_COL_EMP_USER_ID", "user_id"),
            col_emp_department=env("ML_COL_EMP_DEPARTMENT", "department"),
            col_emp_position=env("ML_COL_EMP_POSITION", "position"),
            col_emp_hire_date=env("ML_COL_EMP_HIRE_DATE", "hire_date"),
            approval_positive_statuses=_split(env("ML_APPROVAL_POSITIVE_STATUSES", "approved")),
            approval_negative_statuses=_split(env("ML_APPROVAL_NEGATIVE_STATUSES", "rejected")),
            approval_ignore_statuses=_split(env("ML_APPROVAL_IGNORE_STATUSES", "pending")),
            approval_predict_statuses=_split(env("ML_APPROVAL_PREDICT_STATUSES", "pending")),
            random_state=int(env("ML_RANDOM_STATE", "42")),
        )


def _split(value: str) -> list:
    if value is None:
        return []
    return [item.strip() for item in value.split(",") if item.strip()]
