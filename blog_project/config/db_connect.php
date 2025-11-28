<?php
$servername = "localhost";
$username = "root"; // default username for XAMPP
$password = ""; // default password is empty
$database = "blog_db";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// echo "Connected successfully"; // uncomment to test
?>
