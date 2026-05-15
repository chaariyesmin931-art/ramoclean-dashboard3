<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>