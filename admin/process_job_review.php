<?php
// admin/process_job_review.php
session_start();
require_once '../config.php'; // Điều chỉnh đường dẫn đến config.php

// --- Kiểm tra quyền Admin (quan trọng) ---
if (!isset($_SESSION['usernameadmin'])) {
    // Nếu không phải admin, chuyển hướng hoặc hiển thị lỗi
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'], $_POST['action'])) {
    $jobId = intval($_POST['job_id']);
    $action = $_POST['action'];
    $adminNote = trim((string) ($_POST['admin_note'] ?? ''));

    $newStatus = '';
    if ($action === 'approve') {
        $newStatus = 'approved';
    } elseif ($action === 'reject') {
        // Bắt buộc phải có lý do từ chối
        if ($adminNote === '') {
            echo "<script>alert('Vui lòng nhập lý do từ chối công việc này!'); window.history.back();</script>";
            exit();
        }
        $newStatus = 'rejected';
    } else {
        // Hành động không hợp lệ
        echo "<script>alert('Hành động không hợp lệ!'); window.history.back();</script>";
        exit();
    }

    $stmt = $conn->prepare("UPDATE job SET status = ?, admin_notes = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("ssi", $newStatus, $adminNote, $jobId);
        if ($stmt->execute()) {
            echo "<script>alert('Đã " . ($newStatus === 'approved' ? 'phê duyệt' : 'từ chối') . " tin tuyển dụng thành công!'); window.location.href = 'jobpending.php';</script>";
        } else {
            echo "<script>alert('Lỗi khi cập nhật trạng thái tin tuyển dụng: " . $stmt->error . "'); window.history.back();</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Lỗi khi chuẩn bị truy vấn: " . $conn->error . "'); window.history.back();</script>";
    }
    $conn->close();
    exit();
} else {
    // Yêu cầu không hợp lệ (không phải POST hoặc thiếu dữ liệu)
    echo "<script>alert('Yêu cầu không hợp lệ!'); window.history.back();</script>";
    exit();
}
?>
