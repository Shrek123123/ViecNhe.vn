<?php require_once 'config.php';
session_start();
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Nếu chưa có ngôn ngữ thì mặc định là tiếng Việt
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'vi';
}

// Load file ngôn ngữ tương ứng
include 'lang/' . $_SESSION['lang'] . '.php';


$jobseeker_id = $_SESSION['jobseeker_id'] ?? ''; // Ví dụ: jobseeker_id của người dùng hiện tại

$profile_data = null; // Khởi tạo biến profile_data
$user_email = "Email not available"; // Giá trị mặc định
$is_email_verified = false; // Giá trị mặc định
$user_display_name = "My Profile"; // Giá trị mặc định cho tên người dùng

// Truy vấn dữ liệu từ cả hai bảng jobseeker_profile và user
// Sử dụng JOIN để lấy dữ liệu liên quan
$sql = "SELECT jp.*, u.email, u.verify_email, u.username 
        FROM jobseeker_profile jp 
        JOIN user u ON jp.jobseeker_id = u.id 
        WHERE jp.jobseeker_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $jobseeker_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile_data = $result->fetch_assoc();
    $user_email = $profile_data['email'];
    $is_email_verified = (bool) $profile_data['verify_email']; // Ép kiểu sang boolean
    $user_display_name = htmlspecialchars($profile_data['username']); // Lấy username từ bảng user
}

$stmt->close();
// KẾT THÚC: KHỐI MÃ PHP XỬ LÝ DỮ LIỆU
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['jobseekerpersonalinfo_title'] ?></title>
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
    <?php
    require_once 'jobseekerprofile/header.php';
    ?>
    <!-- Top bar -->
    <div class="top-bar">
        <div class="title">Jobseeker profile</div>
        <div class="company">
            <img src="https://via.placeholder.com/34" alt="jobseeker logo">
            <?php
            // Lấy jobseeker_id từ session hoặc từ URL
            $jobseeker_id = $_SESSION['jobseeker_id'] ?? 0;

            // Lấy ảnh đại diện jobseeker
            $sql = "SELECT profile_pic FROM jobseeker_profile WHERE id = $jobseeker_id LIMIT 1";
            $result = $conn->query($sql);
            $profile_pic = "https://via.placeholder.com/34";
            if ($result && $row = $result->fetch_assoc()) {
                $profile_pic = !empty($row['profile_pic']) ? htmlspecialchars($row['profile_pic']) : $profile_pic;
            }
            $conn->close();
            $jobseeker_name = "Tên ứng viên";
            ?>
            <img src="<?php echo $profile_pic; ?>" alt="jobseeker logo">
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
            <button id="btn-myprofile" class="active">My profile</button>
            <button id="btn-myapplications">My applications</button>
            <button id="btn-savedjobs">Saved jobs</button>
            <button id="btn-changeinfo">Change person information</button>

            <h3>Applied jobs</h3>

            <!-- Applicants -->
            <div class="applicant-card">
                <img src="<?php echo $user1['avatar']; ?>" alt="avatar">
                <div class="applicant-info">
                    Alex<br>
                    <span class="status-viewed">✔ Viewed</span>
                </div>
            </div>

            <div class="applicant-card">
                <img src="<?php echo $user2['avatar']; ?>" alt="avatar">
                <div class="applicant-info">
                    Selena<br>
                    <span class="status-new">★ New</span>
                </div>
            </div>

            <div class="applicant-card">
                <img src="<?php echo $user3['avatar']; ?>" alt="avatar">
                <div class="applicant-info">
                    Tyler<br>
                    <span class="status-new">★ New</span>
                </div>
            </div>

            <div class="applicant-card">
                <img src="<?php echo $user4['avatar']; ?>" alt="avatar">
                <div class="applicant-info">
                    Patrica<br>
                    <span class="status-new">★ New</span>
                </div>
            </div>

            <div class="applicant-card">
                <img src="<?php echo $user5['avatar']; ?>" alt="avatar">
                <div class="applicant-info">
                    Miranda<br>
                    <span class="status-new">★ New</span>
                </div>
            </div>

            <div class="view-more">View more</div>
        </div>

        <!-- Right side content -->
        <div class="content-area">
            <div style="padding: 20px; text-align: center;">
                <div
                    style="background-color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 20px; max-width: 800px; margin-left: auto; margin-right: auto;">
                    <div style="flex-shrink: 0; text-align: center;">
                        <?php if (!empty($profile_data['profile_pic'])): ?>
                            <div
                                style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; margin: 0 auto 10px;">
                                <img src="<?php echo htmlspecialchars($profile_data['profile_pic']); ?>"
                                    alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        <?php else: ?>
                            <div
                                style="width: 80px; height: 80px; background-color: #f0f0f0; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 10px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#cccccc"
                                    viewBox="0 0 16 16">
                                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                                    <path fill-rule="evenodd"
                                        d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                                </svg>
                            </div>
                        <?php endif; ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#6c757d"
                            viewBox="0 0 16 16" style="margin-top: -30px; margin-left: 50px;">
                            <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0" />
                            <path
                                d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v9.5l-1.074-.742A2 2 0 0 0 13 12c-.729 0-1.4.407-1.757 1.058L9.798 16H2.002a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z" />
                        </svg>
                    </div>

                    <div style="flex-grow: 1; text-align: left;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div style="font-size: 1.2em; font-weight: bold;">
                                <?php echo $user_display_name; ?>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#8a2be2"
                                viewBox="0 0 16 16" style="cursor: pointer;">
                                <path
                                    d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                <path fill-rule="evenodd"
                                    d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                            </svg>
                        </div>

                        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                            <div style="flex: 1 1 45%;">
                                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                    <?php echo htmlspecialchars($user_email); ?>
                                    <?php if ($is_email_verified): ?>
                                        <img src="image/greentick.png" alt="green tich icon"
                                            style="margin-left: 5px; width: 16px; height: 16px;">
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                    <?php echo !empty($profile_data['phone_number']) ? htmlspecialchars($profile_data['phone_number']) : 'Add mobile phone'; ?>
                                </div>
                                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                    <?php echo !empty($profile_data['date_of_birth']) ? htmlspecialchars($profile_data['date_of_birth']) : 'Add date of birth'; ?>
                                </div>
                            </div>

                            <div style="flex: 1 1 45%;">
                                <div style="display: flex; align-items: center; margin-bottom: 5px;">      
                                    <?php echo !empty($profile_data['address']) ? htmlspecialchars($profile_data['address']) : 'Add current address'; ?>
                                </div>
                                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                    
                                    <?php echo !empty($profile_data['gender']) ? htmlspecialchars($profile_data['gender']) : 'Add gender'; ?>
                                </div>
                                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                  
                                    <?php echo !empty($profile_data['marital_status']) ? htmlspecialchars($profile_data['marital_status']) : 'Add marital status'; ?>
                                </div>
                            </div>
                        </div>

                        <div style="border-top: 1px solid #eee; margin-top: 20px; padding-top: 15px;">
                            <div style="font-size: 1.2em; font-weight: bold;">Nội dung description</div>
                            <p style="margin-top: 10px; color: #6c757d;">
                                <?php echo !empty($profile_data['profile_description']) ? htmlspecialchars($profile_data['profile_description']) : 'Đây là nơi bạn có thể thêm mô tả chi tiết về bản thân hoặc hồ sơ của mình.'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    style="background-color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; text-align: center; max-width: 800px; margin-left: auto; margin-right: auto;">
                    <div style="font-size: 1.2em; font-weight: bold; margin-bottom: 15px;">My CV</div>
                    <div style="border: 2px dashed #e0e0e0; border-radius: 8px; padding: 40px 20px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#8a2be2"
                            viewBox="0 0 16 16" style="margin-bottom: 10px;">
                            <path fill-rule="evenodd"
                                d="M7.646 5.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L5.354 8.854a.5.5 0 1 1-.708-.708z" />
                            <path fill-rule="evenodd"
                                d="M1.5 14.5A1.5 1.5 0 0 0 3 16h10a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 13 4h-3.5a.5.5 0 0 1-.5-.5v-3A1.5 1.5 0 0 0 8.5 0h-7A1.5 1.5 0 0 0 0 1.5v13zm9-13V2A1.5 1.5 0 0 0 11.5 3h2A1.5 1.5 0 0 0 15 1.5v-1A.5.5 0 0 0 14.5 0h-7A.5.5 0 0 0 7 0zM7 0h1.5A1.5 1.5 0 0 1 10 1.5V3h-.5a.5.5 0 0 0-.5.5v2A1.5 1.5 0 0 1 7 7h-2A1.5 1.5 0 0 1 3.5 5.5v-2A.5.5 0 0 0 3 3V1.5A1.5 1.5 0 0 1 4.5 0H7z" />
                        </svg>
                        <div style="color: #8a2be2; font-weight: bold; margin-bottom: 5px;">Upload existing CV</div>
                        <div style="font-size: 0.9em; color: #6c757d;">Supported formats: doc, docx, pdf, maximum 5MB
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
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
                    .fail(function (jqXHR, textStatus, errorThrown) {
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
                    .fail(function (jqXHR, textStatus, errorThrown) {
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
<footer>
    <?php
    require_once 'jobseekerprofile/footer.php';
    ?>

</html>