<?php
// admin/jobpending.php
session_start();
require_once '../config.php'; // Điều chỉnh đường dẫn đến config.php

// --- Kiểm tra quyền Admin (quan trọng) ---
if (!isset($_SESSION['usernameadmin'])) { // Giả định 'usernameadmin' là biến session cho admin
    header('Location: ../index.php'); // Chuyển hướng đến trang đăng nhập admin
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Review Pending Job Posts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f0f2;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
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
        .job-item {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: background-color 0.2s;
        }
        .job-item:hover {
            background-color: #f0f0f0;
            cursor: pointer;
        }
        .job-info {
            flex-grow: 1;
        }
        .job-info h3 {
            margin: 0 0 5px 0;
            color: #15294c;
        }
        .job-info p {
            margin: 0;
            color: #555;
            font-size: 0.9em;
        }
        .job-item a {
            text-decoration: none;
            color: inherit; /* Kế thừa màu chữ từ cha */
            display: block; /* Làm cho toàn bộ thẻ job là một liên kết */
        }
        .no-jobs {
            text-align: center;
            color: #888;
            padding: 30px;
            font-size: 1.1em;
            background-color: #fefefe;
            border-radius: 8px;
            border: 1px dashed #ccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admindashboard.php" class="back-button">&larr; Back to Dashboard</a>
        <h1>Job Posts Awaiting Review</h1>

        <?php
        $sql = "SELECT j.id, j.job_title, c.company_name, j.posted_by_employer_id
            FROM job j
            LEFT JOIN company c ON c.id = j.company_id
            WHERE j.status = 'pending_review'
            ORDER BY j.created_at ASC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="job-item">
                    <a href="jobpendingdetail.php?id=<?php echo htmlspecialchars($row['id']); ?>">
                        <div class="job-info">
                            <h3><?php echo htmlspecialchars($row['job_title']); ?></h3>
                            <p><strong>Company:</strong> <?php echo htmlspecialchars($row['company_name']); ?></p>
                            <p><strong>Posted by Employer ID:</strong> <?php echo htmlspecialchars($row['posted_by_employer_id']); ?></p>
                        </div>
                    </a>
                </div>
                <?php
            }
        } else {
            echo '<div class="no-jobs">No job posts currently awaiting review.</div>';
        }
        $conn->close();
        ?>
    </div>
</body>
</html>