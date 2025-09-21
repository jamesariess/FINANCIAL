<?php

define('API_KEY', 'FinancialMalakas');                   
define('LOG_FILE', __DIR__ . '/api_access.log');         
define('RATE_LIMIT', 100);                               
define('RATE_WINDOW', 60);                               
define('ALLOWED_ORIGIN', 'https://finance.slatefreight-ph.com'); 


$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

$dbHost = 'localhost';
$dbName = 'fina_financial';
$dbUser = 'fina_finances';
$dbPass = '7rO-@mwup07Io^g0'; 

//  $dbHost = 'localhost';
// $dbName = 'financial';
// $dbUser = 'root';
// $dbPass = ''; 
$allowedOrigins = [
    'https://finance.slatefreight-ph.com',
    'http://localhost',
    'http://127.0.0.1'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-KEY');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}


function logRequest($status, $message = '')
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $time = date('Y-m-d H:i:s');
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $line = "[$time] IP:$ip STATUS:$status URI:$uri MSG:$message" . PHP_EOL;

    @file_put_contents(LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}


function get_header_value($name)
{
    $h = getallheaders() ?: [];
    $nameLower = strtolower($name);
    foreach ($h as $k => $v) {
        if (strtolower($k) === $nameLower) return $v;
    }
    return null;
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!isset($_SESSION['rate'])) {
    $_SESSION['rate'] = [];
}
if (!isset($_SESSION['rate'][$ip])) {
    $_SESSION['rate'][$ip] = ['count' => 0, 'time' => time()];
}
$elapsed = time() - $_SESSION['rate'][$ip]['time'];
if ($elapsed > RATE_WINDOW) {
  
    $_SESSION['rate'][$ip] = ['count' => 0, 'time' => time()];
}
$_SESSION['rate'][$ip]['count']++;
if ($_SESSION['rate'][$ip]['count'] > RATE_LIMIT) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests, slow down']);
    logRequest(429, 'Rate limit exceeded');
    exit;
}


$providedKey = get_header_value('X-API-KEY');
if (!isset($providedKey) || $providedKey !== API_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    logRequest(401, 'Invalid API key');
    exit;
}


$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    logRequest(500, "DB error: " . $e->getMessage());
    exit;
}


$departmentMap = [
    'HR2'      => ['HR'],
    'HR3'      => ['HR'],
    'Logistic1'=> ['Operations'],
    'Logistic2'=> ['Maintenance'],
    'Financial'=> ['Finance'],
];


$action = $_GET['action'] ?? null;
$dept   = $_GET['dept'] ?? null;

if (!$action || !$dept) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing action or dept parameter']);
    exit;
}


if (!array_key_exists($dept, $departmentMap)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid department']);
    exit;
}
$departments = $departmentMap[$dept];
$placeholders = str_repeat('?,', count($departments) - 1) . '?';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($action === 'get_allocations') {
            $sql = "SELECT 
                        ch.accountName AS title,
                        c.allocationID,
                        d.Name AS department
                    FROM costallocation c
                    JOIN chartofaccount ch ON c.accountID = ch.accountID
                    JOIN departmentbudget d ON c.Deptbudget = d.Deptbudget
                    WHERE d.Name IN ($placeholders)
                      AND c.Status = 'Activate'
                      AND ch.Archive = 'NO'
                      AND ch.status = 'Active'
                      AND ch.accounType IN ('Liabilities','Expenses')";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($departments);
            $results = $stmt->fetchAll();
            echo json_encode($results);
            exit;
        } elseif ($action === 'get_requests') {
            $sql = "SELECT
                        r.requestTitle AS title,
                        r.Amount AS ApprovedAmount,
                        r.Requested_by,
                        r.Due,
                        r.status,
                        r.Remarks,
                        r.Purpuse
                    FROM request r 
                    JOIN costallocation c ON r.allocationID = c.allocationID
                    JOIN chartofaccount ch ON c.accountID = ch.accountID
                    JOIN departmentbudget d ON c.Deptbudget = d.Deptbudget
                    WHERE d.Name IN ($placeholders)
                      AND c.Status = 'Activate'
                      AND ch.Archive = 'NO'
                      AND ch.status = 'Active'";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($departments);
            $results = $stmt->fetchAll();
            echo json_encode($results);
            exit;
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            exit;
        }
    }

 
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'insert_request') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON input']);
            exit;
        }

        $requiredFields = ['requestTitle', 'Amount', 'Requested_by', 'Due', 'Purpuse', 'allocationID'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || trim((string)$input[$field]) === '') {
                http_response_code(400);
                echo json_encode(['error' => "Missing or empty field: $field"]);
                exit;
            }
        }

        
        if (!is_numeric($input['Amount']) || $input['Amount'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Amount must be a positive number']);
            exit;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['Due'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Due must be in YYYY-MM-DD format']);
            exit;
        }

       
        $validationSql = "SELECT c.allocationID
                          FROM costallocation c
                          JOIN departmentbudget d ON c.Deptbudget = d.Deptbudget
                          WHERE c.allocationID = ? AND d.Name IN ($placeholders)";
        $stmt = $pdo->prepare($validationSql);
        $stmt->execute(array_merge([$input['allocationID']], $departments));
        if (!$stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid allocationID for the specified department']);
            exit;
        }


        $insertSql = "INSERT INTO request (requestTitle, Amount, Requested_by, Due, Purpuse, allocationID)
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($insertSql);
        $stmt->execute([
            $input['requestTitle'],
            $input['Amount'],
            $input['Requested_by'],
            $input['Due'],
            $input['Purpuse'],
            $input['allocationID']
        ]);

        http_response_code(201);
        echo json_encode(['message' => 'Request inserted successfully']);
        exit;
    }

    
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
} catch (PDOException $e) {
    
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    logRequest(500, 'PDOException: ' . $e->getMessage());
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    logRequest(500, 'Exception: ' . $e->getMessage());
    exit;
}
?>