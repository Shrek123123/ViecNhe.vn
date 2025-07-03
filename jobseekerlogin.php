<?php
session_start();
require_once 'config.php';

// Nếu người dùng bấm đổi ngôn ngữ
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Nếu chưa có ngôn ngữ thì mặc định là tiếng Việt
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'vi';
}

// Load file ngôn ngữ tương ứng
include 'lang/' . $_SESSION['lang'] . '.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check if the email and password are correct
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? AND user_type = 'jobseeker'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Password is correct
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = 'jobseeker';
            $_SESSION['jobseeker_id'] = $row['id'];
            // Clear any previous output before redirecting
            if (ob_get_level()) {
                ob_end_clean();
            }
            header("Location: jobseekerdashboard.php");
            exit();

        } else {
            // Invalid password
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                var errorDiv = document.createElement('div');
                errorDiv.style.color = 'red';
                errorDiv.style.textAlign = 'center';
                errorDiv.style.marginTop = '10px';
                errorDiv.innerText = 'Invalid password. Please try again.';
                document.querySelector('input[name=\"password\"]').parentNode.appendChild(errorDiv);
            });
            </script>";
        }
    } else {
        // Email not found
        echo "<script>alert('Invalid email. Please try again.');</script>";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= $lang['jobseeker_login_title']; ?> - JobHive</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

    <style>
   body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column; /* Changed to column to stack header and content */
            min-height: 100vh; /* Use min-height to allow content to push height */
            background-color: #f3f3f3; /* Optional: A default background for the whole page */
        }

        /* --- Header Styling (adjust as needed for your actual header.php) --- */
        /* If your header takes up space, you might want to give it a background or fixed height */
        header { /* Assuming header.php will render inside a <header> tag or similar */
            width: 100%;
            /* background-color: #ffffff; */ /* Example header background */
            /* padding: 10px 20px; */ /* Example header padding */
            /* box-shadow: 0 2px 4px rgba(0,0,0,0.1); */ /* Example header shadow */
        }

        /* --- Main Content Area for Login Form --- */
        .main-content {
            flex: 1; /* Allows this area to take up remaining vertical space */
            display: flex;
            justify-content: center; /* Horizontally center the login wrapper */
            align-items: center; /* Vertically center the login wrapper */
            padding: 20px; /* Add some padding around the form */
            box-sizing: border-box; /* Include padding in element's total width and height */
        }

        .login-form-container {
            background: #ffffff;
            color: #8B0000;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px; /* Max width for the form block */
            text-align: center;
            box-sizing: border-box;
        }

        h2 {
            color: #d90000;
            margin-bottom: 10px;
        }

        p {
            margin-top: 0;
            margin-bottom: 25px;
            color: #555;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left; /* Align labels and inputs to the left within their group */
        }

        label {
            display: block; /* Make label take full width and stack above input */
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
            box-sizing: border-box; /* Include padding and border in the element's total width */
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
            margin-top: 20px; /* More space above the button */
            transition: background-color 0.3s ease;
        }

        .btn-login:hover {
            background-color: #a60000; /* Darker red on hover */
        }

        .forgot {
            text-align: right;
            margin-top: 10px;
            font-size: 14px;
        }

        .forgot a,
        .signup a {
            color: #d90000;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .forgot a:hover,
        .signup a:hover {
            color: #a60000;
        }

        .signup {
            text-align: center;
            margin-top: 30px;
            font-size: 15px;
            color: #555;
        }

        /* --- Responsive adjustments (optional but recommended) --- */
        @media (max-width: 600px) {
            .login-form-container {
                padding: 25px;
                margin: 0 15px; /* Add some margin on smaller screens */
            }
        }
    </style>
</head>

<body>
      <?php include_once(__DIR__ . '/header_loginlogout/jobseeker.php'); ?>

         <div class="main-content">
        <div class="login-form-container">
            <h2><?= $lang['jobseeker_login_subtitle_h2']; ?></h2>
            <p><?= $lang['jobseeker_login_subtitle_p']; ?></p>

            <form action="jobseekerlogin.php" method="POST">
                <div class="form-group">
                    <label for="emailInput"><?= $lang['jobseeker_login_email']; ?></label>
                    <input type="email"
                           id="emailInput"
                           name="email"
                           placeholder="<?= $lang['jobseeker_login_email_placeholder']; ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="passwordInput"><?= $lang['jobseeker_login_password']; ?></label>
                    <input type="password"
                           id="passwordInput"
                           name="password"
                           placeholder="<?= $lang['jobseeker_login_password_placeholder']; ?>"
                           required>
                    <div class="forgot">
                        <a href="jobseekerforgetpassword.php"><?= $lang['jobseeker_login_forgot_password']; ?></a>
                    </div>
                </div>

                <button type="submit" class="btn-login"><?= $lang['jobseeker_login_button']; ?></button>
            </form>

            <div class="signup">
                <?= $lang['jobseeker_login_signup']; ?>
            </div>

            <div style="margin-top: 20px; text-align: center;">
                <p><?= $lang['jobseeker_login_redirect_employer']; ?></p>
            </div>
        </div>
    </div>

</body>

</html>