<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Test1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Set charset to support Vietnamese
$conn->set_charset("utf8mb4");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>