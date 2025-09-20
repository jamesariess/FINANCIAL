<?php

$host = 'localhost';
$db   = 'financial';
$username = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE              => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE   => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $username, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

define("API_KEY", "FinancialMalakas");


$headers = getallheaders();
if (!isset($headers['X-API-KEY']) || $headers['X-API-KEY'] !== API_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: Invalid API Key']);
    exit;
}


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://your-frontend-domain.com');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, X-API-KEY');



$departmentMap = [
    "HR2"        => ["HR"],
    "HR3"        => ["HR"],
    "Logistic1"      => ["Maintenance", "Operations"],
    "Logistic2"      => ["Maintenance", "Operations"],
    "Financial" => ["Finance"],
];

// --- MAIN LOGIC ---
if (isset($_GET['action']) && isset($_GET['dept'])) {
    $dept = $_GET['dept'];
    if (!isset($departmentMap[$dept])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid department']);
        exit;
    }

    $departments = $departmentMap[$dept];
    $placeholders = str_repeat('?,', count($departments) - 1) . '?';

  
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($_GET['action'] === 'get_allocations') {
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

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($departments);
                $results = $stmt->fetchAll();
                echo json_encode($results);
            } catch (\PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Database query failed']);
            }
            exit;
        } elseif ($_GET['action'] === 'get_requests') {
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

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute($departments);
                $results = $stmt->fetchAll();
                echo json_encode($results);
            } catch (\PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Database query failed']);
            }
            exit;
        }
    }


    elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'insert_request') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON input']);
            exit;
        }

        $requiredFields = ['requestTitle', 'Amount', 'Requested_by', 'Due', 'Purpuse', 'allocationID'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty(trim($input[$field]))) {
                http_response_code(400);
                echo json_encode(['error' => "Missing or empty field: $field"]);
                exit;
            }
        }

        // --- Extra validation ---
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

       
        $sql = "SELECT c.allocationID 
                FROM costallocation c
                JOIN departmentbudget d ON c.Deptbudget = d.Deptbudget
                WHERE c.allocationID = ? AND d.Name IN ($placeholders)";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge([$input['allocationID']], $departments));
            if (!$stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid allocationID for the specified department']);
                exit;
            }
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Validation query failed']);
            exit;
        }

        // Insert request into the database
        $sql = "INSERT INTO request (requestTitle, Amount, Requested_by, Due, Purpuse, allocationID)
                VALUES (?, ?, ?, ?, ?, ?)";

        try {
            $stmt = $pdo->prepare($sql);
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
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Insert failed']);
        }
        exit;
    }

    // Invalid action
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
    exit;
}
?>
