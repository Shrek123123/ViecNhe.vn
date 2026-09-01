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

            <div class="action-buttons">
                <form method="POST" action="process_job_review.php" style="display: inline-block;">
                    <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['id']); ?>">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="approve-btn">Approve Job</button>
                </form>
                <form method="POST" action="process_job_review.php" style="display: inline-block;">
                    <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['id']); ?>">
                    <input type="hidden" name="action" value="reject">
                    <button type="submit" class="reject-btn">Reject Job</button>
                </form>
            </div>
        <?php else: ?>
            <div class="not-found">
                Job not found or not awaiting review.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>