<?php
$servername = "localhost"; // Or your database server IP
$username = "root";        // Your MySQL username
$password = "";            // Your MySQL password
$dbname = "db_crime"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
