<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Nếu người dùng bấm đổi ngôn ngữ
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Nếu chưa có ngôn ngữ thì mặc định là tiếng Việt
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'vi';
}

// Load file ngôn ngữ tương ứng
include __DIR__ . '/../lang/' . $_SESSION['lang'] . '.php';
$jobseeker_id = isset($_SESSION['jobseeker_id']) ? intval($_SESSION['jobseeker_id']) : 0;
$user_data = null;
if ($jobseeker_id > 0) {
    $conn = new mysqli("localhost", "root", "", "jobhive");
    if (!$conn->connect_error) {
        $sql = "SELECT username FROM user WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $jobseeker_id);
        $stmt->execute();
        $stmt->bind_result($username);
        if ($stmt->fetch()) {
            $user_data = $username;
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<style>
    .loggined_option {
        display: flex;
        gap: 15px;
        margin-left: auto;
        align-items: center;
    }

    .loggined_option button {
        border: none;
        background: #f5f5f5;
        cursor: pointer;
        border-radius: 4px;
        padding: 8px 16px;
        margin-right: 50px;
    }
    .ra_trang_ntd {
        text-decoration: none;
        color: #333;
        margin-right: 15px;
    }
    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-right: 50px;
    }

    .user-info img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
    }

    .user-info button {
        padding: 8px 16px;
        border: none;
        background: #f5f5f5;
        cursor: pointer;
        border-radius: 4px;
        margin-left: 10px;
        /* Thêm khoảng cách giữa ảnh và nút */
    }

    .user-info span {
        font-size: 16px;
        font-weight: bold;
    }

    .user-dropdown {
        position: relative;
        display: inline-block;
        cursor: pointer;
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
        min-width: 120px;
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

    .guest-options {
        display: flex;
        gap: 15px;
        margin-left: auto;
    }

    .btn-login,
    .btn-register {
        text-decoration: none;
    }
</style>

<div class="header">
    <div style="display: flex; align-items: center;">
        <a href="index.php">
            <img src="image/logo.png" alt="JobHive Logo" style="height: 50px; margin-right: 10px;">
        </a>
    </div>
    <div style="display: flex; gap: 30px; margin-left: 50px;">
        <a href="#" style="text-decoration: none; color: #333;">
            <?= $lang['create_cv']; ?>
        </a>
        <a href="contactus_jobseeker.php" style="text-decoration: none; color: #333;">
            <?= $lang['contact_us']; ?>
        </a>
        <a href="feedback_jobseeker.php" style="text-decoration: none; color: #333;">
            <?= $lang['feedback']; ?>
        </a>
    </div>

    <div class="loggined_option">
        <?php if (isset($_SESSION['username'])): ?>
            <a class="ra_trang_ntd" href="employerpage.php"><?= $lang['Ra_trang_nha_tuyen_dung']; ?></a>
            <div class="user-dropdown">
                <div class="user-info">
                    <?php
                    $profilePic = "image/defaultavatar.jpg"; // ảnh mặc định
                    if (isset($_SESSION['jobseeker_id'])) {
                        $conn = new mysqli("localhost", "root", "", "jobhive");

                        if (!$conn->connect_error) {
                            $sql = "SELECT profile_pic FROM jobseeker_profile WHERE jobseeker_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $jobseeker_id);
                            $stmt->execute();
                            $stmt->bind_result($pic);
                            if ($stmt->fetch() && !empty($pic)) {
                                // Nếu ảnh đã có 'uploads/' ở đầu thì giữ nguyên, nếu không thì thêm vào
                                $profilePic = (strpos($pic, 'uploads/') === 0) ? $pic : 'uploads/' . basename($pic);
                            }
                            $stmt->close();
                        }
                    }
                    ?>
                    <img src="<?= htmlspecialchars($profilePic); ?>" alt="User Avatar">
                    <span><?= htmlspecialchars($username ?? 'Người dùng'); ?></span>
                </div>
                <div class="dropdown-menu">
                    <?php
                    $jobseekerId = isset($_SESSION['jobseeker_id']) ? intval($_SESSION['jobseeker_id']) : 0;
                    ?>
                    <a href="jobseekerprofile/ho_so_ung_vien.php">
                        <?= $lang['profile']; ?>
                    </a>
                    <a href="jobseekerlogout.php">
                        <?= $lang['logout']; ?>
                    </a>
                </div>
            </div>
            <button id="lang-switch-btn">
                <?php echo ($_SESSION['lang'] === 'vi') ? 'English' : 'Tiếng Việt'; ?>
            </button>
        <?php else: ?>
            <div class="guest-options">
                <a href="jobseekerlogin.php" class="btn-login">
                    <?= $lang['login/register_jobseeker']; ?>
                </a>
                <a href="employerpage.php" class="btn-register">
                    <?= $lang['employer_redirect']; ?>
                </a>
               
                <button id="lang-switch-btn"
                    style="padding: 8px 16px; border: none; background: #f5f5f5; cursor: pointer; border-radius: 4px;">
                    <?php echo ($_SESSION['lang'] === 'vi') ? 'English' : 'Tiếng Việt'; ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
    document.getElementById('lang-switch-btn').onclick = function () {
        // Lấy URL hiện tại
        const url = new URL(window.location.href);

        // Lấy ngôn ngữ hiện tại từ PHP
        const currentLang = '<?php echo $_SESSION['lang']; ?>';

        // Chuyển đổi ngôn ngữ
        const newLang = currentLang === 'vi' ? 'en' : 'vi';

        // Set lại tham số lang trên URL
        url.searchParams.set('lang', newLang);

        // Chuyển hướng tới URL mới
        window.location.href = url.toString();
    };
</script>