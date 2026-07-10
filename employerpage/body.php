<?php
require_once 'config.php';
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
  $companyName = $_POST['companyName'];
  $companySize = $_POST['company_size']; // This will be set by the hidden input
  if (isset($_POST['custom_company_size']) && !empty($_POST['custom_company_size'])) {
    $companySize = $_POST['custom_company_size'];
  }
  $jobRequirementText = $_POST['jobRequirementText'];
  $jobBenefit = $_POST['jobBenefit'];
  $jobTitle = $_POST['jobTitle'];
  $jobDescription = $_POST['jobDescription'];
  $jobLocation = $_POST['jobLocation'];
  $salary = $_POST['salary'];
  $post_duration = $_POST['post_duration'];
  $contactEmail = $_POST['contactEmail'];
  $contactPhone = $_POST['contactPhone'];
  $jobType = $_POST['jobType'];
  $jobCategory = $_POST['jobCategory'];
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
  $employerid = $_SESSION['employerid'];

  // Handle file upload
  if (isset($_FILES['companylogo']) && $_FILES['companylogo']['error'] == 0) {
    $targetDir = "uploads/companylogo/";
    if (!is_dir($targetDir)) {
      mkdir($targetDir, 0777, true);
    }
    $fileName = basename($_FILES["companylogo"]["name"]);
    $targetFile = $targetDir . uniqid() . "_" . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check file size (1MB limit)
    if ($_FILES["companylogo"]["size"] > 1000000) {
      echo "<script>alert('Sorry, your file is too large.');</script>";
      exit;
    }

    // Allow certain file formats
    $allowedTypes = ['jpg', 'png', 'jpeg', 'gif'];
    if (!in_array($fileType, $allowedTypes)) {
      echo "<script>
      alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');
      window.history.back();
      </script>";
      exit;
    }

    // Upload file
    if (!move_uploaded_file($_FILES["companylogo"]["tmp_name"], $targetFile)) {
      echo "<script>alert('Sorry, there was an error uploading your file.');
      window.history.back();
      </script>";
      exit;
    }
  } else {
    $targetFile = ""; // No file uploaded
  }
  if (empty($targetFile)) {
    $targetFile = "uploads/default.png"; // Default image if no upload
  }

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
    posted_by_employer_id, job_post_number_by_employer, company_name, job_title, job_description, job_location, salary, post_duration, contact_email, contact_phone, job_type, job_category, required_certification, job_experience, company_logo, job_requirement, job_benefit, company_size, no_employee_needed, job_position, job_detailed_location, job_location_district, status
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

  if (!$stmt) {
    echo "<script>alert('Error preparing statement: {$conn->error}');</script>";
    exit;
  }

  $stmt->bind_param(
    "iisssssissssssssssissss",
    $employerid,
    $new_job_number,
    $companyName,
    $jobTitle,
    $jobDescription,
    $jobLocation,
    $salary,
    $post_duration,
    $contactEmail,
    $contactPhone,
    $jobType,
    $jobCategory,
    $requiredcertification,
    $jobExperience,
    $targetFile,
    $jobRequirementText,
    $jobBenefit,
    $companySize,
    $noEmplpoyeeNeeeded,
    $jobPosition,
    $jobDetailedLocation,
    $jobLocationDistrict,
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
// include modal form for posting job
// This modal will be triggered when the user clicks on the "Post a Job Now" button
?>

<div id="myModal" class="modal">
  <div class="modal-content">
    <span class="closeBtn">&times;</span>
    <h2><?= $lang['post_job_form_title']; ?></h2>
    <h4 style="color: grey; margin-bottom: 20px;"><?= $lang['post_job_form_subtitle']; ?></h4>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
      <table class="form-table">
        <tr>
          <td><label for="companyName"><?= $lang['post_job_form_company_name']; ?>:</label></td>
          <td><input type="text" id="companyName" name="companyName" placeholder="<?= $lang['required']; ?>" required>
          </td>
        </tr>
        <tr>
          <td><label for="company_size_select"><?= $lang['post_job_form_company_size']; ?>:</label></td>
          <td>
            <select id="company_size_select" onchange="toggleCustomInput()" required>
              <option value=""><?= $lang['post_job_form_company_size_select']; ?></option>
              <option value="1-10"><?= $lang['post_job_form_company_size_options']['1-10']; ?></option>
              <option value="11-50"><?= $lang['post_job_form_company_size_options']['11-50']; ?></option>
              <option value="51-200"><?= $lang['post_job_form_company_size_options']['51-200']; ?></option>
              <option value="201-500"><?= $lang['post_job_form_company_size_options']['201-500']; ?></option>
              <option value="501-1000"><?= $lang['post_job_form_company_size_options']['501-1000']; ?></option>
              <option value="1001-5000"><?= $lang['post_job_form_company_size_options']['1001-5000']; ?></option>
              <option value="5000+"><?= $lang['post_job_form_company_size_options']['5000+']; ?></option>
              <option value="unknown"><?= $lang['post_job_form_company_size_options']['unknown']; ?></option>
              <option value="other"><?= $lang['post_job_form_company_size_options']['other']; ?></option>
            </select>
            <input type="hidden" name="company_size" id="company_size">
            <div id="custom_input_wrapper" style="display: none; margin-top: 8px;">
              <input type="text" id="custom_company_size" name="custom_company_size"
                placeholder="<?= $lang['custom_companysize_placeholder']; ?>" title="Enter like 10-150 or 5000+">
            </div>
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
          <td><label for="post_duration"><?= $lang['post_job_form_post_duration']; ?>:</label></td>
          <td><input type="number" id="post_duration" name="post_duration"
              placeholder="<?= $lang['job_expiration_placeholder']; ?>"></td>
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
          <td><label for="jobCategory"><?= $lang['post_job_form_job_category']; ?>:</label></td>
          <td>
            <select id="jobCategory" name="jobCategory" onchange="toggleCustomInput2()" required>
              <option value=""><?= $lang['chon_nganh_nghe'] ?></option>
              <option value="kinh_doanh_ban_hang"><?= $lang['kinh_doanh_ban_hang'] ?></option>
              <option value="marketing_truyen_thong"><?= $lang['marketing_truyen_thong'] ?></option>
              <option value="tai_chinh_ke_toan_ngan_hang"><?= $lang['tai_chinh_ke_toan_ngan_hang'] ?></option>
              <option value="cong_nghe_thong_tin"><?= $lang['cong_nghe_thong_tin'] ?></option>
              <option value="nhan_su"><?= $lang['nhan_su'] ?></option>
              <option value="hanh_chinh_van_phong"><?= $lang['hanh_chinh_van_phong'] ?></option>
              <option value="ky_thuat"><?= $lang['ky_thuat'] ?></option>
              <option value="san_xuat_van_hanh_day_chuyen"><?= $lang['san_xuat_van_hanh_day_chuyen'] ?></option>
              <option value="van_tai_logistics"><?= $lang['van_tai_logistics'] ?></option>
              <option value="bat_dong_san"><?= $lang['bat_dong_san'] ?></option>
              <option value="bao_hiem"><?= $lang['bao_hiem'] ?></option>
              <option value="xay_dung_kien_truc"><?= $lang['xay_dung_kien_truc'] ?></option>
              <option value="y_te_duoc_pham"><?= $lang['y_te_duoc_pham'] ?></option>
              <option value="giao_duc_dao_tao"><?= $lang['giao_duc_dao_tao'] ?></option>
              <option value="du_lich_khach_san_nha_hang"><?= $lang['du_lich_khach_san_nha_hang'] ?></option>
              <option value="luat"><?= $lang['luat'] ?></option>
              <option value="thiet_ke_nghe_thuat"><?= $lang['thiet_ke_nghe_thuat'] ?></option>
              <option value="lao_dong_pho_thong"><?= $lang['lao_dong_pho_thong'] ?></option>
              <option value="nong_nghiep"><?= $lang['nong_nghiep'] ?></option>
              <option value="food_beverage"><?= $lang['food_beverage'] ?></option>
              <option value="nha_nuoc_cong_vu"><?= $lang['nha_nuoc_cong_vu'] ?></option>
              <option value="khac"><?= $lang['khac'] ?></option>
            </select>
            <input type="hidden" name="jobCategoryHidden" id="jobCategoryHidden">
            <div id="custom_input_wrapper2" style="display: none; margin-top: 8px;">
              <input type="text" id="custom_job_category" name="custom_job_category"
                placeholder="<?= $lang['custom_jobcategory_placeholder']; ?>">
            </div>
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
          <td><label for="companylogo"><?= $lang['post_job_form_company_logo']; ?>:</label></td>
          <td><input type="file" id="companylogo" name="companylogo" accept="image/*"></td>
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

<script>
  function toggleCustomInput() {
    const select = document.getElementById('company_size_select');
    const customWrapper = document.getElementById('custom_input_wrapper');
    const hiddenInput = document.getElementById('company_size');

    if (select.value === 'other') {
      customWrapper.style.display = 'block';
      hiddenInput.value = ''; // chờ người dùng gõ
    } else {
      customWrapper.style.display = 'none';
      hiddenInput.value = select.value;
    }
  }

  function toggleCustomInput2() {
    const select = document.getElementById('jobCategory');
    const customWrapper = document.getElementById('custom_input_wrapper2');
    const hiddenInput = document.getElementById('jobCategoryHidden');

    if (select.value === 'khac') {
      customWrapper.style.display = 'block';
      hiddenInput.value = ''; // chờ người dùng gõ
    } else {
      customWrapper.style.display = 'none';
      hiddenInput.value = select.value;
    }
  }

  function updateHiddenInput() {
    const customInput = document.getElementById('custom_company_size');
    const hiddenInput = document.getElementById('company_size');
    hiddenInput.value = customInput.value;
  }
</script>