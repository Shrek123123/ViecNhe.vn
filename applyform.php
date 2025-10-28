<?php
require_once 'config.php';
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
    <h5 class="w-100 text-center">Application Form</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form id="apply-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="job_id" value="<?= htmlspecialchars($job_id) ?>">
    <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" class="form-control" name="fullname" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" class="form-control" name="email" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone Number *</label>
            <input type="tel" class="form-control" name="phone" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Upload CV</label>
            <input type="file" class="form-control" name="cv_file" accept=".pdf,.doc,.docx" required>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="allow_search" id="allowSearch">
            <label class="form-check-label" for="allowSearch">
                Allow employers to find you
            </label>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-success w-100 ">Apply</button>
    </div>
    <p class="note mt-3">
        By clicking the submit button, I agree to share my personal information with employers according to the
        <a href="#">Terms of Use</a>,
        <a href="#">Privacy Policy</a>, and
        <a href="#">Personal Data Policy</a>.
    </p>
</form>