<?php
session_start();

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'vi';
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
include __DIR__ . '/lang/' . $_SESSION['lang'] . '.php';

$company_id = isset($_GET['company_id']) ? (int) $_GET['company_id'] : 0;
if ($company_id <= 0) {
    $company_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
}

$company = null;
if (isset($conn) && $company_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM company WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $company_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $company = $result->fetch_assoc();
        $stmt->close();
    }
}

if (!$company) {
    $stmt = $conn->prepare("SELECT * FROM company ORDER BY id DESC LIMIT 1");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $company = $result->fetch_assoc();
        $stmt->close();
    }
}

if (!$company) {
    $company = [
        'company_name' => 'JobHive Company',
        'company_address' => 'Hồ Chí Minh, Việt Nam',
        'industry' => 'Human Resources',
        'information' => 'No company information available yet.',
        'company_logo' => 'image/logo.png',
        'banner_logo' => 'image/banner.jpg'
    ];
}

$company_id_for_query = isset($company['id']) ? (int) $company['id'] : 0;
$related_jobs = [];
if ($company_id_for_query > 0 && isset($conn)) {
    $job_sql = "SELECT id, job_title, job_location, salary, job_experience, company_logo, company_name, company_size, created_at
                FROM job
                WHERE company_id = ?
                ORDER BY created_at ASC
                LIMIT 6";
    $job_stmt = $conn->prepare($job_sql);
    if ($job_stmt) {
        $job_stmt->bind_param('i', $company_id_for_query);
        $job_stmt->execute();
        $job_result = $job_stmt->get_result();
        while ($row = $job_result->fetch_assoc()) {
            $related_jobs[] = $row;
        }
        $job_stmt->close();
    }
}

$company_logo = normalizeAssetPath(trim((string) ($company['company_logo'] ?? '')));
if ($company_logo === '') {
    $company_logo = 'image/logo.png';
}

$banner_logo = normalizeAssetPath(trim((string) ($company['banner_logo'] ?? '')));
if ($banner_logo === '') {
    $banner_logo = 'image/banner.jpg';
}

$company_address = htmlspecialchars((string) ($company['company_address'] ?? 'Chưa cập nhật'));
$company_industry = htmlspecialchars(translateCompanyIndustry($company['industry'] ?? 'Chưa cập nhật', $lang));
$company_info = nl2br(htmlspecialchars((string) ($company['information'] ?? 'Chưa có thông tin mô tả.')));
$company_name = htmlspecialchars((string) ($company['company_name'] ?? 'JobHive Company'));
$company_size_display = '';
if (!empty($company['company_size'] ?? '')) {
    $company_size_display = htmlspecialchars((string) $company['company_size']);
} else {
    foreach ($related_jobs as $job) {
        if (!empty($job['company_size'] ?? '')) {
            $company_size_display = htmlspecialchars((string) $job['company_size']);
            break;
        }
    }
}

$company_profile_info_title = $lang['company_profile_info_title'] ?? 'Company information';
$company_profile_company_name_label = $lang['company_profile_company_name_label'] ?? 'Company name:';
$company_profile_industry_label = $lang['company_profile_industry_label'] ?? 'Industry:';
$company_profile_address_label = $lang['company_profile_address_label'] ?? 'Address:';
$company_profile_company_size_label = $lang['jobdetail_company_size'] ?? 'Company size:';
$company_profile_introduction_title = $lang['company_profile_introduction_title'] ?? 'Company introduction';
$company_profile_jobs_title = $lang['company_profile_jobs_title'] ?? 'Job openings at :company_name';
$company_profile_job_location = $lang['company_profile_job_location'] ?? 'Location:';
$company_profile_job_salary = $lang['company_profile_job_salary'] ?? 'Salary:';
$company_profile_job_experience = $lang['company_profile_job_experience'] ?? 'Experience:';
$company_profile_job_view_details = $lang['company_profile_job_view_details'] ?? 'View details';
$company_profile_no_jobs = $lang['company_profile_no_jobs'] ?? 'This company does not have any job postings yet.';
$company_profile_jobs_title = str_replace(':company_name', $company_name, $company_profile_jobs_title);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang']) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $company_name ?> | JobHive</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #222;
        }
        a { text-decoration: none; }
        .company-page {
            background: #f5f5f5;
            min-height: 100vh;
        }
        .company-hero {
            position: relative;
            height: 260px;
            width: min(1200px, 92%);
            margin: 0 auto;
            border-radius: 0 0 18px 18px;
            overflow: hidden;
            background-color: #d9d9d9;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .company-hero .banner-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
            display: block;
            background-color: #d9d9d9;
        }
        .company-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.12);
        }
        .hero-content {
            position: relative;
            z-index: 1;
            width: min(1200px, 92%);
            display: flex;
            align-items: center;
            gap: 20px;
            color: white;
            justify-content: flex-start;
        }
        .hero-logo {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 18px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 12px 28px rgba(0,0,0,0.2);
        }
        .hero-logo img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: contain;
            object-position: center;
            border-radius: 18px;
            background: white;
        }
        .hero-text h1 {
            margin: 0 0 8px;
            font-size: 38px;
            line-height: 1.2;
        }
        .hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            font-size: 14px;
            opacity: 0.95;
        }
        .company-shell {
            width: min(1200px, 92%);
            margin: 30px auto 60px;
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 28px;
        }
        .info-card,
        .job-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 24px rgba(28, 36, 51, 0.06);
        }
        .info-card {
            padding: 24px;
            height: fit-content;
        }
        .info-card h3 {
            margin: 0 0 18px;
            font-size: 22px;
            color: #1f2937;
        }
        .info-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .info-item {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            color: #444;
            line-height: 1.5;
        }
        .info-item i {
            width: 18px;
            color: #d43d3d;
            margin-top: 3px;
        }
        .main-section {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .company-summary {
            padding: 24px 28px;
        }
        .section-title {
            margin: 0 0 16px;
            font-size: 24px;
            color: #1f2937;
        }
        .summary-text {
            color: #4b5563;
            line-height: 1.8;
            font-size: 16px;
        }
        .jobs-box {
            padding: 24px 28px;
        }
        .jobs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 18px;
            margin-top: 18px;
        }
        .job-card {
            padding: 18px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .job-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(28, 36, 51, 0.1);
        }
        .job-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 10px;
        }
        .job-meta {
            color: #555;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .job-meta strong { color: #333; }
        .job-link {
            display: inline-block;
            margin-top: 12px;
            padding: 9px 14px;
            background: #d43d3d;
            color: white;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
        }
        .empty-state {
            padding: 18px;
            color: #666;
            background: #fff;
            border-radius: 12px;
            border: 1px dashed #d9d9d9;
        }
        @media (max-width: 900px) {
            .company-shell {
                grid-template-columns: 1fr;
            }
            .hero-content {
                flex-direction: column;
                align-items: flex-start;
            }
            .hero-text h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/homepage/header.php'; ?>

    <div class="company-page">
        <section class="company-hero">
            <img class="banner-image" src="<?= htmlspecialchars($banner_logo) ?>" alt="<?= $company_name ?> banner">
            <div class="hero-content">
                <div class="hero-logo" style="margin-left: 20px;">
                    <img src="<?= htmlspecialchars($company_logo) ?>" alt="<?= $company_name ?> logo">
                </div>
                <div class="hero-text" style="margin-left: 8px;">
                    <h1><?= $company_name ?></h1>
                    <div class="hero-meta">
                        <span><i class="fa-solid fa-building"></i> <?= $company_industry ?></span>
                        <span><i class="fa-solid fa-location-dot"></i> <?= $company_address ?></span>
                        <?php if ($company_size_display !== ''): ?>
                            <span><i class="fa-solid fa-users"></i> <?= $company_size_display ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <div class="company-shell">
            <aside class="info-card">
                <h3><?= $company_profile_info_title ?></h3>
                <div class="info-list">
                    <div class="info-item">
                        <i class="fa-solid fa-building"></i>
                        <div><strong><?= $company_profile_company_name_label ?></strong><br><?= $company_name ?></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-industry"></i>
                        <div><strong><?= $company_profile_industry_label ?></strong><br><?= $company_industry ?></div>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <div><strong><?= $company_profile_address_label ?></strong><br><?= $company_address ?></div>
                    </div>
                    <?php if ($company_size_display !== ''): ?>
                        <div class="info-item">
                            <i class="fa-solid fa-users"></i>
                            <div><strong><?= $company_profile_company_size_label ?></strong><br><?= $company_size_display ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>

            <main class="main-section">
                <section class="company-summary info-card">
                    <h2 class="section-title"><?= $company_profile_introduction_title ?></h2>
                    <div class="summary-text">
                        <?= $company_info ?>
                    </div>
                </section>

                <section class="jobs-box info-card">
                    <h2 class="section-title"><?= $company_profile_jobs_title ?></h2>

                    <?php if (!empty($related_jobs)): ?>
                        <div class="jobs-grid">
                            <?php foreach ($related_jobs as $job): ?>
                                <?php
                                $job_title = htmlspecialchars((string) ($job['job_title'] ?? ''));
                                $job_location = htmlspecialchars((string) ($job['job_location'] ?? ''));
                                $job_salary = htmlspecialchars((string) ($job['salary'] ?? 'Thỏa thuận'));
                                $job_experience = htmlspecialchars(translateJobExperience($job['job_experience'] ?? '', $lang) ?: ($lang['jobdetail_no_data'] ?? 'No data'));
                                $job_logo = normalizeAssetPath(trim((string) ($job['company_logo'] ?? '')));
                                if ($job_logo === '') {
                                    $job_logo = $company_logo;
                                }
                                ?>
                                <article class="job-card">
                                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                                        <img src="<?= htmlspecialchars($job_logo) ?>" alt="<?= $job_title ?>" style="width:42px;height:42px;object-fit:cover;border-radius:10px;border:1px solid #eee;">
                                        <div style="font-size:13px;color:#666;"><?= $company_name ?></div>
                                    </div>
                                    <div class="job-title"><?= $job_title ?></div>
                                    <div class="job-meta"><strong><?= $company_profile_job_location ?></strong> <?= $job_location ?></div>
                                    <div class="job-meta"><strong><?= $company_profile_job_salary ?></strong> <?= $job_salary ?></div>
                                    <div class="job-meta"><strong><?= $company_profile_job_experience ?></strong> <?= $job_experience ?></div>
                                    <a href="jobdetail.php?id=<?= (int) ($job['id'] ?? 0) ?>" class="job-link"><?= $company_profile_job_view_details ?></a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state"><?= $company_profile_no_jobs ?></div>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    </div>

    <?php include __DIR__ . '/homepage/footer.php'; ?>
</body>
</html>
