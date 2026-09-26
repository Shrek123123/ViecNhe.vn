<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'sql_viecnhe_io_vn');
define('DB_PASS', '83188d1874c778');
define('DB_NAME', 'sql_viecnhe_io_vn');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}