<?php
$server   = 'localhost:3307';
$username = 'root';
$password = '';
$db       = [
    'collection'=> 'financial',
    'disbursement'=>'financial',
    'general'=> 'financial',
    'budget'=> 'financial',
    'account'=>'financial',
    'ar'=>'financial'
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
