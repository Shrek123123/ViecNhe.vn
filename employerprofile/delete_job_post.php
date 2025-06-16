<?php
require_once '../config.php'; // Đảm bảo config.php được bao gồm

header('Content-Type: application/json'); // Trả về JSON response

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy job_id và employer_id từ request POST
    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    $employer_id = isset($_POST['employer_id']) ? intval($_POST['employer_id']) : 0;

    // Kiểm tra tính hợp lệ của dữ liệu đầu vào
    if ($job_id <= 0 || $employer_id <= 0) {
        $response['message'] = 'Invalid Job ID or Employer ID.';
        echo json_encode($response);
        exit();
    }

    // Kiểm tra kết nối cơ sở dữ liệu
    if (isset($conn) && $conn->ping()) {
        // Chuẩn bị câu lệnh SQL DELETE.
        // Quan trọng: Chỉ cho phép xóa bài đăng nếu job_id và posted_by_employer_id khớp
        // Điều này đảm bảo rằng nhà tuyển dụng chỉ có thể xóa bài đăng của chính họ.
        $sql = "DELETE FROM job WHERE id = ? AND posted_by_employer_id = ?";
        
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind các tham số: 'ii' vì cả hai đều là số nguyên (integer)
            $stmt->bind_param("ii", $job_id, $employer_id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Job post deleted successfully.';
                } else {
                    $response['message'] = 'No job post found with the given ID for this employer, or no changes were made.';
                }
            } else {
                $response['message'] = 'Database error during deletion: ' . $stmt->error;
                error_log("Delete Job Error: " . $stmt->error); // Log lỗi để debug
            }
            $stmt->close();
        } else {
            $response['message'] = 'Failed to prepare SQL statement: ' . $conn->error;
            error_log("Prepare Statement Error: " . $conn->error); // Log lỗi để debug
        }
    } else {
        $response['message'] = 'Database connection error. Please check your config.php.';
    }
} else {
    $response['message'] = 'Invalid request method. Only POST requests are allowed.';
}

echo json_encode($response);
// Không đóng kết nối $conn ở đây nếu nó được dùng lại trong body.php hoặc các file khác.
// if (isset($conn)) { $conn->close(); }
?>