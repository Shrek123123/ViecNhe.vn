<?php
ob_start(static function ($output) {
    json_decode($output);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $output;
    }

    error_log('Invalid avatar upload response: ' . trim(strip_tags($output)));
    http_response_code(500);
    return json_encode(['status' => 'error', 'message' => 'Máy chủ gặp lỗi khi xử lý ảnh đại diện.']);
});
ini_set('display_errors', '0');
error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});
header('Content-Type: application/json; charset=utf-8');

try {
require_once __DIR__ . '/../config.php';
session_start();

$language = $_SESSION['lang'] ?? 'vi';
if (!in_array($language, ['vi', 'en'], true)) {
    $language = 'vi';
}
include '../lang/' . $language . '.php';

if (!isset($_SESSION['jobseeker_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập.']);
    exit;
}

$jobseeker_id = $_SESSION['jobseeker_id'];

if (!isset($_FILES['avatar'])) {
    echo json_encode(['status' => 'error', 'message' => 'Không có file được tải lên.']);
    exit;
}

$file = $_FILES['avatar'];
$uploadErrors = [
    UPLOAD_ERR_INI_SIZE => 'File vượt quá giới hạn upload của máy chủ.',
    UPLOAD_ERR_FORM_SIZE => 'File vượt quá giới hạn cho phép.',
    UPLOAD_ERR_PARTIAL => 'File chỉ được tải lên một phần. Vui lòng thử lại.',
    UPLOAD_ERR_NO_FILE => 'Vui lòng chọn ảnh đại diện.',
    UPLOAD_ERR_NO_TMP_DIR => 'Máy chủ thiếu thư mục tạm để nhận file.',
    UPLOAD_ERR_CANT_WRITE => 'Máy chủ không thể ghi file tải lên.',
    UPLOAD_ERR_EXTENSION => 'Một tiện ích PHP đã dừng việc tải file.',
];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => $uploadErrors[$file['error']] ?? 'File tải lên không hợp lệ.']);
    exit;
}

$allowed_types = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
];
$max_size = 5 * 1024 * 1024;

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$fileInfo = new finfo(FILEINFO_MIME_TYPE);
$file_type = $fileInfo->file($file['tmp_name']);

if (!isset($allowed_types[$extension]) || $file_type !== $allowed_types[$extension]) {
    echo json_encode(['status' => 'error', 'message' => 'Ảnh đại diện chỉ hỗ trợ định dạng JPG, JPEG hoặc PNG.']);
    exit;
}

if ($file['size'] > $max_size) {
    echo json_encode(['status' => 'error', 'message' => $lang['upload_file_too_large'] ?? 'Ảnh không được vượt quá 5MB.']);
    exit;
}

$upload_dir = __DIR__ . '/../uploads';
if (!is_dir($upload_dir) && !mkdir($upload_dir, 0775, true) && !is_dir($upload_dir)) {
    echo json_encode(['status' => 'error', 'message' => 'Không thể tạo thư mục lưu ảnh.']);
    exit;
}

$new_filename = 'avatar_' . (int) $jobseeker_id . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
$destination = $upload_dir . DIRECTORY_SEPARATOR . $new_filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['status' => 'error', 'message' => $lang['upload_error'] ?? 'Không thể lưu ảnh tải lên.']);
    exit;
}

$relativePath = 'uploads/' . $new_filename;
$stmt = $conn->prepare('UPDATE jobseeker_profile SET profile_pic = ? WHERE jobseeker_id = ?');
if (!$stmt) {
    unlink($destination);
    echo json_encode(['status' => 'error', 'message' => 'Không thể chuẩn bị lưu ảnh vào hồ sơ.']);
    exit;
}
$stmt->bind_param('si', $relativePath, $jobseeker_id);
$saved = $stmt->execute();
$hasProfile = $stmt->affected_rows > 0;
$stmt->close();

if ($saved && !$hasProfile) {
    $stmt = $conn->prepare('INSERT INTO jobseeker_profile (jobseeker_id, profile_pic) VALUES (?, ?)');
    if ($stmt) {
        $stmt->bind_param('is', $jobseeker_id, $relativePath);
        $saved = $stmt->execute();
        $stmt->close();
    } else {
        $saved = false;
    }
}

if (!$saved) {
    unlink($destination);
    echo json_encode(['status' => 'error', 'message' => 'Không thể cập nhật ảnh đại diện vào hồ sơ.']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => $lang['upload_success'] ?? 'Ảnh đại diện đã được tải lên thành công!',
    'path' => $relativePath
]);
exit;
} catch (Throwable $error) {
    error_log('Avatar upload failed: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Máy chủ gặp lỗi khi xử lý ảnh đại diện.']);
}