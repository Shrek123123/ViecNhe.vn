<?php

function sendRegistrationSuccessEmail($recipientEmail, $accountType, $loginPath) {
    $recipientEmail = trim((string) $recipientEmail);
    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    require_once __DIR__ . '/config/mail.php';

    $accountLabel = $accountType === 'employer' ? 'nhà tuyển dụng' : 'ứng viên';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $loginUrl = $scheme . '://' . $host . ($basePath ? $basePath . '/' : '/') . ltrim($loginPath, '/');

    $subject = 'Việc Nhẹ.VN - Tạo tài khoản thành công';
    $safeLoginUrl = htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8');
    $message = '<p>Việc Nhẹ.VN thông báo bạn đã tạo tài khoản ' . $accountLabel . ' thành công!</p>'
        . '<p>Đăng nhập tại: <a href="' . $safeLoginUrl . '">' . $safeLoginUrl . '</a></p>';

    return sendEmail($recipientEmail, '', $subject, $message);
}

function sendApplicationStatusEmail($recipientEmail, $jobTitle, $companyName, $status) {
    $recipientEmail = trim((string) $recipientEmail);
    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    require_once __DIR__ . '/config/mail.php';

    $statusLabels = [
        'pending' => 'Đang chờ',
        'viewed' => 'Đã xem',
        'interviewed' => 'Đã phỏng vấn',
        'hired' => 'Đã tuyển dụng',
        'rejected' => 'Đã từ chối',
    ];
    $statusLabel = $statusLabels[strtolower((string) $status)] ?? (string) $status;
    $jobTitle = trim((string) $jobTitle);
    $companyName = trim((string) $companyName);

    $subject = 'Việc Nhẹ.VN thông báo trạng thái đơn ứng tuyển của bạn cho vị trí '
        . $jobTitle . ' - ' . $companyName;
    $detailsUrl = 'http://localhost/JobHive-main/jobseekerprofile/don_ung_tuyen.php';
    $safeJobTitle = htmlspecialchars($jobTitle, ENT_QUOTES, 'UTF-8');
    $safeCompanyName = htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8');
    $safeStatus = htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8');
    $safeDetailsUrl = htmlspecialchars($detailsUrl, ENT_QUOTES, 'UTF-8');
    $message = '<p>Nhà tuyển dụng công ty ' . $safeCompanyName . '</p>'
        . '<p>đã cập nhật trạng thái đơn ứng tuyển của bạn là: <strong>' . $safeStatus . '</strong>'
        . ' cho vị trí <strong>' . $safeJobTitle . '</strong></p>'
        . '<p>Vào trang sau để xem chi tiết: <a href="' . $safeDetailsUrl . '">' . $safeDetailsUrl . '</a></p>'
        . '<p>Trân trọng,<br>ViecNhe.vn</p>';

    return sendEmail($recipientEmail, '', $subject, $message);
}

function sendEmailChangeNotification($newEmail, $userType) {
    $newEmail = trim((string) $newEmail);
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    require_once __DIR__ . '/config/mail.php';

    $accountLabel = strtolower((string) $userType) === 'employer' ? 'nhà tuyển dụng' : 'tìm việc';
    $safeEmail = htmlspecialchars($newEmail, ENT_QUOTES, 'UTF-8');
    $subject = 'Việc Nhẹ.VN thông báo bạn đã đổi email tài khoản ' . $accountLabel;
    $message = '<p>Việc Nhẹ.VN thông báo bạn đã đổi email tài khoản ' . $accountLabel
        . ' sang email mới có địa chỉ: <strong>' . $safeEmail . '</strong></p>'
        . '<p>Trân trọng,<br>ViecNhe.vn</p>';

    return sendEmail($newEmail, '', $subject, $message);
}

function sendCompanyReviewEmail($recipientEmail, $companyName, $status, $adminNote = '') {
    $recipientEmail = trim((string) $recipientEmail);
    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    require_once __DIR__ . '/config/mail.php';

    $isApproved = strtolower((string) $status) === 'approved';
    $statusLabel = $isApproved ? 'đã được phê duyệt' : 'đã bị từ chối';
    $safeCompanyName = htmlspecialchars(trim((string) $companyName), ENT_QUOTES, 'UTF-8');
    $subject = 'Việc Nhẹ.VN thông báo kết quả duyệt thông tin công ty ' . trim((string) $companyName);
    $message = '<p>Việc Nhẹ.VN thông báo thông tin công ty <strong>' . $safeCompanyName
        . '</strong> của bạn ' . $statusLabel . '.</p>';

    if (!$isApproved && trim((string) $adminNote) !== '') {
        $message .= '<p>Lý do từ chối: <strong>'
            . htmlspecialchars(trim((string) $adminNote), ENT_QUOTES, 'UTF-8') . '</strong></p>';
    }

    $message .= '<p>Trân trọng,<br>ViecNhe.vn</p>';
    return sendEmail($recipientEmail, '', $subject, $message);
}

function sendJobReviewEmail($recipientEmail, $jobTitle, $companyName, $status, $adminNote = '') {
    $recipientEmail = trim((string) $recipientEmail);
    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    require_once __DIR__ . '/config/mail.php';

    $isApproved = strtolower((string) $status) === 'approved';
    $statusLabel = $isApproved ? 'đã được phê duyệt' : 'đã bị từ chối';
    $jobTitle = trim((string) $jobTitle);
    $companyName = trim((string) $companyName);
    $safeJobTitle = htmlspecialchars($jobTitle, ENT_QUOTES, 'UTF-8');
    $safeCompanyName = htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8');
    $subject = 'Việc Nhẹ.VN thông báo kết quả duyệt bài đăng tuyển dụng ' . $jobTitle;
    $message = '<p>Việc Nhẹ.VN thông báo bài đăng tuyển dụng <strong>' . $safeJobTitle
        . '</strong> của công ty <strong>' . $safeCompanyName . '</strong> ' . $statusLabel . '.</p>';

    if (!$isApproved && trim((string) $adminNote) !== '') {
        $message .= '<p>Lý do từ chối: <strong>'
            . htmlspecialchars(trim((string) $adminNote), ENT_QUOTES, 'UTF-8') . '</strong></p>';
    }

    $message .= '<p>Trân trọng,<br>ViecNhe.vn</p>';
    return sendEmail($recipientEmail, '', $subject, $message);
}

function sendFiveApplicationsNotification($conn, $jobId) {
    $jobId = (int) $jobId;
    if ($jobId <= 0 || !($conn instanceof mysqli)) {
        return false;
    }

    $jobStmt = $conn->prepare(
        'SELECT u.email, j.job_title
         FROM job j
         JOIN user u ON u.id = j.posted_by_employer_id
         WHERE j.id = ?
         LIMIT 1'
    );
    if (!$jobStmt) {
        return false;
    }

    $jobStmt->bind_param('i', $jobId);
    $jobStmt->execute();
    $jobResult = $jobStmt->get_result();
    $jobData = $jobResult->fetch_assoc();
    $jobStmt->close();

    $recipientEmail = trim((string) ($jobData['email'] ?? ''));
    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $jobTitle = trim((string) ($jobData['job_title'] ?? ''));

    $applicationsStmt = $conn->prepare(
        'SELECT fullname
         FROM application
         WHERE job_id = ?
         ORDER BY created_at DESC, id DESC
         LIMIT 5'
    );
    if (!$applicationsStmt) {
        return false;
    }

    $applicationsStmt->bind_param('i', $jobId);
    $applicationsStmt->execute();
    $applicationsResult = $applicationsStmt->get_result();
    $applications = [];
    while ($application = $applicationsResult->fetch_assoc()) {
        $applications[] = $application;
    }
    $applicationsStmt->close();

    if (count($applications) < 5) {
        return false;
    }

    require_once __DIR__ . '/config/mail.php';

    $applicationUrl = 'http://localhost/JobHive-main/employerprofile/view_applications.php?job_id=' . $jobId;
    $safeApplicationUrl = htmlspecialchars($applicationUrl, ENT_QUOTES, 'UTF-8');
    $applicationLinks = '';
    foreach ($applications as $index => $application) {
        $safeFullname = htmlspecialchars((string) ($application['fullname'] ?? ''), ENT_QUOTES, 'UTF-8');
        $applicationLinks .= '<li>Ứng viên <strong>' . $safeFullname . '</strong>, '
            . '<a href="' . $safeApplicationUrl . '">truy cập trang xem hồ sơ ứng tuyển</a></li>';
    }

    $subject = 'Việc Nhẹ.vn thông báo bạn có thêm 5 đơn ứng tuyển mới từ người tìm việc cho vị trí '
        . $jobTitle;
    $safeJobTitle = htmlspecialchars($jobTitle, ENT_QUOTES, 'UTF-8');
    $message = '<p>Việc Nhẹ.vn thông báo bạn có thêm 5 đơn ứng tuyển mới từ người tìm việc cho vị trí '
        . '<strong>' . $safeJobTitle . '</strong>.</p>'
        . '<p>Hãy rà soát hồ sơ ứng viên ngay tại đây:</p>'
        . '<ol>' . $applicationLinks . '</ol>'
        . '<p>Trân trọng,<br>ViecNhe.vn</p>';

    return sendEmail($recipientEmail, '', $subject, $message);
}

function trans($key, $replace = []) {
    global $lang;
    $text = $lang[$key] ?? $key;
    foreach ($replace as $k => $v) {
        $text = str_replace($k, $v, $text);
    }
    return $text;
}

function normalizeIndustryKey($value) {
    $raw = trim((string) $value);
    if ($raw === '') {
        return '';
    }

    $key = strtolower($raw);
    $key = str_replace(['/', '&', '-', '.', ','], ' ', $key);
    $key = preg_replace('/[^a-z0-9_\s]+/u', '', $key);
    $key = preg_replace('/\s+/', '_', trim($key));
    $key = preg_replace('/_+/', '_', $key);
    return trim($key, '_');
}

function translateIndustryDisplay($value, $lang = []) {
    $raw = trim((string) $value);
    if ($raw === '') {
        return '';
    }

    if (isset($lang[$raw]) && is_string($lang[$raw])) {
        return $lang[$raw];
    }

    $key = normalizeIndustryKey($raw);
    if ($key === '') {
        return trim(preg_replace('/_+/', ' ', $raw) ?? $raw);
    }

    if (isset($lang[$key]) && is_string($lang[$key])) {
        return $lang[$key];
    }

    $mappedKey = [
        'kinh_doanh_ban_hang' => 'kinh_doanh_ban_hang',
        'marketing_truyen_thong' => 'marketing_truyen_thong',
        'tai_chinh_ke_toan_ngan_hang' => 'tai_chinh_ke_toan_ngan_hang',
        'cong_nghe_thong_tin' => 'cong_nghe_thong_tin',
        'nhan_su' => 'nhan_su',
        'hanh_chinh_van_phong' => 'hanh_chinh_van_phong',
        'ky_thuat' => 'ky_thuat',
        'san_xuat_van_hanh_day_chuyen' => 'san_xuat_van_hanh_day_chuyen',
        'van_tai_logistics' => 'van_tai_logistics',
        'bat_dong_san' => 'bat_dong_san',
        'bao_hiem' => 'bao_hiem',
        'xay_dung_kien_truc' => 'xay_dung_kien_truc',
        'y_te_duoc_pham' => 'y_te_duoc_pham',
        'giao_duc_dao_tao' => 'giao_duc_dao_tao',
        'du_lich_khach_san_nha_hang' => 'du_lich_khach_san_nha_hang',
        'luat' => 'luat',
        'thiet_ke_nghe_thuat' => 'thiet_ke_nghe_thuat',
        'lao_dong_pho_thong' => 'lao_dong_pho_thong',
        'nong_nghiep' => 'nong_nghiep',
        'food_beverage' => 'food_beverage',
        'nha_nuoc_cong_vu' => 'nha_nuoc_cong_vu',
        'khac' => 'khac',
    ];

    if (isset($mappedKey[$key]) && isset($lang[$mappedKey[$key]]) && is_string($lang[$mappedKey[$key]])) {
        return $lang[$mappedKey[$key]];
    }

    $pretty = preg_replace('/_+/', ' ', $raw);
    $pretty = preg_replace('/\s+/', ' ', $pretty);
    $pretty = trim((string) $pretty);

    return $pretty === '' ? $raw : $pretty;
}

function translateCompanyIndustry($value, $lang = []) {
    return translateIndustryDisplay($value, $lang);
}

function normalizeJobExperienceKey($value) {
    $raw = trim((string) $value);
    if ($raw === '') {
        return '';
    }

    $normalized = strtolower($raw);
    $normalized = preg_replace('/[_\-]+/', ' ', $normalized);
    $normalized = preg_replace('/\s+/', ' ', $normalized);
    $normalized = trim($normalized);

    $aliases = [
        'none' => 'no_experience',
        'no experience' => 'no_experience',
        'no experience required' => 'no_experience',
        'not required' => 'no_experience',
        'khong yeu cau kinh nghiem' => 'no_experience',
        'under 1 year' => 'under_1_year',
        'under 1 year experience' => 'under_1_year',
        'under 1 year kinh nghiem' => 'under_1_year',
        'duoi 1 nam' => 'under_1_year',
        '1 year' => '1_year',
        '1 year experience' => '1_year',
        '1 year kinh nghiem' => '1_year',
        '1 nam' => '1_year',
        '2 years' => '2_years',
        '2 years experience' => '2_years',
        '2 years kinh nghiem' => '2_years',
        '2 nam' => '2_years',
        '3 years' => '3_years',
        '3 years experience' => '3_years',
        '3 years kinh nghiem' => '3_years',
        '3 nam' => '3_years',
        '4 years' => '4_years',
        '4 years experience' => '4_years',
        '4 years kinh nghiem' => '4_years',
        '4 nam' => '4_years',
        '5 years' => '5_years_plus',
        '5 years plus' => '5_years_plus',
        '5 years or more' => '5_years_plus',
        '5 years or more experience' => '5_years_plus',
        '5 years kinh nghiem' => '5_years_plus',
        '5 nam' => '5_years_plus',
        '6 months' => '6_months',
        '6 month' => '6_months',
        '6 months experience' => '6_months',
        '6 months kinh nghiem' => '6_months',
        '6 thang' => '6_months',
    ];

    if (isset($aliases[$normalized])) {
        return $aliases[$normalized];
    }

    $canonicalKey = str_replace(' ', '_', $normalized);
    $canonicalKey = preg_replace('/_+/', '_', $canonicalKey);
    return trim($canonicalKey, '_');
}

function normalizeAssetPath($path) {
    $raw = trim((string) $path);
    if ($raw === '') {
        return '';
    }

    if (preg_match('#^(https?://|data:|//)#i', $raw)) {
        return $raw;
    }

    $hasUploadsSegment = stripos($raw, 'uploads/') !== false || stripos($raw, 'companylogo/') !== false;
    if (!$hasUploadsSegment) {
        return $raw;
    }

    $normalized = preg_replace('#^(?:\./)+#', '', $raw);
    $normalized = preg_replace('#^(?:\.\./)+#', '', $normalized);
    $normalized = preg_replace('#^/+#', '', $normalized);

    return $normalized;
}

function translateJobExperience($value, $lang) {
    $raw = trim((string) $value);
    if ($raw === '') {
        return '';
    }

    $canonicalKey = normalizeJobExperienceKey($raw);

    if (isset($lang['job_experience_labels'][$canonicalKey])) {
        $currentLang = $_SESSION['lang'] ?? 'en';
        return $lang['job_experience_labels'][$canonicalKey][$currentLang]
            ?? $lang['job_experience_labels'][$canonicalKey]['en']
            ?? $lang['job_experience_labels'][$canonicalKey];
    }

    return $raw;
}

function translateJobPosition($value, $lang = [], $displayLanguage = null) {
    $raw = trim((string) $value);
    if ($raw === '') {
        return '';
    }

    $locale = $displayLanguage ?: ($_SESSION['lang'] ?? 'en');
    $normalizedKey = strtolower($raw);
    $normalizedKey = preg_replace('/[\s\-_]+/', '_', $normalizedKey);
    $normalizedKey = trim((string) $normalizedKey, '_');

    if (isset($lang['job_position_options']) && is_array($lang['job_position_options'])) {
        $optionMap = $lang['job_position_options'];
        if (isset($optionMap[$normalizedKey])) {
            $option = $optionMap[$normalizedKey];
            if (is_array($option)) {
                if (isset($option[$locale])) {
                    return (string) $option[$locale];
                }
                if (isset($option['en'])) {
                    return (string) $option['en'];
                }
                return (string) reset($option);
            }
            return (string) $option;
        }
    }

    $fallbackMap = [
        'senior' => ['en' => 'Senior', 'vi' => 'Chuyên viên cấp cao'],
        'junior' => ['en' => 'Junior', 'vi' => 'Chuyên viên'],
        'cong_tac_vien' => ['en' => 'Associate', 'vi' => 'Công tác viên'],
        'manager' => ['en' => 'Manager', 'vi' => 'Quản lý'],
        'director' => ['en' => 'Director', 'vi' => 'Giám đốc'],
        'lead' => ['en' => 'Team Lead', 'vi' => 'Trưởng nhóm'],
        'staff' => ['en' => 'Staff', 'vi' => 'Nhân viên'],
        'specialist' => ['en' => 'Specialist', 'vi' => 'Chuyên gia'],
        'assistant' => ['en' => 'Assistant', 'vi' => 'Trợ lý'],
        'executive' => ['en' => 'Executive', 'vi' => 'Chuyên viên cao cấp'],
        'other' => ['en' => 'Other', 'vi' => 'Khác'],
    ];

    if (isset($fallbackMap[$normalizedKey])) {
        return $fallbackMap[$normalizedKey][$locale] ?? $fallbackMap[$normalizedKey]['en'];
    }

    return ucfirst(str_replace('_', ' ', $normalizedKey));
}

function translateJobSalary($value, $lang = []) {
    $raw = trim((string) $value);
    if ($raw === '') {
        return '';
    }

    $key = strtolower(preg_replace('/[\s\-]+/', '_', $raw));
    if ($key === 'thoa_thuan' || $key === 'negotiable') {
        return $lang['salary_negotiate'] ?? ($key === 'thoa_thuan' ? 'Thỏa thuận' : 'Negotiable');
    }

    return $raw;
}

function translateJobType($value, $lang = []) {
    $raw = trim((string) $value);
    if ($raw === '') {
        return '';
    }

    $key = strtolower(preg_replace('/[\s\-]+/', '_', $raw));
    if ($key === 'internship') {
        $key = 'intern';
    }
    if (isset($lang['job_type_filters'][$key])) {
        $label = $lang['job_type_filters'][$key];
        if (is_array($label)) {
            $locale = $_SESSION['lang'] ?? 'en';
            return (string) ($label[$locale] ?? $label['en'] ?? reset($label));
        }
        return (string) $label;
    }

    return $raw;
}

// format ngày theo session ngôn ngữ
function formatDateByLocale($datetime) {
    $created_at = new DateTime($datetime);
    $lang = $_SESSION['lang'];

    if ($lang === 'vi') {
        $months = [
            'Jan'=>'Tháng 1','Feb'=>'Tháng 2','Mar'=>'Tháng 3','Apr'=>'Tháng 4',
            'May'=>'Tháng 5','Jun'=>'Tháng 6','Jul'=>'Tháng 7','Aug'=>'Tháng 8',
            'Sep'=>'Tháng 9','Oct'=>'Tháng 10','Nov'=>'Tháng 11','Dec'=>'Tháng 12',
        ];
        $month = $created_at->format('M');
        return $created_at->format('d ') . $months[$month] . $created_at->format(' Y, H:i');
    } else {
        return $created_at->format('M d, Y H:i');
    }
}
