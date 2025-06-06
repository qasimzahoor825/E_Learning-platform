<?php
$conn = new mysqli("localhost", "root", "", "edu_platform");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
