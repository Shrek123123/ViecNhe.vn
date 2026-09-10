<?php
session_start();
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'vi';
}
include_once __DIR__ . '/../lang/' . $_SESSION['lang'] . '.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $accountLockedMessage = $lang['account_locked'] ?? 'Tài khoản đã bị khoá. Vui lòng liên hệ quản trị viên.';

    // Check if the email and password are correct
    $stmt = $conn->prepare("SELECT id, username, password, status FROM user WHERE email = ? AND user_type = 'admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            if (($row['status'] ?? 'unlocked') === 'locked') {
                echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    var errorDiv = document.createElement('div');
                    errorDiv.style.color = 'red';
                    errorDiv.style.textAlign = 'center';
                    errorDiv.style.marginTop = '10px';
                    errorDiv.innerText = " . json_encode($accountLockedMessage, JSON_UNESCAPED_UNICODE) . ";
                    document.querySelector('input[name=\"password\"]').parentNode.appendChild(errorDiv);
                });
                </script>";
            } else {
                // Password is correct
                $_SESSION['usernameadmin'] = $row['username'];
                $_SESSION['admin_id'] = (int) $row['id'];
                header("Location: admindashboard.php");
                exit();
            }
        } else {
            // Invalid password
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                var errorDiv = document.createElement('div');
                errorDiv.style.color = 'red';
                errorDiv.style.textAlign = 'center';
                errorDiv.style.marginTop = '10px';
                errorDiv.innerText = 'Mật khẩu không đúng. Vui lòng thử lại.';
                document.querySelector('input[name=\"password\"]').parentNode.appendChild(errorDiv);
            });
            </script>";
        }
    } else {
        // Email not found
        echo "<script>alert('Email không tồn tại. Vui lòng thử lại.');</script>";
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
    <title>Đăng nhập quản trị - JobHive</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
        }

        .container {
            display: flex;
            flex: 1;
        }

        .left {
            flex: 1;
            background: #f3f3f3;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right {
            flex: 1;
            background: white;
            color: #8B0000;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        h2 {
            color: #d90000;
        }

        .form-group {
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #d90000;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .forgot {
            text-align: right;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="left">
            <h2>Chào mừng quản trị viên</h2>
            <p>Hãy cùng đưa hệ thống JobHive lên một tầm cao mới</p>
            <form accept="adminlogin.php" method="POST">
                <div class="form-group">
                    <label>Email</label><br>
                    <input type="email" placeholder="Email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Mật khẩu</label><br>
                    <input type="password" placeholder="Mật khẩu" name="password" required>
                    <div class="forgot"><a href="jobseekerforgetpassword.php">Quên mật khẩu</a></div>
                </div>
                <button class="btn-login">Đăng nhập</button>
            </form>
        </div>
        <div class="right">
            <a href="../index.php">
                <img src="../image/logo.png" alt="JobHive Logo" style="height: 70px; margin-bottom: 20px;">
            </a>
            <h2>Việc làm của bạn<br>là tổ ấm của chúng tôi</h2>
            <p>JobHive - Cổng thông tin việc làm tiên phong dành cho người nước ngoài tại Việt Nam</p>
            <div class="support">
                <p>Gặp khó khăn khi đăng nhập?</p>
                <p>Gọi cho chúng tôi: <strong>012 345 6789</strong></p>
                <p>Gửi email cho chúng tôi: <a href="mailto:support@jobhive.com">support@jobhive.com</a></p>
            </div>
        </div>

    </div>
</body>

</html>