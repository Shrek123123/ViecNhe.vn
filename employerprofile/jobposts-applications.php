<?php
require_once '../config.php'; // Đảm bảo config.php được bao gồm và thiết lập kết nối $conn

// Lấy job_id từ request Ajax
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
require_once '../config.php';


// Phần code xử lý hiển thị danh sách ứng tuyển vẫn giữ nguyên
// ...

// Thêm đoạn code này vào cuối file (hoặc tạo một file riêng, ví dụ: update_viewed_status.php)
if (isset($_POST['action']) && $_POST['action'] === 'updateViewedStatus' && isset($_POST['application_id'])) {
    $application_id = intval($_POST['application_id']);

    if (isset($conn) && $conn->ping()) {
        $sql = "UPDATE application SET status = 'viewed' WHERE id = ? AND status = 'applied'";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $application_id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => true]); // Trả về JSON để báo thành công
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Database connection error']);
    }
    exit; // Dừng việc thực thi file sau khi xử lý yêu cầu
}
?>

<div style="padding: 10px;">
    <?php if ($job_id > 0): ?>
        <h4 style="margin-bottom: 15px; color: #b23b3b;">
            Applications for Job ID: <strong><?php echo htmlspecialchars($job_id); ?></strong>
        </h4>

        <div style="display: flex; flex-direction: column; gap: 15px;">
            <?php
            // Kiểm tra kết nối database
            if (isset($conn) && $conn->ping()) {
                // Truy vấn để lấy các ứng dụng cho job_id cụ thể
                // Lấy thông tin từ bảng 'application'
                $sql = "SELECT id, fullname, phonenumber, email, status, created_at, cv_file
                        FROM application
                        WHERE job_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $job_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    echo '<div style="color: #888; text-align: center; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">No applications found for this job post yet.</div>';
                } else {
                    while ($row = $result->fetch_assoc()):
                        $application_id = htmlspecialchars($row['id']);
                        $fullname = htmlspecialchars($row['fullname']);
                        $phonenumber = htmlspecialchars($row['phonenumber']);
                        $email = htmlspecialchars($row['email']);
                        $status = htmlspecialchars($row['status']);
                        $created_at = new DateTime($row['created_at']);
                        $formatted_created_at = $created_at->format('M d, Y H:i'); // Format ngày tháng
                        $cv_file = !empty($row['cv_file']) ? htmlspecialchars($row['cv_file']) : '';

                        // Định nghĩa màu cho trạng thái
                        $status_color = '';
                        switch ($status) {
                            case 'applied':
                                $status_color = '#007bff'; // Blue
                                break;
                            case 'interviewed':
                                $status_color = '#ffc107'; // Yellow
                                break;
                            case 'hired':
                                $status_color = '#28a745'; // Green
                                break;
                            case 'rejected':
                                $status_color = '#dc3545'; // Red
                                break;
                            default:
                                $status_color = '#6c757d'; // Grey
                                break;
                        }
                        ?>
                        <div
                            style="display: flex; flex-direction: column; background: #f9f9f9; padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <div style="font-weight: bold; font-size: 18px; color: #333;"><?php echo $fullname; ?></div>
                                <div style="font-size: 13px; color: #777;">Applied: <?php echo $formatted_created_at; ?></div>
                            </div>
                            <div style="font-size: 14px; color: #555; margin-bottom: 5px;">
                                Email: <strong><?php echo $email; ?></strong>
                            </div>
                            <div style="font-size: 14px; color: #555; margin-bottom: 10px;">
                                Phone: <strong><?php echo $phonenumber; ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    Status: <span
                                        style="font-weight: bold; color: <?php echo $status_color; ?>;"><?php echo ucfirst($status); ?></span>
                                </div>
                                <div>
                                    <?php if (!empty($cv_file)): ?>
                                        <a href="<?php echo htmlspecialchars($cv_file); ?>" class="view-cv-button"
                                            data-application-id="<?php echo $application_id; ?>"
                                            style="background: #b23b3b; color: #fff; border: none; border-radius: 5px; padding: 6px 12px; font-size: 13px; text-decoration: none; margin-right: 8px;">
                                            View CV
                                        </a> <?php endif; ?>
                                    <button data-application-id="<?php echo $application_id; ?>" class="btn-manage-application"
                                        style="background: #007bff; color: #fff; border: none; border-radius: 5px; padding: 6px 12px; font-size: 13px; cursor: pointer;">Manage</button>
                                </div>
                            </div>
                        </div>
                        <?php
                    endwhile;
                }
            } else {
                echo '<div style="color: red; text-align: center; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">Database connection error.</div>';
            }
            ?>
        </div>
    <?php else: ?>
        <div
            style="color: #888; text-align: center; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            No Job ID provided.</div>
    <?php endif; ?>
</div>