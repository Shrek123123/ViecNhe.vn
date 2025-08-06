<?php
session_start();
require_once 'config.php';

// Nếu người dùng bấm đổi ngôn ngữ
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'vi';
}

include 'lang/' . $_SESSION['lang'] . '.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['employerforgetpassword_title'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f3f3f3;
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .form-container {
            background: #ffffff;
            color: #8B0000;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-sizing: border-box;
        }

        h2 {
            color: #d90000;
            margin-bottom: 10px;
            font-size: 22px;
        }

        p {
            margin-top: 0;
            margin-bottom: 25px;
            color: #555;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #d90000;
            color: #ffffff;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        .btn-login:hover {
            background-color: #a60000;
        }

        .signup {
            text-align: center;
            margin-top: 30px;
            font-size: 15px;
            color: #555;
        }

        .signup a {
            color: #d90000;
            text-decoration: none;
            font-weight: bold;
        }

        .signup a:hover {
            color: #a60000;
        }

        .redirect {
            margin-top: 20px;
            font-size: 14px;
            color: #555;
        }

        .redirect a {
            color: #d90000;
            text-decoration: none;
            font-weight: bold;
        }

        .redirect a:hover {
            color: #a60000;
        }

        @media (max-width: 600px) {
            .form-container {
                padding: 25px;
                margin: 0 15px;
            }
        }
    </style>
</head>

<body>
    <?php include_once(__DIR__ . '/header_loginlogout/employer.php'); ?>
    <div class="main-content">
        <div class="form-container">
            <h2><?= $lang['employerforgetpassword_title']; ?></h2>
            <p><?= $lang['employerforgetpassword_intro'] ?? 'Enter your email and we will help you recover your password.'; ?></p>

            <form action="employerforgetpassword_process.php" method="POST">
                <div class="form-group">
                    <label for="emailInput"><?= $lang['email']; ?></label>
                    <input type="email" id="emailInput" name="email" placeholder="<?= $lang['employer_login_email_placeholder'] ?>" required>
                </div>
                <button type="submit" class="btn-login"><?= $lang['employerforgetpassword_button'] ?? 'Submit'; ?></button>
            </form>

            <div class="signup">
                <?= $lang['employer_login_signup']; ?>
            </div>

            <div class="redirect">
                <p><?= $lang['employer_login_redirect_jobseeker']; ?></p>
            </div>
        </div>
    </div>
</body>

</html>
