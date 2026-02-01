import argparse
import os
import subprocess
import sys


def run(cmd, cwd):
    print(f"\n>> {cmd}")
    result = subprocess.run(cmd, cwd=cwd, shell=True)
    if result.returncode != 0:
        raise SystemExit(result.returncode)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--python", default=None, help="Python executable to use (e.g. py -3.12)")
    parser.add_argument("--skip-export", action="store_true")
    parser.add_argument("--skip-train-approval", action="store_true")
    parser.add_argument("--skip-train-demand", action="store_true")
    parser.add_argument("--skip-predict", action="store_true")
    args = parser.parse_args()

    repo_root = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
    if args.python:
        py = args.python
    else:
        if sys.platform.startswith("win"):
            py = "py -3.12"
        else:
            py = "python3"

    def script_path(name):
        path = os.path.join("ml", name)
        if not sys.platform.startswith("win"):
            path = path.replace("\\", "/")
        return path

    if not args.skip_export:
        run(f"{py} {script_path('export_dataset.py')}", repo_root)
    if not args.skip_train_approval:
        run(f"{py} {script_path('train_approval_model.py')}", repo_root)
    if not args.skip_train_demand:
        run(f"{py} {script_path('train_demand_model.py')}", repo_root)
    if not args.skip_predict:
        run(f"{py} {script_path('generate_predictions.py')}", repo_root)

    print("\nPipeline completed.")


if __name__ == "__main__":
    main()
