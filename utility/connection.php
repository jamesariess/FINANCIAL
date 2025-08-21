<?php
$server   = "localhost";
$username = "root";
$password = "";
$db       = "financial_collection";

try {
    $pdo = new PDO("mysql:host=$server;dbname=$db", $username, $password);
    // Correct spelling: setAttribute, and correct constant name
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
