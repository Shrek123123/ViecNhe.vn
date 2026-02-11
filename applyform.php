<?php
require_once 'config.php';
session_start();
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
$job_id = $_GET['id'] ?? 0;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    session_start();
    if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'jobseeker') {
        echo json_encode(['success' => false, 'error' => 'You must be logged in as a job seeker to apply for a job.']);
        exit();
    } else {
        $jobseeker_id = $_SESSION['jobseeker_id'] ?? 0;
        $job_id = $_POST['job_id'] ?? 0;
        if ($job_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid job ID.']);
            exit();
        }
        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $allow_search = isset($_POST['allow_search']) ? 1 : 0;

        if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] == UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['cv_file']['tmp_name'];
            $file_name = basename($_FILES['cv_file']['name']);
            $upload_dir = 'uploads/cv_file/';
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($file_tmp, $file_path)) {
                $stmt = $conn->prepare("INSERT INTO application (job_id, jobseeker_id, fullname, email, phonenumber, cv_file, allow_search, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $status = "pending";
                $stmt->bind_param("iissssss", $job_id, $jobseeker_id, $fullname, $email, $phone, $file_path, $allow_search, $status);
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Application submitted successfully!']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Error submitting application. Please try again.']);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'error' => 'Error uploading file. Please try again.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Please upload a valid CV file.']);
            exit();
        }
    }
}
?>
<div class="modal-header">
    <h5 class="w-100 text-center"><?= $lang['application_form'] ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= $lang['close'] ?>"></button>
</div>

<form id="apply-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="job_id" value="<?= htmlspecialchars($job_id) ?>">

    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label"><?= $lang['full_name'] ?> *</label>
            <input type="text" class="form-control" name="fullname" required>
        </div>

        <div class="mb-3">
            <label class="form-label"><?= $lang['email'] ?> *</label>
            <input type="email" class="form-control" name="email" required>
        </div>

        <div class="mb-3">
            <label class="form-label"><?= $lang['phone_number'] ?> *</label>
            <input type="tel" class="form-control" name="phone" required>
        </div>

        <div class="mb-3">
            <label class="form-label"><?= $lang['upload_cv'] ?></label>
            <input type="file" class="form-control" name="cv_file" accept=".pdf,.doc,.docx" required>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="allow_search" id="allowSearch">
            <label class="form-check-label" for="allowSearch">
                <?= $lang['allow_employers_find'] ?>
            </label>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-success w-100">
            <?= $lang['apply'] ?>
        </button>
    </div>

    <p class="note mt-3" style="margin-left: 15px; margin-right: 15px; text-align: center;">
        <?= $lang['agreement_text'] ?>
        <a href="#"><?= $lang['terms_of_use'] ?></a>,
        <a href="#"><?= $lang['privacy_policy'] ?></a>,
        <?= $lang['and'] ?>
        <a href="#"><?= $lang['personal_data_policy'] ?></a>.
    </p>
</form>
