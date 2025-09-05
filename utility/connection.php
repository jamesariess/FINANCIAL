<?php
$server   = '23.94.230.146';
$username = 'fina_finances';
$password = 'finance';
$db       = [
    'collection'=> 'fina_financial',
    'disbursement'=>'fina_financial',
    'general'=> 'fina_financial',
    'budget'=> 'fina_financial',
    'account'=>'fina_financial',
    'ar'=>'fina_financial'
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
