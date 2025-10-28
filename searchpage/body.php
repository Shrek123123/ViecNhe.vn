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
        display: flex;
        flex-direction: column;
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

    /* CSS mới cho nút Sort By */
    .sort-by-container {
        max-width: 1000px; /* Căn chỉnh với job-section */
        margin: 0 auto 20px auto; /* Margin dưới để tạo khoảng cách */
        display: flex;
        justify-content: flex-end; /* Căn phải */
        padding: 0 20px; /* Đảm bảo padding khớp với job-section */
        box-sizing: border-box; /* Tính cả padding vào width */
    }

    .sort-by-container select {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #ccc;
        background-color: white;
        font-size: 14px;
        cursor: pointer;
        outline: none; /* Bỏ viền focus mặc định */
        min-width: 180px; /* Đảm bảo đủ rộng cho text */
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
    .sort-by-container {
         max-width: 1000px;
        margin: 0 auto 20px auto;
        display: flex;
        justify-content: flex-end;
        padding: 0 20px;
        box-sizing: border-box;
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
        <div class="title">Find jobs fast 24/7, the latest jobs nationwide.</div>
        <div class="subtitle">Access 40,000+ new job postings every day from thousands of reputable companies in
            Vietnam.
        </div>

        <div class="search-box">
            <form action="" method="get" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <select name="category">
                    <option value="">Job Category</option>
                    <option value="IT & Software" <?php echo (isset($_GET['category']) && $_GET['category'] == 'IT & Software') ? 'selected' : ''; ?>>IT & Software</option>
                    <option value="Marketing" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                    <option value="Finance" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Finance') ? 'selected' : ''; ?>>Finance</option>
                    <option value="Healthcare" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Healthcare') ? 'selected' : ''; ?>>Healthcare</option>
                    <option value="Government & Public Sector" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Government & Public Sector') ? 'selected' : ''; ?>>Government & Public Sector</option>
                    </select>
                <input type="text" name="q" placeholder="Job title, company name" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                <input type="text" name="location" placeholder="Location" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
    </div>
</section>

<div class="info">
    <section class="section-2">
        <?php
        // Lấy giá trị sắp xếp hiện tại từ URL, mặc định là 'newest_first'
        $current_sort = $_GET['sort'] ?? 'newest_first';
        ?>
        <div class="sort-by-container">
            <form action="" method="get">
                <?php
                // Giữ lại tất cả các tham số GET hiện tại (trừ 'sort' và 'page')
                $hidden_params = $_GET;
                unset($hidden_params['sort']);
                unset($hidden_params['page']);
                foreach ($hidden_params as $key => $value) {
                    echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                }
                ?>
                <select name="sort" onchange="this.form.submit()">
                    <option value="newest_first" <?php echo ($current_sort == 'newest_first') ? 'selected' : ''; ?>>Date posted: Newest first</option>
                    <option value="oldest_first" <?php echo ($current_sort == 'oldest_first') ? 'selected' : ''; ?>>Date posted: Oldest first</option>
                    <option value="salary_highest" <?php echo ($current_sort == 'salary_highest') ? 'selected' : ''; ?>>Salary: Highest to lowest</option>
                    <option value="salary_lowest" <?php echo ($current_sort == 'salary_lowest') ? 'selected' : ''; ?>>Salary: Lowest to highest</option>
                </select>
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
            $sort_order = $_GET['sort'] ?? 'newest_first'; // Lấy tham số sắp xếp

            // Bắt đầu xây dựng câu truy vấn SQL
            $sql_base = "SELECT id, job_title, company_logo, company_name, salary, job_location, created_at, post_duration FROM job";
            $sql_count_base = "SELECT COUNT(*) as total FROM job";

            $conditions[] = "status = 'approved'";
            $params = [];
            $param_types = '';

            // Thêm điều kiện cho job_category
            if (!empty($search_category)) {
                $conditions[] = "job_category = ?";
                $params[] = $search_category;
                $param_types .= 's';
            }

            // Thêm điều kiện cho job_title hoặc company_name
            if (!empty($search_query)) {
                // Sử dụng LIKE cho tìm kiếm một phần, %...% để tìm bất kỳ đâu trong chuỗi
                $conditions[] = "(job_title LIKE ? OR company_name LIKE ?)";
                $params[] = '%' . $search_query . '%';
                $params[] = '%' . $search_query . '%';
                $param_types .= 'ss';
            }

            // Thêm điều kiện cho job_location
            if (!empty($search_location)) {
                $conditions[] = "job_location LIKE ?";
                $params[] = '%' . $search_location . '%';
                $param_types .= 's';
            }

            // Hoàn thiện mệnh đề WHERE
            $where_clause = '';
            if (!empty($conditions)) {
                $where_clause = " WHERE " . implode(" AND ", $conditions);
            }

            // --- Xử lý sắp xếp ---
            $order_by_clause = "";
            switch ($sort_order) {
                case 'newest_first':
                    $order_by_clause = " ORDER BY created_at DESC";
                    break;
                case 'oldest_first':
                    $order_by_clause = " ORDER BY created_at ASC";
                    break;
                case 'salary_highest':
                    // Logic phức tạp cho lương:
                    // 1. Ưu tiên USD cao nhất.
                    // 2. Sau đó, chuyển đổi '2 - 3 triệu' thành số (2) để so sánh.
                    // 3. Xử lý các trường hợp khác không phải số.
                    // Giả định 1 USD = 25000 VND. Vui lòng điều chỉnh tỷ giá này nếu cần.
                    $order_by_clause = " ORDER BY
                        CASE
                            WHEN salary LIKE '%USD%' THEN 1      -- Ưu tiên USD cao nhất (đưa các job có USD lên trên cùng)
                            ELSE 0                               -- Các loại khác
                        END DESC,
                        CASE
                            WHEN salary REGEXP '^[0-9]+([.,][0-9]+)?( - [0-9]+([.,][0-9]+)?)? *[Tt]riệu' THEN CAST(REPLACE(SUBSTRING_INDEX(salary, ' ', 1), ',', '.') AS DECIMAL(10,2)) * 1000000 -- Lấy số đầu tiên, nếu có 'triệu'
                            WHEN salary REGEXP '^[0-9]+([.,][0-9]+)?( - [0-9]+([.,][0-9]+)?)? *[Vv][Nn][Dd]?' THEN CAST(REPLACE(SUBSTRING_INDEX(salary, ' ', 1), ',', '.') AS DECIMAL(10,2)) -- Lấy số đầu tiên, nếu có 'VND'
                            WHEN salary REGEXP '^[0-9]+([.,][0-9]+)?( - [0-9]+([.,][0-9]+)?)? *[Uu][Ss][Dd]?' THEN CAST(REPLACE(SUBSTRING_INDEX(salary, ' ', 1), ',', '.') AS DECIMAL(10,2)) * 25000 -- Lấy số đầu tiên, nếu có 'USD'
                            ELSE 0 -- Giá trị mặc định nếu không khớp mẫu, để xuống cuối
                        END DESC,
                        -- So sánh số thứ hai nếu số đầu tiên trùng và có khoảng lương (chỉ khi có dấu gạch ngang)
                        CASE
                            WHEN salary REGEXP '^[0-9]+([.,][0-9]+)? - ([0-9]+([.,][0-9]+)?) *[Tt]riệu' THEN CAST(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(salary, ' - ', -1), ' ', 1), ',', '.') AS DECIMAL(10,2)) * 1000000
                            WHEN salary REGEXP '^[0-9]+([.,][0-9]+)? - ([0-9]+([.,][0-9]+)?) *[Vv][Nn][Dd]?' THEN CAST(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(salary, ' - ', -1), ' ', 1), ',', '.') AS DECIMAL(10,2))
                            WHEN salary REGEXP '^[0-9]+([.,][0-9]+)? - ([0-9]+([.,][0-9]+)?) *[Uu][Ss][Dd]?' THEN CAST(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(salary, ' - ', -1), ' ', 1), ',', '.') AS DECIMAL(10,2)) * 25000
                            ELSE 0
                        END DESC
                    ";
                    break;
                case 'salary_lowest':
                    // Logic tương tự cho lương, nhưng ASC (thấp đến cao)
                    $order_by_clause = " ORDER BY
                        CASE
                            WHEN salary LIKE '%USD%' THEN 1      -- Ưu tiên USD cao nhất (vẫn giữ USD ở trên cùng khi so sánh)
                            ELSE 0                               -- Các loại khác
                        END DESC, -- USD vẫn ưu tiên cao nhất, sau đó mới đến sắp xếp tăng dần của các giá trị còn lại
                        CASE
                            WHEN salary REGEXP '^[0-9]+([.,][0-9]+)?( - [0-9]+([.,][0-9]+)?)? *[Tt]riệu' THEN CAST(REPLACE(SUBSTRING_INDEX(salary, ' ', 1), ',', '.') AS DECIMAL(10,2)) * 1000000
                            WHEN salary REGEXP '^[0-9]+([.,][0-9]+)?( - [0-9]+([.,][0-9]+)?)? *[Vv][Nn][Dd]?' THEN CAST(REPLACE(SUBSTRING_INDEX(salary, ' ', 1), ',', '.') AS DECIMAL(10,2))
                            WHEN salary REGEXP '^[0-9]+([.,][0-9]+)?( - [0-9]+([.,][0-9]+)?)? *[Uu][Ss][Dd]?' THEN CAST(REPLACE(SUBSTRING_INDEX(salary, ' ', 1), ',', '.') AS DECIMAL(10,2)) * 25000
                            ELSE 9999999999 -- Giá trị mặc định lớn nếu không khớp mẫu, để xuống cuối khi sắp xếp tăng dần
                        END ASC,
                        CASE
                            WHEN salary REGEXP '^[0-9]+([.,][0-9]+)? - ([0-9]+([.,][0-9]+)?) *[Tt]riệu' THEN CAST(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(salary, ' - ', -1), ' ', 1), ',', '.') AS DECIMAL(10,2)) * 1000000
                            WHEN salary REGEXP '^[0-9]+([.,][0-9]+)? - ([0-9]+([.,][0-9]+)?) *[Vv][Nn][Dd]?' THEN CAST(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(salary, ' - ', -1), ' ', 1), ',', '.') AS DECIMAL(10,2))
                            WHEN salary REGEXP '^[0-9]+([.,][0-9]+)? - ([0-9]+([.,][0-9]+)?) *[Uu][Ss][Dd]?' THEN CAST(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(salary, ' - ', -1), ' ', 1), ',', '.') AS DECIMAL(10,2)) * 25000
                            ELSE 9999999999
                        END ASC
                    ";
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
                    $days_left_text = $days_left > 0 ? $days_left . ' days left' : 'Expired';
                    $job_id = (int) $row['id'];
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
                                    <img src="<?php echo htmlspecialchars($row['company_logo']); ?>" alt="Company Logo">
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
                <div style="text-align: center; padding: 20px; font-size: 16px; color: #555;">No jobs found matching your criteria.</div>
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
                    showPopup('Job saved successfully!');
                    action = 'save';
                } else { // Đã lưu -> Hủy lưu
                    heartPath.setAttribute('fill', '#fff');
                    btn.setAttribute('aria-pressed', 'false');
                    currentSavedJobs = currentSavedJobs.filter(id => id !== jobId);
                    showPopup('Job unsaved successfully.');
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