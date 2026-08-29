<link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
<?php
// Đặt ở ĐẦU file jobs.php (hoặc index.php)
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../helpers.php');

function t($key, $replace = [])
{
  global $lang;
  $text = $lang[$key] ?? $key;
  foreach ($replace as $k => $v) {
    $text = str_replace(":$k", $v, $text);
  }
  return $text;
}

$isLoggedIn = isset($_SESSION['jobseeker_id']) && $_SESSION['jobseeker_id'] > 0;
$userId = $isLoggedIn ? (int) $_SESSION['jobseeker_id'] : 0;

// Lấy danh sách job_id mà người dùng hiện tại đã lưu từ database
$savedJobIds = [];
if ($isLoggedIn) {
  $stmt = $conn->prepare("SELECT job_id FROM saved_jobs WHERE user_id = ?");
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result_saved_jobs = $stmt->get_result();
    while ($row = $result_saved_jobs->fetch_assoc()) {
      $savedJobIds[] = (string) $row['job_id']; // Đảm bảo chuyển về string để khớp với data-job-id của HTML
    }
    $stmt->close();
  } else {
    error_log("Error preparing query to fetch saved_jobs: " . $conn->error);
  }
}
$savedJobIdsJson = json_encode($savedJobIds);

?>
<style>
  body {
    margin: 0;
    font-family: 'Roboto', sans-serif;
    color: #000;
  }

  .section-1 {
    background: linear-gradient(to right, #c31432, rgb(84, 37, 117));
  }

  .container {
    max-width: 1100px;
    margin: auto;
    padding: 40px 20px;
  }

  .title {
    font-size: 24px;
    font-weight: bold;
    text-align: center;
  }

  .subtitle {
    text-align: center;
    margin-top: 5px;
    font-size: 14px;
    color: #ddd;
  }

  .search-box {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;

  }

  .search-box input,
  .search-box select {
    padding: 10px;
    border-radius: 8px;
    border: none;
    min-width: 220px;
  }

  .search-box button {
    background-color: #c4002f;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
  }

  .main-content {
    display: flex;
    margin-top: 30px;
    gap: 20px;
    flex-wrap: wrap;
  }

  .left-menu {
    flex: 1;
    min-width: 200px;
    background: white;
    color: black;
    border-radius: 10px;
    padding: 20px;
  }

  .left-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .left-menu li {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
    cursor: pointer;
  }

  .right-banner {
    flex: 3;
    min-width: 300px;
  }

  .right-banner img {
    width: 100%;
    border-radius: 10px;
  }

  .job-section {
    padding: 40px 20px;
    border-radius: 10px;
    background-color: #eee;
    margin: 10px;
  }



  .job-filters {
    margin: 20px 0;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .job-filters button {
    padding: 8px 15px;
    border: none;
    border-radius: 20px;
    background-color: #eee;
    color: #333;
    cursor: pointer;
  }

  .job-filters button.active {
    background-color: #d70018;
    color: white;
    font-weight: bold;
  }

  .sort-dropdown button {
    background-color: #e0e0e0;
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    cursor: pointer;
  }

  .section-2 {
    background-color: #eee;
  }


  .circle-logo {
    background-color: #888;
    color: white;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    text-align: center;
    line-height: 32px;
    font-weight: bold;
  }



  .pagination {
    margin-top: 30px;
    text-align: center;
  }

  .pagination .dot {
    height: 10px;
    width: 10px;
    margin: 0 4px;
    background-color: #ccc;
    border-radius: 50%;
    display: inline-block;
  }

  .pagination .dot.active {
    background-color: #d70018;
  }

  .section-3 {
    background-color: #eee;

  }

  .section-3 .container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 20px;
  }

  .section-3 .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .section-3 h2 {
    font-size: 24px;
    font-weight: bold;
    color: #000;
  }

  .section-3 .view-all {
    color: #c00;
    text-decoration: none;
    font-weight: bold;
  }

  .logos {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0;
    gap: 20px;
  }

  .logos img {
    max-height: 60px;
    object-fit: contain;
  }

  .section-4 {
    background-color: #f9f5f5;

  }

  .section-4 h3 {
    font-size: 20px;
    font-weight: bold;
    margin-top: 30px;
  }

  .section-4 ul {
    list-style: disc;
    margin-left: 20px;
  }

  .info {
    background-color: #f9f5f5;

  }

  .job-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    max-width: 1300px;
    margin: 0 auto;

  }

  .job-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
  }

  .job-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .job-header h3 {
    font-size: 16px;
    margin: 0;
  }

  .save-btn {
    background: none;
    border: none;
    font-size: 20px;
    color: #e74c3c;
    cursor: pointer;
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 10;
  }

  .job-body {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    margin-top: 10px;
  }

  .company-logo {
    width: 90px;
    height: 80px;
    object-fit: contain;
    border-radius: 8px;
    flex-shrink: 0;
    margin-right: 12px;
  }

  .job-info {
    font-size: 14px;
  }

  .job-info .company-name {
    font-weight: bold;
    margin-bottom: 5px;
    color: #818080;
  }

  .job-meta-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 4px;
  }

  .job-meta-item {
    display: inline-flex;
    align-items: center;
    font-size: 13px;
    color: #d45a5a;
  }

  .job-meta-divider {
    width: 1px;
    height: 12px;
    background-color: #ccc;
    display: inline-block;
  }

  .divider {
    border-top: 1px solid #e0e0e0;
    margin: 10px 0;
  }

  .job-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-top: 8px;
  }

  .job-footer-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    flex: 1;
  }

  .job-footer-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 8px;
    border-radius: 999px;
    background: #f3f4f6;
    color: #555;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
  }

  .save-btn {
    background: #fff1f3;
    border: 1px #e74c3c;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    margin-left: auto;
    flex-shrink: 0;
    position: static;
  }

  .save-btn .heart-icon {
    width: 18px;
    height: 18px;
  }

  @media (max-width: 992px) {
    .job-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 600px) {
    .job-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<section class="section-1">
  <div class="container">

    <!-- Tiêu đề & mô tả -->
    <div class="title"><?= $lang['job_search']; ?></div>
    <div class="subtitle"><?= $lang['job_search_subtitle']; ?></div>

    <!-- Hộp tìm kiếm -->
    <div class="search-box">
      <form action="searchpage.php" method="get" style="display: flex; gap: 10px; flex-wrap: wrap;">

        <!-- GIỮ NGUYÊN HTML TĨNH CHO DANH MỤC NGÀNH -->
        <select name="category">
          <option value=""><?php echo $lang['job_category_select_menu']; ?></option>
          <option value="IT & Software">IT & Software</option>
          <option value="Marketing">Marketing</option>
          <option value="Finance">Finance</option>
          <option value="Healthcare">Healthcare</option>
          <option value="Government & Public Sector">Government & Public Sector</option>
        </select>

        <input type="text" name="q" placeholder="<?= $lang['job_title']; ?>">
        <input type="text" name="location" placeholder="<?= $lang['location']; ?>">
        <button type="submit"><?= $lang['search']; ?></button>
      </form>
    </div>

    <!-- Bộ lọc loại công việc -->
    <div class="main-content">
      <div class="left-menu">
        <ul>
          <li id="filter-fulltime" class="job-type-filter" data-job-type="Full-time"><?= $lang['job_type_filters']['full_time']; ?></li>
          <li id="filter-parttime" class="job-type-filter" data-job-type="Part-time"><?= $lang['job_type_filters']['part_time']; ?></li>
          <li id="filter-internship" class="job-type-filter" data-job-type="Internship"><?= $lang['job_type_filters']['internship']; ?></li>
          <li id="filter-freelance" class="job-type-filter" data-job-type="Freelance"><?= $lang['job_type_filters']['freelance']; ?></li>
          <li id="filter-remote" class="job-type-filter" data-job-type="Remote"><?= $lang['job_type_filters']['remote']; ?></li>
        </ul>


        <style>
          .job-type-filter.active {
            background: #d70018;
            color: #fff;
            border-radius: 10px;
            transition: background 0.2s, color 0.2s;
          }

          .left-menu .job-type-filter {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding-left: 12px;
          }
        </style>
        <script>
          function buildJobsUrl(overrides) {
            const params = new URLSearchParams(window.location.search);
            Object.entries(overrides).forEach(function ([key, value]) {
              if (value === null || value === '') {
                params.delete(key);
              } else {
                params.set(key, value);
              }
            });
            const query = params.toString();
            return (query ? '?' + query : '?') + '#job-grid';
          }

          document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const activeType = (urlParams.get('job_type') || '').toLowerCase();

            document.querySelectorAll('.job-type-filter').forEach(function (item) {
              const itemType = (item.getAttribute('data-job-type') || '').toLowerCase();
              item.classList.toggle('active', itemType === activeType);

              item.addEventListener('click', function () {
                const selectedType = this.getAttribute('data-job-type') || '';
                window.location.href = buildJobsUrl({
                  page: '1',
                  job_type: selectedType,
                });
              });
            });
          });
        </script>

      </div>
      <div class="right-banner">
        <img src="image/jobhive.png" alt="Tuyển dụng">
      </div>
    </div>
  </div>
</section>

<div class="info">
  <section class="section-2">
    <div class="job-section">
      <div class="job-header">
        <h2><?= $lang['urgent_job_openings']; ?></h2>
      </div> <!-- giữ nguyên thẻ đóng của khối bên ngoài nếu có -->

      <div class="job-filters">
        <button class="job-category-filter" data-category="">
          <?= $lang['all_jobs']; ?>
        </button>

        <button class="job-category-filter" data-category="IT & Software">
          <?= $lang['it_software']; ?>
        </button>

        <button class="job-category-filter" data-category="Marketing">
          <?= $lang['marketing']; ?>
        </button>

        <button class="job-category-filter" data-category="Finance">
          <?= $lang['finance']; ?>
        </button>

        <button class="job-category-filter" data-category="Healthcare">
          <?= $lang['healthcare']; ?>
        </button>

        <button class="job-category-filter" data-category="Government & Public Sector">
          <?= $lang['government_public_sector']; ?>
        </button>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', function () {
          const urlParams = new URLSearchParams(window.location.search);
          const category = urlParams.get('category') || '';
          document.querySelectorAll('.job-category-filter').forEach(function (btn) {
            if (btn.getAttribute('data-category') === category) {
              btn.classList.add('active');
            } else {
              btn.classList.remove('active');
            }
          });
        });
        document.querySelectorAll('.job-category-filter').forEach(function (btn) {
          btn.addEventListener('click', function () {
            const category = btn.getAttribute('data-category') || '';
            const params = new URLSearchParams(window.location.search);
            params.set('page', '1');
            if (category) {
              params.set('category', category);
            } else {
              params.delete('category');
            }
            const query = params.toString();
            window.location.href = (query ? '?' + query : '?') + '#job-grid';
          });
        });
      </script>

      <div class="job-grid" id="job-grid">
        <?php
        // Pagination logic
        $jobs_per_page = 9;
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        $job_type = isset($_GET['job_type']) ? trim($_GET['job_type']) : '';

        $jobTypeMap = [
          'full-time' => 'full-time',
          'full time' => 'full-time',
          'toàn thời gian' => 'full-time',
          'bán thời gian' => 'part-time',
          'part-time' => 'part-time',
          'part time' => 'part-time',
          'thực tập' => 'internship',
          'internship' => 'internship',
          'thời vụ' => 'freelance',
          'freelance' => 'freelance',
          'làm việc từ xa' => 'remote',
          'remote' => 'remote',
        ];
        $normalized_job_type = strtolower($job_type);
        if ($job_type !== '' && isset($jobTypeMap[$normalized_job_type])) {
          $job_type = $jobTypeMap[$normalized_job_type];
        } elseif ($job_type !== '') {
          $job_type = strtolower($job_type);
        }

        // Get total APPROVED jobs count
        $count_sql = "SELECT COUNT(*) as total FROM job WHERE status = 'approved'";
        $count_bind_types = '';
        $count_params = [];
        if ($category !== '') {
          $count_sql .= " AND job_category = ?";
          $count_bind_types .= 's';
          $count_params[] = $category;
        }
        if ($job_type !== '') {
          $count_sql .= " AND LOWER(job_type) = ?";
          $count_bind_types .= 's';
          $count_params[] = $job_type;
        }
        $stmt_count = $conn->prepare($count_sql);
        if (!empty($count_params)) {
          $stmt_count->bind_param($count_bind_types, ...$count_params);
        }
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $total_jobs = $result_count ? (int) $result_count->fetch_assoc()['total'] : 0;
        $total_pages = max(1, (int) ceil($total_jobs / $jobs_per_page));

        // Fetch APPROVED jobs for current page
        $offset = ($page - 1) * $jobs_per_page;
        $sql = "SELECT id, job_title, company_logo, company_name, salary, job_location, job_type, job_experience, created_at, post_duration 
                        FROM job 
                        WHERE status = 'approved'";
        $bind_types = '';
        $params = [];
        if ($category !== '') {
          $sql .= " AND job_category = ?";
          $bind_types .= 's';
          $params[] = $category;
        }
        if ($job_type !== '') {
          $sql .= " AND LOWER(job_type) = ?";
          $bind_types .= 's';
          $params[] = $job_type;
        }
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $bind_types .= 'ii';
        $params[] = $jobs_per_page;
        $params[] = $offset;
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
          $stmt->bind_param($bind_types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Output jobs (only 9 per page)
        if ($result && $result->num_rows > 0):
          while ($row = $result->fetch_assoc()):
            $created_at = new DateTime($row['created_at']);
            $post_duration = (int) $row['post_duration'];
            $expire_at = clone $created_at;
            $expire_at->modify("+$post_duration days");
            $now = new DateTime();
            $interval = $now->diff($expire_at);
            $days_left = (int) $interval->format('%r%a');
            $days_left_text = $days_left > 0
              ? t('dayleft', ['d' => $days_left])
              : t('expired');
            $job_id = (int) $row['id'];
            $jobExperienceLabel = translateJobExperience($row['job_experience'] ?? '', $lang);
            $isJobAlreadySaved = in_array((string) $job_id, $savedJobIds);
            if ($isJobAlreadySaved) {
              $savedClass = 'active';
            } else {
              $savedClass = '';
            }
            ?>
            <div class="job-card">
              <a href="jobdetail.php?id=<?php echo $job_id; ?>" style="text-decoration:none;color:inherit;">
                <div class="job-body">
                  <img src="<?php echo htmlspecialchars($row['company_logo']); ?>" alt="Company Logo" class="company-logo">
                  <div class="job-info">
                    <div class="job-header">
                    <h3><?php echo htmlspecialchars($row['job_title']); ?></h3>
                    </div>
                    <div class="company-name"><?php echo htmlspecialchars($row['company_name']); ?></div>
                    <div class="job-meta-row">
                      <div class="job-meta-item"><?php echo htmlspecialchars($row['salary']); ?></div>
                      <span class="job-meta-divider" aria-hidden="true"></span>
                      <div class="job-meta-item"><?php echo htmlspecialchars($row['job_location']); ?></div>
                    </div>
                  </div>
                </div>
              </a>
              <div class="job-footer">
                <div class="job-footer-meta">
                  <div class="job-footer-pill"><?php echo htmlspecialchars($row['job_type'] ?? ''); ?></div>
                  <div class="job-footer-pill"><?php echo htmlspecialchars($jobExperienceLabel); ?></div>
                  <div class="job-footer-pill"><?php echo $days_left_text; ?></div>
                </div>
                <button class="save-btn" aria-pressed="<?php echo $isJobAlreadySaved ? 'true' : 'false'; ?>"
                  title="Save job" data-job-id="<?php echo $job_id; ?>">
                  <svg class="heart-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#e74c3c"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path
                      d="M12 21s-6.5-5.5-8.5-8.5C1.5 9.5 3.5 6 7 6c1.7 0 3.4 1.1 4.1 2.7C11.6 7.1 13.3 6 15 6c3.5 0 5.5 3.5 3.5 6.5C18.5 15.5 12 21 12 21z"
                      fill="<?php echo $isJobAlreadySaved ? '#e74c3c' : '#fff'; ?>" />
                  </svg>
                </button>
              </div>
            </div>
            <?php
          endwhile;
        else:
          ?>
          <div><?= $lang['no_jobs_found']; ?></div>
        <?php endif; ?>
      </div>

      <script>
        // Lưu trữ biến lang trong window để sử dụng trong các script khác
        window.lang = <?= json_encode($lang) ?>;
        // Các biến PHP truyền sang JavaScript
        window.isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        window.currentUserId = <?php echo $userId; ?>;
        // Danh sách Job ID đã lưu của người dùng hiện tại, lấy từ database
        window.initialSavedJobIds = <?php echo $savedJobIdsJson; ?>;

        document.addEventListener('DOMContentLoaded', function () {
          // Khởi tạo trạng thái cho localStorage nếu chưa có hoặc không khớp
          // Điều này đảm bảo trạng thái localStorage luôn phản ánh dữ liệu từ DB khi tải trang
          localStorage.setItem('savedJobs', JSON.stringify(window.initialSavedJobIds));

          document.querySelectorAll('.save-btn').forEach(function (btn) {
            const jobId = btn.getAttribute('data-job-id');
            const heartPath = btn.querySelector('.heart-icon path');
            // Lấy trạng thái hiện tại từ localStorage (đã được đồng bộ với DB ở trên)
            let savedJobs = JSON.parse(localStorage.getItem('savedJobs') || '[]');

            // Cập nhật trạng thái ban đầu của nút dựa trên dữ liệu PHP (và localStorage)
            if (savedJobs.includes(jobId)) {
              heartPath.setAttribute('fill', '#e74c3c');
              btn.setAttribute('aria-pressed', 'true');
            } else {
              heartPath.setAttribute('fill', '#fff');
              btn.setAttribute('aria-pressed', 'false');
            }

            btn.addEventListener('click', function (e) {
              // Kiểm tra đăng nhập
              if (!window.isLoggedIn) {
                showPopup(window.lang.login_to_save_jobs);
                return;
              }

              let currentSavedJobs = JSON.parse(localStorage.getItem('savedJobs') || '[]');
              const isCurrentlySaved = btn.getAttribute('aria-pressed') === 'true';
              let action;

              if (!isCurrentlySaved) { // Chưa lưu -> Lưu
                heartPath.setAttribute('fill', '#e74c3c');
                btn.setAttribute('aria-pressed', 'true');
                currentSavedJobs.push(jobId);
                showPopup(window.lang.job_saved_successfully);
                action = 'save';
              } else { // Đã lưu -> Hủy lưu
                heartPath.setAttribute('fill', '#fff');
                btn.setAttribute('aria-pressed', 'false');
                currentSavedJobs = currentSavedJobs.filter(id => id !== jobId);
                showPopup(window.lang.job_unsaved_successfully);
                action = 'unsave';
              }
              localStorage.setItem('savedJobs', JSON.stringify(currentSavedJobs));
              saveJobAjax(jobId, action); // Gửi yêu cầu AJAX
            });
          });

          // Hàm gửi yêu cầu AJAX đến save_job_action.php
          function saveJobAjax(jobId, action) {
            fetch('save_job_action.php', { // Tên file xử lý AJAX mới
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: 'action=' + encodeURIComponent(action) + '&job_id=' + encodeURIComponent(jobId)
            })
              .then(response => {
                if (!response.ok) {
                  throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json(); // Chờ phản hồi JSON từ server
              })
              .then(data => {
                if (data.status === 'success') {
                  console.log('Database update successful:', data.message);
                } else {
                  console.error('Database update failed:', data.message);
                  // Optionally handle error here, e.g., revert UI changes
                  showPopup('Error: ' + data.message); // Show error from server
                }
              })
              .catch(error => {
                console.error('AJAX request error:', error);
                showPopup('Connection or processing error. Please try again.');
              });
          }

          // Hàm hiển thị Popup (giữ nguyên từ code của bạn)
          function showPopup(message) {
            let existing = document.getElementById('job-popup-message');
            if (existing) {
              existing.remove();
            }
            let popup = document.createElement('div');
            popup.id = 'job-popup-message';
            popup.textContent = message;
            popup.style.position = 'fixed';
            popup.style.top = '30px';
            popup.style.left = '50%';
            popup.style.transform = 'translateX(-50%)';
            popup.style.background = '#fff';
            popup.style.color = '#e74c3c';
            popup.style.padding = '12px 28px';
            popup.style.borderRadius = '8px';
            popup.style.boxShadow = '0 2px 12px rgba(0,0,0,0.15)';
            popup.style.zIndex = 9999;
            popup.style.opacity = 1;
            document.body.appendChild(popup);
            setTimeout(() => {
              popup.style.transition = 'opacity 0.4s';
              popup.style.opacity = 0;
              setTimeout(() => popup.remove(), 400);
            }, 3000);
          }
        });
      </script>

      <div class="pagination" id="job-pagination">
        <a href="<?php echo $page > 1 ? '?page=' . ($page - 1) . ($category ? '&category=' . urlencode($category) : '') . ($job_type ? '&job_type=' . urlencode($job_type) : '') : 'javascript:void(0);'; ?>" class="arrow<?php if ($page <= 1)
                       echo ' disabled'; ?>" <?php if ($page <= 1)
                           echo 'tabindex="-1" aria-disabled="true"'; ?>
          data-scroll="1">
          &larr;
        </a>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <a href="?page=<?php echo $i; ?><?php if ($category) echo '&category=' . urlencode($category); ?><?php if ($job_type) echo '&job_type=' . urlencode($job_type); ?>" class="page-number<?php if ($i == $page)
               echo ' active'; ?>" data-scroll="1">
            <?php echo $i; ?>
          </a>
        <?php endfor; ?>
        <a href="<?php echo $page < $total_pages ? '?page=' . ($page + 1) . ($category ? '&category=' . urlencode($category) : '') . ($job_type ? '&job_type=' . urlencode($job_type) : '') : 'javascript:void(0);'; ?>" class="arrow<?php if ($page >= $total_pages)
                       echo ' disabled'; ?>" <?php if ($page >= $total_pages)
                           echo 'tabindex="-1" aria-disabled="true"'; ?> data-scroll="1">&rarr;
        </a>
      </div>
      <script>
        // Khi click vào pagination, thêm hash để biết cần scroll sau khi reload
        document.querySelectorAll('#job-pagination a[data-scroll="1"]').forEach(function (a) {
          a.addEventListener('click', function (e) {
            if (this.getAttribute('href').startsWith('?page=')) {
              // Thêm hash để sau reload biết cần scroll
              window.location.href = this.getAttribute('href') + '#job-grid';
              e.preventDefault();
            }
          });
        });

        // Khi trang tải lại, nếu có hash #job-grid thì scroll tới .job-grid
        document.addEventListener('DOMContentLoaded', function () {
          if (window.location.hash === '#job-grid') {
            var el = document.querySelector('.job-grid');
            if (el) {
              el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
          }
        });
      </script>
      <style>
        .pagination .page-number {
          display: inline-block;
          min-width: 24px;
          padding: 4px 8px;
          margin: 0 2px;
          border-radius: 6px;
          background: #ccc;
          color: #333;
          font-weight: bold;
          text-align: center;
          text-decoration: none;
          transition: background 0.2s, color 0.2s;
        }

        .pagination .page-number.active {
          background: #d70018;
          color: #fff;
          pointer-events: none;
        }

        .pagination .arrow {
          display: inline-block;
          min-width: 24px;
          padding: 4px 8px;
          margin: 0 2px;
          border-radius: 6px;
          background: #eee;
          color: #333;
          font-weight: bold;
          text-align: center;
          cursor: pointer;
          text-decoration: none;
          transition: background 0.2s, color 0.2s;
        }

        .pagination .arrow.disabled {
          background: #f3f3f3;
          color: #bbb;
          cursor: not-allowed;
          pointer-events: none;
        }

        .pagination a {
          text-decoration: none;
        }
      </style>
    </div>
  </section>


  <section class="section-3">
    <div class="container">
      <div class="header">
        <h2><?= $lang['featured_companies']; ?></h2>
        <a href="#" class="view-all"><?= $lang['view_all']; ?></a>
      </div>

      <div class="logos">
        <img src="image/logo1.png" alt="Tabtab.me" />
        <img src="image/logo2.png" alt="DIC" />
        <img src="image/logo3.png" alt="NhanHoa" />
        <img src="image/logo4.png" alt="EY" />
        <img src="image/logo5.png" alt="Karofi" />
      </div>
    </div>
  </section>

  <section class="section-4">
    <div class="container">
      <h3><?= $lang['opportunities_at_top_companies']; ?></h3>
      <p><?= $lang['opportunities_at_top_companies_desc']; ?></p>

      <h3><?= $lang['why_jobhive']; ?></h3>
      <ul>
        <li>
          <strong><?= $lang['quality_jobs']; ?></strong><br />
          <?= $lang['quality_jobs_desc']; ?>
        </li>

        <li>
          <strong><?= $lang['free_cv_builder']; ?></strong><br />
          <?= $lang['free_cv_builder_desc']; ?>
        </li>

        <li>
          <strong><?= $lang['job_seeker_support']; ?></strong><br />
          <?= $lang['job_seeker_support_desc']; ?>
        </li>
      </ul>

      <p><?= $lang['find_best_salary']; ?></p>
    </div>
  </section>

</div>