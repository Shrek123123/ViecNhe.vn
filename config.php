<?php
$conn = new mysqli("localhost", "root", "");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS jobhive");
if (!$conn->select_db("jobhive")) {
    die("Unable to select database jobhive: " . $conn->error);
}