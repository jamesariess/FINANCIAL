<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$successMessage = "";
$errorMessage   = "";

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM chartofaccount WHERE accountID = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    if (isset($_POST['archive'])) {
        $accountID = $_POST['accountID'];
        $sql = "UPDATE chartofaccount SET Archive = 'YES' WHERE accountID = :accountID";
        $stmt =$pdo->prepare($sql);
        $stmt->bindParam(':accountID', $accountID);

        try {
            $stmt->execute();
            $successMessage = "Account archived successfully.";
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
        }
    }

    if(isset($_POST['update'])){
        $accountID = $_POST['accountID'];
        $accountCode = $_POST['accountCode'];
        $accountName = $_POST['accountName'];
        $accounType = $_POST['accountType'];
        $accountstatus = $_POST['status'];

        if (empty($accountCode) || empty($accountName) || empty($accounType)) {
            $errorMessage = "Please fill in all required fields.";
        } else {
            $sql = "UPDATE chartofaccount 
                       SET accountCode = :accountCode, 
                           accountName = :accountName, 
                           accounType = :accounType, 
                           status = :status 
                     WHERE accountID = :accountID";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':accountCode', $accountCode);
            $stmt->bindParam(':accountName', $accountName);
            $stmt->bindParam(':accounType', $accounType);
            $stmt->bindParam(':status', $accountstatus);
            $stmt->bindParam(':accountID', $accountID);

            try {
                $stmt->execute();
                $successMessage = "Account updated successfully.";
            } catch (PDOException $e) {
                $errorMessage = $e->getMessage();
            }
        }
    }

    if (isset($_POST['submit'])) {
        $accountName = $_POST['accountName'];
        $accounType = $_POST['accountType'];

        if (empty($accountName) || empty($accounType)) {
            $errorMessage = "Please fill in all required fields.";
        } else {
            $prefixMap = [
                'Assets'      => 'AS',
                'Liabilities' => 'LI',
                'Equity'      => 'EQ',
                'Revenue'     => 'RE',
                'Expenses'    => 'EX'
            ];

            if (!array_key_exists($accounType, $prefixMap)) {
                $errorMessage = "Invalid account type.";
            } else {
                $prefix = $prefixMap[$accounType];

                $sql = "SELECT accountCode FROM chartofaccount 
                        WHERE accounType = :accounType 
                        ORDER BY accountCode DESC LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':accounType', $accounType);
                $stmt->execute();
                $lastAccount = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($lastAccount) {
                    $lastNumber = (int)substr($lastAccount['accountCode'], strpos($lastAccount['accountCode'], '-') + 1);
                    $newNumber  = str_pad($lastNumber + 1, 3, "0", STR_PAD_LEFT);
                } else {
                    $newNumber = "001";
                }

                $accountCode = $prefix . "-" . $newNumber;

                $sql = "INSERT INTO chartofaccount (accountCode, accountName, accounType, Archive) 
                        VALUES (:accountCode, :accountName, :accounType, 'NO')";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':accountCode', $accountCode);
                $stmt->bindParam(':accountName', $accountName);
                $stmt->bindParam(':accounType', $accounType);

                try {
                    $stmt->execute();
                    $successMessage = "Account added successfully. Code: $accountCode";
                } catch (PDOException $e) {
                    $errorMessage = $e->getMessage();
                }
            }
        }
    }
}

try {
    $sql = "SELECT * FROM chartofaccount WHERE Archive = 'NO'";
    $stmt = $pdo->query($sql);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = $e->getMessage();
}
?>
