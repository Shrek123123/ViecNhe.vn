<?php
require_once '../config.php';
require_once '../helpers.php';
session_start();

if (!isset($_SESSION['username'])) {
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php';
    header("Location: $redirect");
    exit();
}

if (!isset($_SESSION['jobseeker_id'])) {
    header('Location: ../index.php');
    exit();
}

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'vi';
}

include '../lang/' . $_SESSION['lang'] . '.php';

$jobseeker_id = (int) $_SESSION['jobseeker_id'];
$followed_companies = [];

$sql = 'SELECT c.id, c.company_logo, c.company_name, c.industry, COUNT(j.id) AS job_count
        FROM company_followers cf
        INNER JOIN company c ON c.id = cf.company_id
        LEFT JOIN job j ON j.company_id = c.id
        WHERE cf.jobseeker_id = ?
        GROUP BY c.id, c.company_logo, c.company_name, c.industry
        ORDER BY c.company_name ASC';
$stmt = $conn->prepare($sql);
if (!$stmt) {
    throw new RuntimeException('Unable to prepare followed companies query.');
}
$stmt->bind_param('i', $jobseeker_id);
$stmt->execute();
$result = $stmt->get_result();
while ($company = $result->fetch_assoc()) {
    $followed_companies[] = $company;
}
$stmt->close();

function followedCompanyLogoPath($logo) {
    $logo = trim((string) $logo);
    if ($logo === '') {
        return '../image/logo.png';
    }

    if (preg_match('#^(https?:|data:|/|\\.\\./)#i', $logo)) {
        return $logo;
    }

    return '../' . ltrim($logo, '/');
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'], ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($lang['followed_companies_title']) ?></title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }

        .top-bar {
            background-color: #943737;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            padding: 10px 20px;
        }

        .top-bar .title {
            font-weight: bold;
            font-size: 15px;
        }

        .main {
            display: flex;
        }

        .sidebar {
            width: 160px;
            border-right: 1px solid #ccc;
            padding: 10px;
            background: #fff;
        }

        .sidebar button {
            width: 100%;
            padding: 6px;
            margin-bottom: 10px;
            font-size: 13px;
            border: 1px solid black;
            background-color: white;
            cursor: pointer;
        }

        .sidebar button:hover {
            background-color: #f2f2f2;
        }

        .change-info-wrap {
            position: relative;
            width: auto;
            display: block;
        }

        .change-info-menu {
            position: absolute;
            top: 0;
            left: calc(100% + 10px);
            display: none;
            flex-direction: column;
            gap: 8px;
            min-width: 190px;
            padding: 10px;
            background: #fff;
            border: 1px solid #e8dede;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.14);
            z-index: 50;
        }

        .change-info-menu.show {
            display: flex;
        }

        .change-info-option {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #f0d8d8;
            background: #fff8f8;
            color: #733333;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        #btn-changeinfo.active,
        #btn-followed-companies.active {
            background: #b23b3b;
            color: #fff;
            font-weight: bold;
        }

        .content-area {
            flex-grow: 1;
            background-color: #f3f3f3;
        }

        .companies-content {
            padding: 30px;
        }

        .companies-content h2 {
            color: #b23b3b;
            margin: 0 0 20px;
        }

        .companies-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 18px;
        }

        .company-card {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            min-width: 0;
            padding: 18px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            color: inherit;
            text-decoration: none;
        }

        .company-card:hover {
            border-color: #b23b3b;
        }

        .company-card img {
            width: 70px;
            height: 70px;
            flex: 0 0 70px;
            object-fit: cover;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .company-card-info {
            min-width: 0;
        }

        .company-card-name {
            margin: 0 0 8px;
            color: #333;
            font-size: 18px;
            overflow-wrap: anywhere;
        }

        .company-card-industry {
            margin: 0 0 8px;
            color: #666;
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .company-card-jobs {
            margin: 0;
            color: #b23b3b;
            font-size: 13px;
            font-weight: bold;
        }

        .empty-companies {
            padding: 24px;
            color: #888;
            text-align: center;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .top-bar {
                flex-direction: row;
            }

            .main {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                box-sizing: border-box;
                border-right: none;
                border-bottom: 1px solid #ccc;
            }

            .change-info-menu {
                position: static;
                width: 100%;
                box-sizing: border-box;
                margin-top: 5px;
                box-shadow: none;
            }

            .companies-content {
                padding: 22px 16px;
            }
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="top-bar">
        <div class="title"><?= htmlspecialchars($lang['jobseekerprofile']) ?></div>
        <button class="profile-sidebar-toggle" type="button" aria-label="Toggle profile menu">
            <span></span><span></span><span></span>
        </button>
    </div>

    <div class="main">
        <div class="sidebar">
            <button id="btn-myprofile" onclick="window.location.href='ho_so_ung_vien.php'"><?= $lang['btn_myprofile'] ?></button>
            <button id="btn-myapplications" onclick="window.location.href='don_ung_tuyen.php'"><?= $lang['btn_myapplications'] ?></button>
            <button id="btn-savedjobs" onclick="window.location.href='cong_viec_da_luu.php'"><?= $lang['btn_savedjobs'] ?></button>
            <button id="btn-followed-companies" class="active" onclick="window.location.href='followed_company.php'"><?= $lang['btn_followed_companies'] ?></button>
            <div class="change-info-wrap">
                <button id="btn-changeinfo" type="button"><?= $lang['btn_changeinfo'] ?></button>
                <div id="changeInfoMenu" class="change-info-menu" aria-label="Change info actions">
                    <button type="button" class="change-info-option" data-href="thay_doi_email.php"><?= $lang['change_email_action'] ?></button>
                    <button type="button" class="change-info-option" data-href="thay_doi_mat_khau.php"><?= $lang['change_password_action'] ?></button>
                </div>
            </div>
        </div>

        <div class="content-area">
            <div class="companies-content">
                <h2><?= htmlspecialchars($lang['followed_companies_title']) ?></h2>
                <?php if (empty($followed_companies)): ?>
                    <div class="empty-companies"><?= htmlspecialchars($lang['no_followed_companies']) ?></div>
                <?php else: ?>
                    <div class="companies-list">
                        <?php foreach ($followed_companies as $company): ?>
                            <?php
                            $company_name = (string) ($company['company_name'] ?? '');
                            $industry = translateIndustryDisplay($company['industry'] ?? '', $lang);
                            $job_count_text = str_replace(':count', number_format((int) $company['job_count']), $lang['number_of_job_company_has']);
                            ?>
                            <a class="company-card" href="../companyprofile.php?id=<?= (int) $company['id'] ?>">
                                <img src="<?= htmlspecialchars(followedCompanyLogoPath($company['company_logo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8') ?>">
                                <div class="company-card-info">
                                    <h3 class="company-card-name"><?= htmlspecialchars($company_name) ?></h3>
                                    <p class="company-card-industry"><?= htmlspecialchars($industry) ?></p>
                                    <p class="company-card-jobs"><?= htmlspecialchars($job_count_text) ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('btn-changeinfo');
            const menu = document.getElementById('changeInfoMenu');
            if (!button || !menu) return;

            button.addEventListener('click', function (event) {
                event.stopPropagation();
                menu.classList.toggle('show');
                button.classList.toggle('active');
            });

            menu.querySelectorAll('.change-info-option').forEach(function (option) {
                option.addEventListener('click', function () {
                    window.location.href = option.dataset.href;
                });
            });

            document.addEventListener('click', function (event) {
                if (!button.contains(event.target) && !menu.contains(event.target)) {
                    menu.classList.remove('show');
                    button.classList.remove('active');
                }
            });
        });
    </script>

    <footer>
        <?php require_once '../jobseekerprofile/footer.php'; ?>
    </footer>

    <?php include 'mobile_sidebar.php'; ?>
</body>

</html>
