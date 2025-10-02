<?php

include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');


function getBankBalance($pdo) {
    $stmt = $pdo->query("SELECT SUM(Amount - UsedAmount) AS balance FROM funds WHERE Archive='NO' AND fundType=!'PettyCash'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['balance'] ?? 0;
}


function getOwnerDeposit($pdo) {
    $sql = "
        SELECT 
            COALESCE(SUM(jd.credit),0) - COALESCE(SUM(jd.debit),0) AS balance
        FROM details jd
        JOIN chartofaccount c ON jd.accountID = c.accountID
        JOIN entries e ON jd.journalID = e.journalID
        WHERE c.accountName = 'Owner Capital'
    ";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['balance'] ?? 0;
}



function getCashOnHand($pdo, $selectedPeriodId = null) {
    try {      
        $sql = "
            SELECT 
                SUM(jd.debit) - SUM(jd.credit) AS balance
            FROM details jd
            JOIN chartofaccount c ON jd.accountID = c.accountID
            JOIN entries e ON jd.journalID = e.journalID
            WHERE c.accountName = 'Cash On Hand'
        ";
        if ($selectedPeriodId) {
            $sql .= " AND e.periodID = :period_id";
        }
        $stmt = $pdo->prepare($sql);
        if ($selectedPeriodId) {
            $stmt->bindValue(':period_id', $selectedPeriodId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC); 
        return $result && $result['balance'] !== null ? $result['balance'] : 0;
    } catch (Exception $e) {
        error_log("Error fetching Cash On Hand: " . $e->getMessage());
        return 0;
    }
}


    function getOrCreateAccount($pdo, $accountName, $accountType) {
        $checkSql = "SELECT accountID FROM chartofaccount 
                     WHERE accountName = :name AND Archive = 'NO' LIMIT 1";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([':name' => $accountName]);
        $account = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($account) return $account['accountID'];

        $lastCodeSql = "SELECT accountCode FROM chartofaccount 
                        WHERE accounType = :type ORDER BY accountCode DESC LIMIT 1";
        $lastCodeStmt = $pdo->prepare($lastCodeSql);
        $lastCodeStmt->execute([':type' => $accountType]);
        $lastCode = $lastCodeStmt->fetch(PDO::FETCH_ASSOC);

        $newCodeNumber = $lastCode ? (int)substr($lastCode['accountCode'], 3) + 1 : 1;
        $prefix = strtoupper(substr($accountType, 0, 2));
        $newAccountCode = $prefix . '-' . str_pad($newCodeNumber, 3, '0', STR_PAD_LEFT);

        $insertSql = "INSERT INTO chartofaccount (accountCode, accountName, accounType, Archive, status) 
                      VALUES (:code, :name, :type, 'NO', 'Active')";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            ':code' => $newAccountCode,
            ':name' => $accountName,
            ':type' => $accountType
        ]);
        return $pdo->lastInsertId();
    }

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $bankNamesStmt = $pdo->query("SELECT bankName FROM bank WHERE archive='NO' AND status='Active'");
    $bankNames = $bankNamesStmt->fetchAll(PDO::FETCH_COLUMN);
   
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $bankNames = [];
}

if (isset($_POST['saveDeposit'])) {
    $depositType   = $_POST['depositType']; 
    $bankID        = $_POST['bankID'] ?? null;
    $newBankName   = trim($_POST['newBankName'] ?? '');
    $amount        = floatval($_POST['amount']);
    $reference     = $_POST['reference'] ?: "N/A";
    $date          = date("Y-m-d H:i:s");


    if ($newBankName !== "" && $depositType === "Bank") {
        $normalizedBank = strtoupper($newBankName);
        $check = $pdo->prepare("SELECT bankID FROM bank 
                                WHERE UPPER(bankName) = :bankName AND Archive = 'NO'");
        $check->execute([':bankName' => $normalizedBank]);
        $existingBank = $check->fetch(PDO::FETCH_ASSOC);

        if ($existingBank) {
            echo "<script>alert('Bank name already exists!');</script>";
            $bankID = $existingBank['bankID'];
        } else {
            $cleanBank = ucwords(strtolower($newBankName));
            $stmt = $pdo->prepare("INSERT INTO bank (bankName, status, Archive) 
                                   VALUES (:bankName, 'Active', 'NO')");
            $stmt->execute([':bankName' => $cleanBank]);
            $bankID = $pdo->lastInsertId();
        }
    }



    $entrySql = "INSERT INTO entries (date, description, referenceType, createdBy, Archive)
                 VALUES (:date, :description, :ref, :createdBy, 'NO')";
    $entryStmt = $pdo->prepare($entrySql);
    $entryStmt->execute([
        ':date' => $date,
        ':description' => "Deposit ($depositType)",
        ':ref' => $depositType,
        ':createdBy' => 'System'
    ]);
    $journalID = $pdo->lastInsertId();

    $detailSql = "INSERT INTO details (journalID, accountID, debit, credit, Archive)
                  VALUES (:journalID, :accountID, :debit, :credit, 'NO')";
    $detailStmt = $pdo->prepare($detailSql);

  
    if ($depositType === "Bank" && $bankID) {
   
        $stmt = $pdo->prepare("INSERT INTO funds (bankID, Amount, UsedAmount, reference,Notes, Archive, Date, fundType) 
                               VALUES (:bankID, :amount, 0, :reference,:typet, 'NO', :date, 'Bank')");
        $stmt->execute([
            ':bankID' => $bankID,
            ':amount' => $amount,
            'typet' =>$depositType,
            ':reference' => $reference,
            ':date' => $date
        ]);

        
        $bankAccountID = getOrCreateAccount($pdo, "Cash On Bank", "Assets");
        $detailStmt->execute([
            ':journalID' => $journalID,
            ':accountID' => $bankAccountID,
            ':debit' => $amount,
            ':credit' => 0
        ]);

        $cashOnHandID = getOrCreateAccount($pdo, "Cash On Hand", "Assets");
        $detailStmt->execute([
            ':journalID' => $journalID,
            ':accountID' => $cashOnHandID,
            ':debit' => 0,
            ':credit' => $amount
        ]);

    } elseif ($depositType === "Owner" && $bankID) {
     
        $stmt = $pdo->prepare("INSERT INTO funds (bankID, Amount, UsedAmount, reference,Notes, Archive, Date, fundType) 
                               VALUES (:bankID, :amount, 0, :reference,:typet, 'NO', :date, 'Owner')");
        $stmt->execute([
            ':bankID' => $bankID,
            ':amount' => $amount,
            'typet' =>$depositType,
            ':reference' => "Owner Deposit",
            ':date' => $date
        ]);

     
        $bankAccountID = getOrCreateAccount($pdo, "Cash On Bank", "Assets");
        $detailStmt->execute([
            ':journalID' => $journalID,
            ':accountID' => $bankAccountID,
            ':debit' => $amount,
            ':credit' => 0
        ]);

        $equityAccountID = getOrCreateAccount($pdo, "Owner Capital", "Equity");
        $detailStmt->execute([
            ':journalID' => $journalID,
            ':accountID' => $equityAccountID,
            ':debit' => 0,
            ':credit' => $amount
        ]);

    } elseif ($depositType === "Petty Cash") {
   
        $stmt = $pdo->prepare("INSERT INTO funds (bankID, Amount, UsedAmount, reference,Notes, Archive, Date, fundType) 
                               VALUES (NULL, :amount, 0, :reference,:typet, 'NO', :date, 'PettyCash')");
        $stmt->execute([
            ':amount' => $amount,
            ':reference' => "Add Money to Petty Cash",
            'typet' =>$depositType,
            ':date' => $date
        ]);

        
        $cashOnHandID = getOrCreateAccount($pdo, "Cash On Hand", "Assets");
        $detailStmt->execute([
            ':journalID' => $journalID,
            ':accountID' => $cashOnHandID,
            ':debit' => $amount,
            ':credit' => 0
        ]);

        $capitalID = getOrCreateAccount($pdo, "Petty Cash Funding", "Equity");
        $detailStmt->execute([
            ':journalID' => $journalID,
            ':accountID' => $capitalID,
            ':debit' => 0,
            ':credit' => $amount
        ]);
    }
}
function getBanksWithBalance($pdo) {
    $sql = "
      SELECT b.bankID, b.bankName, 
             SUM(f.Amount) - SUM(COALESCE(f.UsedAmount,0)) AS useAmount
      FROM funds f
      JOIN bank b ON f.bankID = b.bankID
      WHERE f.Archive = 'NO'
      GROUP BY b.bankID, b.bankName
      HAVING useAmount > 0
      ORDER BY b.bankName
    ";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
$banksForWithdraw = getBanksWithBalance($pdo);

function getthebank($pdo){
    $sql = "
    SELECT b.bankName,
           SUM(f.Amount - COALESCE(f.UsedAmount,0)) AS availableBalance
    FROM funds f
    JOIN bank b ON f.bankID = b.bankID
    WHERE f.Archive = 'NO'
    GROUP BY b.bankID, b.bankName
    ORDER BY b.bankName ASC
";
 return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

}
$bankBalances = getthebank($pdo);

if (isset($_POST['saveWithdraw'])) {
    $bankID = $_POST['fundID'] ?? null; 
    $amount = floatval($_POST['amount'] ?? 0);
    $date = date("Y-m-d H:i:s");

    if (!$bankID || $amount <= 0) {
        echo "<script>alert('Invalid bank or amount.');</script>";
    } else {
     
        $stmt = $pdo->prepare("
            SELECT fundsID, Amount, COALESCE(UsedAmount,0) AS UsedAmount
            FROM funds
            WHERE bankID = ? AND Archive = 'NO'
            ORDER BY Date ASC, fundsID ASC
        ");
        $stmt->execute([$bankID]);
        $fundRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalAvailable = 0;
        foreach ($fundRows as $f) {
            $totalAvailable += ($f['Amount'] - $f['UsedAmount']);
        }

        if ($amount > $totalAvailable) {
            echo "<script>alert('Insufficient total balance. Available ₱" . number_format($totalAvailable,2) . "');</script>";
        } else {
            $remaining = $amount;

            foreach ($fundRows as $row) {
                $available = $row['Amount'] - $row['UsedAmount'];
                if ($available <= 0) continue;

                $deduct = min($available, $remaining);
                $newUsed = $row['UsedAmount'] + $deduct;

                $updateStmt = $pdo->prepare("UPDATE funds SET UsedAmount = ? WHERE fundsID = ?");
                $updateStmt->execute([$newUsed, $row['fundsID']]);

                $remaining -= $deduct;
                if ($remaining <= 0) break;
            }

 
            $entrySql = "INSERT INTO entries (date, description, referenceType, createdBy, Archive)
                         VALUES (:date, :description, :ref, :createdBy, 'NO')";
            $entryStmt = $pdo->prepare($entrySql);
            $entryStmt->execute([
                ':date' => $date,
                ':description' => "Withdrawal from Bank",
                ':ref' => 'Withdrawal',
                ':createdBy' => 'System'
            ]);
            $journalID = $pdo->lastInsertId();

            $detailSql = "INSERT INTO details (journalID, accountID, debit, credit, Archive)
                          VALUES (:journalID, :accountID, :debit, :credit, 'NO')";
            $detailStmt = $pdo->prepare($detailSql);


            $bankAccountID = getOrCreateAccount($pdo, "Cash On Bank", "Assets");
            $detailStmt->execute([
                ':journalID' => $journalID,
                ':accountID' => $bankAccountID,
                ':debit' => 0,
                ':credit' => $amount
            ]);

            $ownerEquityID = getOrCreateAccount($pdo, "Owner Capital", "Equity");
            $detailStmt->execute([
                ':journalID' => $journalID,
                ':accountID' => $ownerEquityID,
                ':debit' => $amount,
                ':credit' => 0
            ]);

            echo "<script>alert('Withdrawal successful!');</script>";
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['editBank'])) {
            $id = $_POST['bankId'] ?? null;
            $status = $_POST['status'] ?? null;

            if ($id && $status) {
                $stmt = $pdo->prepare("UPDATE bank SET status = :status WHERE bankID = :id");
                $stmt->execute([
                    ':status' => $status,
                    ':id' => $id
                ]);
                echo "Bank status updated successfully!";
            } else {
                echo "Missing bankId or status.";
            }
        }

        if (isset($_POST['archiveBank'])) {
            $id = $_POST['bankId'] ?? null;

            if ($id) {
                $stmt = $pdo->prepare("UPDATE bank SET archive = 'YES' WHERE bankID = :id");
                $stmt->execute([':id' => $id]);
                echo "Bank archived successfully!";
            } else {
                echo "Missing bankId.";
            }
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   

   $sql = "
    SELECT 
        f.fundsID, 
        f.Amount, 
        f.UsedAmount, 
        f.Date, 
        f.reference, 
        f.Notes, 
        f.Archive,
        b.bankName
    FROM funds f
    LEFT JOIN bank b ON f.bankID = b.bankID
    WHERE f.Archive = 'NO'
    ORDER BY f.Date DESC, b.bankName ASC
";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $fundDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $fundDetails = [];
}

try{
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   
    $sql = "SELECT * FROM bank WHERE Archive='NO'  ORDER BY bankName ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $banks = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $banks = [];
}

?>