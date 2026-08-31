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

?>
<?php
require_once '../config.php';
if (!isset($_SESSION['usernameemployer'])) {
  echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      var openBtn = document.getElementById('openModalBtn');
      if (openBtn) {
        openBtn.onclick = function() {
          window.location.href = 'employerlogin.php';
        }
      }
    });
  </script>";
}

$employerid = isset($_SESSION['employerid']) ? (int) $_SESSION['employerid'] : 0;
$company_options = [];
if ($employerid > 0 && isset($conn)) {
    $company_options_stmt = $conn->prepare("SELECT id, company_name, company_address, industry, company_size, company_logo FROM company WHERE employer_id = ? ORDER BY company_name ASC");
    if ($company_options_stmt) {
        $company_options_stmt->bind_param('i', $employerid);
        $company_options_stmt->execute();
        $company_options_result = $company_options_stmt->get_result();
        while ($company_row = $company_options_result->fetch_assoc()) {
            $company_options[] = [
                'id' => (int) $company_row['id'],
                'company_name' => $company_row['company_name'] ?? '',
                'company_address' => $company_row['company_address'] ?? '',
                'industry' => $company_row['industry'] ?? '',
                'company_size' => $company_row['company_size'] ?? '',
                'company_logo' => $company_row['company_logo'] ?? '',
            ];
        }
        $company_options_stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $selected_company_id = isset($_POST['selected_company_id']) ? (int) $_POST['selected_company_id'] : 0;
  $company = null;
  if ($selected_company_id > 0 && isset($conn)) {
      $selected_company_stmt = $conn->prepare("SELECT id, company_name, company_address, industry, company_size, company_logo FROM company WHERE id = ? AND employer_id = ? LIMIT 1");
      if ($selected_company_stmt) {
          $selected_company_stmt->bind_param('ii', $selected_company_id, $employerid);
          $selected_company_stmt->execute();
          $selected_company_result = $selected_company_stmt->get_result();
          $company = $selected_company_result->fetch_assoc();
          $selected_company_stmt->close();
      }
  }

  $companyName = $company['company_name'] ?? '';
  $companyAddress = $company['company_address'] ?? '';
  $companySize = $company['company_size'] ?? '';
  $industry = $company['industry'] ?? '';
  $companyLogo = $company['company_logo'] ?? '';
  $jobRequirementText = $_POST['jobRequirementText'];
  $jobBenefit = $_POST['jobBenefit'];
  $jobTitle = $_POST['jobTitle'];
  $jobDescription = $_POST['jobDescription'];
  $jobLocation = $_POST['jobLocation'];
  $salary = $_POST['salary'];
  $post_duration = 60;
  $contactEmail = $_POST['contactEmail'];
  $contactPhone = $_POST['contactPhone'];
  $jobType = $_POST['jobType'];
  $jobCategory = $_POST['jobCategory'];
  $requiredcertification = $_POST['requiredcertification'];
  $jobExperience = $_POST['jobExperience'];
  $jobPosition = $_POST['jobPosition'] ?? '';
  $noEmplpoyeeNeeeded = $_POST['noEmplpoyeeNeeded'] ?? 0;

  $jobDetailedLocation = $_POST['jobDetailedLocation'] ?? '';
  $jobLocationDistrict = $_POST['jobLocationDistrict'] ?? '';
  if (!isset($_SESSION['employerid'])) {
    echo "<script>alert('You must be logged in as an employer to post a job.');</script>";
    exit;
  }
  $employerid = $_SESSION['employerid'];

  // Use the selected company's existing logo instead of uploading a new company logo here.
  $companyLogo = trim((string) ($company['company_logo'] ?? ''));
  if ($companyLogo === '') {
    $companyLogo = 'uploads/default.png';
  }
  $companyLogo = preg_replace('#^(?:\./)+#', '', $companyLogo);
  $companyLogo = preg_replace('#^(?:\.\./)+#', '', $companyLogo);
  $companyLogo = preg_replace('#^/+#', '', $companyLogo);

  // Validate required fields
  // $requiredFields = [
  //   'companyName' => $companyName,
  //   'jobTitle' => $jobTitle,
  //   'jobDescription' => $jobDescription,
  //   'jobLocation' => $jobLocation,
  //   'salary' => $salary,
  //   'contactEmail' => $contactEmail,
  //   'contactPhone' => $contactPhone,
  //   'jobType' => $jobType,
  //   'jobCategory' => $jobCategory,
  //   'requiredcertification' => $requiredcertification,
  //   'jobExperience' => $jobExperience,
  //   'post_duration' => $post_duration,
  //   'jobRequirementText' => $jobRequirementText,
  //   'jobBenefit' => $jobBenefit,
  //   'companySize' => $companySize,
  //   'jobPosition' => $jobPosition,
  //   'noEmplpoyeeNeeded' => $noEmplpoyeeNeeeded,
  //   'jobDetailedLocation' => $jobDetailedLocation,
  //   'jobLocationDistrict' => $jobLocationDistrict
  // ];

  // foreach ($requiredFields as $field => $value) {
  //   if (empty($value)) {
  //     echo "<script>alert('Please fill out all required information.');</script>";
  //   }
  // }
  $jobStatus = 'pending_review'; // Mặc định là chờ duyệt

  // Database connection and insertion logic here
  // Lấy số thứ tự bài đăng mới nhất của employer này
  $sql = "SELECT MAX(job_post_number_by_employer) AS last_number FROM job WHERE posted_by_employer_id = ?";
  $stmt_number = $conn->prepare($sql);
  if (!$stmt_number) {
    echo "<script>alert('Error preparing statement for job number: {$conn->error}');</script>";
    exit;
  }
  $stmt_number->bind_param("i", $employerid);
  $stmt_number->execute();
  $result = $stmt_number->get_result();
  $row = $result ? $result->fetch_assoc() : null;
  $last_number = $row && isset($row['last_number']) ? (int)$row['last_number'] : 0;
  $stmt_number->close();

  $new_job_number = $last_number + 1;

  $company_id = $selected_company_id > 0 ? $selected_company_id : null;
  if (!$company_id) {
    echo "<script>alert('Vui lòng chọn công ty đã đăng ký để tạo bài đăng.'); window.history.back();</script>";
    exit;
  }

  $job_insert_sql = "INSERT INTO job (
    posted_by_employer_id,
    company_id,
    job_post_number_by_employer,
    company_name,
    job_title,
    job_description,
    job_location,
    salary,
    post_duration,
    contact_email,
    contact_phone,
    job_type,
    job_category,
    required_certification,
    job_experience,
    company_logo,
    job_requirement,
    job_benefit,
    company_size,
    no_employee_needed,
    job_position,
    job_detailed_location,
    job_location_district,
    status
  ) VALUES (
    " . (int)$employerid . ",
    " . (int)$company_id . ",
    " . (int)$new_job_number . ",
    '" . $conn->real_escape_string($companyName) . "',
    '" . $conn->real_escape_string($jobTitle) . "',
    '" . $conn->real_escape_string($jobDescription) . "',
    '" . $conn->real_escape_string($jobLocation) . "',
    '" . $conn->real_escape_string($salary) . "',
    " . (int)$post_duration . ",
    '" . $conn->real_escape_string($contactEmail) . "',
    '" . $conn->real_escape_string($contactPhone) . "',
    '" . $conn->real_escape_string($jobType) . "',
    '" . $conn->real_escape_string($jobCategory) . "',
    '" . $conn->real_escape_string($requiredcertification) . "',
    '" . $conn->real_escape_string($jobExperience) . "',
    '" . $conn->real_escape_string($companyLogo) . "',
    '" . $conn->real_escape_string($jobRequirementText) . "',
    '" . $conn->real_escape_string($jobBenefit) . "',
    '" . $conn->real_escape_string($companySize) . "',
    " . (int)$noEmplpoyeeNeeeded . ",
    '" . $conn->real_escape_string($jobPosition) . "',
    '" . $conn->real_escape_string($jobDetailedLocation) . "',
    '" . $conn->real_escape_string($jobLocationDistrict) . "',
    '" . $conn->real_escape_string($jobStatus) . "'
  )";

  if ($conn->query($job_insert_sql) === TRUE) {
    echo "<script>alert('{$lang['job_post_is_saved_for_review']}'); window.location.href = window.location.href;</script>";
  } else {
    echo "<script>alert('Error posting job: {$conn->error}');</script>";
  }

  $conn->close();
  exit;
}
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
            padding: 20px;
        }

        /* button post job */
        #btn-postjob.active {
            background: #b23b3b;
            color: #fff;
            font-weight: bold;
        }

        /* ===== FORM ===== */
        .job-form {
            padding: 5px 10px;
        }

        .form-title {
            text-align: center;
            margin-bottom: 18px;
            font-size: 22px;
            font-weight: 700;
            color: #1e40af;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }

        .form-column {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;

        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 14px;
        }

        .readonly-field {
            background-color: #e5e7eb !important;
            color: #4b5563 !important;
            border-color: #d1d5db !important;
            cursor: not-allowed;
        }

        .readonly-field:focus {
            outline: none;
            box-shadow: none;
        }

        .form-group textarea {
            min-height: 70px;
            resize: vertical;
        }

        .submit-wrapper {
            text-align: center;
            margin-top: 20px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #2563eb, #1e3a8a);
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
        }

        @media (max-width: 900px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
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
                <button id="btn-postjob" onclick="window.location.href='add_job_post.php'"
                    class="<?= ($currentPage == 'add_job_post_60_day.php') ? 'active' : '' ?>"><?= $lang['employerprofile_postajob'] ?></button>
                <button onclick="window.location.href='my_job_posts.php'"><?= $lang['employerprofile_myjobposts'] ?></button>
                <button onclick="window.location.href='company_profile.php'"><?= $lang['employerprofile_companyprofile'] ?></button>
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
                <div id="myModal" class="modal">
                    <div class="modal-content">
                        <h2 class="form-title">
                            <?= $lang['60_day_posting_option']; ?>
                        </h2>

                        <form method="POST" enctype="multipart/form-data" class="job-form">
                            <input type="hidden" name="MAX_FILE_SIZE" value="1000000">

                            <div class="form-grid">

                                <!-- CỘT TRÁI -->
                                <div class="form-column">

                                    <!-- Select company -->
                                    <div class="form-group">
                                        <label><?= $lang['choose_company']; ?></label>
                                        <select id="company_select" name="selected_company_id" required>
                                            <option value=""><?= $lang['post_job_form_company_name_select'] ?? '-- Chọn công ty --'; ?></option>
                                            <?php foreach ($company_options as $company_option): ?>
                                                <option value="<?= (int) $company_option['id']; ?>"
                                                    data-company_name="<?= htmlspecialchars($company_option['company_name'], ENT_QUOTES); ?>"
                                                    data-company_address="<?= htmlspecialchars($company_option['company_address'], ENT_QUOTES); ?>"
                                                    data-company_size="<?= htmlspecialchars($company_option['company_size'], ENT_QUOTES); ?>"
                                                    data-company_industry="<?= htmlspecialchars($company_option['industry'], ENT_QUOTES); ?>"
                                                    data-company_logo="<?= htmlspecialchars($company_option['company_logo'], ENT_QUOTES); ?>">
                                                    <?= htmlspecialchars($company_option['company_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Company Name (readonly display) -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_company_name']; ?></label>
                                        <input type="text" id="company_name_display" class="readonly-field" readonly placeholder="<?= $lang['required']; ?>">
                                    </div>

                                    <!-- Company Address (readonly display) -->
                                    <div class="form-group">
                                        <label><?= $lang['company_address']; ?></label>
                                        <input type="text" id="company_address_display" class="readonly-field" readonly placeholder="<?= $lang['required']; ?>">
                                    </div>

                                    <!-- Company Size (readonly display) -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_company_size']; ?></label>
                                        <input type="text" id="company_size_display" class="readonly-field" readonly placeholder="<?= $lang['required']; ?>">
                                    </div>

                                    <!-- Job Title -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_job_title']; ?></label>
                                        <input type="text" name="jobTitle"
                                            placeholder="<?= $lang['job_title_placeholder']; ?>" required>
                                    </div>

                                    <!-- Job Description -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_job_description']; ?></label>
                                        <textarea name="jobDescription"
                                            placeholder="<?= $lang['job_description_placeholder']; ?>"
                                            required></textarea>
                                    </div>

                                    <!-- Job Location -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_job_location']; ?></label>
                                        <input type="text" name="jobLocation"
                                            placeholder="<?= $lang['job_city_placeholder']; ?>" required>
                                    </div>

                                    <!-- Salary -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_salary']; ?></label>
                                        <input type="text" name="salary" placeholder="<?= $lang['required']; ?>"
                                            required>
                                    </div>

                                    <!-- Gender -->
                                    <div class="form-group">
                                        <label><?= $lang['employee_gender']; ?></label>
                                        <select name="employee_gender" required>
                                            <option value="Nam"><?= $lang['employee_gender_male']; ?></option>
                                            <option value="Nữ"><?= $lang['employee_gender_female']; ?></option>
                                            <option value="Not_required"><?= $lang['employee_gender_not_required']; ?>
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Contact Phone -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_contact_phone']; ?></label>
                                        <input type="text" name="contactPhone" placeholder="<?= $lang['required']; ?>"
                                            required>
                                    </div>

                                    <!-- Number Needed -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_no_employee_needed']; ?></label>
                                        <input type="number" name="noEmplpoyeeNeeded"
                                            placeholder="<?= $lang['required']; ?>" min="0"
                                            oninput="this.value = Math.abs(this.value)">
                                    </div>



                                </div>


                                <!-- CỘT PHẢI -->
                                <div class="form-column">

                                    <!-- Detailed Location -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_job_detailed_location']; ?></label>
                                        <input type="text" name="jobDetailedLocation"
                                            placeholder="<?= $lang['job_detailedlocation_placeholder']; ?>">
                                    </div>

                                    <!-- Contact Email -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_contact_email']; ?></label>
                                        <input type="email" name="contactEmail" placeholder="<?= $lang['required']; ?>"
                                            required>
                                    </div>

                                    <!-- District
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_job_location_district']; ?></label>
                                        <input type="text" name="jobLocationDistrict"
                                            placeholder="<?= $lang['optional']; ?>">
                                    </div> -->

                                    <!-- Job Type -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_job_type']; ?></label>
                                        <select name="jobType" required>
                                            <option value="Full-time">Full-time</option>
                                            <option value="Part-time">Part-time</option>
                                            <option value="Internship">Internship</option>
                                            <option value="Freelance">Freelance</option>
                                            <option value="Remote">Remote</option>
                                        </select>
                                    </div>

                                    <!-- Job Category -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_job_category']; ?></label>

                                        <select id="jobCategory" name="jobCategory" onchange="toggleCustomInput2()"
                                            required>
                                            <option value=""><?= $lang['chon_nganh_nghe'] ?></option>
                                            <option value="kinh_doanh_ban_hang"><?= $lang['kinh_doanh_ban_hang'] ?>
                                            </option>
                                            <option value="marketing_truyen_thong">
                                                <?= $lang['marketing_truyen_thong'] ?>
                                            </option>
                                            <option value="tai_chinh_ke_toan_ngan_hang">
                                                <?= $lang['tai_chinh_ke_toan_ngan_hang'] ?>
                                            </option>
                                            <option value="cong_nghe_thong_tin"><?= $lang['cong_nghe_thong_tin'] ?>
                                            </option>
                                            <option value="nhan_su"><?= $lang['nhan_su'] ?></option>
                                            <option value="hanh_chinh_van_phong"><?= $lang['hanh_chinh_van_phong'] ?>
                                            </option>
                                            <option value="ky_thuat"><?= $lang['ky_thuat'] ?></option>
                                            <option value="san_xuat_van_hanh_day_chuyen">
                                                <?= $lang['san_xuat_van_hanh_day_chuyen'] ?>
                                            </option>
                                            <option value="van_tai_logistics"><?= $lang['van_tai_logistics'] ?></option>
                                            <option value="bat_dong_san"><?= $lang['bat_dong_san'] ?></option>
                                            <option value="bao_hiem"><?= $lang['bao_hiem'] ?></option>
                                            <option value="xay_dung_kien_truc"><?= $lang['xay_dung_kien_truc'] ?>
                                            </option>
                                            <option value="y_te_duoc_pham"><?= $lang['y_te_duoc_pham'] ?></option>
                                            <option value="giao_duc_dao_tao"><?= $lang['giao_duc_dao_tao'] ?></option>
                                            <option value="du_lich_khach_san_nha_hang">
                                                <?= $lang['du_lich_khach_san_nha_hang'] ?>
                                            </option>
                                            <option value="luat"><?= $lang['luat'] ?></option>
                                            <option value="thiet_ke_nghe_thuat"><?= $lang['thiet_ke_nghe_thuat'] ?>
                                            </option>
                                            <option value="lao_dong_pho_thong"><?= $lang['lao_dong_pho_thong'] ?>
                                            </option>
                                            <option value="nong_nghiep"><?= $lang['nong_nghiep'] ?></option>
                                            <option value="food_beverage"><?= $lang['food_beverage'] ?></option>
                                            <option value="nha_nuoc_cong_vu"><?= $lang['nha_nuoc_cong_vu'] ?></option>
                                            <option value="khac"><?= $lang['khac'] ?></option>
                                        </select>

                                        <input type="hidden" name="jobCategoryHidden" id="jobCategoryHidden">

                                        <div id="custom_input_wrapper2" style="display:none; margin-top:6px;">
                                            <input type="text" id="custom_job_category" name="custom_job_category"
                                                placeholder="<?= $lang['custom_jobcategory_placeholder']; ?>">
                                        </div>
                                    </div>

                                    <!-- Required Certification -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_required_certification']; ?></label>
                                        <input type="text" name="requiredcertification"
                                            placeholder="<?= $lang['optional']; ?>">
                                    </div>

                                    <!-- Experience -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_job_experience']; ?></label>
                                        <select name="jobExperience" required>
                                            <option value=""><?= $lang['post_job_form_experience_placeholder']; ?></option>
                                            <?php foreach ($lang['post_job_form_experience_options'] as $value => $label): ?>
                                                <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Requirement -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_job_requirement']; ?></label>
                                        <textarea name="jobRequirementText"
                                            placeholder="<?= $lang['job_requirement_placeholder']; ?>"
                                            required></textarea>
                                    </div>

                                    <!-- Benefit -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_job_benefit']; ?></label>
                                        <textarea name="jobBenefit"
                                            placeholder="<?= $lang['job_benefit_placeholder']; ?>"></textarea>
                                    </div>

                                    <!-- Company Industry (readonly display) -->
                                    <div class="form-group">
                                        <label><?= $lang['field_of_work']; ?></label>
                                        <input type="text" id="company_industry_display" class="readonly-field" readonly placeholder="<?= $lang['required']; ?>">
                                    </div>

                                    <!-- Company Logo (readonly display) -->
                                    <div class="form-group">
                                        <label><?= $lang['post_job_form_company_logo']; ?></label>
                                        <div class="company-logo-preview" style="display:flex; align-items:center; justify-content:center; min-height:90px; background:#f7f7f7; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
                                            <a id="company_logo_link" href="" target="_blank" rel="noopener noreferrer" style="display:none;">
                                                <img id="company_logo_preview" src="" alt="company logo" style="max-width:120px; max-height:90px; object-fit:contain;">
                                            </a>
                                        </div>
                                    </div>

                                </div>

                            </div>

                            <div class="submit-wrapper">
                                <button type="submit" class="submit-btn">
                                    <?= $lang['post_job_form_submit']; ?>
                                </button>
                            </div>

                        </form>
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
        function toggleCustomInput() {
            const select = document.getElementById('company_size_select');
            const customWrapper = document.getElementById('custom_input_wrapper');
            const hiddenInput = document.getElementById('company_size');

            if (select && select.value === 'other') {
                if (customWrapper) customWrapper.style.display = 'block';
                if (hiddenInput) hiddenInput.value = '';
            } else {
                if (customWrapper) customWrapper.style.display = 'none';
                if (hiddenInput && select) hiddenInput.value = select.value;
            }
        }

        function toggleCustomInput2() {
            const select = document.getElementById('jobCategory');
            const customWrapper = document.getElementById('custom_input_wrapper2');
            const hiddenInput = document.getElementById('jobCategoryHidden');

            if (select && select.value === 'khac') {
                if (customWrapper) customWrapper.style.display = 'block';
                if (hiddenInput) hiddenInput.value = '';
            } else {
                if (customWrapper) customWrapper.style.display = 'none';
                if (hiddenInput && select) hiddenInput.value = select.value;
            }
        }

        function updateHiddenInput() {
            const customInput = document.getElementById('custom_company_size');
            const hiddenInput = document.getElementById('company_size');
            if (customInput && hiddenInput) {
                hiddenInput.value = customInput.value;
            }
        }

        function normalizeCompanyLogoUrl(logoValue) {
            if (!logoValue) return '';
            const trimmed = logoValue.trim();
            if (!trimmed) return '';
            if (/^(https?:)?\/\//i.test(trimmed)) return trimmed;
            if (trimmed.startsWith('/')) return trimmed;
            if (trimmed.startsWith('../')) return trimmed;
            if (trimmed.startsWith('uploads/')) return '../' + trimmed;
            if (trimmed.startsWith('companylogo/')) return '../' + trimmed;
            return trimmed;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const companySelect = document.getElementById('company_select');
            const companyNameDisplay = document.getElementById('company_name_display');
            const companyAddressDisplay = document.getElementById('company_address_display');
            const companySizeDisplay = document.getElementById('company_size_display');
            const companyIndustryDisplay = document.getElementById('company_industry_display');
            const companyLogoPreview = document.getElementById('company_logo_preview');
            const companyLogoLink = document.getElementById('company_logo_link');

            if (!companySelect) {
                return;
            }

            const companyData = Array.from(companySelect.options).filter(option => option.value).map(option => ({
                id: option.value,
                company_name: option.dataset.company_name || '',
                company_address: option.dataset.company_address || '',
                company_size: option.dataset.company_size || '',
                company_industry: option.dataset.company_industry || '',
                company_logo: option.dataset.company_logo || ''
            }));

            function fillCompanyInfo() {
                const selected = companyData.find(item => String(item.id) === String(companySelect.value));

                if (!selected) {
                    if (companyNameDisplay) companyNameDisplay.value = '';
                    if (companyAddressDisplay) companyAddressDisplay.value = '';
                    if (companySizeDisplay) companySizeDisplay.value = '';
                    if (companyIndustryDisplay) companyIndustryDisplay.value = '';
                    if (companyLogoPreview) companyLogoPreview.src = '';
                    if (companyLogoLink) {
                        companyLogoLink.href = '';
                        companyLogoLink.style.display = 'none';
                    }
                    return;
                }

                if (companyNameDisplay) companyNameDisplay.value = selected.company_name || '';
                if (companyAddressDisplay) companyAddressDisplay.value = selected.company_address || '';
                if (companySizeDisplay) companySizeDisplay.value = selected.company_size || '';
                if (companyIndustryDisplay) companyIndustryDisplay.value = selected.company_industry || '';
                if (companyLogoPreview) {
                    const logoValue = normalizeCompanyLogoUrl(selected.company_logo || '');
                    if (logoValue) {
                        companyLogoPreview.src = logoValue;
                        if (companyLogoLink) {
                            companyLogoLink.href = logoValue;
                            companyLogoLink.style.display = 'inline-block';
                        }
                    } else {
                        companyLogoPreview.src = '';
                        if (companyLogoLink) {
                            companyLogoLink.href = '';
                            companyLogoLink.style.display = 'none';
                        }
                    }
                }
            }

            companySelect.addEventListener('change', fillCompanyInfo);
            fillCompanyInfo();
        });
    </script>

</html>