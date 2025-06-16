<?php
$conn = new mysqli("localhost", "root", "", "jobhive");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}