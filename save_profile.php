<?php
session_start();
include_once 'lang/' . $_SESSION['lang'] . '.php'; // Nạp ngôn ngữ từ session
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

$jobseeker_id = $_SESSION['jobseeker_id'] ?? null;
if (!$jobseeker_id) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập']);
    exit;
}

// Lấy dữ liệu
$full_name = trim($_POST['full_name'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$address = trim($_POST['address'] ?? '');
$date_of_birth = isset($_POST['date_of_birth']) && !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
$gender = $_POST['gender'] ?? '';
$marital_status = $_POST['marital_status'] ?? '';
$profile_description = trim($_POST['profile_description'] ?? '');


$validGenders = ['Male', 'Female', 'Other', 'Prefer not to say'];
$validStatus = ['Single', 'Married', 'Other'];


$mysqli = new mysqli("localhost", "root", "", "jobhive");
if ($mysqli->connect_errno) {
    echo json_encode(['success' => false, 'message' => "Lỗi kết nối DB: " . $mysqli->connect_error]);
    exit;
}

// Cập nhật username trong bảng user
$stmtUser = $mysqli->prepare("UPDATE user SET username = ? WHERE id = ?");
$stmtUser->bind_param("si", $full_name, $jobseeker_id);
if (!$stmtUser->execute()) {
    echo json_encode(['success' => false, 'message' => "Lỗi cập nhật tên người dùng: " . $stmtUser->error]);
    exit;
}
$stmtUser->close();

// Kiểm tra bản ghi jobseeker_profile đã tồn tại
$stmtCheck = $mysqli->prepare("SELECT COUNT(*) FROM jobseeker_profile WHERE jobseeker_id = ?");
$stmtCheck->bind_param("i", $jobseeker_id);
$stmtCheck->execute();
$stmtCheck->bind_result($count);
$stmtCheck->fetch();
$stmtCheck->close();

if ($count > 0) {
    $stmt = $mysqli->prepare("UPDATE jobseeker_profile SET 
        profile_description = ?, phone_number = ?, gender = ?, address = ?, date_of_birth = ?, marital_status = ?
        WHERE jobseeker_id = ?");
    $stmt->bind_param("ssssssi", $profile_description, $phone_number, $gender, $address, $date_of_birth, $marital_status, $jobseeker_id);
} else {
    $stmt = $mysqli->prepare("INSERT INTO jobseeker_profile
        (jobseeker_id, profile_description, phone_number, gender, address, date_of_birth, marital_status)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $jobseeker_id, $profile_description, $phone_number, $gender, $address, $date_of_birth, $marital_status);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => $lang['luu_ho_so_thanh_cong']]);
} else {
    echo json_encode(['success' => false, 'message' => $lang['loi_luu_ho_so'] . $stmt->error]);
}

$stmt->close();
$mysqli->close();
