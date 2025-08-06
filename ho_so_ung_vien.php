<?php require_once 'config.php';
session_start();
if (!isset($_SESSION['username'])) {
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
    header("Location: $redirect");
    exit();
}

// Kiểm tra quyền truy cập jobseekerprofile.php?id=...
if (!isset($_SESSION['jobseeker_id'])) {
    header("Location: index.php");
    exit();
}

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

$sql_user = "SELECT email, verify_email, username FROM user WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $jobseeker_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $user_data = $result_user->fetch_assoc();
    $user_email = $user_data['email'];
    $is_email_verified = (bool) $user_data['verify_email'];
    $user_display_name = htmlspecialchars($user_data['username']);
}

$stmt_user->close();

// $profiledata của bảng jobseeker_profile
$sql_profile = "SELECT * FROM jobseeker_profile WHERE jobseeker_id = ?";
$stmt_profile = $conn->prepare($sql_profile);
$stmt_profile->bind_param("i", $jobseeker_id);
$stmt_profile->execute();
$result_profile = $stmt_profile->get_result();

if ($result_profile->num_rows > 0) {
    $profile_data = $result_profile->fetch_assoc();
}
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

        .main_profile_pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
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

        .avatar-wrapper {
            position: relative;
            width: 100px;
            /* hoặc tuỳ bạn */
            height: 100px;
            cursor: pointer;
        }

        .avatar-circle img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
        }

        .camera-icon {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 24px;
            /* tuỳ chỉnh kích cỡ */
            height: 24px;
            background: white;
            border-radius: 50%;
            padding: 2px;
            box-shadow: 0 0 2px rgba(0, 0, 0, 0.3);
        }

        /* Cơ bản cho modal */
        /* ====== Modal Base ====== */
        /* === Modal Overlay === */
        .open-modal {
            cursor: pointer;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        /* === Modal Content === */
        .modal-content {
            background: #fff;
            width: 95%;
            max-width: 600px;
            padding: 24px;
            border-radius: 10px;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        /* === Close Button === */
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: black;
            border-radius: 50%;
            padding: 4px 10px;
            transition: background-color 0.2s ease;
        }

        .close:hover,
        .close:focus {
            background-color: #ccc;
            /* màu xám nhẹ khi hover */
            text-decoration: none;
            cursor: pointer;
        }


        /* === Form === */
        form label {
            display: block;
            margin-top: 16px;
            font-weight: bold;
            font-size: 14px;
        }

        form input,
        form select {
            width: 100%;
            padding: 8px 10px;
            margin-top: 6px;
            font-size: 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        form input:disabled {
            background: #f8f8f8;
            color: #999;
        }

        /* === Flex group for province/district === */
        .flex {
            display: flex;
            gap: 10px;
        }

        .flex select {
            flex: 1;
        }

        /* === Row for dob + gender === */
        .form-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .half-width {
            flex: 1;
            min-width: 180px;
        }

        /* === Gender Group === */
        .gender-group {
            flex: 1;
            min-width: 150px;
        }

        .gender-buttons,
        .status-buttons {
            display: flex;
            gap: 10px;
            margin-top: 6px;
        }

        .gender-btn,
        .status-btn {
            padding: 6px 16px;
            border-radius: 20px;
            border: 1px solid #ccc;
            background-color: #f2f2f2;
            cursor: pointer;
            font-size: 13px;
            min-width: 80px;
            transition: 0.3s;
        }

        .gender-btn.active,
        .status-btn.active {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        /* === Action Buttons === */
        .button-group {
            display: flex;
            justify-content: flex-end;
            /* giữ nút ở bên phải */
            gap: 10px;
            margin-top: 24px;
        }

        .cancel,
        .save {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .cancel {
            background-color: transparent;
            color: #e74c3c;
            border: 2px solid #e74c3c;
        }

        .cancel:hover {
            background-color: #e74c3c;
            color: white;
        }

        .save {
            background-color: #c0392b;
            color: white;
            border: 2px solid #c0392b;
        }

        .save:hover {
            background-color: #a93226;
            border-color: #a93226;
        }

        /* === Responsive === */
        @media (max-width: 480px) {
            .form-row {
                flex-direction: column;
            }

            .gender-btn,
            .status-btn {
                flex: 1;
                font-size: 13px;
                padding: 6px 10px;
            }
        }
    </style>
</head>

<body>
    <?php
    include 'jobseekerprofile/header.php';
    ?>
    <!-- Top bar -->
    <div class="top-bar">
        <div class="title"><?= $lang['jobseekerprofile'] ?></div>
        <div class="company">
            <?php
            // Lấy jobseeker_id từ session hoặc từ URL
            $jobseeker_id = $_SESSION['jobseeker_id'] ?? 0;

            // Lấy ảnh đại diện jobseeker
            $sql = "SELECT profile_pic FROM jobseeker_profile WHERE jobseeker_id = $jobseeker_id LIMIT 1";
            $result = $conn->query($sql);
            $profile_pic = "";
            if ($result && $row = $result->fetch_assoc()) {
                // Nếu có ảnh thì lấy đường dẫn uploads/
                if (!empty($row['profile_pic'])) {
                    // Kiểm tra nếu đã có đường dẫn uploads/ thì không thêm lại
                    if (strpos($row['profile_pic'], 'uploads/') === 0) {
                        $profile_pic = $row['profile_pic'];
                    } else {
                        $profile_pic = 'uploads/' . basename($row['profile_pic']);
                    }
                }
            }

            // KHÔNG đóng kết nối ở đây nếu còn dùng $conn ở dưới
            ?>
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="jobseeker logo">
            <div class="company-info">
                <div><strong><?php echo $user_display_name; ?></strong></div>
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
                        <form id="uploadAvatarForm" action="jobseekerprofile/upload-avatar.php" method="POST"
                            enctype="multipart/form-data">
                            <input type="file" name="avatar" id="avatarInput" accept=".jpg,.jpeg,.png,.svg,.pdf"
                                style="display: none;" onchange="handleAvatarChange(event)">
                            <div class="avatar-wrapper" onclick="document.getElementById('avatarInput').click();">
                                <?php
                                // Xác định đường dẫn ảnh đại diện
                                $avatar_src = "https://via.placeholder.com/80?text=Avatar";
                                if (!empty($profile_data['profile_pic'])) {
                                    if (strpos($profile_data['profile_pic'], 'uploads/') === 0) {
                                        $avatar_src = $profile_data['profile_pic'];
                                    } else {
                                        $avatar_src = 'uploads/' . basename($profile_data['profile_pic']);
                                    }
                                }
                                ?>
                                <div class="avatar-circle">
                                    <img src="<?= htmlspecialchars($avatar_src); ?>" alt="Avatar">
                                </div>
                                <img src="image/camera.svg" class="camera-icon" alt="camera icon">
                            </div>
                        </form>

                    </div>


                    <div style="flex-grow: 1; text-align: left;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div style="font-size: 1.2em; font-weight: bold;">
                                <?php echo $user_display_name; ?>
                            </div>
                            <svg class="open-modal" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                fill="#8a2be2" viewBox="0 0 16 16" style="cursor: pointer;">
                                <path
                                    d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                <path fill-rule="evenodd"
                                    d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                            </svg>
                        </div>

                        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                            <div style="flex: 1 1 45%;">
                                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                    <img src="image/email.svg" alt="email icon"
                                        style="width: 16px; height: 16px; margin-right: 5px;">
                                    <?php echo htmlspecialchars($user_email); ?>
                                    <?php if ($is_email_verified): ?>
                                        <img src="image/greentick.png" alt="green tich icon"
                                            style="margin-left: 5px; width: 16px; height: 16px;">
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                    <img src="image/phone.svg" alt="phone icon"
                                        style="width: 16px; height: 16px; margin-right: 5px;">
                                    <div class="open-modal">
                                        <?php echo !empty($profile_data['phone_number']) ? htmlspecialchars($profile_data['phone_number']) : $lang['addmobilephone']; ?>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                    <img src="image/birthday.svg" alt="birthday icon"
                                        style="width: 16px; height: 16px; margin-right: 5px;">
                                    <div class="open-modal">
                                        <?php echo !empty($profile_data['date_of_birth']) ? htmlspecialchars($profile_data['date_of_birth']) : $lang['adddateofbirth']; ?>
                                    </div>
                                </div>
                            </div>

                            <div style="flex: 1 1 45%;">
                                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                    <img src="image/address.svg" alt="address icon"
                                        style="width: 16px; height: 16px; margin-right: 5px;">
                                    <div class="open-modal">
                                        <?php echo !empty($profile_data['address']) ? htmlspecialchars($profile_data['address']) : $lang['addcurrentaddress']; ?>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                    <img src="image/gender.svg" alt="gender icon"
                                        style="width: 16px; height: 16px; margin-right: 5px;">
                                    <div class="open-modal">
                                        <?php echo !empty($profile_data['gender']) ? htmlspecialchars($profile_data['gender']) : $lang['addgender']; ?>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                    <img src="image/marriage.svg" alt="marital status icon"
                                        style="width: 16px; margin-right: 5px;">
                                    <div class="open-modal">
                                        <?php echo !empty($profile_data['marital_status']) ? htmlspecialchars($profile_data['marital_status']) : $lang['addmaritalstatus']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="border-top: 1px solid #eee; margin-top: 20px; padding-top: 15px;">
                            <div style="font-size: 1.2em; font-weight: bold;"><?= $lang['profile_description'] ?></div>
                            <p style="margin-top: 10px; color: #6c757d;">
                            <div class="open-modal">
                                <?php echo !empty($profile_data['profile_description']) ? htmlspecialchars($profile_data['profile_description']) : $lang['profile_description_default']; ?>
                            </div>
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    style="background-color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; text-align: center; max-width: 800px; margin-left: auto; margin-right: auto;">
                    <div style="font-size: 1.2em; font-weight: bold; margin-bottom: 15px;"><?php echo $lang['my_cv']; ?>
                    </div>
                    <div style="border: 2px dashed #e0e0e0; border-radius: 8px; padding: 40px 20px;">
                        <img src="image/Uploadlogo2.svg" alt="Upload Icon" width="20" height="20">
                        <div style="color: #ff0000ff; font-weight: bold; margin-bottom: 5px;">
                            <?php echo $lang['upload_existing_CV'] ?>
                        </div>
                        <div style="font-size: 0.9em; color: #6c757d;"><?php echo $lang['supported_format_file'] ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div id="modalForm" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><?= $lang['personal_info'] ?></h2>

            <form>
                <!-- Full Name -->
                <label><?= $lang['full_name'] ?> *</label>
                <input type="text" placeholder="<?= $lang['full_name_placeholder'] ?>">

                <!-- Email -->
                <label><?= $lang['email'] ?> *</label>
                <input type="email" placeholder="example@mail.com" disabled>

                <!-- Phone Number -->
                <label><?= $lang['phone_number'] ?> *</label>
                <input type="tel" placeholder="<?= $lang['enter_phone'] ?>">

                <!-- Current Address -->
                <label><?= $lang['current_address'] ?> *</label>
                <div class="flex">
                    <select>
                        <option><?= $lang['select_province'] ?></option>
                    </select>
                    <select>
                        <option><?= $lang['select_district'] ?></option>
                    </select>
                </div>

                <!-- Date of Birth + Gender -->
                <div class="form-row">
                    <!-- Ngày sinh -->
                    <div class="form-group half-width">
                        <label for="dob"><?= $lang['date_of_birth'] ?></label>
                        <input type="date" id="dob" name="dob" placeholder="DD/MM/YYYY">
                    </div>

                    <!-- Giới tính -->
                    <div class="form-group gender-group">
                        <label><?= $lang['gender'] ?></label>
                        <div class="gender-buttons">
                            <button type="button" class="gender-btn"><?= $lang['female'] ?></button>
                            <button type="button" class="gender-btn"><?= $lang['male'] ?></button>
                        </div>
                    </div>
                </div>

                <!-- Tình trạng hôn nhân -->
                <div class="form-group">
                    <label><?= $lang['marital_status'] ?></label>
                    <div class="status-buttons">
                        <button type="button" class="status-btn"><?= $lang['single'] ?></button>
                        <button type="button" class="status-btn"><?= $lang['married'] ?></button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="button-group">
                    <button type="button" class="cancel"><?= $lang['cancel'] ?></button>
                    <button type="submit" class="save"><?= $lang['save'] ?></button>
                </div>
            </form>
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
    <script>
        var lang = {
            upload_success: "<?= $lang['upload_success'] ?>"
        };
    </script>
    <script>
        function handleAvatarChange(event) {
            const file = event.target.files[0];
            const validTypes = ['image/jpeg', 'image/png', 'image/svg+xml'];
            const maxSize = 5 * 1024 * 1024;

            if (!file) return;

            if (!validTypes.includes(file.type)) {
                alert("Chỉ cho phép JPG, PNG, SVG.");
                return;
            }

            if (file.size > maxSize) {
                alert("File phải nhỏ hơn 5MB.");
                return;
            }

            // Hiển thị ảnh preview trước khi upload
            const reader = new FileReader();
            reader.onload = function (e) {
                const imgContainer = event.target.closest("form").querySelector("div[onclick]");
                imgContainer.innerHTML = '<img src="' + e.target.result + '" style="width: 100%; height: 100%; object-fit: cover;">';
            };
            reader.readAsDataURL(file);

            // Tạo FormData từ form upload
            const form = document.getElementById("uploadAvatarForm");
            const formData = new FormData(form);

            // Gửi ảnh qua fetch đến PHP
            fetch('jobseekerprofile/upload-avatar.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message || lang.upload_success);
                        location.reload(); // Tải lại trang để cập nhật ảnh
                        // Nếu muốn cập nhật ảnh avatar ở chỗ khác nữa (ví dụ topbar), thì làm tiếp ở đây
                        // document.querySelector('.top-bar .company img').src = data.path;
                    } else {
                        alert("Lỗi khi tải ảnh: " + (data.message || "Không rõ lỗi."));
                    }
                })
                .catch(err => {
                    console.error("Lỗi kết nối:", err);
                    alert("Lỗi kết nối khi tải ảnh.");
                });
        }
    </script>
    <!-- script mở modal -->
    <script>
        // Đóng modal
        document.querySelector('.close').addEventListener('click', function () {
            document.getElementById('modalForm').style.display = 'none';
        });

        // Nút Hủy cũng đóng modal
        document.querySelector('.cancel').addEventListener('click', function () {
            document.getElementById('modalForm').style.display = 'none';
        });

        // Toggle nút giới tính
        document.querySelectorAll('.gender-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.gender-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Toggle tình trạng hôn nhân
        document.querySelectorAll('.status-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Mở modal (tuỳ chọn - gọi từ nút ngoài)
        document.querySelectorAll('.open-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('modalForm').style.display = 'flex';
            });
        });
    </script>



</body>
<footer>
    <?php
    require_once 'jobseekerprofile/footer.php';
    ?>
</footer>

</html>