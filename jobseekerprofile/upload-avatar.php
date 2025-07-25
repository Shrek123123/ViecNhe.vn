<?php
require_once '../config.php';
session_start();
include '../lang/' . $_SESSION['lang'] . '.php';
header('Content-Type: application/json');

if (!isset($_SESSION['jobseeker_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập.']);
    exit;
}

$jobseeker_id = $_SESSION['jobseeker_id'];

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Không có file hoặc file lỗi.']);
    exit;
}

$file = $_FILES['avatar'];
$allowed_types = ['image/jpeg', 'image/png', 'image/svg+xml'];
$max_size = 5 * 1024 * 1024;

$file_type = mime_content_type($file['tmp_name']);
$file_size = $file['size'];

if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['status' => 'error', 'message' => $lang['upload_invalid_type']]);
    exit;
}

if ($file_size > $max_size) {
    echo json_encode(['status' => 'error', 'message' => $lang['upload_file_too_large']]);
    exit;
}

$upload_dir = '../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_filename = 'avatar_' . $jobseeker_id . '_' . time() . '.' . $extension;
$destination = $upload_dir . $new_filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['status' => 'error', 'message' => $lang['upload_error']]);
    exit;
}

// Xử lý INSERT hoặc UPDATE
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM jobseeker_profile WHERE jobseeker_id = ?");
$stmt->bind_param("i", $jobseeker_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total = $row['total'];
$stmt->close();

if ($total > 0) {
    $stmt = $conn->prepare("UPDATE jobseeker_profile SET profile_pic = ? WHERE jobseeker_id = ?");
    $stmt->bind_param("si", $destination, $jobseeker_id);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $conn->prepare("INSERT INTO jobseeker_profile (jobseeker_id, profile_pic) VALUES (?, ?)");
    $stmt->bind_param("is", $jobseeker_id, $destination);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

echo json_encode([
    'status' => 'success',
    'message' => $lang['upload_success'],
    'path' => $destination
]);
exit;