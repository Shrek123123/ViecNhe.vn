<?php
$conn = new mysqli("localhost", "root", "", "jobhive");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start(); // Bắt đầu session để sử dụng biến $_SESSION
include_once '../lang/' . $_SESSION['lang'] . '.php'; // Nạp ngôn ngữ từ session
?>
<!DOCTYPE html>
<html lang="en">

<head>
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

        .top-bar .company {
            display: flex;
            align-items: center;
        }

        .top-bar .company img {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .top-bar .company-info {
            text-align: right;
            font-size: 12px;
        }

        .top-bar .status {
            font-size: 11px;
            color: #ffdddd;
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

        .sidebar h3 {
            margin-top: 20px;
            font-size: 14px;
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

        .applicant-card {
            background-color: #b76d6d;
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

        .status-viewed {
            font-size: 11px;
            color: #b2ffb2;
        }

        .status-new {
            font-size: 11px;
            color: #cce0ff;
        }

        .view-more {
            font-size: 12px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff;
            border-radius: 4px;
        }

        .content-area {
            flex-grow: 1;
            background-color: #f3f3f3;
        }

        #btn-myapplications.active {
            background: #b23b3b;
            color: #fff;
            font-weight: bold;
        }

        #btn-savedjobs.active {
            background: #b23b3b;
            color: #fff;
            font-weight: bold;
        }

        #btn-myprofile.active {
            background: #b23b3b;
            color: #fff;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- Top bar -->
    <div class="top-bar">
        <div class="title"><?= $lang['jobseekerprofile'] ?></div>
        <div class="company">
            <img src="https://via.placeholder.com/34" alt="jobseeker logo">
            <?php
            // Lấy jobseeker_id từ session hoặc từ URL
            $jobseeker_id = $_SESSION['jobseeker_id'] ?? 0;

            // Lấy ảnh đại diện jobseeker
            $sql = "SELECT profile_pic FROM jobseeker_profile WHERE id = $jobseeker_id LIMIT 1";
            $result = $conn->query($sql);
            $profile_pic = "../image/defaultavatar.jpg";
            if ($result && $row = $result->fetch_assoc()) {
                $profile_pic = !empty($row['profile_pic']) ? $row['profile_pic'] : $profile_pic;
            }
            $conn->close();
            $jobseeker_name = "Tên ứng viên";
            ?>
            <img src="<?php echo (preg_match('/^(https?:|data:|\.{2}\/)/', $profile_pic) ? $profile_pic : '../' . ltrim($profile_pic, '/')); ?>" alt="jobseeker logo">
            <div class="company-info">
                <div><strong><?php echo $jobseeker_name; ?></strong></div>
                <div class="status">● Online</div>
            </div>
        </div>
    </div>

    <!-- Main layout -->
    <div class="main">
        <!-- Sidebar -->
        <div class="sidebar">
            <button id="btn-myprofile" class="active"><?= $lang['btn_myprofile'] ?></button>
            <button id="btn-myapplications"><?= $lang['btn_myapplications'] ?></button>
            <button id="btn-savedjobs"><?= $lang['btn_savedjobs'] ?></button>
            <button id="btn-changeinfo"><?= $lang['btn_changeinfo'] ?></button>

            <h3><?= $lang['jobseekerprofile_appliedjobs'] ?></h3>

            <!-- Applicants -->
            <div class="applicant-card">
                <img src="<?php echo (preg_match('/^(https?:|data:|\.{2}\/)/', $user1['avatar']) ? $user1['avatar'] : '../' . ltrim($user1['avatar'], '/')); ?>" alt="avatar">
                <div class="applicant-info">
                    Alex<br>
                    <span class="status-viewed">✔ Viewed</span>
                </div>
            </div>

            <div class="applicant-card">
                <img src="<?php echo (preg_match('/^(https?:|data:|\.{2}\/)/', $user2['avatar']) ? $user2['avatar'] : '../' . ltrim($user2['avatar'], '/')); ?>" alt="avatar">
                <div class="applicant-info">
                    Selena<br>
                    <span class="status-new">★ New</span>
                </div>
            </div>

            <div class="applicant-card">
                <img src="<?php echo (preg_match('/^(https?:|data:|\.{2}\/)/', $user3['avatar']) ? $user3['avatar'] : '../' . ltrim($user3['avatar'], '/')); ?>" alt="avatar">
                <div class="applicant-info">
                    Tyler<br>
                    <span class="status-new">★ New</span>
                </div>
            </div>

            <div class="applicant-card">
                <img src="<?php echo (preg_match('/^(https?:|data:|\.{2}\/)/', $user4['avatar']) ? $user4['avatar'] : '../' . ltrim($user4['avatar'], '/')); ?>" alt="avatar">
                <div class="applicant-info">
                    Patrica<br>
                    <span class="status-new">★ New</span>
                </div>
            </div>

            <div class="applicant-card">
                <img src="<?php echo (preg_match('/^(https?:|data:|\.{2}\/)/', $user5['avatar']) ? $user5['avatar'] : '../' . ltrim($user5['avatar'], '/')); ?>" alt="avatar">
                <div class="applicant-info">
                    Miranda<br>
                    <span class="status-new">★ New</span>
                </div>
            </div>

            <div class="view-more">View more</div>
        </div>

        <!-- Right side content -->
        <div class="content-area">

        </div>
    </div>




    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var currentJobseekerId = <?php echo $jobseeker_id; ?>;

            // Xử lý click cho nút "Saved Jobs"
            $('#btn-savedjobs').on('click', function () {
                $('.sidebar button').removeClass('active');
                $(this).addClass('active');
                $('.content-area').html('<div style="padding:30px;text-align:center;">Đang tải việc làm đã lưu...</div>');

                // Sửa đường dẫn: SỬ DỤNG ĐƯỜNG DẪN TUYỆT ĐỐI TỪ GỐC WEB APP
                // Giả định /JobHive-main/ là gốc của ứng dụng trên server
                $.get('/JobHive-main/jobseekerprofile/savedjobs-modal.php', function (data) { 
                    $('.content-area').html(data);
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error for savedjobs-modal.php:", textStatus, errorThrown, jqXHR.status);
                    $('.content-area').html('<div style="padding:30px;text-align:center;color:red;">Không thể tải việc làm đã lưu. Lỗi: ' + jqXHR.status + ' (' + errorThrown + ')</div>');
                });
            });

            // Xử lý click cho nút "My Applications"
            $('#btn-myapplications').on('click', function () {
                $('.sidebar button').removeClass('active');
                $(this).addClass('active');
                $('.content-area').html('<div style="padding:30px;text-align:center;">Đang tải đơn ứng tuyển...</div>');

                // Sửa đường dẫn: SỬ DỤNG ĐƯỜNG DẪN TUYỆT ĐỐI TỪ GỐC WEB APP
                $.get('/JobHive-main/jobseekerprofile/myapplications-modal.php', function (data) { 
                    $('.content-area').html(data);
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error for myapplications-modal.php:", textStatus, errorThrown, jqXHR.status);
                    $('.content-area').html('<div style="padding:30px;text-align:center;color:red;">Không thể tải đơn ứng tuyển. Lỗi: ' + jqXHR.status + ' (' + errorThrown + ')</div>');
                });
            });

            // Script riêng removejob.js
            // Nếu removejob.js cũng gọi các file PHP, hãy kiểm tra lại đường dẫn trong đó
            // và áp dụng quy tắc đường dẫn tuyệt đối nếu cần.
        });
    </script>
    <script src="jobseekerprofile/removejob.js"></script>
    <script src="jobseekerprofile/myprofile-btn.js"></script>


</body>

</html>