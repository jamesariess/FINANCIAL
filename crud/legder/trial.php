<?php

include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');

try {
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$accountNamesStmt = $pdo->query("SELECT DISTINCT c.accountName 
                                    FROM chartofaccount c
                                    JOIN details d ON c.accountID = d.accountID");
    $accountNames = $accountNamesStmt->fetchAll(PDO::FETCH_COLUMN);

    
    $selectedAccountName = isset($_POST['accountName']) ? $_POST['accountName'] : 'all';

    // SQL query with ORDER BY to group entries by accountID
    $sql = "SELECT 
        jd.entriesID,
        jd.journalID,
        c.accountName,
        c.accountID,
        jd.debit,
        jd.credit,
        c.accounType,
        e.date
    FROM details jd
    JOIN chartofaccount c ON jd.accountID = c.accountID
    JOIN entries e ON jd.journalID   = e.journalID
    ORDER BY c.accountID, jd.entriesID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $journalDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter journal details based on selected account name
    if ($selectedAccountName !== 'all') {
        $journalDetails = array_filter($journalDetails, function ($row) use ($selectedAccountName) {
            return strtolower(trim($row['accountName'])) === strtolower(trim($selectedAccountName));
        });
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $journalDetails = []; // Ensure $journalDetails is defined even on error
}
?>