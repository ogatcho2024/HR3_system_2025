<?php
/**
 * Employee Timesheet API Endpoint (Pure PHP/PDO)
 * 
 * This endpoint retrieves employee timesheets for subdomain system integration
 * with token-based authentication, input validation, and CORS support.
 * 
 * @method GET /api/timesheets.php
 * @authentication Bearer Token
 */

header('Content-Type: application/json');

// CORS Headers
$allowedOrigins = [
    'http://localhost',
    'http://localhost:3000',
    'http://localhost:8080',
    // Add your production/staging domains here
    // 'https://your-subdomain.example.com'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Only GET requests are supported.',
        'error' => 'METHOD_NOT_ALLOWED'
    ]);
    exit;
}

/**
 * Database Configuration
 */
function getDatabaseConnection() {
    $config = [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_DATABASE') ?: 'humanresources3',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4'
    ];

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed',
            'error' => 'DATABASE_ERROR'
        ]);
        exit;
    }
}

/**
 * Extract Bearer token from Authorization header
 */
function getBearerToken() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (preg_match('/Bearer\s+(.+)$/i', $authHeader, $matches)) {
        return $matches[1];
    }
    
    return null;
}

/**
 * Authenticate API token
 */
function authenticateToken($pdo, $token) {
    if (empty($token)) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT 
                at.id,
                at.user_id,
                at.token,
                at.expires_at,
                u.id as user_id,
                u.name as user_name,
                u.email as user_email
            FROM api_tokens at
            INNER JOIN users u ON at.user_id = u.id
            WHERE at.token = :token
            AND (at.expires_at IS NULL OR at.expires_at > NOW())
            LIMIT 1
        ");
        
        $stmt->execute(['token' => $token]);
        $result = $stmt->fetch();
        
        if ($result) {
            // Update last_used_at timestamp
            $updateStmt = $pdo->prepare("
                UPDATE api_tokens 
                SET last_used_at = NOW() 
                WHERE id = :id
            ");
            $updateStmt->execute(['id' => $result['id']]);
            
            return $result;
        }
        
        return null;
    } catch (PDOException $e) {
        error_log('Token authentication error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Validate and sanitize input parameters
 */
function validateInput($params) {
    $errors = [];
    $validated = [];

    // Employee ID validation (optional)
    if (isset($params['employee_id'])) {
        $employeeId = filter_var($params['employee_id'], FILTER_SANITIZE_STRING);
        if (preg_match('/^[A-Z0-9]{1,10}$/i', $employeeId)) {
            $validated['employee_id'] = $employeeId;
        } else {
            $errors[] = 'Invalid employee_id format';
        }
    }

    // User ID validation (optional)
    if (isset($params['user_id'])) {
        $userId = filter_var($params['user_id'], FILTER_VALIDATE_INT);
        if ($userId !== false && $userId > 0) {
            $validated['user_id'] = $userId;
        } else {
            $errors[] = 'Invalid user_id format';
        }
    }

    // Start date validation (optional)
    if (isset($params['start_date'])) {
        $startDate = filter_var($params['start_date'], FILTER_SANITIZE_STRING);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $validated['start_date'] = $startDate;
        } else {
            $errors[] = 'Invalid start_date format. Use YYYY-MM-DD';
        }
    }

    // End date validation (optional)
    if (isset($params['end_date'])) {
        $endDate = filter_var($params['end_date'], FILTER_SANITIZE_STRING);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $validated['end_date'] = $endDate;
        } else {
            $errors[] = 'Invalid end_date format. Use YYYY-MM-DD';
        }
    }

    // Status validation (optional)
    if (isset($params['status'])) {
        $status = filter_var($params['status'], FILTER_SANITIZE_STRING);
        $allowedStatuses = ['draft', 'submitted', 'approved', 'rejected'];
        if (in_array($status, $allowedStatuses)) {
            $validated['status'] = $status;
        } else {
            $errors[] = 'Invalid status. Allowed values: draft, submitted, approved, rejected';
        }
    }

    // Limit validation (optional, default 100, max 1000)
    $limit = isset($params['limit']) ? filter_var($params['limit'], FILTER_VALIDATE_INT) : 100;
    if ($limit !== false && $limit > 0 && $limit <= 1000) {
        $validated['limit'] = $limit;
    } else {
        $validated['limit'] = 100;
    }

    // Offset validation (optional, default 0)
    $offset = isset($params['offset']) ? filter_var($params['offset'], FILTER_VALIDATE_INT) : 0;
    if ($offset !== false && $offset >= 0) {
        $validated['offset'] = $offset;
    } else {
        $validated['offset'] = 0;
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $validated
    ];
}

/**
 * Retrieve employee timesheets
 */
function getTimesheets($pdo, $filters) {
    try {
        // Build WHERE clauses
        $whereClauses = [];
        $params = [];

        if (isset($filters['employee_id'])) {
            $whereClauses[] = "t.employee_id = :employee_id";
            $params['employee_id'] = $filters['employee_id'];
        }

        if (isset($filters['user_id'])) {
            $whereClauses[] = "e.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (isset($filters['start_date'])) {
            $whereClauses[] = "t.date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }

        if (isset($filters['end_date'])) {
            $whereClauses[] = "t.date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }

        if (isset($filters['status'])) {
            $whereClauses[] = "t.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereSQL = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

        // Count total records
        $countSQL = "
            SELECT COUNT(*) as total
            FROM timesheets t
            LEFT JOIN employees e ON t.employee_id = e.id
            $whereSQL
        ";
        
        $countStmt = $pdo->prepare($countSQL);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];

        // Fetch timesheets
        $sql = "
            SELECT 
                t.id,
                t.employee_id,
                t.date,
                t.project_name,
                t.task_description,
                t.hours_worked,
                t.is_overtime,
                t.status,
                t.approved_by,
                t.approved_at,
                t.notes,
                t.created_at,
                t.updated_at,
                e.user_id,
                e.first_name,
                e.last_name,
                e.email,
                e.department,
                u.name as approved_by_name
            FROM timesheets t
            LEFT JOIN employees e ON t.employee_id = e.id
            LEFT JOIN users u ON t.approved_by = u.id
            $whereSQL
            ORDER BY t.date DESC, t.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $params['limit'] = $filters['limit'];
        $params['offset'] = $filters['offset'];

        $stmt = $pdo->prepare($sql);
        
        // Bind parameters with explicit types for LIMIT and OFFSET
        foreach ($params as $key => $value) {
            if ($key === 'limit' || $key === 'offset') {
                $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":$key", $value);
            }
        }
        
        $stmt->execute();
        $timesheets = $stmt->fetchAll();

        // Format the response
        $formattedTimesheets = array_map(function($timesheet) {
            return [
                'id' => (int)$timesheet['id'],
                'employee_id' => $timesheet['employee_id'],
                'user_id' => $timesheet['user_id'] ? (int)$timesheet['user_id'] : null,
                'employee' => [
                    'first_name' => $timesheet['first_name'],
                    'last_name' => $timesheet['last_name'],
                    'full_name' => trim($timesheet['first_name'] . ' ' . $timesheet['last_name']),
                    'email' => $timesheet['email'],
                    'department' => $timesheet['department']
                ],
                'date' => $timesheet['date'],
                'project_name' => $timesheet['project_name'],
                'task_description' => $timesheet['task_description'],
                'hours_worked' => round((int)$timesheet['hours_worked'] / 60, 2), // Convert minutes to hours
                'is_overtime' => (bool)$timesheet['is_overtime'],
                'status' => $timesheet['status'],
                'approved_by' => $timesheet['approved_by'] ? (int)$timesheet['approved_by'] : null,
                'approved_by_name' => $timesheet['approved_by_name'],
                'approved_at' => $timesheet['approved_at'],
                'notes' => $timesheet['notes'],
                'created_at' => $timesheet['created_at'],
                'updated_at' => $timesheet['updated_at']
            ];
        }, $timesheets);

        return [
            'success' => true,
            'data' => $formattedTimesheets,
            'meta' => [
                'total' => (int)$total,
                'count' => count($formattedTimesheets),
                'limit' => $filters['limit'],
                'offset' => $filters['offset'],
                'has_more' => ($filters['offset'] + count($formattedTimesheets)) < $total
            ]
        ];

    } catch (PDOException $e) {
        error_log('Query error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to retrieve timesheets',
            'error' => 'QUERY_ERROR'
        ];
    }
}

// ============================================================================
// Main Execution
// ============================================================================

// Get database connection
$pdo = getDatabaseConnection();

// Authenticate request
$token = getBearerToken();
$authenticatedUser = authenticateToken($pdo, $token);

if (!$authenticatedUser) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Invalid or expired token.',
        'error' => 'UNAUTHORIZED'
    ]);
    exit;
}

// Validate input parameters
$validation = validateInput($_GET);

if (!$validation['valid']) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $validation['errors'],
        'error' => 'VALIDATION_ERROR'
    ]);
    exit;
}

// Retrieve timesheets
$result = getTimesheets($pdo, $validation['data']);

if ($result['success']) {
    http_response_code(200);
    echo json_encode($result);
} else {
    http_response_code(500);
    echo json_encode($result);
}
