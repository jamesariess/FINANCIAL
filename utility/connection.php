<?php
$server   = "localhost";
$username = "root";
$password = "";
$db       = [
    "collection"=> "financial_collection",
    "disbursement"=>"financial_disbursement",
    "general"=> "financial_general_ledger",
    "budget"=> "financial_budget_management",
    "account"=>"financial_account_payable",
    "ar"=>"financial_account_receivable"
];

$pdo=[];

try {
    foreach ($db as $key => $dbname) {
        $pdo[$key] = new PDO("mysql:host=$server;dbname=$dbname", $username, $password);
        $pdo[$key]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>
