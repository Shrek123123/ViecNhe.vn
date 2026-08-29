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
$employerid = $_SESSION['employerid'] ?? 0;
$employername = $_SESSION['usernameemployer'] ?? 'Employer';
$company_id = isset($_GET['company_id']) ? (int) $_GET['company_id'] : 0;
$company_data = null;

if ($employerid > 0 && isset($conn) && $conn->ping()) {
    if ($company_id > 0) {
        $sql = "SELECT * FROM company WHERE id = ? AND employer_id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $company_id, $employerid);
            $stmt->execute();
            $result = $stmt->get_result();
            $company_data = $result->fetch_assoc();
            $stmt->close();
        }
    }

    if (!$company_data) {
        $sql = "SELECT * FROM company WHERE employer_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $employerid);
            $stmt->execute();
            $result = $stmt->get_result();
            $company_data = $result->fetch_assoc();
            $stmt->close();
        }
    }
}

if (!$company_data && $company_id > 0) {
    header('Location: company_profile.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['company_add_title'] ?? 'Register a new company' ?></title>
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

        .dashboard-sidebar button.active {
            background: #b23b3b;
            color: #fff;
            font-weight: bold;
        }
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

        .company-form-card {
            background: #fff;
            border-radius: 18px;
            border: 1px solid #e9e3e3;
            box-shadow: 0 10px 26px rgba(116, 35, 35, 0.08);
            padding: 28px;
            max-width: 760px;
            width: 100%;
            margin: 0 auto;
        }

        .company-form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f0e4e4;
        }

        .company-form-header h2 {
            margin: 0;
            color: #b23b3b;
            font-size: 28px;
        }

        .company-subnav {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .back-link {
            color: #b23b3b;
            text-decoration: none;
            font-weight: 600;
        }

        .company-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .form-input,
        .form-textarea,
        .form-file {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #d9d9d9;
            border-radius: 10px;
            padding: 11px 12px;
            font-size: 14px;
            font-family: Arial, sans-serif;
            background: #fff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-file:focus {
            outline: none;
            border-color: #b23b3b;
            box-shadow: 0 0 0 4px rgba(178, 59, 59, 0.08);
        }

        .form-textarea {
            min-height: 140px;
            resize: vertical;
        }

        .preview-box {
            margin-top: 10px;
            background: #faf7f7;
            border: 1px solid #f0e5e5;
            border-radius: 10px;
            padding: 12px;
        }

        .preview-box img {
            max-width: 220px;
            max-height: 160px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #ddd;
        }

        .helper-text {
            color: #666;
            font-size: 12px;
            margin-top: 4px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid #f0e4e4;
        }

        .secondary-btn,
        .primary-btn {
            border: none;
            border-radius: 10px;
            padding: 11px 20px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.16s ease, transform 0.16s ease;
        }

        .secondary-btn {
            background: #f4f4f4;
            color: #333;
        }

        .primary-btn {
            background: linear-gradient(135deg, #b23b3b, #8a2f2f);
            color: #fff;
        }

        .secondary-btn:hover,
        .primary-btn:hover {
            opacity: 0.97;
            transform: translateY(-1px);
        }

        /* ===== CONTENT ===== */
        .dashboard-content {
            flex: 1;
            background: #fff;
            padding: 20px;
        }

        /* ===== POSTING OPTIONS BUTTONS===== */

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
                <button onclick="window.location.href='my_job_posts.php'"><?= $lang['employerprofile_myjobposts'] ?></button>
                <button onclick="window.location.href='company_profile.php'" class="<?= ($currentPage == 'company_profile.php') ? 'active' : '' ?>"><?= $lang['employerprofile_companyprofile'] ?></button>
                <button><?= $lang['employerprofile_changeinfo'] ?></button>

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
                <div style="padding: 20px; display: flex; justify-content: center; align-items: flex-start; min-height: 400px;">
                    <div style="max-width: 600px; width: 100%;">
                    
                    <?php
                    // Form thêm mới công ty phải luôn rỗng, không kế thừa dữ liệu cũ.
                    ?>
                    <div class="company-form-card">
                        <div class="company-form-header">
                            <h2><?= $lang['company_add_title'] ?? 'Thêm công ty mới' ?></h2>
                            <div class="company-subnav">
                                <a href="../companyprofile.php?company_id=<?= (int) ($company_data['id'] ?? 0) ?>" class="back-link"><?= $lang['detail_company_link'] ?? 'Quay lại hồ sơ công ty' ?></a>
                            </div>
                        </div>

                        <form id="companyProfileForm" enctype="multipart/form-data">
                            <div class="company-form-grid">
                                <div class="form-group">
                                    <label for="companyName" class="form-label"><?= $lang['company_name_label'] ?></label>
                                    <input type="text" id="companyName" name="company_name" class="form-input" value="">
                                </div>

                                <div class="form-group">
                                    <label for="companyAddress" class="form-label"><?= $lang['company_address_label'] ?></label>
                                    <input type="text" id="companyAddress" name="company_address" class="form-input" value="">
                                </div>

                                <div class="form-group full-width">
                                    <label for="industry" class="form-label"><?= $lang['industry_label'] ?></label>
                                    <select id="industry" name="industry" class="form-input">
                                        <option value=""><?= $lang['chon_nganh_nghe'] ?></option>
                                        <option value="kinh_doanh_ban_hang"><?= $lang['kinh_doanh_ban_hang'] ?></option>
                                        <option value="marketing_truyen_thong"><?= $lang['marketing_truyen_thong'] ?></option>
                                        <option value="tai_chinh_ke_toan_ngan_hang"><?= $lang['tai_chinh_ke_toan_ngan_hang'] ?></option>
                                        <option value="cong_nghe_thong_tin"><?= $lang['cong_nghe_thong_tin'] ?></option>
                                        <option value="nhan_su"><?= $lang['nhan_su'] ?></option>
                                        <option value="hanh_chinh_van_phong"><?= $lang['hanh_chinh_van_phong'] ?></option>
                                        <option value="ky_thuat"><?= $lang['ky_thuat'] ?></option>
                                        <option value="san_xuat_van_hanh_day_chuyen"><?= $lang['san_xuat_van_hanh_day_chuyen'] ?></option>
                                        <option value="van_tai_logistics"><?= $lang['van_tai_logistics'] ?></option>
                                        <option value="bat_dong_san"><?= $lang['bat_dong_san'] ?></option>
                                        <option value="bao_hiem"><?= $lang['bao_hiem'] ?></option>
                                        <option value="xay_dung_kien_truc"><?= $lang['xay_dung_kien_truc'] ?></option>
                                        <option value="y_te_duoc_pham"><?= $lang['y_te_duoc_pham'] ?></option>
                                        <option value="giao_duc_dao_tao"><?= $lang['giao_duc_dao_tao'] ?></option>
                                        <option value="du_lich_khach_san_nha_hang"><?= $lang['du_lich_khach_san_nha_hang'] ?></option>
                                        <option value="luat"><?= $lang['luat'] ?></option>
                                        <option value="thiet_ke_nghe_thuat"><?= $lang['thiet_ke_nghe_thuat'] ?></option>
                                        <option value="lao_dong_pho_thong"><?= $lang['lao_dong_pho_thong'] ?></option>
                                        <option value="nong_nghiep"><?= $lang['nong_nghiep'] ?></option>
                                        <option value="food_beverage"><?= $lang['food_beverage'] ?></option>
                                        <option value="nha_nuoc_cong_vu"><?= $lang['nha_nuoc_cong_vu'] ?></option>
                                        <option value="khac"><?= $lang['khac'] ?></option>
                                    </select>
                                    <input type="hidden" name="jobCategoryHidden" id="jobCategoryHidden">
                                    <div id="custom_input_wrapper2" style="display: none; margin-top: 8px;">
                                        <input type="text" id="custom_job_category" name="custom_job_category" placeholder="<?= $lang['custom_jobcategory_placeholder']; ?>">
                                    </div>
                                </div>

                                <div class="form-group full-width">
                                    <label for="information" class="form-label"><?= $lang['company_information_label'] ?></label>
                                    <textarea id="information" name="information" class="form-textarea"></textarea>
                                </div>

                                <div class="form-group full-width">
                                    <label for="companyLogo" class="form-label"><?= $lang['company_logo_label'] ?></label>
                                    <input type="file" id="companyLogo" name="company_logo" accept=".jpg,.jpeg,.png,.gif,.webp" class="form-file">
                                    <div id="companyLogoPreview" class="preview-box" style="display:none;"></div>
                                    <small class="helper-text"><?= $lang['supported_image_formats'] ?></small>
                                </div>

                                <div class="form-group full-width">
                                    <label for="bannerLogo" class="form-label"><?= $lang['banner_logo_label'] ?></label>
                                    <input type="file" id="bannerLogo" name="banner_logo" accept=".jpg,.jpeg,.png,.gif,.webp" class="form-file">
                                    <div id="bannerLogoPreview" class="preview-box" style="display:none;"></div>
                                    <small class="helper-text"><?= $lang['supported_image_formats'] ?></small>
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="company_profile.php" class="secondary-btn"><?= $lang['cancel_button'] ?? 'Cancel' ?></a>
                                <button type="submit" class="primary-btn"><?= $lang['company_update_button'] ?></button>
                            </div>
                        </form>
                    </div>

                    <script>
                        const allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        const maxImageSize = 5 * 1024 * 1024;

                        function attachImagePreview(inputId, previewId) {
                            const input = document.getElementById(inputId);
                            const preview = document.getElementById(previewId);
                            if (!input || !preview) return;

                            input.addEventListener('change', function () {
                                const file = this.files && this.files[0];
                                if (!file) {
                                    preview.style.display = 'none';
                                    preview.innerHTML = '';
                                    return;
                                }

                                if (!allowedImageTypes.includes(file.type)) {
                                    alert('<?php echo addslashes($lang['invalid_file_format'] ?? 'Only JPG, JPEG, PNG, GIF, WEBP files are allowed.'); ?>');
                                    this.value = '';
                                    preview.style.display = 'none';
                                    preview.innerHTML = '';
                                    return;
                                }

                                if (file.size > maxImageSize) {
                                    alert('<?php echo addslashes($lang['file_too_large'] ?? 'File size must not exceed 5MB.'); ?>');
                                    this.value = '';
                                    preview.style.display = 'none';
                                    preview.innerHTML = '';
                                    return;
                                }

                                const reader = new FileReader();
                                reader.onload = function (event) {
                                    preview.innerHTML = '<img src="' + event.target.result + '" alt="Preview" style="max-width:220px;max-height:160px;object-fit:cover;border-radius:8px;">';
                                    preview.style.display = 'block';
                                };
                                reader.readAsDataURL(file);
                            });
                        }

                        attachImagePreview('companyLogo', 'companyLogoPreview');
                        attachImagePreview('bannerLogo', 'bannerLogoPreview');

                        document.getElementById('companyProfileForm').onsubmit = function(e) {
                            e.preventDefault();

                            const formData = new FormData(this);
                            const filesToCheck = ['company_logo', 'banner_logo'];
                            let valid = true;

                            filesToCheck.forEach(function (fieldName) {
                                const file = formData.get(fieldName);
                                if (!file || !(file instanceof File) || file.size === 0) return;
                                if (!allowedImageTypes.includes(file.type)) {
                                    valid = false;
                                    alert('<?php echo addslashes($lang['invalid_file_format'] ?? 'Only JPG, JPEG, PNG, GIF, WEBP files are allowed.'); ?>');
                                }
                                if (file.size > maxImageSize) {
                                    valid = false;
                                    alert('<?php echo addslashes($lang['file_too_large'] ?? 'File size must not exceed 5MB.'); ?>');
                                }
                            });

                            if (!valid) return;
                            
                            fetch('add_company.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert('<?php echo $lang['company_profile_add_success']; ?>');
                                    window.location.href = 'company_profile.php';
                                } else {
                                    alert('<?php echo $lang['company_profile_add_error']; ?>: ' + (data.message || '<?php echo $lang['unknown_error']; ?>'));
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('<?php echo $lang['company_profile_add_error']; ?>');
                            });
                        }
                    </script>
                    <?php
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
</html>