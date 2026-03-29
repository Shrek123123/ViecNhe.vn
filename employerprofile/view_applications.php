<?php
session_start();
if (!isset($_SESSION['usernameemployer'])) {
    $redirect = $_SERVER['HTTP_REFERER'] ?? 'employerpage.php';
    header("Location: $redirect");
    exit();
}

// Kiểm tra quyền truy cập employerprofile.php
if (!isset($_SESSION['employerid'])) {
    header("Location: employerpage.php");
    exit();
}
?>
<?php
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Nếu chưa có ngôn ngữ thì mặc định là tiếng Việt
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'vi';
}

// Load file ngôn ngữ tương ứng
include '../lang/' . $_SESSION['lang'] . '.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$employername = $_SESSION['usernameemployer'] ?? 'Employer';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['employerprofile_postajob'] ?></title>
    <style>
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-info span {
            font-size: 16px;
            font-weight: bold;
        }

        .user-dropdown {
            position: relative;
            display: inline-block;
            cursor: pointer;
            margin-right: 50px;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            /* nằm ngay dưới .user-info */
            left: 0;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            flex-direction: column;
            min-width: 100px;
            z-index: 1000;
        }

        .dropdown-menu a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #333;
            border-bottom: 1px solid #eee;
        }

        .dropdown-menu a:last-child {
            border-bottom: none;
        }

        .user-dropdown:hover .dropdown-menu {
            display: flex;
        }


        body {
            margin: 0;
            padding: 0;
            font-family: Roboto, sans-serif;
        }

        .header {
            display: flex;
            align-items: center;
            background-color: white;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            width: 100%;
            box-sizing: border-box;
        }

        .header nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .header a:hover,
        .header .guest-options a:hover,
        .header .dropdown-menu a:hover {
            color: #d70018 !important;
            transition: color 0.1s;
        }

        .navbar {
            display: flex;
            align-items: center;
        }



        .guest-options {
            display: flex;
            gap: 15px;
            margin-left: auto;
            margin-right: 50px;
        }

        .btn-login,
        .btn-register {
            text-decoration: none;
        }

        .footer-new {
            background-color: #B73C3C;
            color: white;
            font-family: Arial, sans-serif;

        }

        .hotline-section {
            display: flex;
            justify-content: space-around;
            background-color: #fff;
            padding: 30px 10px;
            border: 1px solid #ccc;
            border-bottom: none;
        }

        .hotline-box {
            flex: 1;
            padding: 0 20px;
            text-align: center;
            border-right: 1px solid #ddd;
        }

        .hotline-box:last-child {
            border-right: none;
        }

        .hotline-box h3 {
            color: #D91616;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .hotline-item {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .hotline-item i {
            font-size: 24px;
            color: #333;
            margin-right: 10px;
        }

        .hotline-item div {
            text-align: left;
            font-size: 14px;
            color: #D91616;
        }

        .hotline-btn {
            background-color: #D91616;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 4px;
            margin-top: 10px;
            cursor: pointer;
        }

        .hotline-btn:hover {
            background-color: #880000;
        }



        .footer-container {
            display: flex;
            justify-content: space-between;
            max-width: 100%;
            flex-wrap: wrap;
            margin: auto;
        }

        .footer-column {
            flex: 1 1 30%;
            min-width: 400px;
            margin: 10px 0px 50px 40px;

        }

        .footer-column h4 {
            margin-bottom: 30px;
            font-weight: bold;
        }

        .footer-column p,
        .footer-column ul {
            font-size: 14px;
            line-height: 1.6;
        }

        .footer-column ul {
            list-style: none;
            padding: 0;
        }

        .footer-column ul li {
            margin-bottom: 8px;
        }

        .social-icons {
            margin-bottom: 12px;
        }

        .social-icons i {
            font-size: 20px;
            margin-right: 10px;
            border-radius: 10px;
            color: white;
        }


        .social-icons .facebook {
            background-color: #1877f2;
            padding: 10px;
        }

        .social-icons .tiktok {
            background-color: #000;
            padding: 10px;
        }

        .social-icons .telegram {
            background-color: #24A1DE;
            padding: 10px;
        }

        .social-icons .instagram {
            background: radial-gradient(circle at 30% 107%,
                    #fdf497 0%,
                    #fdf497 5%,
                    #fd5949 45%,
                    #d6249f 60%,
                    #285AEB 90%);
            display: inline-block;
            padding: 10px;
        }

        .qr-and-apps {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .qr-and-apps img {
            height: 100px;
        }

        .app-links img {
            width: 130px;
            display: block;
            height: 60px;
        }

        /**
        body CSS
         */
        /* ===== DASHBOARD BASE ===== */
        .employer-dashboard {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
        }

        /* ===== TOP BAR ===== */
        .dashboard-topbar {
            background-color: #943737;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            padding: 10px 20px;
        }

        .dashboard-title {
            font-weight: bold;
            font-size: 15px;
        }

        .dashboard-company {
            display: flex;
            align-items: center;
        }

        .dashboard-company img {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .dashboard-company-info {
            text-align: right;
            font-size: 12px;
        }

        .company-status {
            font-size: 11px;
            color: #ffdddd;
        }

        /* ===== MAIN LAYOUT ===== */
        .dashboard-main {
            display: flex;
        }

        /* ===== SIDEBAR ===== */
        .dashboard-sidebar {
            width: 160px;
            border-right: 1px solid #ccc;
            padding: 10px;
            background: #fff;
        }

        .dashboard-sidebar h3 {
            margin-top: 20px;
            font-size: 14px;
        }

        .dashboard-sidebar button {
            width: 100%;
            padding: 6px;
            margin-bottom: 10px;
            font-size: 13px;
            border: 1px solid black;
            background: white;
            cursor: pointer;
        }

        .dashboard-sidebar button:hover {
            background: #f2f2f2;
        }

        /* ===== APPLICANT CARD ===== */
        .applicant-card {
            background: #b76d6d;
            color: white;
            border-radius: 6px;
            padding: 6px;
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .applicant-card img {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .applicant-info {
            font-size: 13px;
        }

        .status-viewed {
            font-size: 11px;
            color: #b2ffb2;
        }

        .status-new {
            font-size: 11px;
            color: #cce0ff;
        }

        .view-more {
            font-size: 12px;
            text-align: center;
            background: #fff;
            border-radius: 4px;
            cursor: pointer;
        }

        /* ===== CONTENT ===== */
        .dashboard-content {
            flex: 1;
            background: #fff;
            padding: 20px;
        }

        /* button post job */
        #btn-postjob.active {
            background: #b23b3b;
            color: #fff;
            font-weight: bold;
        }

        /* ===== POSTING OPTIONS BUTTONS===== */
        /* .posting-options {
            display: grid;
            grid-template-columns: repeat(2, 260px);
            /* 2 cột cố định */
        justify-content: center;
        /* căn giữa toàn bộ khối */
        gap: 20px;
        }

        */ .post-btn {
            padding: 14px 18px;
            border: none;
            border-radius: 14px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        /* Màu */
        .post-btn.blue {
            background: linear-gradient(135deg, #2563eb, #1e40af);
        }

        .post-btn.teal {
            background: linear-gradient(135deg, #0d9488, #0f766e);
        }

        .post-btn.green {
            background: linear-gradient(135deg, #16a34a, #166534);
        }

        .post-btn.indigo {
            background: linear-gradient(135deg, #4f46e5, #3730a3);
        }

        .post-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>


<body>
    <div class="header">
        <div style="display: flex; align-items: center;">
            <a href="../employerpage.php">
                <img src="../image/logo.png" alt="JobHive Logo" style="height: 50px; margin-right: 10px;">
            </a>
        </div>
        <div style="display: flex; gap: 50px; margin-left: 50px;">
            <a href="#" style="text-decoration: none; color: #333;"><?php echo $lang['create_cv']; ?></a>
            <a href="../contactus_employer.php"
                style="text-decoration: none; color: #333;"><?php echo $lang['contact_us']; ?></a>
            <a href="../feedback_employer.php"
                style="text-decoration: none; color: #333;"><?php echo $lang['feedback']; ?></a>
        </div>
        <div style="display: flex; gap: 15px; margin-left: auto;">
            <?php if (isset($_SESSION['usernameemployer'])): ?>
                <div class="user-dropdown">
                    <div class="user-info">
                        <img src="../image/defaultavatar.jpg" alt="User Avatar">
                        <span><?php echo htmlspecialchars($_SESSION['usernameemployer']); ?></span>
                    </div>
                    <div class="dropdown-menu">
                        <?php
                        $employerId = isset($_SESSION['employerid']) ? intval($_SESSION['employerid']) : 0;
                        ?>
                        <a href="../employerlogout.php"><?php echo $lang['logout']; ?></a>
                    </div>
                </div>
                <button id="lang-switch-btn"
                    style="padding: 8px 16px; border: none; background: #f5f5f5; cursor: pointer; border-radius: 4px; margin-right: 50px;">
                    <?php echo ($_SESSION['lang'] === 'vi') ? 'English' : 'Tiếng Việt'; ?>
                </button>
            <?php else: ?>
                <div class="guest-options">
                    <a href="employerlogin.php" class="btn-login"><?php echo $lang['login/register_employer']; ?></a>
                    <a href="index.php" class="btn-register"><?php echo $lang['jobseeker_redirect']; ?></a>
                    <a href="admin/adminlogin.php" class="btn-register"><?php echo $lang['admin_login']; ?></a>
                    <button id="lang-switch-btn"
                        style="padding: 8px 16px; border: none; background: #f5f5f5; cursor: pointer; border-radius: 4px;">
                        <?php echo ($_SESSION['lang'] === 'vi') ? 'English' : 'Tiếng Việt'; ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <script>
            document.getElementById('lang-switch-btn').onclick = function () {
                var currentLang = '<?php echo $_SESSION['lang']; ?>';
                if (currentLang === 'vi') {
                    window.location.href = '?lang=en';
                } else {
                    window.location.href = '?lang=vi';
                }
            };
        </script>
    </div>
    <!-- #region -->
    <div class="employer-dashboard">

        <!-- TOP BAR -->
        <div class="dashboard-topbar">
            <div class="dashboard-title">
                <?= $lang['employerprofile'] ?>
            </div>

            <div class="dashboard-company">
                <img src="<?= $profile_pic ?? 'https://via.placeholder.com/34' ?>" alt="company logo">

                <div class="dashboard-company-info">
                    <div class="company-name">
                        <?= $employername ?? 'Employer' ?>
                    </div>
                    <div class="company-status">● Online</div>
                </div>
            </div>
        </div>

        <!-- MAIN -->
        <div class="dashboard-main">

            <!-- SIDEBAR -->
            <div class="dashboard-sidebar">
                <button id="btn-postjob"
                    class="<?= ($currentPage == 'add_job_post.php') ? 'active' : '' ?>"><?= $lang['employerprofile_postajob'] ?></button>
                <button
                    onclick="window.location.href='my_job_posts.php'"><?= $lang['employerprofile_myjobposts'] ?></button>
                <button><?= $lang['employerprofile_myprofile'] ?></button>
                <button><?= $lang['employerprofile_changeinfo'] ?></button>

                <h3><?= $lang['employerprofile_applicantprofiles'] ?></h3>

                <!-- Applicant preview (static demo) -->
                <div class="applicant-card">
                    <img src="https://via.placeholder.com/28">
                    <div class="applicant-info">
                        Alex<br>
                        <span class="status-viewed">✔ Viewed</span>
                    </div>
                </div>

                <div class="applicant-card">
                    <img src="https://via.placeholder.com/28">
                    <div class="applicant-info">
                        Selena<br>
                        <span class="status-new">★ New</span>
                    </div>
                </div>

                <div class="view-more">View more</div>
            </div>

            <!-- CONTENT -->
            <div class="dashboard-content">
                <!-- Content load sau -->
                <?php
                require_once '../config.php'; // Đảm bảo config.php được bao gồm và thiết lập kết nối $conn
                include '../lang/' . $_SESSION['lang'] . '.php';
                include '../helpers.php';
                require_once '../config.php';

                // Lấy job_id ưu tiên từ POST (form submit), nếu không có dùng GET
                $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : (isset($_GET['job_id']) ? intval($_GET['job_id']) : 0);

                $flash_message = '';
                if (isset($_SESSION['flash_message'])) {
                    $flash_message = $_SESSION['flash_message'];
                    unset($_SESSION['flash_message']);
                }

                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
                    if (!isset($_SESSION['employerid']) || empty($_SESSION['employerid'])) {
                        header('Location: employerpage.php');
                        exit;
                    }

                    $action = $_POST['action'];
                    $employerid = $_SESSION['employerid'];

                    if ($action === 'hideFromEmployer') {
                        $application_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;
                        if ($application_id <= 0) {
                            $_SESSION['flash_message'] = $lang['invalid_application_id'] ?? 'Invalid application ID.';
                        } else {
                            // Kiểm tra quyền
                            $checkSql = "SELECT j.posted_by_employer_id FROM application a JOIN job j ON a.job_id = j.id WHERE a.id = ? LIMIT 1";
                            $checkStmt = $conn->prepare($checkSql);
                            if ($checkStmt) {
                                $checkStmt->bind_param('i', $application_id);
                                $checkStmt->execute();
                                $res = $checkStmt->get_result();
                                if ($row = $res->fetch_assoc()) {
                                    if (intval($row['posted_by_employer_id']) === intval($employerid)) {
                                        $updSql = "UPDATE application SET hidden_from_employer = 1 WHERE id = ?";
                                        $updStmt = $conn->prepare($updSql);
                                        if ($updStmt) {
                                            $updStmt->bind_param('i', $application_id);
                                            if ($updStmt->execute()) {
                                                $_SESSION['flash_message'] = $lang['delete_application_success_message'] ?? 'Application hidden successfully.';
                                            } else {
                                                $_SESSION['flash_message'] = $lang['database_error'] ?? 'Database error.';
                                            }
                                            $updStmt->close();
                                        }
                                    } else {
                                        $_SESSION['flash_message'] = $lang['unauthorized_action'] ?? 'Unauthorized action.';
                                    }
                                } else {
                                    $_SESSION['flash_message'] = $lang['application_not_found'] ?? 'Application not found.';
                                }
                                $checkStmt->close();
                            }
                        }

                        header('Location: view_applications.php?job_id=' . $job_id);
                        exit;
                    }

                    if ($action === 'updateApplicationStatus') {
                        $application_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;
                        $new_status = isset($_POST['new_status']) ? $_POST['new_status'] : '';
                        $allowed_statuses = ['applied', 'viewed', 'interviewed', 'hired', 'rejected'];

                        if ($application_id <= 0 || !in_array($new_status, $allowed_statuses, true)) {
                            $_SESSION['flash_message'] = $lang['invalid_request'] ?? 'Invalid request.';
                        } else {
                            $checkSql = "SELECT j.posted_by_employer_id FROM application a JOIN job j ON a.job_id = j.id WHERE a.id = ? LIMIT 1";
                            $checkStmt = $conn->prepare($checkSql);
                            if ($checkStmt) {
                                $checkStmt->bind_param('i', $application_id);
                                $checkStmt->execute();
                                $res = $checkStmt->get_result();
                                if (($row = $res->fetch_assoc()) && intval($row['posted_by_employer_id']) === intval($employerid)) {
                                    $sql = "UPDATE application SET status = ? WHERE id = ?";
                                    $stmt = $conn->prepare($sql);
                                    if ($stmt) {
                                        $stmt->bind_param('si', $new_status, $application_id);
                                        if ($stmt->execute()) {
                                            $_SESSION['flash_message'] = $lang['status_update_success'] ?? 'Status updated successfully.';
                                        } else {
                                            $_SESSION['flash_message'] = $lang['database_error'] ?? 'Database error.';
                                        }
                                        $stmt->close();
                                    }
                                } else {
                                    $_SESSION['flash_message'] = $lang['unauthorized_action'] ?? 'Unauthorized action.';
                                }
                                $checkStmt->close();
                            }
                        }

                        echo '<form id="redirectForm" method="POST" action="view_applications.php" style="display:none;">
                            <input type="hidden" name="job_id" value="' . htmlspecialchars($job_id) . '">
                        </form>';
                        echo '<script>document.getElementById("redirectForm").submit();</script>';
                        exit;
                    }
                }
                $employerid = $_SESSION['employerid'] ?? 0;
                $stmt = $conn->prepare("
    SELECT *
    FROM job
    WHERE id = ? AND posted_by_employer_id = ?
    LIMIT 1
");
                $stmt->bind_param("ii", $job_id, $employerid);
                $stmt->execute();
                $result = $stmt->get_result();
                $job_row = $result->fetch_assoc();
                $stmt->close();

                if (!$job_row) {
                    die("Job không tồn tại hoặc không thuộc employer.");
                }

                // Lấy job_post_number
                $job_post_number = $job_row['job_post_number_by_employer'] ?? 0;

                // Tạo message
                $lang_key = 'your_job_post#';
                $message = sprintf($lang[$lang_key], $job_post_number);

                // Lấy tổng số application cho cùng một job
                $sql = "SELECT COUNT(id) AS total_applications
    FROM application
    WHERE job_id = ? AND (hidden_from_employer = 0 OR hidden_from_employer IS NULL)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $job_id);
                $stmt->execute();
                $result = $stmt->get_result();

                $total_applications = 0;
                if ($row = $result->fetch_assoc()) {
                    $total_applications = $row['total_applications'];
                }
                ?>


                <div style="color: #b23b3b; font-weight: bold; font-size: 20px; text-align: center;">
                    <?php echo $message ?><span></span>
                </div>
                <?php if (!empty($flash_message)): ?>
                    <div style="margin: 10px auto; width: fit-content; padding: 10px 16px; border-radius: 6px; background: #eaf8ed; color: #2a662d; border: 1px solid #b9e6c0; font-weight: bold;">
                        <?php echo htmlspecialchars($flash_message); ?>
                    </div>
                <?php endif; ?>
                <div style="padding: 10px;">
                    <?php if ($job_id > 0): ?>
                        <h4 style="margin-bottom: 15px; color: #b23b3b;">
                            <?= $lang['application_detail_to_job'] ?>
                            <strong><?php echo htmlspecialchars($total_applications); ?></strong>
                        </h4>

                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <?php
                            // Kiểm tra kết nối database
                            if (isset($conn) && $conn->ping()) {
                                // Truy vấn để lấy các ứng dụng cho job_id cụ thể
                                // Lấy thông tin từ bảng 'application'
                                $sql = "SELECT id, fullname, phonenumber, email, status, created_at, cv_file
            FROM application
            WHERE job_id = ? AND (hidden_from_employer = 0 OR hidden_from_employer IS NULL)";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $job_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows === 0) {
                                    echo '<div style="color: #888; text-align: center; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">' . $lang['no_application_found_for_this_job_post'] . '</div>';
                                } else {
                                    while ($row = $result->fetch_assoc()):
                                        $application_id = htmlspecialchars($row['id']);
                                        $fullname = htmlspecialchars($row['fullname']);
                                        $phonenumber = htmlspecialchars($row['phonenumber']);
                                        $email = htmlspecialchars($row['email']);
                                        $job_status_raw = htmlspecialchars($row['status']);
                                        $created_at = new DateTime($row['created_at']);
                                        $formatted_created_at = formatDateByLocale($row['created_at']);
                                        $final_formatted_date = trans('cv_application_date', [':date' => $formatted_created_at]);

                                        // Chuẩn hóa đường dẫn CV để luôn trỏ vào root folder JobHive-main
                                        $cv_file = '';
                                        if (!empty($row['cv_file'])) {
                                            $cv_file_raw = trim($row['cv_file']);
                                            $baseFolder = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
                                            if ($baseFolder === '') {
                                                $baseFolder = '/';
                                            }

                                            // Nếu là URL đầy đủ thì giữ nguyên
                                            if (preg_match('#^(https?://)#i', $cv_file_raw)) {
                                                $cv_file = $cv_file_raw;
                                            } else {
                                                // Nếu là đường dẫn bắt đầu / thì cho vào base folder JobHive-main
                                                if (strpos($cv_file_raw, '/') === 0) {
                                                    $cv_file = $baseFolder . rtrim($cv_file_raw, '/');
                                                } else {
                                                    // path tương đối như uploads/cv_file/abc.pdf
                                                    $cv_file = rtrim($baseFolder, '/') . '/' . ltrim($cv_file_raw, '/');
                                                }
                                            }
                                        }

                                        $status_lang_keys = [
                                            'hired' => 'hired',
                                            'pending' => 'pending',
                                            'rejected' => 'rejected_application',
                                            'interviewed' => 'interviewed',
                                        ];

                                        // For employer view: hide 'viewed' status (show blank). Only jobseeker sees 'viewed'.
                                        if ($job_status_raw === 'viewed') {
                                            // change nothing to employer
                                            $job_status_raw = 'pending';
                                            $display_status = $lang[$status_lang_keys[$job_status_raw]];
                                        } else {
                                            $display_status = isset($status_lang_keys[$job_status_raw]) ? $lang[$status_lang_keys[$job_status_raw]] : $job_status_raw;
                                        }

                                        // Định nghĩa màu cho trạng thái
                                        $status_color = '';
                                        switch ($job_status_raw) {
                                            case 'pending':
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
                                        <div class="application-card" data-application-id="<?php echo $application_id; ?>"
                                            style="display: flex; flex-direction: column; background: #f9f9f9; padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                            <div
                                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                                <div style="font-weight: bold; font-size: 18px; color: #333;"><?php echo $fullname; ?>
                                                </div>
                                                <div style="font-size: 13px; color: #777;"><?php echo $final_formatted_date; ?></div>
                                            </div>
                                            <div style="font-size: 14px; color: #555; margin-bottom: 5px;">
                                                Email: <strong><?php echo $email; ?></strong>
                                            </div>
                                            <div style="font-size: 14px; color: #555; margin-bottom: 10px;">
                                                <?= $lang['phone'] ?> <strong><?php echo $phonenumber; ?></strong>
                                            </div>
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <div>
                                                    <?= $lang['status2'] ?> <span
                                                        style="font-weight: bold; color: <?php echo $status_color; ?>;"><?php echo ucfirst($display_status); ?></span>
                                                </div>
                                                <div>
                                                    <?php if (!empty($cv_file)): ?>
                                                        <a href="<?php echo htmlspecialchars($cv_file); ?>" class="view-cv-button"
                                                            data-application-id="<?php echo $application_id; ?>"
                                                            style="background: #b23b3b; color: #fff; border: none; border-radius: 5px; padding: 6px 12px; font-size: 13px; text-decoration: none; margin-right: 8px;"
                                                            target="_blank" rel="noopener noreferrer">
                                                            <?= $lang['view_cv'] ?>
                                                        </a>
                                                        <button type="button"
                                                            class="btn-manage-application"
                                                            data-application-id="<?php echo $application_id; ?>"
                                                            data-application-name="<?php echo $fullname; ?>"
                                                            data-current-status="<?php echo $job_status_raw; ?>"
                                                            style="background: #007bff; color: #fff; border: none; border-radius: 5px; padding: 6px 12px; font-size: 13px; cursor: pointer; margin-right: 8px;"><?= $lang['manage'] ?></button>
                                                    <?php endif; ?>

                                                    <!-- Hide (archive from employer) button -->
                                                    <button type="button" data-application-id="<?php echo $application_id; ?>"
                                                        class="hide-application-button"
                                                        style="background: #dc3545; color: #fff; border: none; border-radius: 5px; padding: 6px 12px; font-size: 13px; cursor: pointer;"><?= $lang['delete_submitted_applications'] ?></button>
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
                        <script>
                            // Tạo modal cho Manage và Xóa
                            document.body.insertAdjacentHTML('beforeend', `
                                <div id="appModalOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9998;"></div>
                                <div id="manageApplicationModal" style="display:none; position:fixed; inset:0; z-index:10000; align-items:center; justify-content:center;">
                                    <div style="background:#fff; border-radius:8px; width:95%; max-width:420px; padding:20px; box-shadow:0 10px 24px rgba(0,0,0,0.2);">
                                        <h3 style="margin-top:0;"><?= addslashes($lang['manage'] ?? 'Manage') ?></h3>
                                        <div id="manageAppInfo" style="margin-bottom:12px; font-size:14px; color:#333;"></div>
                                        <form id="updateStatusForm" method="POST" action="view_applications.php">
                                            <input type="hidden" name="action" value="updateApplicationStatus">
                                            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                                            <input type="hidden" name="application_id" id="modalApplicationId" value="">
                                            <input type="hidden" name="new_status" id="modalNewStatus" value="">
                                            <div style="display:flex; gap:8px; margin-bottom:12px;">
                                                <button type="button" class="status-action" data-status="interviewed" style="flex:1; background:#ffc107; border:none; color:#000; border-radius:6px; padding:8px; cursor:pointer;"><?= addslashes($lang['interviewed'] ?? 'Interviewed') ?></button>
                                                <button type="button" class="status-action" data-status="hired" style="flex:1; background:#28a745; border:none; color:#fff; border-radius:6px; padding:8px; cursor:pointer;"><?= addslashes($lang['hired'] ?? 'Hired') ?></button>
                                                <button type="button" class="status-action" data-status="rejected" style="flex:1; background:#dc3545; border:none; color:#fff; border-radius:6px; padding:8px; cursor:pointer;"><?= addslashes($lang['reject'] ?? 'Rejected') ?></button>
                                            </div>
                                        </form>
                                        <div style="text-align:right;"><button id="closeManageModal" style="background:#ddd; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;"><?= addslashes($lang['cancel'] ?? 'Cancel') ?></button></div>
                                    </div>
                                </div>
                                <div id="confirmHideModal" style="display:none; position:fixed; inset:0; z-index:10001; align-items:center; justify-content:center;">
                                    <div style="position:absolute; inset:0; background:rgba(0,0,0,0.45);"></div>
                                    <div style="position:relative; background:#fff; border-radius:8px; max-width:420px; width:90%; padding:18px; box-shadow:0 8px 24px rgba(0,0,0,0.2); z-index:10002;">
                                        <p id="confirmHideText" style="margin:0 0 14px;"><?= addslashes($lang['confirm_delete_submitted_applications'] ?? 'Delete submitted application?') ?></p>
                                        <form method="POST" action="view_applications.php">
                                            <input type="hidden" name="action" value="hideFromEmployer">
                                            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                                            <input type="hidden" name="application_id" id="confirmHideApplicationId" value="">
                                            <div style="display:flex; justify-content:flex-end; gap:8px;">
                                                <button type="button" id="cancelHideBtn" style="background:#ccc; border:none; padding:8px 12px; border-radius:6px; cursor:pointer;"><?= addslashes($lang['cancel'] ?? 'Cancel') ?></button>
                                                <button type="submit" style="background:#dc3545; border:none; color:#fff; padding:8px 12px; border-radius:6px; cursor:pointer;"><?= addslashes($lang['delete_submitted_applications'] ?? 'Delete') ?></button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            `);

                            var overlay = document.getElementById('appModalOverlay');
                            var manageModal = document.getElementById('manageApplicationModal');
                            var confirmHideModal = document.getElementById('confirmHideModal');

                            function showModal(modal) {
                                modal.style.display = 'flex';
                                overlay.style.display = 'block';
                            }
                            function hideModal(modal) {
                                modal.style.display = 'none';
                                overlay.style.display = 'none';
                            }

                            document.body.addEventListener('click', function (e) {
                                var manageBtn = e.target.closest('.btn-manage-application');
                                if (manageBtn) {
                                    e.preventDefault();
                                    var applicationId = manageBtn.getAttribute('data-application-id');
                                    var appName = manageBtn.getAttribute('data-application-name');
                                    var currentStatus = manageBtn.getAttribute('data-current-status');
                                    document.getElementById('modalApplicationId').value = applicationId;
                                    document.getElementById('manageAppInfo').textContent = '<?= addslashes($lang['application'] ?? 'Application') ?> ' + appName + ' (<?= addslashes($lang['current'] ?? 'current') ?>' + currentStatus + ')';
                                    showModal(manageModal);
                                    return;
                                }

                                var hideBtn = e.target.closest('.hide-application-button');
                                if (hideBtn) {
                                    e.preventDefault();
                                    var applicationId = hideBtn.getAttribute('data-application-id');
                                    document.getElementById('confirmHideApplicationId').value = applicationId;
                                    document.getElementById('confirmHideText').textContent = '<?= addslashes($lang['confirm_delete_submitted_applications'] ?? 'Delete submitted application?') ?>';
                                    showModal(confirmHideModal);
                                    return;
                                }
                            });

                            overlay.addEventListener('click', function () {
                                hideModal(manageModal);
                                hideModal(confirmHideModal);
                            });
                            document.getElementById('closeManageModal').addEventListener('click', function () {
                                hideModal(manageModal);
                            });
                            document.getElementById('cancelHideBtn').addEventListener('click', function () {
                                hideModal(confirmHideModal);
                            });

                            document.querySelectorAll('.status-action').forEach(function (btn) {
                                btn.addEventListener('click', function () {
                                    document.getElementById('modalNewStatus').value = this.getAttribute('data-status');
                                    document.getElementById('updateStatusForm').submit();
                                });
                            });
                        </script>
                    <?php else: ?>
                        <div
                            style="color: #888; text-align: center; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <?= $lang['no_job_id_provided'] ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>


</body>

<footer>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <footer class="footer-new">

        <!-- Hotline -->
        <div class="hotline-section">

            <!-- Box 1 – Người tìm việc -->
            <div class="hotline-box">
                <h3><?= $lang['hotline_job_seekers_title']; ?></h3>

                <div class="hotline-item">
                    <i class="fas fa-headset"></i>
                    <div>
                        <strong><?= $lang['hotline_southern_support']; ?></strong><br>
                        HCM: (028) 7109 440
                    </div>
                </div>

                <div class="hotline-item">
                    <i class="fas fa-headset"></i>
                    <div>
                        <strong><?= $lang['hotline_northern_support']; ?></strong><br>
                        HN: (028) 7109 424
                    </div>
                </div>

                <button class="hotline-btn">
                    <?= $lang['hotline_job_seekers_consult_btn']; ?>
                </button>
            </div>

            <!-- Box 2 – Nhà tuyển dụng -->
            <div class="hotline-box">
                <h3><?= $lang['hotline_employers_title']; ?></h3>

                <div class="hotline-item">
                    <i class="fas fa-headset"></i>
                    <div>
                        <strong><?= $lang['hotline_southern_support']; ?></strong><br>
                        HCM: (029) 7109 440
                    </div>
                </div>

                <div class="hotline-item">
                    <i class="fas fa-headset"></i>
                    <div>
                        <strong><?= $lang['hotline_northern_support']; ?></strong><br>
                        HN: (029) 7109 424
                    </div>
                </div>

                <button class="hotline-btn">
                    <?= $lang['hotline_employers_consult_btn']; ?>
                </button>
            </div>
        </div>

        <!-- Thông tin & liên kết -->
        <div class="footer-container">

            <!-- Cột 1: About -->
            <div class="footer-column">
                <h4><?= $lang['footer_about_us']; ?></h4>
                <p>
                    <strong><?= $lang['footer_company_full']; ?></strong><br>
                    <?= $lang['footer_address_hcm']; ?><br>
                    <?= $lang['footer_address_hn']; ?><br>
                    <?= $lang['footer_license']; ?><br>
                    <?= $lang['footer_phone']; ?><br>
                    <?= $lang['footer_email_job_seeker']; ?><br>
                    <?= $lang['footer_email_employer']; ?>
                </p>
            </div>

            <!-- Cột 2: Information -->
            <div class="footer-column">
                <h4><?= $lang['footer_information']; ?></h4>
                <ul>
                    <li><?= $lang['footer_career_handbook']; ?></li>
                    <li><?= $lang['footer_service_pricing']; ?></li>
                    <li><?= $lang['footer_terms_of_use']; ?></li>
                    <li><?= $lang['footer_privacy_policy']; ?></li>
                    <li><?= $lang['footer_site_map']; ?></li>
                    <li><?= $lang['footer_personal_data_policy']; ?></li>
                    <li><?= $lang['footer_compliance_consent']; ?></li>
                </ul>
            </div>

            <!-- Cột 3: Kết nối & App -->
            <div class="footer-column">
                <h4><?= $lang['footer_connect_with_us']; ?></h4>
                <div class="social-icons">
                    <a href=""><i class="fab fa-facebook-f facebook"></i></a>
                    <a href=""><i class="fab fa-tiktok tiktok"></i></a>
                    <a href=""><i class="fab fa-telegram telegram"></i></a>
                    <a href=""><i class="fab fa-instagram instagram"></i></a>
                </div>

                <h4><?= $lang['footer_download_app']; ?></h4>
                <div class="qr-and-apps">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?data=https://jobhive.vn&size=100x100"
                        alt="QR Code">
                    <div class="app-links">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
                            alt="Google Play">
                        <img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg"
                            alt="App Store">
                    </div>
                </div>
            </div>

        </div>
    </footer>

</html>