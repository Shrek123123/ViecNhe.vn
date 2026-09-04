<link href="https://fonts.com/css2?family=Roboto&display=swap" rel="stylesheet">
<?php
// Đặt ở ĐẦU file jobs.php (hoặc index.php)
require_once('config.php'); // Đảm bảo file này chứa kết nối $conn

$isLoggedIn = isset($_SESSION['jobseeker_id']) && $_SESSION['jobseeker_id'] > 0;
$userId = $isLoggedIn ? (int) $_SESSION['jobseeker_id'] : 0;

// Helper function for bind_param with dynamic arguments
// Hàm này tạo ra các tham chiếu từ một mảng, cần thiết cho bind_param()
function refValues($arr){
    if (strnatcmp(phpversion(),'5.3') >= 0) // Kiểm tra phiên bản PHP >= 5.3
    {
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key]; // Lấy tham chiếu của mỗi phần tử
        return $refs;
    }
    return $arr; // Đối với PHP < 5.3, không cần thiết phải làm gì đặc biệt
}

function normalizeCompanyLogoPath($path)
{
    $value = trim((string) $path);
    if ($value === '') {
        return 'image/logo1.png';
    }

    $value = preg_replace('#^(?:\./)+#', '', $value);
    $value = preg_replace('#^(?:\.\./)+#', '', $value);
    $value = preg_replace('#^/+#', '', $value);

    if (stripos($value, 'uploads/companylogo/') === 0) {
        return $value;
    }

    if (stripos($value, 'companylogo/') === 0) {
        return 'uploads/' . $value;
    }

    if (stripos($value, 'uploads/') === 0) {
        return $value;
    }

    if (stripos($value, 'http://') === 0 || stripos($value, 'https://') === 0) {
        return $value;
    }

    return 'uploads/companylogo/' . basename($value);
}


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

$industryOptionKeys = [
    'kinh_doanh_ban_hang',
    'marketing_truyen_thong',
    'tai_chinh_ke_toan_ngan_hang',
    'cong_nghe_thong_tin',
    'y_te_duoc_pham',
    'nha_nuoc_cong_vu',
    'giao_duc_dao_tao',
    'san_xuat_van_hanh_day_chuyen',
    'van_tai_logistics',
    'bat_dong_san',
    'bao_hiem',
    'xay_dung_kien_truc',
    'du_lich_khach_san_nha_hang',
    'luat',
    'thiet_ke_nghe_thuat',
    'nong_nghiep',
    'food_beverage',
    'nhan_su',
    'hanh_chinh_van_phong',
    'ky_thuat',
    'khac'
];

$industryFilterOptions = [];
foreach ($industryOptionKeys as $industryKey) {
    if (isset($lang[$industryKey]) && is_string($lang[$industryKey]) && $lang[$industryKey] !== '') {
        $industryFilterOptions[] = [
            'value' => $industryKey,
            'label' => $lang[$industryKey],
        ];
    }
}

?>
<style>
    body {
        margin: 0;
        font-family: 'Roboto', sans-serif;
        color: #000;
    }

    .section-1 {
        background: linear-gradient(to right, #c31432, #240b36);
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

    .job-section {
        padding: 40px 20px;
        border-radius: 10px;
        background-color: #eee;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        max-width: 1000px;
        margin: 0 auto;
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

    .sort-by-container {
        max-width: 1000px;
        margin: 0 auto 20px;
        display: flex;
        justify-content: flex-end;
        padding: 0 20px;
        box-sizing: border-box;
    }

    .sort-by-form {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sort-by-form select,
    .sort-by-form button {
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 14px;
    }

    .sort-by-form select {
        border: 1px solid #ccc;
        background: #fff;
        cursor: pointer;
    }

    .sort-by-form button {
        border: 0;
        background: #d70018;
        color: #fff;
        cursor: pointer;
    }

    .section-2 {
        background-color: #eee;
        padding: 20px 0;
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

    .job-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        width: 100%;
        box-sizing: border-box;
        position: relative;
        min-height: 120px;
    }

    .job-section > .no-jobs-message {
        grid-column: 1 / -1;
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
        gap: 15px;
        align-items: flex-start;
        margin-top: 0;
        padding-right: 40px;
    }

    .company-logo {
        width: 90px;
        height: auto;
        max-height: 90px;
        object-fit: contain;
        border-radius: 5px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .company-logo img {
        max-width: 100%;
        max-height: 100%;
        display: block;
    }

    .job-info-right {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .job-title-new {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 8px;
        margin-top: 0;
    }

    .job-info {
        font-size: 14px;
        line-height: 1.5;
    }

    .job-info .company-name {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .icon {
        margin-right: 5px;
    }

    .divider {
        border-top: 1px solid #e0e0e0;
        margin: 15px 0;
    }

    .job-footer {
        font-size: 13px;
        color: #999;
        text-align: right;
    }

    /* Media queries: Đảm bảo responsive */
    @media (max-width: 1024px) {
        .job-section {
            max-width: 90%;
        }
        .sort-by-container {
             max-width: 90%;
        }
    }

    @media (max-width: 768px) {
        .job-section {
            max-width: 95%;
            padding: 30px 15px;
            grid-template-columns: 1fr;
        }
        .sort-by-container {
            max-width: 95%;
            padding: 0 15px;
        }
        .job-card {
            min-height: 100px;
        }
    }

    @media (max-width: 600px) {
        .job-section {
            max-width: 100%;
            padding: 20px 10px;
            grid-template-columns: 1fr;
        }
        .sort-by-container {
            max-width: 100%;
            padding: 0 10px;
            justify-content: center; /* Căn giữa trên mobile */
        }
        .job-body {
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding-right: 0;
        }
        .company-logo {
            width: 70px;
            max-height: 70px;
            margin-bottom: 10px;
        }
        .job-info-right {
            align-items: center;
            text-align: center;
        }
        .save-btn {
            top: 10px;
            right: 10px;
        }
        .job-card {
            min-height: 100px;
        }
    }
    .job-section {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
        box-sizing: border-box;
    }
</style>

<section class="section-1">
    <div class="container">
        <div class="title"><?php echo $lang['find_jobs_title']; ?></div>
        <div class="subtitle"><?php echo $lang['find_jobs_subtitle']; ?>
        </div>

        <div class="search-box">
            <form action="" method="get" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <select name="category">
                    <option value=""><?php echo $lang['job_category_select_menu']; ?></option>
                    <?php foreach ($industryFilterOptions as $option): ?>
                        <option value="<?= htmlspecialchars($option['value']) ?>" <?= (isset($_GET['category']) && $_GET['category'] === $option['value']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($option['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="q" placeholder="<?php echo $lang['job_title_company_placeholder'] ?? 'Job title, company name'; ?>" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                <input type="text" name="location" placeholder="<?php echo $lang['location_placeholder'] ?? 'Location'; ?>" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                <input type="hidden" name="job_type" value="<?php echo htmlspecialchars($_GET['job_type'] ?? ''); ?>">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort'] ?? 'newest_first'); ?>">
                <button type="submit"><?php echo $lang['search']; ?></button>
            </form>
        </div>
    </div>
</section>

<div class="info">
    <section class="section-2">
        <?php $current_sort = $_GET['sort'] ?? 'newest_first'; ?>
        <div class="sort-by-container">
            <form class="sort-by-form" action="" method="get">
                <select name="sort" aria-label="<?php echo htmlspecialchars($lang['sort'] ?? 'Sort jobs'); ?>">
                    <option value="newest_first" <?php echo ($current_sort == 'newest_first') ? 'selected' : ''; ?>><?php echo $lang['sort_newest_first'] ?? 'Date posted: Newest first'; ?></option>
                    <option value="oldest_first" <?php echo ($current_sort == 'oldest_first') ? 'selected' : ''; ?>><?php echo $lang['sort_oldest_first'] ?? 'Date posted: Oldest first'; ?></option>
                    <option value="salary_highest" <?php echo ($current_sort == 'salary_highest') ? 'selected' : ''; ?>><?php echo $lang['sort_salary_highest'] ?? 'Salary: Highest to lowest'; ?></option>
                    <option value="salary_lowest" <?php echo ($current_sort == 'salary_lowest') ? 'selected' : ''; ?>><?php echo $lang['sort_salary_lowest'] ?? 'Salary: Lowest to highest'; ?></option>
                </select>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($_GET['category'] ?? ''); ?>">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                <input type="hidden" name="location" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                <input type="hidden" name="job_type" value="<?php echo htmlspecialchars($_GET['job_type'] ?? ''); ?>">
                <button type="submit"><?php echo $lang['filter'] ?? 'Filter'; ?></button>
            </form>
        </div>

        <div class="job-section">
            <?php
            // --- Logic Xử lý Tìm kiếm và Phân trang ---

            $jobs_per_page = 10;
            $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

            $search_category = $_GET['category'] ?? '';
            $search_query = $_GET['q'] ?? ''; // job title, company name
            $search_location = $_GET['location'] ?? '';
            $search_job_type = strtolower(trim((string) ($_GET['job_type'] ?? '')));
            $sort_order = $_GET['sort'] ?? 'newest_first';

            $job_type_aliases = [
                'full time' => 'full-time',
                'toàn thời gian' => 'full-time',
                'part time' => 'part-time',
                'bán thời gian' => 'part-time',
                'thực tập' => 'internship',
                'thời vụ' => 'freelance',
                'làm việc từ xa' => 'remote',
            ];
            if (isset($job_type_aliases[$search_job_type])) {
                $search_job_type = $job_type_aliases[$search_job_type];
            }

            // Bắt đầu xây dựng câu truy vấn SQL
            $sql_base = "SELECT j.id, j.job_title, c.company_logo AS company_logo,
                c.company_name AS company_name,
                j.salary, j.job_location, j.created_at, j.post_duration
                FROM job j
                LEFT JOIN company c ON c.id = j.company_id";
            $sql_count_base = "SELECT COUNT(*) as total FROM job j LEFT JOIN company c ON c.id = j.company_id";

            $conditions[] = "j.status = 'approved'";
            $params = [];
            $param_types = '';

            // Thêm điều kiện cho industry của công ty
            if (!empty($search_category)) {
                $conditions[] = "LOWER(c.industry) = ?";
                $params[] = strtolower(trim($search_category));
                $param_types .= 's';
            }

            // Thêm điều kiện cho job_title hoặc company_name
            if (!empty($search_query)) {
                $conditions[] = "(j.job_title LIKE ? OR c.company_name LIKE ?)";
                $params[] = '%' . $search_query . '%';
                $params[] = '%' . $search_query . '%';
                $param_types .= 'ss';
            }

            // Thêm điều kiện cho job_location
            if (!empty($search_location)) {
                $conditions[] = "j.job_location LIKE ?";
                $params[] = '%' . $search_location . '%';
                $param_types .= 's';
            }

            if (in_array($search_job_type, ['full-time', 'part-time', 'internship', 'freelance', 'remote'], true)) {
                $conditions[] = "LOWER(j.job_type) = ?";
                $params[] = $search_job_type;
                $param_types .= 's';
            }

            // Hoàn thiện mệnh đề WHERE
            $where_clause = '';
            if (!empty($conditions)) {
                $where_clause = " WHERE " . implode(" AND ", $conditions);
            }

            // --- Xử lý sắp xếp ---
            $salary_numeric_min = "
                CASE
                    WHEN LOWER(TRIM(j.salary)) LIKE '%usd%' THEN
                        CAST(REPLACE(REPLACE(REPLACE(SUBSTRING_INDEX(REPLACE(SUBSTRING_INDEX(LOWER(TRIM(j.salary)), 'usd', 1), '-', ' '), ' ', 1), '$', ''), ',', ''), '.', '') AS DECIMAL(20,2)) * 25000
                    WHEN LOWER(TRIM(j.salary)) LIKE '%vnd%' THEN
                        CAST(REPLACE(REPLACE(REPLACE(SUBSTRING_INDEX(REPLACE(SUBSTRING_INDEX(LOWER(TRIM(j.salary)), 'vnd', 1), '-', ' '), ' ', 1), ',', ''), '.', ''), '$', '') AS DECIMAL(20,2))
                    WHEN LOWER(TRIM(j.salary)) LIKE '%triệu%' THEN
                        CAST(REPLACE(REPLACE(SUBSTRING_INDEX(REPLACE(SUBSTRING_INDEX(LOWER(TRIM(j.salary)), 'triệu', 1), '-', ' '), ' ', 1), ',', ''), '.', '') AS DECIMAL(20,2)) * 1000000
                    ELSE 0
                END";

            $salary_numeric_max = "
                CASE
                    WHEN LOWER(TRIM(j.salary)) LIKE '%usd%' THEN
                        CAST(REPLACE(REPLACE(REPLACE(SUBSTRING_INDEX(REPLACE(SUBSTRING_INDEX(LOWER(TRIM(j.salary)), 'usd', 1), '-', ' '), ' ', -1), '$', ''), ',', ''), '.', '') AS DECIMAL(20,2)) * 25000
                    WHEN LOWER(TRIM(j.salary)) LIKE '%vnd%' THEN
                        CAST(REPLACE(REPLACE(REPLACE(SUBSTRING_INDEX(REPLACE(SUBSTRING_INDEX(LOWER(TRIM(j.salary)), 'vnd', 1), '-', ' '), ' ', -1), ',', ''), '.', ''), '$', '') AS DECIMAL(20,2))
                    WHEN LOWER(TRIM(j.salary)) LIKE '%triệu%' THEN
                        CAST(REPLACE(REPLACE(SUBSTRING_INDEX(REPLACE(SUBSTRING_INDEX(LOWER(TRIM(j.salary)), 'triệu', 1), '-', ' '), ' ', -1), ',', ''), '.', '') AS DECIMAL(20,2)) * 1000000
                    ELSE 0
                END";

            $allowed_sort_orders = ['newest_first', 'oldest_first', 'salary_highest', 'salary_lowest'];
            if (!in_array($sort_order, $allowed_sort_orders, true)) {
                $sort_order = 'newest_first';
            }

            switch ($sort_order) {
                case 'newest_first':
                    $order_by_clause = " ORDER BY j.created_at DESC";
                    break;
                case 'oldest_first':
                    $order_by_clause = " ORDER BY j.created_at ASC";
                    break;
                case 'salary_highest':
                    $order_by_clause = " ORDER BY " . $salary_numeric_max . " DESC, j.created_at DESC";
                    break;
                case 'salary_lowest':
                    $order_by_clause = " ORDER BY " . $salary_numeric_min . " ASC, j.created_at DESC";
                    break;
                default:
                    $order_by_clause = " ORDER BY j.created_at DESC";
                    break;
            }

            // Đếm tổng số jobs phù hợp
            $count_stmt = $conn->prepare($sql_count_base . $where_clause);
            if ($count_stmt) {
                if (!empty($params)) {
                    // Dùng refValues() cho bind_param() của truy vấn đếm
                    $bind_params = array_merge([$param_types], refValues($params));
                    call_user_func_array([$count_stmt, 'bind_param'], refValues($bind_params));
                }
                $count_stmt->execute();
                $count_result = $count_stmt->get_result();
                $total_jobs = $count_result ? (int) $count_result->fetch_assoc()['total'] : 0;
                $count_stmt->close();
            } else {
                error_log("Error preparing count query: " . $conn->error);
                $total_jobs = 0; // Đặt về 0 nếu có lỗi
            }

            $total_pages = ceil($total_jobs / $jobs_per_page);
            $offset = ($page - 1) * $jobs_per_page;

            // Lấy dữ liệu jobs phù hợp cho trang hiện tại
            $sql_fetch = $sql_base . $where_clause . $order_by_clause . " LIMIT ? OFFSET ?"; // Thêm ORDER BY
            $fetch_stmt = $conn->prepare($sql_fetch);

            if ($fetch_stmt) {
                // Tạo một bản sao của mảng params và param_types cho truy vấn fetch
                // Vì chúng ta sẽ thêm LIMIT và OFFSET vào
                $fetch_params = $params;
                $fetch_param_types = $param_types;

                // Thêm type cho LIMIT và OFFSET
                $fetch_param_types .= 'ii';
                $fetch_params[] = $jobs_per_page;
                $fetch_params[] = $offset;

                // Dùng refValues() cho bind_param() của truy vấn lấy dữ liệu
                $bind_fetch_params = array_merge([$fetch_param_types], refValues($fetch_params));
                call_user_func_array([$fetch_stmt, 'bind_param'], refValues($bind_fetch_params));

                $fetch_stmt->execute();
                $result = $fetch_stmt->get_result();
                $fetch_stmt->close();
            } else {
                error_log("Error preparing fetch query: " . $conn->error);
                $result = false;
            }
            // --- Kết thúc Logic Xử lý Tìm kiếm và Phân trang ---
            ?>

            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()):
                    $created_at = new DateTime($row['created_at']);
                    $post_duration = (int) $row['post_duration'];
                    $expire_at = clone $created_at;
                    $expire_at->modify("+$post_duration days");
                    $now = new DateTime();
                    $interval = $now->diff($expire_at);
                    $days_left = (int) $interval->format('%r%a');
                    $days_left_text = $days_left > 0 ? sprintf($lang['days_left'], $days_left) : $lang['expired'];
                    $job_id = (int) $row['id'];
                    $companyLogo = normalizeCompanyLogoPath($row['company_logo'] ?? 'image/logo1.png');
                    $isJobAlreadySaved = in_array((string) $job_id, $savedJobIds);
                    ?>
                    <a href="jobdetail.php?id=<?php echo $job_id; ?>" style="text-decoration:none;color:inherit;">
                        <div class="job-card" data-job-id="<?php echo $job_id; ?>">
                            <?php
                            $isLoggedIn = isset($_SESSION['jobseeker_id']);
                            $userId = $isLoggedIn ? (int) $_SESSION['jobseeker_id'] : 0;
                            ?>
                            <button class="save-btn" aria-pressed="<?php echo $isJobAlreadySaved ? 'true' : 'false'; ?>"
                                title="Save job" data-job-id="<?php echo $job_id; ?>">
                                <svg class="heart-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M12 21s-6.5-5.5-8.5-8.5C1.5 9.5 3.5 6 7 6c1.7 0 3.4 1.1 4.1 2.7C11.6 7.1 13.3 6 15 6c3.5 0 5.5 3.5 3.5 6.5C18.5 15.5 12 21 12 21z"
                                        fill="<?php echo $isJobAlreadySaved ? '#e74c3c' : '#fff'; ?>" />
                                </svg>
                            </button>

                            <div class="job-body">
                                <div class="company-logo">
                                    <img src="<?php echo htmlspecialchars($companyLogo); ?>" alt="Company Logo">
                                </div>
                                <div class="job-info-right">
                                    <h3 class="job-title-new"><?php echo htmlspecialchars($row['job_title']); ?></h3>
                                    <div class="job-info">
                                        <div class="company-name"><?php echo htmlspecialchars($row['company_name']); ?></div>
                                        <div><span class="icon">💰</span> <?php echo htmlspecialchars($row['salary']); ?></div>
                                        <div><span class="icon">📍</span> <?php echo htmlspecialchars($row['job_location']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="divider"></div>
                            <div class="job-footer">
                                <div class="deadline"><?php echo $days_left_text; ?></div>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-jobs-message" style="text-align: center; padding: 20px; font-size: 16px; color: #555;"><?php echo $lang['searchpage_no_jobs_found']; ?></div>
            <?php endif; ?>
        </div>

        <div class="pagination">
            <?php
            // Lấy tất cả các tham số GET hiện có để giữ lại chúng khi chuyển trang
            $current_get_params = $_GET;
            unset($current_get_params['page']); // Loại bỏ tham số page hiện tại

            // Chuyển các tham số GET thành chuỗi truy vấn URL
            $query_string = http_build_query($current_get_params);

            // Bổ sung dấu '&' nếu đã có tham số
            if (!empty($query_string)) {
                $query_string .= '&';
            }
            ?>
            <a href="?<?php echo $query_string; ?>page=<?php echo $page > 1 ? ($page - 1) : 1; ?>" class="arrow<?php if ($page <= 1) echo ' disabled'; ?>" <?php if ($page <= 1) echo 'tabindex="-1" aria-disabled="true"'; ?>>
                &larr;
            </a>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?<?php echo $query_string; ?>page=<?php echo $i; ?>" class="page-number<?php if ($i == $page) echo ' active'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            <a href="?<?php echo $query_string; ?>page=<?php echo $page < $total_pages ? ($page + 1) : $total_pages; ?>" class="arrow<?php if ($page >= $total_pages) echo ' disabled'; ?>" <?php if ($page >= $total_pages) echo 'tabindex="-1" aria-disabled="true"'; ?>>
                &rarr;
            </a>
        </div>
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
    </section>


    <section class="section-3">
        <div class="container">
            <div class="header">
                <h2>🌟 Featured Companies</h2>
                <a href="#" class="view-all">View all →</a>
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
            <h3>Opportunities to apply for attractive jobs at top companies</h3>
            <p>With the rapid development of the economy... professional environment.</p>

            <h3>Why should you look for jobs at JobHive?</h3>
            <ul>
                <li><strong>Quality Jobs</strong><br />
                    Thousands of high-quality job postings... to your CV.</li>
                <li><strong>Free Beautiful CV Builder</strong><br />
                    Many beautiful CV templates... in just 5 minutes.</li>
                <li><strong>Job Seeker Support</strong><br />
                    Employers... view your CV and send invitations.</li>
                <li><strong>Data Protection</strong><br />
                    We commit to protecting your personal information...</li>
            </ul>

            <p>At JobHive, you can find... the best salary!</p>
        </div>
    </section>

</div>

<script>
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
                e.preventDefault(); // Ngăn chặn hành vi mặc định của button

                // Kiểm tra đăng nhập
                if (!window.isLoggedIn) {
                    showPopup('Please login to save jobs to your account.');
                    return;
                }

                let currentSavedJobs = JSON.parse(localStorage.getItem('savedJobs') || '[]');
                const isCurrentlySaved = btn.getAttribute('aria-pressed') === 'true';
                let action;

                if (!isCurrentlySaved) { // Chưa lưu -> Lưu
                    heartPath.setAttribute('fill', '#e74c3c');
                    btn.setAttribute('aria-pressed', 'true');
                    currentSavedJobs.push(jobId);
                    showPopup(<?php echo json_encode($lang['job_saved_successfully']); ?>);
                    action = 'save';
                } else { // Đã lưu -> Hủy lưu
                    heartPath.setAttribute('fill', '#fff');
                    btn.setAttribute('aria-pressed', 'false');
                    currentSavedJobs = currentSavedJobs.filter(id => id !== jobId);
                    showPopup(<?php echo json_encode($lang['job_unsaved_successfully']); ?>);
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
            }, 1200);
        }
    });
</script>