<?php
require_once '../config.php'; // Đảm bảo config.php được bao gồm
session_start(); // Bắt đầu phiên làm việc
include '../lang/' . $_SESSION['lang'] . '.php';


header('Content-Type: application/json'); // Trả về JSON response

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    $posted_by_employer_id = isset($_POST['posted_by_employer_id']) ? intval($_POST['posted_by_employer_id']) : 0;

    $company_name = $_POST['company_name'] ?? '';
    $company_size = $_POST['company_size'] ?? '';
    $job_title = $_POST['job_title'] ?? '';
    $job_description = $_POST['job_description'] ?? '';
    $job_benefit = $_POST['job_benefit'] ?? '';
    $job_requirement = $_POST['job_requirement'] ?? '';
    $job_location = $_POST['job_location'] ?? '';
    $no_employee_needed = $_POST['no_employee_needed'] ?? 0;
    $salary = $_POST['salary'] ?? '';
    $post_duration = $_POST['post_duration'] ?? 0;
    $contact_email = $_POST['contact_email'] ?? '';
    $contact_phone = $_POST['contact_phone'] ?? '';
    $job_type = $_POST['job_type'] ?? '';
    $job_category = $_POST['job_category'] ?? '';
    $required_certification = $_POST['required_certification'] ?? '';
    $job_experience = $_POST['job_experience'] ?? '';
    $company_logo = $_POST['company_logo'] ?? '';

    // Validate essential fields
    if ($job_id <= 0 || $posted_by_employer_id <= 0 || empty($company_name) || empty($job_title) || empty($job_description) || empty($job_location) || $no_employee_needed <= 0 || empty($salary) || $post_duration <= 0 || empty($contact_email) || empty($job_type) || empty($job_category)) {
        $response['message'] = 'Missing or invalid required fields.';
        echo json_encode($response);
        exit();
    }

    if (isset($conn) && $conn->ping()) {
        $sql = "UPDATE job SET
                    company_name = ?,
                    company_size = ?,
                    job_title = ?,
                    job_description = ?,
                    job_benefit = ?,
                    job_requirement = ?,
                    job_location = ?,
                    no_employee_needed = ?,
                    salary = ?,
                    post_duration = ?,
                    contact_email = ?,
                    contact_phone = ?,
                    job_type = ?,
                    job_category = ?,
                    required_certification = ?,
                    job_experience = ?,
                    company_logo = ?,
                    last_updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND posted_by_employer_id = ?"; // Đảm bảo chỉ employer đó mới được sửa bài của mình

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param(
                "sssssssisisssssssii", // Chuỗi định nghĩa kiểu dữ liệu mới
                $company_name,
                $company_size,
                $job_title,
                $job_description,
                $job_benefit,
                $job_requirement,
                $job_location,
                $no_employee_needed, // i
                $salary,
                $post_duration, // i
                $contact_email,
                $contact_phone,
                $job_type,
                $job_category,
                $required_certification,
                $job_experience,
                $company_logo,
                $job_id, // i (cho WHERE)
                $posted_by_employer_id // i (cho WHERE)
            );

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = $lang['job_update_success'];
                } else {
                    $response['message'] = $lang['no_changes_made'] ?? 'No changes made or job not found/authorized.';
                }
            } else {
                $response['message'] = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        }
    } else {
        $response['message'] = 'Database connection error.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
// Không đóng kết nối $conn ở đây nếu nó được dùng lại ở body.php
// $conn->close();
?>