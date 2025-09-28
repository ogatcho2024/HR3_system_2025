-- Example: Update IT department to set manager_id to employee ID 23
-- (Run this when you have an employee with ID 23)
UPDATE departments 
SET manager_id = 23 
WHERE department_code = 'IT';

-- Example: Check current employees to find valid manager IDs
-- SELECT id, employee_id, department FROM employees WHERE status = 'active';

-- Example: Update all departments to set their managers
-- UPDATE departments SET manager_id = 1 WHERE department_code = 'HR';
-- UPDATE departments SET manager_id = 2 WHERE department_code = 'FIN';

-- Query to view departments with manager information
SELECT 
    d.department_id,
    d.department_name,
    d.department_code,
    d.description,
    d.manager_id,
    e.employee_id as manager_employee_id,
    u.name as manager_name,
    d.status
FROM departments d
LEFT JOIN employees e ON d.manager_id = e.id
LEFT JOIN users u ON e.user_id = u.id
ORDER BY d.department_name;