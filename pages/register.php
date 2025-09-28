<?php
// Maglagay ng database connection details dito
$servername = "localhost";
$username = "root"; // Palitan mo ito ng iyong database username
$password = ""; // Palitan mo ito ng iyong database password
$dbname = "financial"; // Palitan mo ito ng pangalan ng iyong database

// Gumawa ng connection
$conn = new mysqli($servername, $username, $password, $dbname);

// I-check ang connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Kunin ang data mula sa form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    // I-hash ang password gamit ang PHP's built-in function
    $hashed_password = password_hash($input_password, PASSWORD_DEFAULT);

    // SQL query para i-insert ang data sa 'users' table
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $input_username, $hashed_password);

    if ($stmt->execute()) {
        echo "Bagong user ay matagumpay na nairehistro!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>