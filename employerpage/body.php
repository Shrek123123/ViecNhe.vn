<?php
require_once 'config.php';
include 'helpers.php';
if (!isset($_SESSION['usernameemployer'])) {
  echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      var openBtn = document.getElementById('openModalBtn');
      if (openBtn) {
        openBtn.onclick = function() {
          window.location.href = 'employerlogin.php';
        }
      }
    });
  </script>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $companyId = (int)($_POST['company_id'] ?? 0);
  $jobRequirementText = $_POST['jobRequirementText'];
  $jobBenefit = $_POST['jobBenefit'];
  $jobTitle = $_POST['jobTitle'];
  $jobDescription = $_POST['jobDescription'];
  $jobLocation = $_POST['jobLocation'];
  $salary = $_POST['salary'];
  $contactEmail = $_POST['contactEmail'];
  $contactPhone = $_POST['contactPhone'];
  $jobType = $_POST['jobType'];
  $requiredcertification = $_POST['requiredcertification'];
  $jobExperience = $_POST['jobExperience'];
  $jobPosition = $_POST['jobPosition'] ?? '';
  $noEmplpoyeeNeeeded = $_POST['noEmplpoyeeNeeded'] ?? 0;

  $jobDetailedLocation = $_POST['jobDetailedLocation'] ?? '';
  $jobLocationDistrict = $_POST['jobLocationDistrict'] ?? '';
  if (!isset($_SESSION['employerid'])) {
    echo "<script>alert('You must be logged in as an employer to post a job.');</script>";
    exit;
  }
  $employerid = (int) $_SESSION['employerid'];

  if ($companyId <= 0) {
    echo "<script>alert('Please select a company before posting a job.'); window.history.back();</script>";
    exit;
  }

  $companyCheckStmt = $conn->prepare("SELECT id FROM company WHERE id = ? AND employer_id = ? LIMIT 1");
  if (!$companyCheckStmt) {
    echo "<script>alert('Error verifying company selection: {$conn->error}');</script>";
    exit;
  }
  $companyCheckStmt->bind_param("ii", $companyId, $employerid);
  $companyCheckStmt->execute();
  $companyCheckResult = $companyCheckStmt->get_result();
  if ($companyCheckResult->num_rows === 0) {
    $companyCheckStmt->close();
    echo "<script>alert('Selected company is invalid for this employer.'); window.history.back();</script>";
    exit;
  }
  $companyCheckStmt->close();

  // Remove company logo upload handling for this simplified job-post flow.

  // Validate required fields
  // $requiredFields = [
  //   'companyName' => $companyName,
  //   'jobTitle' => $jobTitle,
  //   'jobDescription' => $jobDescription,
  //   'jobLocation' => $jobLocation,
  //   'salary' => $salary,
  //   'contactEmail' => $contactEmail,
  //   'contactPhone' => $contactPhone,
  //   'jobType' => $jobType,
  //   'jobCategory' => $jobCategory,
  //   'requiredcertification' => $requiredcertification,
  //   'jobExperience' => $jobExperience,
  //   'post_duration' => $post_duration,
  //   'jobRequirementText' => $jobRequirementText,
  //   'jobBenefit' => $jobBenefit,
  //   'companySize' => $companySize,
  //   'jobPosition' => $jobPosition,
  //   'noEmplpoyeeNeeded' => $noEmplpoyeeNeeeded,
  //   'jobDetailedLocation' => $jobDetailedLocation,
  //   'jobLocationDistrict' => $jobLocationDistrict
  // ];

  // foreach ($requiredFields as $field => $value) {
  //   if (empty($value)) {
  //     echo "<script>alert('Please fill out all required information.');</script>";
  //   }
  // }
  $jobStatus = 'pending_review'; // Mặc định là chờ duyệt

  // Database connection and insertion logic here
  // Lấy số thứ tự bài đăng mới nhất của employer này
  $sql = "SELECT MAX(job_post_number_by_employer) AS last_number FROM job WHERE posted_by_employer_id = ?";
  $stmt_number = $conn->prepare($sql);
  if (!$stmt_number) {
    echo "<script>alert('Error preparing statement for job number: {$conn->error}');</script>";
    exit;
  }
  $stmt_number->bind_param("i", $employerid);
  $stmt_number->execute();
  $result = $stmt_number->get_result();
  $row = $result ? $result->fetch_assoc() : null;
  $last_number = $row && isset($row['last_number']) ? (int)$row['last_number'] : 0;
  $stmt_number->close();

  $new_job_number = $last_number + 1;

  $stmt = $conn->prepare("INSERT INTO job (
    posted_by_employer_id, company_id, job_post_number_by_employer, job_title, job_description, job_location, salary, contact_email, contact_phone, job_type, required_certification, job_experience, job_requirement, job_benefit, no_employee_needed, job_position, job_detailed_location, job_location_district, post_duration, status
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

  if (!$stmt) {
    echo "<script>alert('Error preparing statement: {$conn->error}');</script>";
    exit;
  }

  $postDuration = 30;
  $types = "ii" . "i" . str_repeat("s", 11) . "i" . str_repeat("s", 3) . "i" . "s";
  $stmt->bind_param(
    $types,
    $employerid,
    $companyId,
    $new_job_number,
    $jobTitle,
    $jobDescription,
    $jobLocation,
    $salary,
    $contactEmail,
    $contactPhone,
    $jobType,
    $requiredcertification,
    $jobExperience,
    $jobRequirementText,
    $jobBenefit,
    $noEmplpoyeeNeeeded,
    $jobPosition,
    $jobDetailedLocation,
    $jobLocationDistrict,
    $postDuration,
    $jobStatus
  );

  //   if ($stmt->execute()) {
//     echo "<script>alert('Job posted successfully!');</script>";
//   } else {
//     echo "<script>alert('Error posting job: {$stmt->error}');</script>";
//   }
//   $stmt->close();
// }
  if ($stmt->execute()) {
    // Thay đổi thông báo và cách thoát ra
    echo "<script>alert('{$lang['job_post_is_saved_for_review']}'); window.location.href = window.location.href;</script>";
    // window.location.href = window.location.href; sẽ tải lại trang hiện tại (body.php)
    // và đóng modal, làm mới trạng thái
  } else {
    echo "<script>alert('Error posting job: {$stmt->error}');</script>";
  }
  $stmt->close();
  // Đảm bảo kết nối được đóng sau khi xử lý POST
  $conn->close(); // Nếu bạn không đóng ở config.php
  exit; // Quan trọng: dừng script sau khi xử lý POST
}
?>
<style>
  .hero {
    position: relative;
    color: white;
    background: url('https://via.placeholder.com/1500x700') no-repeat center center/cover;
    height: 90vh;
    display: flex;
    align-items: center;
    padding: 40px;
  }

  .hero-overlay {
    background: rgba(0, 0, 0, 0.5);
    padding: 40px;
    max-width: 700px;
    border-radius: 12px;
  }

  .hero h1 {
    font-size: 48px;
    color: #ff0000;
    margin-bottom: 20px;
  }

  .hero ul {
    list-style: disc;
    padding-left: 20px;
    color: white;
    font-size: 18px;
    margin-bottom: 30px;
  }

  .hero-buttons {
    display: flex;
    gap: 20px;
  }

  .hero-buttons button {
    padding: 12px 20px;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
  }

  .consult-btn {
    background: white;
    color: #d40000;
    border: 2px solid #d40000;
  }

  .post-now-btn {
    background: #d40000;
    color: white;
  }

  .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow-y: auto;
  }

  .modal-content {
    background-color: #fff;
    margin: 40px auto 40px auto;
    /* Cách đều trên/dưới */
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    text-align: center;
    position: relative;
    top: 0;
  }

  .closeBtn {
    float: right;
    font-size: 24px;
    cursor: pointer;
  }

  @media (max-width: 768px) {
    .hero {
      height: auto;
      padding: 20px;
      justify-content: center;
    }

    .hero-overlay {
      padding: 20px;
      text-align: center;
    }

    .hero h1 {
      font-size: 28px;
    }

    .hero ul {
      font-size: 16px;
      text-align: left;
    }

    .hero-buttons {
      flex-direction: column;
      gap: 10px;
    }

    .hero-buttons button {
      width: 100%;
    }

    .form-table,
    .form-table tr,
    .form-table td {
      display: block;
      width: 100%;
    }

    .form-table td {
      padding: 5px 0;
    }

    .form-table label {
      margin-bottom: 5px;
      display: block;
    }

    .form-table input,
    .form-table select,
    .form-table textarea {
      width: 100%;
      box-sizing: border-box;
    }
  }
</style>
<section class="hero" style="background: url('image/employerpagebackground.png') no-repeat center center/cover;">
  <div class="hero-overlay">
    <h1><?php echo $lang['hero_section']; ?></h1>
    <ul>
      <li><?php echo $lang['hero_section_desc']['post_jobs_free']; ?></li>
      <li><?php echo $lang['hero_section_desc']['candidate_pool']; ?></li>
      <li><?php echo $lang['hero_section_desc']['toppy_ai']; ?></li>
    </ul>
    <div class="hero-buttons">
      <button class="consult-btn"><?php echo $lang['recruitment_consulting']; ?></button>
      <button class="post-now-btn" id="openModalBtn"><?php echo $lang['post_job_now']; ?></button>
    </div>
  </div>
</section>

<?php
$companyOptions = [];
if (isset($_SESSION['employerid'])) {
  $currentEmployerId = (int) $_SESSION['employerid'];
  $companyOptionsStmt = $conn->prepare("SELECT id, company_name FROM company WHERE employer_id = ? ORDER BY company_name ASC");
  if ($companyOptionsStmt) {
    $companyOptionsStmt->bind_param("i", $currentEmployerId);
    $companyOptionsStmt->execute();
    $companyOptionsResult = $companyOptionsStmt->get_result();
    while ($companyRow = $companyOptionsResult->fetch_assoc()) {
      $companyOptions[] = $companyRow;
    }
    $companyOptionsStmt->close();
  }
}
?>

<div id="myModal" class="modal">
  <div class="modal-content">
    <span class="closeBtn">&times;</span>
    <h2><?= $lang['post_job_form_title']; ?></h2>
    <h4 style="color: grey; margin-bottom: 20px;"><?= $lang['post_job_form_subtitle']; ?></h4>
    <form method="POST">
      <table class="form-table">
        <tr>
          <td><label for="company_id"><?= $lang['choose_company']; ?>:</label></td>
          <td>
            <select id="company_id" name="company_id" required>
              <option value=""><?= $lang['choose_company']; ?></option>
              <?php foreach ($companyOptions as $company): ?>
                <option value="<?= (int) $company['id']; ?>"><?= htmlspecialchars((string) $company['company_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <tr>
          <td><label for="jobTitle"><?= $lang['post_job_form_job_title']; ?>:</label></td>
          <td><input type="text" id="jobTitle" name="jobTitle" placeholder="<?= $lang['job_title_placeholder']; ?>"
              required></td>
        </tr>
        <tr>
          <td><label for="jobDescription"><?= $lang['post_job_form_job_description']; ?>:</label></td>
          <td><textarea id="jobDescription" name="jobDescription"
              placeholder="<?= $lang['job_description_placeholder']; ?>" required></textarea>
          </td>
        </tr>
        <tr>
          <td><label for="jobPosition"><?= $lang['post_job_form_job_position']; ?>:</label></td>
          <td>
            <select id="jobPosition" name="jobPosition" required>
              <option value=""><?= $lang['post_job_form_job_position']; ?></option>
              <?php foreach (['senior', 'junior', 'cong_tac_vien', 'manager', 'director', 'lead', 'staff', 'specialist', 'assistant', 'executive', 'other'] as $positionValue): ?>
                <option value="<?= htmlspecialchars($positionValue) ?>"><?= htmlspecialchars(translateJobPosition($positionValue, $lang)) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <tr>
          <td><label for="jobLocation"><?= $lang['post_job_form_job_location']; ?>:</label></td>
          <td><input type="text" id="jobLocation" name="jobLocation" placeholder="<?= $lang['job_city_placeholder']; ?>"
              required></td>
        </tr>
        <tr>
          <td><label for="salary"><?= $lang['post_job_form_salary']; ?>:</label></td>
          <td><input type="text" id="salary" name="salary" placeholder="<?= $lang['required']; ?>" required></td>
        </tr>
        <tr>
          <td><label for="employee_gender"><?= $lang['employee_gender_quick_post']; ?></label></td>
          <td>
            <select id="employee_gender" required>
              <option value="Nam"><?= $lang['employee_gender_male']; ?></option>
              <option value="Nữ"><?= $lang['employee_gender_female']; ?></option>
              <option value="Not_required"><?= $lang['employee_gender_not_required']; ?></option>
            </select>
        </tr>
        <tr>
          <td><label for="contactEmail"><?= $lang['post_job_form_contact_email']; ?>:</label></td>
          <td><input type="email" id="contactEmail" name="contactEmail" placeholder="<?= $lang['required']; ?>"
              required></td>
        </tr>
        <tr>
          <td><label for="contactPhone"><?= $lang['post_job_form_contact_phone']; ?>:</label></td>
          <td><input type="text" id="contactPhone" name="contactPhone" placeholder="<?= $lang['required']; ?>" required>
          </td>
        </tr>
        <tr>
          <td><label for="noEmplpoyeeNeeded"><?= $lang['post_job_form_no_employee_needed']; ?>:</label></td>
          <td>
            <input type="number" id="noEmplpoyeeNeeded" name="noEmplpoyeeNeeded" placeholder="<?= $lang['required']; ?>"
              min="0" oninput="this.value = Math.abs(this.value)">
          </td>
        </tr>
        <tr>
          <td><label for="jobDetailedLocation"><?= $lang['post_job_form_job_detailed_location']; ?>:</label></td>
          <td><input type="text" id="jobDetailedLocation" name="jobDetailedLocation"
              placeholder="<?= $lang['job_detailedlocation_placeholder']; ?>"></td>
        </tr>
        <tr>
          <td><label for="jobLocationDistrict"><?= $lang['post_job_form_job_location_district']; ?>:</label></td>
          <td><input type="text" id="jobLocationDistrict" name="jobLocationDistrict"
              placeholder="<?= $lang['optional']; ?>"></td>
        </tr>
        <tr>
          <td><label for="jobType"><?= $lang['post_job_form_job_type']; ?>:</label></td>
          <td>
            <select id="jobType" name="jobType" required>
              <option value="Full-time">Full-time</option>
              <option value="Part-time">Part-time</option>
              <option value="Internship">Internship</option>
              <option value="Freelance">Freelance</option>
              <option value="Remote">Remote</option>
            </select>
          </td>
        </tr>
        <tr>
          <td><label for="requiredcertification"><?= $lang['post_job_form_required_certification']; ?>:</label></td>
          <td><input type="text" id="requiredcertification" name="requiredcertification"
              placeholder="<?= $lang['optional']; ?>" required></td>
        </tr>
        <tr>
          <td><label for="jobExperience"><?= $lang['post_job_form_job_experience']; ?>:</label></td>
          <td>
            <select id="jobExperience" name="jobExperience" required>
              <option value=""><?= $lang['post_job_form_experience_placeholder']; ?></option>
              <?php foreach ($lang['post_job_form_experience_options'] as $value => $label): ?>
                <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <tr>
          <td><label for="jobRequirementText"><?= $lang['post_job_form_job_requirement']; ?>:</label></td>
          <td>
            <textarea id="jobRequirementText" name="jobRequirementText"
              placeholder="<?= $lang['job_requirement_placeholder']; ?>" required></textarea>
          </td>
        </tr>
        <tr>
          <td><label for="jobBenefit"><?= $lang['post_job_form_job_benefit']; ?>:</label></td>
          <td>
            <textarea id="jobBenefit" name="jobBenefit"
              placeholder="<?= $lang['job_benefit_placeholder']; ?>"></textarea>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="text-align:center;">
            <button type="submit"><?= $lang['post_job_form_submit']; ?></button>
          </td>
        </tr>
      </table>
    </form>
  </div>
</div>
<style>
  .form-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 10px;
  }

  .form-table td {
    padding: 6px 8px;
    vertical-align: middle;
  }

  .form-table label {
    font-weight: 500;
    white-space: nowrap;
  }

  .form-table input[type="text"],
  .form-table input[type="email"],
  .form-table input[type="number"],
  .form-table input[type="file"],
  .form-table textarea,
  .form-table select {
    width: 100%;
    padding: 7px 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 15px;
    box-sizing: border-box;
  }

  .form-table textarea {
    resize: vertical;
    min-height: 60px;
  }

  .form-table button[type="submit"] {
    padding: 10px 30px;
    background: #d40000;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
  }
</style>


<script>
  const modal = document.getElementById("myModal");
  const openBtn = document.getElementById("openModalBtn");
  const closeBtn = document.querySelector(".closeBtn");

  openBtn.onclick = () => {
    modal.style.display = "block";
  }

  closeBtn.onclick = () => {
    modal.style.display = "none";
  }

  window.onclick = (event) => {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }

</script>

