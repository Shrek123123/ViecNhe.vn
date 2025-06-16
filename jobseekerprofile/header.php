<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<style>
    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-right: 50px;
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
</style>

<div class="header">
    <div style="display: flex; align-items: center;">
        <a href="index.php">
            <img src="image/logo.png" alt="JobHive Logo" style="height: 50px; margin-right: 10px;">
        </a>
    </div>
    <div style="display: flex; gap: 15px; margin-left: 50px;">
        <a href="#" style="text-decoration: none; color: #333;">Create CV</a>
        <a href="contactus_jobseeker.php" style="text-decoration: none; color: #333;">Contact Us</a>
        <a href="feedback_jobseeker.php" style="text-decoration: none; color: #333;">Feedback</a>
    </div>
    <div style="display: flex; gap: 15px; margin-left: auto">
        <?php if (isset($_SESSION['username'])): ?>
            <div class="user-dropdown">
                <div class="user-info">
                    <img src="image/defaultavatar.jpg" alt="User Avatar">
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
                <div class="dropdown-menu">
                    <a href="jobseekerlogout.php">Logout</a>
                </div>
            </div>


        <?php else: ?>
            <div class="guest-options">
                <a href="jobseekerlogin.php" class="btn-login">Login/Register for Job Seekers</a>
                <a href="employerpage.php" class="btn-register">Are you an employer? <br><span>Click here to redirect</span></a>
                <a href="admin/adminlogin.php" class="btn-register">Admin login</a>
            </div>
        <?php endif; ?>
    </div>

</div>