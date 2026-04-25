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

require_once '../config.php';

$currentPage = basename($_SERVER['PHP_SELF']);

$employername = $_SESSION['usernameemployer'] ?? 'Employer';
$employer_id = $_SESSION['employerid'] ?? 0;
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

        .status-new {
            font-size: 11px;
            color: #17a2b8;
            font-weight: bold;
        }

        .status-viewed {
            font-size: 11px;
            color: #28a745;
            font-weight: bold;
        }

        .status-hired {
            font-size: 11px;
            color: #20c997;
            font-weight: bold;
        }

        .status-interviewed {
            font-size: 11px;
            color: #6f42c1;
            font-weight: bold;
        }

        .status-rejected {
            font-size: 11px;
            color: #dc3545;
            font-weight: bold;
        }

        .status-default {
            font-size: 11px;
            color: #6c757d;
            font-weight: bold;
        }

        .view-more {
            font-size: 12px;
            text-align: center;
            background: #fff;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: underline;
        }

        /* ===== CONTENT ===== */
        .dashboard-content {
            flex: 1;
            background: #fff;
        }

        /* button post job */
        #btn-postjob.active {
            background: #b23b3b;
            color: #fff;
            font-weight: bold;
        }

        #btn-myjobposts.active {
            background: #b23b3b;
            color: #fff;
            font-weight: bold;
        }



        /* ===== POSTING OPTIONS BUTTONS===== */
        .posting-options {
            display: grid;
            grid-template-columns: repeat(2, 260px);
            /* 2 cột cố định */
            justify-content: center;
            /* căn giữa toàn bộ khối */
            gap: 20px;
        }

        .post-btn {
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
                    onclick="window.location.href='add_job_post.php'"><?= $lang['employerprofile_postajob'] ?></button>
                <button id="btn-myjobposts" onclick="window.location.href='my_job_posts.php'"
                    class="<?= ($currentPage == 'my_job_posts.php') ? 'active' : '' ?>"><?= $lang['employerprofile_myjobposts'] ?></button>
                <button onclick="window.location.href='company_profile.php'" class="<?= ($currentPage == 'company_profile.php') ? 'active' : '' ?>"><?= $lang['employerprofile_companyprofile'] ?></button>
                <button onclick="window.location.href='change_employer_info.php'" class="<?= ($currentPage == 'change_employer_info.php') ? 'active' : '' ?>"><?= $lang['employerprofile_changeinfo'] ?></button>

                <?php
                // Tính tổng số đơn ứng tuyển cho employer hiện tại
                $total_applications = 0;
                $employerid = $_SESSION['employerid'] ?? 0;
                if ($employerid > 0 && isset($conn) && $conn->ping()) {
                    $sql_total = "SELECT COUNT(a.id) AS total FROM application a JOIN job j ON a.job_id = j.id WHERE j.posted_by_employer_id = ? AND (a.hidden_from_employer = 0 OR a.hidden_from_employer IS NULL)";
                    $stmt_total = $conn->prepare($sql_total);
                    $stmt_total->bind_param("i", $employerid);
                    $stmt_total->execute();
                    $result_total = $stmt_total->get_result();
                    if ($row_total = $result_total->fetch_assoc()) {
                        $total_applications = $row_total['total'];
                    }
                    $stmt_total->close();
                }
                ?>

                <h3><?= $lang['employerprofile_applicantprofiles'] ?> <span style="color: #b23b3b; font-weight: bold;">(<?php echo $total_applications; ?>)</span></h3>

                <?php
                // Lấy 2 đơn ứng tuyển đầu tiên từ database
                $applications = [];
                $employerid = $_SESSION['employerid'] ?? 0;
                if ($employerid > 0 && isset($conn) && $conn->ping()) {
                    $sql_app = "SELECT a.fullname, a.status FROM application a JOIN job j ON a.job_id = j.id WHERE j.posted_by_employer_id = ? AND (a.hidden_from_employer = 0 OR a.hidden_from_employer IS NULL) ORDER BY a.id DESC LIMIT 2";
                    $stmt_app = $conn->prepare($sql_app);
                    $stmt_app->bind_param("i", $employerid);
                    $stmt_app->execute();
                    $result_app = $stmt_app->get_result();
                    while ($row_app = $result_app->fetch_assoc()) {
                        $applications[] = $row_app;
                    }
                    $stmt_app->close();
                }
                ?>

                <!-- Applicant preview (dynamic) -->
                <?php foreach ($applications as $app): 
                    $status_raw = strtolower($app['status']);
                    
                    // Map status với CSS class và language key
                    $status_mapping = [
                        'pending' => ['class' => 'status-new', 'lang_key' => 'application_pending'],
                        'viewed' => ['class' => 'status-viewed', 'lang_key' => 'application_viewed'],
                        'hired' => ['class' => 'status-hired', 'lang_key' => 'application_hired'],
                        'interviewed' => ['class' => 'status-interviewed', 'lang_key' => 'application_interviewed'],
                        'rejected' => ['class' => 'status-rejected', 'lang_key' => 'application_rejected'],
                    ];
                    
                    $config = $status_mapping[$status_raw] ?? ['class' => 'status-default', 'lang_key' => $status_raw];
                    $status_class = $config['class'];
                    $status_icon = isset($lang[$config['lang_key']]) ? $lang[$config['lang_key']] : ucfirst($status_raw);
                ?>
                    <div class="applicant-card">
                        <img src="https://via.placeholder.com/28">
                        <div class="applicant-info">
                            <?= htmlspecialchars($app['fullname']) ?><br>
                            <span class="<?= $status_class ?>"><?= $status_icon ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="view-more" onclick="window.location.href='view_more_list_application.php'" style="cursor: pointer;"><?= $lang['view_more'] ?></div>
            </div>

            <!-- CONTENT -->
            <div class="dashboard-content">
                <!-- Content load sau -->

                <div style="padding: 30px; border-radius: 8px;">
                    <h2 style="color: #b23b3b; margin-bottom: 20px;"><?= $lang['my_job_post'] ?></h2>
                    <div style="display: flex; flex-direction: column; gap: 18px;">
                        <?php
                        if ($employer_id > 0 && isset($conn) && $conn->ping()) {
                            $sql = "SELECT * FROM job WHERE posted_by_employer_id = ? ORDER BY created_at DESC";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $employer_id);
                            $stmt->execute();
                            $result = $stmt->get_result();


                            if ($result->num_rows === 0) {
                                echo '<div style="color: #888; text-align: center; padding: 20px;">' . $lang['no_jobs_found'] . '</div>';
                            }

                            while ($row = $result->fetch_assoc()):
                                // Xử lý dữ liệu hiển thị
                                $job_id = htmlspecialchars($row['id']);
                                // Lấy đường dẫn từ DB (ví dụ: uploads/abc.jpg)
                                $db_logo = $row['company_logo'];

                                // Nếu không trống, thêm ../ để thoát khỏi thư mục employerprofile
                                if (!empty($db_logo)) {
                                    $logo = htmlspecialchars($db_logo);
                                } else {
                                    $logo = 'https://placehold.co/120x70/cccccc/333333?text=No+Logo';
                                }
                                // Tính toán thời gian
                                $created_at = new DateTime($row['created_at']);
                                $expire_at = (clone $created_at)->modify("+" . intval($row['post_duration']) . " days");
                                $now_dt = new DateTime();
                                $days_left = $now_dt < $expire_at ? $now_dt->diff($expire_at)->days : 0;

                                // Tính x phút/giờ trước
                                $last_updated = strtotime($row['last_updated_at'] ?? $row['created_at']);
                                $diff = time() - $last_updated;
                                if ($diff < 60)
                                    $updated_str = sprintf($lang['updated_now'], $diff);
                                elseif ($diff < 3600)
                                    $updated_str = sprintf($lang['updated_minutes'], floor($diff / 60));
                                elseif ($diff < 86400)
                                    $updated_str = sprintf($lang['updated_hours'], floor($diff / 3600));
                                else
                                    $updated_str = sprintf($lang['updated_days'], floor($diff / 86400));

                                // Xử lý Trạng thái
                                $job_status_raw = $row['status'];
                                $status_colors = ['pending_review' => '#ffc107', 'approved' => '#28a745', 'rejected' => '#dc3545'];
                                $status_color = $status_colors[$job_status_raw] ?? '#6c757d';
                                $job_status_text = $lang[$job_status_raw] ?? $job_status_raw;

                                ?>
                                <div
                                    style="background: #fff; border: 1px solid #ddd; border-radius: 10px; padding: 18px; display: flex; align-items: flex-start; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                    <img src="<?= $logo ?>" alt="logo"
                                        style="width: 120px; height: 70px; border-radius: 8px; object-fit: cover; margin-right: 18px; border: 1px solid #eee;">

                                    <div style="flex: 1;">
                                        <div style="font-weight: bold; font-size: 18px; margin-bottom: 4px;">
                                            <a href="../jobdetail.php?id=<?= $job_id ?>"
                                                style="color: #333; text-decoration: none;"><?= htmlspecialchars($row['job_title']) ?></a>
                                        </div>
                                        <div style="color: #888; font-size: 13px; margin-bottom: 8px;">
                                            <?= htmlspecialchars($row['company_name']) ?>
                                        </div>

                                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                            <span
                                                style="background: #f8f8f8; color: #b23b3b; border-radius: 6px; padding: 2px 10px; font-size: 12px;"><?= htmlspecialchars($row['job_location']) ?></span>
                                            <span
                                                style="background: #f8f8f8; color: #b23b3b; border-radius: 6px; padding: 2px 10px; font-size: 12px;">
                                                <?= $days_left > 0 ? sprintf($lang['job_days_left'], $days_left) : $lang['job_closed'] ?>
                                            </span>
                                            <span
                                                style="background: #f8f8f8; color: #888; border-radius: 6px; padding: 2px 10px; font-size: 12px;"><?= $updated_str ?></span>
                                        </div>

                                        <div style="color: #b23b3b; font-weight: bold; font-size: 16px; margin-bottom: 5px;">
                                            <?= htmlspecialchars($row['salary']) ?>
                                        </div>
                                        <div style="font-size: 14px;">
                                            <?= $lang['status'] ?>: <span
                                                style="font-weight: bold; color: <?= $status_color ?>;"><?= $job_status_text ?></span>
                                        </div>
                                    </div>

                                    <div
                                        style="display: flex; flex-direction: column; gap: 8px; margin-left: 18px; align-items: flex-end;">
                                        <form action="jobposts_edit.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="id_to_edit" value="<?php echo $job_id; ?>">
                                            <button type="submit"
                                                style="background: #d90000; color: #fff; border: none; border-radius: 6px; padding: 6px 18px; font-size: 13px; cursor: pointer;">
                                                <?= $lang['edit'] ?>
                                            </button>
                                        </form>
                                        <form style="display: inline;" onsubmit="deleteJobPost(event, <?= $job_id ?>)">
                                            <button type="submit"
                                                style="background: #fff; color: #d90000; border: 1px solid #d90000; border-radius: 6px; padding: 6px 18px; font-size: 13px; cursor: pointer;">
                                                <?= $lang['delete'] ?>
                                            </button>
                                        </form>
                                        <form action="view_applications.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                                            <button type="submit"
                                                style="background: #b23b3b; color: #fff; border: none; border-radius: 6px; padding: 6px 18px; font-size: 13px; cursor: pointer; text-decoration: none; display: inline-block;">
                                                <?= $lang['check_submitted_applications'] ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php
                            endwhile;
                        } else {
                            echo '<div style="color: #888; text-align: center; padding: 20px;">Vui lòng đăng nhập để xem bài đăng.</div>';
                        }
                        ?>
                    </div>
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

    <script>
        function deleteJobPost(event, jobId) {
            event.preventDefault();

            if (!confirm("<?php echo $lang['confirm-deletejobpost']; ?>")) return;

            fetch('delete_job_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'job_id=' + jobId
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast("<?php echo $lang['delete_job_post_success']; ?>", "success");

                        // 👉 reload sau 2 giây
                        setTimeout(() => {
                            location.reload();
                        }, 2000);

                    } else {
                        showToast(data.message, "error");
                    }
                })
                .catch(err => {
                    console.error(err);
                    showToast("Lỗi server!", "error");
                });
        }


        // 👉 fix lỗi showToast undefined
        function showToast(message, type) {
            // overlay (nền mờ)
            let overlay = document.createElement("div");
            overlay.style.position = "fixed";
            overlay.style.top = "0";
            overlay.style.left = "0";
            overlay.style.width = "100%";
            overlay.style.height = "100%";
            overlay.style.background = "rgba(0,0,0,0.4)";
            overlay.style.display = "flex";
            overlay.style.alignItems = "center";
            overlay.style.justifyContent = "center";
            overlay.style.zIndex = "9999";

            // box thông báo
            let box = document.createElement("div");
            box.innerText = message;

            box.style.background = "#fff";
            box.style.padding = "20px 40px";
            box.style.borderRadius = "10px";
            box.style.fontSize = "16px";
            box.style.fontWeight = "500";
            box.style.boxShadow = "0 4px 20px rgba(0,0,0,0.2)";
            box.style.textAlign = "center";

            if (type === "success") {
                box.style.color = "#28a745";
            } else {
                box.style.color = "#dc3545";
            }

            overlay.appendChild(box);
            document.body.appendChild(overlay);

            // tự biến mất sau 2s
            setTimeout(() => {
                overlay.remove();
            }, 2000);
        }
    </script>

</html>