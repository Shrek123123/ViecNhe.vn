<?php
require_once '../config.php'; // Đảm bảo config.php được bao gồm
?>
<div style="padding: 30px; border-radius: 8px;">
    <h2 style="color: #b23b3b; margin-bottom: 20px;">My Job Posts</h2>
    <div style="display: flex; flex-direction: column; gap: 18px;">
        <?php
        // Get employer id from URL (?id=...)
        $employer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        // If no employer id, do not query
        if ($employer_id > 0) {
            // Check if $conn is defined and connected before querying
            if (isset($conn) && $conn->ping()) {
                // Query ALL columns for editing purposes
                $sql = "SELECT * FROM job WHERE posted_by_employer_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $employer_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    echo '<div style="color: #888; text-align: center; padding: 20px;">No job posts found.</div>';
                }

                while ($row = $result->fetch_assoc()):
                    $job_id = htmlspecialchars($row['id']); // Get job ID
                    $logo = !empty($row['company_logo']) ? htmlspecialchars($row['company_logo']) : 'https://placehold.co/120x70/cccccc/333333?text=No+Logo';
                    $created_at = new DateTime($row['created_at']);
                    $duration = intval($row['post_duration']);
                    $expire_at = clone $created_at;
                    $expire_at->modify("+$duration days");
                    $now = new DateTime();
                    $days_left = $now < $expire_at ? $now->diff($expire_at)->days : 0;
                    $salary = htmlspecialchars($row['salary']);
                    $company_name = htmlspecialchars($row['company_name']);
                    $job_title = htmlspecialchars($row['job_title']);
                    $job_location = htmlspecialchars($row['job_location']);
                    $job_status = htmlspecialchars($row['status']); // Get the job status

                    $updated = $created_at->diff($now);
                    if ($updated->days > 0) {
                        $updated_str = "Updated {$updated->days} days ago";
                    } elseif ($updated->h > 0) {
                        $updated_str = "Updated {$updated->h} hours ago";
                    } else {
                        $updated_str = "Just updated";
                    }

                    // Determine status color for display
                    $status_color = '#6c757d'; // Default grey
                    switch ($job_status) {
                        case 'pending_review': $status_color = '#ffc107'; break; // Yellow
                        case 'approved':       $status_color = '#28a745'; break; // Green
                        case 'rejected':       $status_color = '#dc3545'; break; // Red
                    }
                ?>
                    <div
                        style="background: #fff; border: 1px solid black; border-radius: 10px; padding: 18px 18px 18px 0; display: flex; align-items: flex-start; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <img src="<?php echo $logo; ?>"
                            alt="job"
                            style="width: 120px; height: 70px; border-radius: 8px; object-fit: cover; margin-right: 18px;">
                        <div style="flex: 1;">
                            <div style="font-weight: bold; font-size: 18px;"><?php echo $job_title; ?></div>
                            <div style="color: #888; font-size: 13px; margin-bottom: 6px;"><?php echo $company_name; ?></div>
                            <div style="display: flex; gap: 10px; margin-bottom: 8px;">
                                <span
                                    style="background: #eee; color: #b23b3b; border-radius: 6px; padding: 2px 10px; font-size: 12px;"><?php echo htmlspecialchars($job_location); ?></span>
                                <span
                                    style="background: #eee; color: #b23b3b; border-radius: 6px; padding: 2px 10px; font-size: 12px;">
                                    <?php echo $days_left > 0 ? "$days_left days left to apply" : "Application closed"; ?>
                                </span>
                                <span
                                    style="background: #eee; color: #888; border-radius: 6px; padding: 2px 10px; font-size: 12px;"><?php echo $updated_str; ?></span>
                            </div>
                            <div style="color: #b23b3b; font-weight: bold; font-size: 15px; margin-bottom: 5px;"><?php echo $salary; ?></div>
                            <div style="font-size: 14px;">
                                Status: <span style="font-weight: bold; color: <?php echo $status_color; ?>;"><?php echo $job_status; ?></span>
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-left: 18px; align-items: flex-end;">
                            <button class="edit-job-button"
                                data-jobid="<?php echo $job_id; ?>"
                                style="background: #d90000; color: #fff; border: none; border-radius: 6px; padding: 6px 18px; font-size: 13px; cursor: pointer;">Edit</button>
                            <button class="delete-job-button"
                                data-jobid="<?php echo $job_id; ?>"
                                style="background: #fff; color: #d90000; border: 1px solid #d90000; border-radius: 6px; padding: 6px 18px; font-size: 13px; cursor: pointer;">Delete</button>
                            <button id="btn-jobpostsapplications"
                                data-jobid="<?php echo $job_id; ?>" style="background: #b23b3b; color: #fff; border: none; border-radius: 6px; padding: 6px 18px; font-size: 13px; cursor: pointer;">Check submitted applications</button>
                        </div>
                    </div>
                <?php endwhile;
                } else {
                    echo '<div style="color: #888; text-align: center; padding: 20px;">Database connection error or no employer ID.</div>';
                }
            } else {
                echo '<div style="color: #888; text-align: center; padding: 20px;">No employer selected.</div>';
            }
            // Không đóng kết nối $conn ở đây nếu nó được dùng lại trong body.php
            // $conn->close();
        ?>
    </div>
</div>

<script>
    // Event delegation for "Edit" button inside jobposts-content.php
    $(document).off('click', '.edit-job-button').on('click', '.edit-job-button', function () {
        var jobId = $(this).data('jobid');
        if (jobId) {
            // 1. Clear the current content-area (modal is already removed as it's part of content-area in this setup)
            $('.content-area').empty().html('<div style="padding:30px;text-align:center;">Loading job edit form...</div>');

            // 2. Load the jobposts_edit-modal.php into content-area
            $.ajax({
                url: 'employerprofile/jobposts_edit-modal.php',
                type: 'GET',
                data: { job_id: jobId }, // Pass job ID to the edit form
                success: function (response) {
                    $('.content-area').html(response);
                },
                error: function () {
                    $('.content-area').html('<div style="color: red; text-align: center; padding: 20px;">Error loading job edit form.</div>');
                }
            });
        } else {
            console.error("Job ID not found for edit button.");
        }
    });

    // --- Bổ sung đoạn code xử lý nút Delete ---
    $(document).off('click', '.delete-job-button').on('click', '.delete-job-button', function () {
        var jobId = $(this).data('jobid');
        var employerId = <?php echo $employer_id; ?>; // Lấy employer_id từ PHP

        if (confirm('Are you sure you want to delete this job post? This action cannot be undone.')) {
            if (jobId && employerId) {
                $.ajax({
                    url: 'employerprofile/delete_job_post.php', // Đường dẫn tới file PHP xử lý xóa
                    type: 'POST',
                    dataType: 'json', // Mong đợi phản hồi JSON
                    data: { job_id: jobId, employer_id: employerId },
                    success: function (response) {
                        if (response.success) {
                            alert(response.message);
                            // Tải lại nội dung trang jobposts-content.php sau khi xóa thành công
                            // Để cập nhật danh sách bài đăng
                            $('.content-area').load('employerprofile/jobposts-content.php?id=' + employerId);
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        // Xử lý lỗi AJAX (ví dụ: PHP trả về không phải JSON)
                        console.error("AJAX Error: ", status, error);
                        console.log("Response Text: ", xhr.responseText);
                        alert('An error occurred while deleting the job post. Please check the console for details.');
                    }
                });
            } else {
                alert("Cannot delete job. Job ID or Employer ID is missing.");
            }
        }
    });
    // --- Kết thúc bổ sung ---

</script>