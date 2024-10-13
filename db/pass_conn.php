<?php
// Define your database connection parameters
$host = 'localhost'; // Your database host
$dbName = 'hr2'; // Replace with your actual database name
$username = 'root'; // Replace with your actual database username
$password = ''; // Replace with your actual database password

try {
    // Create a new PDO instance
    $db = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $username, $password);
    // Set the PDO error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optional: Uncomment for debugging
    // echo "Database connected successfully";
} catch (PDOException $e) {
    // Handle connection errors
    echo "Connection failed: " . $e->getMessage();
}
