<?php
// admin/jobpendingdetail.php
session_start();
require_once '../config.php'; // Điều chỉnh đường dẫn đến config.php

// --- Kiểm tra quyền Admin (quan trọng) ---
if (!isset($_SESSION['usernameadmin'])) {
    header('Location: ../index.php');
    exit();
}

$job = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $jobId = intval($_GET['id']);

    $sql = "SELECT j.id, j.posted_by_employer_id, c.company_name, c.company_size, j.job_title,
                j.job_description, j.job_benefit, j.job_requirement, j.job_location,
                j.no_employee_needed, j.salary, j.post_duration, j.contact_email,
                j.contact_phone, j.job_type, c.industry AS job_category, j.required_certification,
                j.job_experience, c.company_logo, j.created_at, j.status, j.job_detailed_location, j.job_location_district
            FROM job j
            LEFT JOIN company c ON c.id = j.company_id
            WHERE j.id = ? AND j.status = 'pending_review'"; // Chỉ lấy job đang chờ duyệt
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $jobId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $job = $result->fetch_assoc();
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Job Details for Review</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f0f2;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0b1e45;
            text-align: center;
            margin-bottom: 25px;
        }
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.2s;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
        .job-details {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            background-color: #fefefe;
        }
        .job-details p {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        .job-details p strong {
            color: #15294c;
            display: inline-block;
            width: 180px; /* Căn chỉnh các label */
            vertical-align: top;
        }
        .job-details .section-title {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #0b1e45;
            font-size: 1.1em;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
        }
        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }
        .action-buttons button {
            padding: 12px 25px;
            font-size: 1em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 10px;
            transition: background-color 0.2s;
        }
        .action-buttons .approve-btn {
            background-color: #28a745;
            color: white;
        }
        .action-buttons .approve-btn:hover {
            background-color: #218838;
        }
        .action-buttons .reject-btn {
            background-color: #dc3545;
            color: white;
        }
        .action-buttons .reject-btn:hover {
            background-color: #c82333;
        }
        .review-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(11, 30, 69, 0.55);
        }
        .review-modal-overlay[hidden] {
            display: none;
        }
        .review-modal {
            position: relative;
            width: min(100%, 560px);
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.24);
        }
        .review-modal h2 {
            margin: 0 42px 8px 0;
            color: #0b1e45;
            font-size: 1.35rem;
        }
        .modal-description {
            margin: 0 0 20px;
            color: #555;
            line-height: 1.5;
        }
        .modal-close {
            position: absolute;
            top: 12px;
            right: 16px;
            border: 0;
            background: transparent;
            color: #555;
            font-size: 1.8rem;
            line-height: 1;
            cursor: pointer;
        }
        .modal-close:hover,
        .modal-close:focus-visible {
            color: #0b1e45;
        }
        .review-modal label {
            display: block;
            margin-bottom: 8px;
            color: #15294c;
            font-weight: 600;
        }
        .review-modal textarea {
            display: block;
            width: 100%;
            min-height: 130px;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: #15294c;
            font: inherit;
            resize: vertical;
        }
        .review-modal textarea:focus {
            outline: 3px solid rgba(0, 123, 255, 0.2);
            border-color: #007bff;
        }
        .field-help {
            margin: 6px 0 0;
            color: #777;
            font-size: 0.85rem;
        }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 24px;
        }
        .modal-actions button {
            min-width: 110px;
            padding: 10px 16px;
            border: 0;
            border-radius: 5px;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }
        .cancel-btn {
            background: #e9ecef;
            color: #15294c;
        }
        .cancel-btn:hover,
        .cancel-btn:focus-visible {
            background: #d8dde3;
        }
        .modal-submit-btn {
            color: #fff;
        }
        .approve-submit {
            background: #28a745;
        }
        .approve-submit:hover,
        .approve-submit:focus-visible {
            background: #218838;
        }
        .reject-submit {
            background: #dc3545;
        }
        .reject-submit:hover,
        .reject-submit:focus-visible {
            background: #c82333;
        }
        .modal-actions button:disabled {
            cursor: wait;
            opacity: 0.65;
        }
        .modal-close:focus-visible,
        .modal-actions button:focus-visible {
            outline: 3px solid rgba(0, 123, 255, 0.35);
            outline-offset: 2px;
        }
        body.modal-open {
            overflow: hidden;
        }
        @media (max-width: 600px) {
            .review-modal {
                padding: 24px 20px;
            }
            .modal-actions {
                flex-direction: column-reverse;
            }
            .modal-actions button {
                width: 100%;
            }
        }
        .not-found {
            text-align: center;
            color: #d90000;
            padding: 50px;
            font-size: 1.2em;
            background-color: #fefefe;
            border-radius: 8px;
            border: 1px dashed #d90000;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="jobpending.php" class="back-button">&larr; Back to Pending Jobs</a>
        <h1>Job Details for Review</h1>

        <?php if ($job): ?>
            <div class="job-details">
                <p><strong>Job ID:</strong> <?php echo htmlspecialchars($job['id']); ?></p>
                <p><strong>Posted by Employer ID:</strong> <?php echo htmlspecialchars($job['posted_by_employer_id']); ?></p>
                <p><strong>Company Name:</strong> <?php echo htmlspecialchars($job['company_name']); ?></p>
                <p><strong>Company Size:</strong> <?php echo htmlspecialchars($job['company_size']); ?></p>
                <p><strong>Job Title:</strong> <?php echo htmlspecialchars($job['job_title']); ?></p>
                
                <div class="section-title">Job Description</div>
                <p><?php echo nl2br(htmlspecialchars($job['job_description'])); ?></p>
                
                <div class="section-title">Job Requirements</div>
                <p><?php echo nl2br(htmlspecialchars($job['job_requirement'])); ?></p>
                
                <div class="section-title">Job Benefits</div>
                <p><?php echo nl2br(htmlspecialchars($job['job_benefit'])); ?></p>
                
                <p><strong>Job Location:</strong> <?php echo htmlspecialchars($job['job_location']); ?></p>
                <p><strong>Detailed Location:</strong> <?php echo htmlspecialchars($job['job_detailed_location']); ?></p>
                <p><strong>District:</strong> <?php echo htmlspecialchars($job['job_location_district']); ?></p>
                <p><strong>Number of Employees Needed:</strong> <?php echo htmlspecialchars($job['no_employee_needed']); ?></p>
                <p><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></p>
                <p><strong>Post Duration (days):</strong> <?php echo htmlspecialchars($job['post_duration']); ?></p>
                <p><strong>Contact Email:</strong> <?php echo htmlspecialchars($job['contact_email']); ?></p>
                <p><strong>Contact Phone:</strong> <?php echo htmlspecialchars($job['contact_phone']); ?></p>
                <p><strong>Job Type:</strong> <?php echo htmlspecialchars($job['job_type']); ?></p>
                <p><strong>Job Category:</strong> <?php echo htmlspecialchars($job['job_category']); ?></p>
                <p><strong>Required Certification:</strong> <?php echo htmlspecialchars($job['required_certification']); ?></p>
                <p><strong>Job Experience:</strong> <?php echo htmlspecialchars($job['job_experience']); ?></p>
                <p><strong>Company Logo:</strong> 
                    <?php if (!empty($job['company_logo'])): ?>
                        <a href="../<?php echo htmlspecialchars($job['company_logo']); ?>" target="_blank">View Logo</a>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </p>
                <p><strong>Posted On:</strong> <?php echo (new DateTime($job['created_at']))->format('d/m/Y H:i'); ?></p>
                <p><strong>Current Status:</strong> <span style="font-weight: bold; color: #ffc107;"><?php echo htmlspecialchars($job['status']); ?></span></p>
            </div>

            <div class="action-buttons" aria-label="Job review actions">
                <button type="button" class="approve-btn" data-review-action="approve">Approve Job</button>
                <button type="button" class="reject-btn" data-review-action="reject">Reject Job</button>
            </div>

            <div class="review-modal-overlay" id="review-modal" hidden>
                <div class="review-modal" role="dialog" aria-modal="true" aria-labelledby="review-modal-title">
                    <button type="button" class="modal-close" id="close-review-modal" aria-label="Close review note dialog">&times;</button>
                    <h2 id="review-modal-title">Ghi chú cho nhà tuyển dụng (nếu có)</h2>
                    <p class="modal-description">Bạn có thể để lại ghi chú trước khi hoàn tất đánh giá công việc này.</p>

                    <form method="POST" action="process_job_review.php" id="job-review-form">
                        <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="action" id="review-action" value="">

                        <label for="admin-note">Ghi chú</label>
                        <textarea id="admin-note" name="admin_note" rows="6" maxlength="2000" placeholder="Nhập ghi chú cho nhà tuyển dụng (không bắt buộc)" aria-describedby="admin-note-help"></textarea>
                        <p class="field-help" id="admin-note-help">Tối đa 2.000 ký tự.</p>

                        <div class="modal-actions">
                            <button type="button" class="cancel-btn" id="cancel-review">Hủy</button>
                            <button type="submit" class="modal-submit-btn" id="submit-review">Submit Review</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                (function () {
                    const reviewModal = document.getElementById('review-modal');
                    const reviewForm = document.getElementById('job-review-form');
                    const reviewAction = document.getElementById('review-action');
                    const adminNote = document.getElementById('admin-note');
                    const submitReview = document.getElementById('submit-review');
                    const closeReviewModal = document.getElementById('close-review-modal');
                    const cancelReview = document.getElementById('cancel-review');
                    const reviewButtons = document.querySelectorAll('[data-review-action]');

                    function closeModal() {
                        reviewModal.hidden = true;
                        document.body.classList.remove('modal-open');
                        reviewAction.value = '';
                    }

                    function openModal(action) {
                        const isApprove = action === 'approve';
                        reviewAction.value = action;
                        submitReview.textContent = isApprove ? 'Approve Job' : 'Reject Job';
                        submitReview.classList.toggle('approve-submit', isApprove);
                        submitReview.classList.toggle('reject-submit', !isApprove);
                        reviewModal.hidden = false;
                        document.body.classList.add('modal-open');
                        adminNote.focus();
                    }

                    reviewButtons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            openModal(button.dataset.reviewAction);
                        });
                    });

                    closeReviewModal.addEventListener('click', closeModal);
                    cancelReview.addEventListener('click', closeModal);

                    reviewModal.addEventListener('click', function (event) {
                        if (event.target === reviewModal) {
                            closeModal();
                        }
                    });

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape' && !reviewModal.hidden) {
                            closeModal();
                        }
                    });

                    reviewForm.addEventListener('submit', function () {
                        submitReview.disabled = true;
                        submitReview.textContent = 'Processing...';
                    });
                }());
            </script>
        <?php else: ?>
            <div class="not-found">
                Job not found or not awaiting review.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>