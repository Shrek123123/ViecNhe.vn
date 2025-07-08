<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Submit Feedback with 5 Stars</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f6f8;
        padding: 20px;
    }
    form {
        background: #fff;
        max-width: 450px;
        margin: 0 auto;
        padding: 25px 30px;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    label {
        font-weight: bold;
        display: block;
        margin-bottom: 8px;
        color: #333;
    }
    .stars {
        display: flex;
        flex-direction: row-reverse;
        justify-content: center;
        margin-bottom: 20px;
    }
    .stars input {
        display: none;
    }
    .stars label {
        cursor: pointer;
        font-size: 2rem;
        color: #ccc;
        transition: color 0.2s;
        padding: 0 5px;
    }
    .stars input:checked ~ label,
    .stars label:hover,
    .stars label:hover ~ label {
        color: #ffca08;
    }
    textarea {
        width: 100%;
        padding: 8px 10px;
        margin-bottom: 20px;
        border: 1.5px solid #ccc;
        border-radius: 4px;
        font-size: 1rem;
        resize: vertical;
        box-sizing: border-box;
        transition: border-color 0.3s;
    }
    textarea:focus {
        border-color: #007bff;
        outline: none;
    }
    .btn-submit {
        background-color: #007bff;
        color: #fff;
        font-weight: bold;
        border: none;
        padding: 12px 25px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        transition: background-color 0.3s;
    }
    button:hover {
        background-color: #0056b3;
    }
    h1 {
        background-color: #e74c3c;
        color: white;
        padding: 20px 0;
        text-align: center;
        margin-top: 0;
        margin-bottom: 20px;
    }
</style>
</head>
<body>
    <?php
    include 'homepage/header.php';
    ?>
    <header>
        <h1>Feedback</h1>
    </header>

    <form method="post" action="">
        <label>Rating (1-5 stars):</label>
        <div class="stars">
            <input type="radio" id="star5" name="rating" value="5" />
            <label for="star5" title="5 stars">&#9733;</label>
            <input type="radio" id="star4" name="rating" value="4" />
            <label for="star4" title="4 stars">&#9733;</label>
            <input type="radio" id="star3" name="rating" value="3" />
            <label for="star3" title="3 stars">&#9733;</label>
            <input type="radio" id="star2" name="rating" value="2" />
            <label for="star2" title="2 stars">&#9733;</label>
            <input type="radio" id="star1" name="rating" value="1" />
            <label for="star1" title="1 star">&#9733;</label>
        </div>

        <label>Note (optional):</label>
        <textarea name="note" rows="4" placeholder="Enter your feedback..."></textarea>

        <button class="btn-submit" type="submit">Submit Feedback</button>
    </form>

    <?php
    include 'homepage/footer.php';
    ?>
</body>
</html>



<?php
 // Bắt đầu session để truy cập các biến $_SESSION
include 'config.php'; // Đảm bảo config.php chứa kết nối MySQLi ($conn)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra xem người dùng đã đăng nhập chưa
    if (!isset($_SESSION['role']) || (!isset($_SESSION['jobseeker_id']) && !isset($_SESSION['employerid']))) {
        // Người dùng chưa đăng nhập
        echo '<script>alert("Please login to submit feedback.");</script>';
        // Có thể dừng script hoặc chuyển hướng nếu muốn
        // header('Location: /login.php'); // Chuyển hướng đến trang đăng nhập
        // exit();
    } else {
        // Người dùng đã đăng nhập, tiến hành lấy dữ liệu và insert vào DB
        $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
        $content = isset($_POST['note']) ? trim($_POST['note']) : '';

        // Xác định user_id và user_type
        $user_id = null;
        $user_type = null;

        if ($_SESSION['role'] === 'jobseeker' && isset($_SESSION['jobseeker_id'])) {
            $user_id = $_SESSION['jobseeker_id'];
            $user_type = 'jobseeker';
        } elseif ($_SESSION['role'] === 'employer' && isset($_SESSION['employerid'])) { // Lưu ý tên biến employerid
            $user_id = $_SESSION['employerid'];
            $user_type = 'recruiter'; // Dựa trên ENUM 'jobseeker', 'recruiter', 'admin'
        }
        // Thêm trường hợp 'admin' nếu bạn muốn admin cũng có thể gửi feedback
        // elseif ($_SESSION['role'] === 'admin' && isset($_SESSION['admin_id'])) {
        //     $user_id = $_SESSION['admin_id'];
        //     $user_type = 'admin';
        // }

        // Kiểm tra dữ liệu hợp lệ trước khi chèn vào DB
        if ($user_id !== null && $user_type !== null && $rating >= 1 && $rating <= 5) {
            // Sử dụng MySQLi prepared statement để chống SQL Injection
            $stmt = $conn->prepare("INSERT INTO feedback (user_id, user_type, content, star_rating) VALUES (?, ?, ?, ?)");

            if ($stmt) {
                // 'isss' là định dạng: i (integer) cho user_id, s (string) cho user_type, s (string) cho content, s (string) cho star_rating
                $stmt->bind_param("isss", $user_id, $user_type, $content, $rating);

                if ($stmt->execute()) {
                    echo '<script>alert("Thank you for your feedback!");</script>';
                    // Nếu muốn người dùng ở lại trang sau khi gửi thành công, không cần chuyển hướng.
                    // Nếu muốn reset form hoặc chuyển hướng, có thể thêm:
                    // echo '<script>window.location.href = "feedback.php";</script>';
                } else {
                    echo '<script>alert("Error submitting feedback: ' . htmlspecialchars($stmt->error) . '");</script>';
                }
                $stmt->close();
            } else {
                echo '<script>alert("Error preparing statement: ' . htmlspecialchars($conn->error) . '");</script>';
            }
        } else {
            // Trường hợp rating không hợp lệ hoặc thông tin user_id/user_type không đủ
            echo '<script>alert("Please select a rating from 1 to 5 and ensure you are properly logged in.");</script>';
        }
    }
}
// Đóng kết nối database nếu nó vẫn mở (nếu config.php không tự đóng)
// $conn->close(); // Thường đóng kết nối ở cuối script hoặc trong hàm shutdown
?>