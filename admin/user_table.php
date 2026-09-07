<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['usernameadmin'])) {
    header('Location: adminlogin.php');
    exit();
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function setAdminUserFlash(string $type, string $message): void
{
    $_SESSION['admin_user_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function getCurrentAdminId(mysqli $conn): int
{
    if (!empty($_SESSION['admin_id'])) {
        return (int) $_SESSION['admin_id'];
    }

    $username = (string) ($_SESSION['usernameadmin'] ?? '');
    if ($username === '') {
        return 0;
    }

    $stmt = $conn->prepare("SELECT id FROM `user` WHERE username = ? AND user_type = 'admin' LIMIT 1");
    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    $adminId = (int) ($row['id'] ?? 0);
    if ($adminId > 0) {
        $_SESSION['admin_id'] = $adminId;
    }

    return $adminId;
}

function formatUserType(string $userType): string
{
    return match (strtolower($userType)) {
        'admin' => 'Quản trị viên',
        'employer' => 'Nhà tuyển dụng',
        'jobseeker' => 'Ứng viên',
        default => 'Không rõ',
    };
}

function formatVerificationStatus($value): string
{
    return !empty($value) ? 'Đã xác minh' : 'Chưa xác minh';
}

function formatDateValue($value): string
{
    if (empty($value)) {
        return '—';
    }

    $timestamp = strtotime((string) $value);
    return $timestamp === false ? '—' : date('d/m/Y H:i', $timestamp);
}

if (empty($_SESSION['admin_user_csrf'])) {
    $_SESSION['admin_user_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');
    $sessionToken = (string) ($_SESSION['admin_user_csrf'] ?? '');
    $action = (string) ($_POST['action'] ?? '');
    $targetId = filter_var(
        $_POST['user_id'] ?? null,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($sessionToken === '' || $csrfToken === '' || !hash_equals($sessionToken, $csrfToken)) {
        setAdminUserFlash('error', 'Phiên xác nhận không hợp lệ. Vui lòng thử lại.');
        header('Location: user_table.php');
        exit();
    }

    if (!in_array($action, ['lock', 'unlock'], true) || $targetId === false) {
        setAdminUserFlash('error', 'Yêu cầu khoá tài khoản không hợp lệ.');
        header('Location: user_table.php');
        exit();
    }

    $targetId = (int) $targetId;
    $targetStmt = $conn->prepare("SELECT id, username, user_type, status FROM `user` WHERE id = ? LIMIT 1");
    $targetUser = null;

    if ($targetStmt) {
        $targetStmt->bind_param('i', $targetId);
        $targetStmt->execute();
        $targetResult = $targetStmt->get_result();
        $targetUser = $targetResult ? $targetResult->fetch_assoc() : null;
        $targetStmt->close();
    }

    if (!$targetUser) {
        setAdminUserFlash('error', 'Không tìm thấy tài khoản cần cập nhật.');
        header('Location: user_table.php');
        exit();
    }

    $currentAdminId = getCurrentAdminId($conn);
    if ($action === 'lock' && $currentAdminId > 0 && $targetId === $currentAdminId) {
        setAdminUserFlash('error', 'Không thể tự khoá tài khoản quản trị hiện tại.');
        header('Location: user_table.php');
        exit();
    }

    $username = (string) ($targetUser['username'] ?? '');
    $currentStatus = (string) ($targetUser['status'] ?? 'unlocked');
    $updated = false;

    if ($action === 'lock') {
        $lockReason = trim((string) ($_POST['lock_reason'] ?? ''));
        $reasonLength = function_exists('mb_strlen')
            ? mb_strlen($lockReason, 'UTF-8')
            : strlen($lockReason);

        if ($lockReason === '') {
            setAdminUserFlash('error', 'Vui lòng nhập lý do khoá tài khoản.');
            header('Location: user_table.php');
            exit();
        }

        if ($reasonLength > 2000) {
            setAdminUserFlash('error', 'Lý do khoá tài khoản không được vượt quá 2.000 ký tự.');
            header('Location: user_table.php');
            exit();
        }

        $updateStmt = $conn->prepare("UPDATE `user` SET status = 'locked', admin_note = ? WHERE id = ? AND status = 'unlocked'");
        if ($updateStmt) {
            $updateStmt->bind_param('si', $lockReason, $targetId);
            $updateStmt->execute();
            $updated = $updateStmt->affected_rows === 1;
            $updateStmt->close();
        }

        if ($updated) {
            setAdminUserFlash('success', 'Đã khoá tài khoản ' . $username . '.');
        } elseif ($currentStatus === 'locked') {
            setAdminUserFlash('error', 'Tài khoản ' . $username . ' đã được khoá trước đó.');
        } else {
            setAdminUserFlash('error', 'Không thể khoá tài khoản ' . $username . '.');
        }
    } else {
        $updateStmt = $conn->prepare("UPDATE `user` SET status = 'unlocked' WHERE id = ? AND status = 'locked'");
        if ($updateStmt) {
            $updateStmt->bind_param('i', $targetId);
            $updateStmt->execute();
            $updated = $updateStmt->affected_rows === 1;
            $updateStmt->close();
        }

        if ($updated) {
            setAdminUserFlash('success', 'Đã bỏ khoá tài khoản ' . $username . '.');
        } elseif ($currentStatus === 'unlocked') {
            setAdminUserFlash('error', 'Tài khoản ' . $username . ' đang ở trạng thái hoạt động.');
        } else {
            setAdminUserFlash('error', 'Không thể bỏ khoá tài khoản ' . $username . '.');
        }
    }

    header('Location: user_table.php');
    exit();
}

$flash = $_SESSION['admin_user_flash'] ?? null;
unset($_SESSION['admin_user_flash']);

$csrfToken = (string) $_SESSION['admin_user_csrf'];
$currentAdminId = getCurrentAdminId($conn);
$users = [];
$listError = '';

$userQuery = "SELECT id, username, user_type, email, phone_number, created_at, verify_email, updated_at, status, admin_note
              FROM `user`
              ORDER BY user_type DESC, id ASC";
$userStmt = $conn->prepare($userQuery);

if ($userStmt && $userStmt->execute()) {
    $userResult = $userStmt->get_result();
    while ($userResult && ($user = $userResult->fetch_assoc())) {
        $users[] = $user;
    }
    $userStmt->close();
} else {
    $listError = 'Không thể tải danh sách người dùng.';
    if ($userStmt) {
        $userStmt->close();
    }
}

$totalUsers = count($users);
$totalEmployers = 0;
$totalJobseekers = 0;
foreach ($users as $user) {
    if (($user['user_type'] ?? '') === 'employer') {
        $totalEmployers++;
    } elseif (($user['user_type'] ?? '') === 'jobseeker') {
        $totalJobseekers++;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - JobHive</title>
    <style>
        :root {
            --admin-navy: #0b1e45;
            --admin-nav: #15294c;
            --admin-nav-hover: #1e3a66;
            --admin-page: #f3f0f2;
            --admin-surface: #ffffff;
            --admin-border: #d6dae2;
            --admin-text: #172033;
            --admin-muted: #667085;
            --admin-danger: #e74c3c;
            --admin-danger-dark: #b9362b;
            --admin-scrim: rgba(11, 30, 69, 0.45);
            --admin-radius-sm: 4px;
            --admin-radius-md: 8px;
            --admin-shadow-modal: 0 18px 48px rgba(11, 30, 69, 0.22);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: var(--admin-text);
            background: var(--admin-page);
            font-family: Arial, sans-serif;
            line-height: 1.4;
        }

        body.modal-open {
            overflow: hidden;
        }

        button,
        input,
        textarea {
            font: inherit;
        }

        button,
        a {
            -webkit-tap-highlight-color: transparent;
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        textarea:focus-visible {
            outline: 3px solid rgba(30, 58, 102, 0.35);
            outline-offset: 2px;
        }

        .admin-mobile-header {
            display: none;
        }

        .admin-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 20;
            width: 220px;
            padding: 20px 10px;
            overflow-y: auto;
            color: #fff;
            background: var(--admin-navy);
        }

        .admin-sidebar h2 {
            margin: 0 10px 10px;
            font-size: 21px;
        }

        .admin-sidebar__status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 10px 24px;
            font-size: 13px;
        }

        .admin-sidebar__status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4caf50;
        }

        .admin-sidebar nav ul {
            display: grid;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .admin-sidebar nav a {
            display: block;
            padding: 11px 12px;
            border-radius: var(--admin-radius-sm);
            color: #fff;
            font-size: 13px;
            text-decoration: none;
            transition: background-color 120ms ease;
        }

        .admin-sidebar nav a:hover,
        .admin-sidebar nav a[aria-current="page"] {
            background: var(--admin-nav-hover);
        }

        .admin-main {
            min-width: 0;
            min-height: 100vh;
            margin-left: 220px;
            padding: 20px 24px 40px;
        }

        .admin-topbar {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 20px;
            min-height: 58px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--admin-border);
        }

        .admin-topbar h1 {
            margin: 0;
            font-size: clamp(24px, 3vw, 30px);
            line-height: 1.1;
        }

        .admin-topbar__logo {
            display: block;
            width: 200px;
            height: 48px;
            border-radius: 10px;
            object-fit: cover;
        }

        .admin-topbar__logout {
            justify-self: end;
            padding: 9px 15px;
            border: 0;
            border-radius: var(--admin-radius-sm);
            color: #fff;
            background: var(--admin-danger);
            font-weight: 700;
            cursor: pointer;
            transition: background-color 120ms ease;
        }

        .admin-topbar__logout:hover {
            background: var(--admin-danger-dark);
        }

        .admin-page-intro {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin: 22px 0 14px;
        }

        .admin-page-intro h2 {
            margin: 0;
            font-size: 21px;
        }

        .back-link {
            color: var(--admin-nav);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .account-census {
            display: flex;
            align-items: stretch;
            min-height: 58px;
            margin-bottom: 14px;
            overflow: hidden;
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius-sm);
            background: var(--admin-surface);
        }

        .census-item {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 150px;
            padding: 10px 18px;
            border-right: 1px solid var(--admin-border);
        }

        .census-item:last-child {
            border-right: 0;
        }

        .census-item strong {
            font-size: 20px;
            font-variant-numeric: tabular-nums;
        }

        .census-item span {
            color: var(--admin-muted);
            font-size: 12px;
        }

        .flash-message {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
            padding: 11px 14px;
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius-sm);
            background: #eef3fb;
            color: var(--admin-navy);
            font-size: 13px;
        }

        .flash-message--error {
            border-color: #f0b4ae;
            background: #fff1ef;
            color: var(--admin-danger-dark);
        }

        .flash-message__close {
            flex: 0 0 auto;
            padding: 0;
            border: 0;
            color: inherit;
            background: transparent;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
        }

        .table-shell {
            overflow: auto;
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius-sm);
            background: var(--admin-surface);
        }

        .user-table {
            width: 100%;
            min-width: 1160px;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
        }

        .user-table th,
        .user-table td {
            padding: 12px 13px;
            border-bottom: 1px solid var(--admin-border);
            text-align: left;
            vertical-align: middle;
        }

        .user-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            color: #fff;
            background: var(--admin-nav);
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .user-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .user-table tbody tr:hover td {
            background: #f7f8fa;
        }

        .user-table .numeric {
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .user-table .username {
            min-width: 140px;
            font-weight: 700;
        }

        .user-table .muted {
            color: var(--admin-muted);
        }

        .user-table .action-column {
            position: sticky;
            right: 0;
            z-index: 3;
            min-width: 130px;
            border-left: 1px solid var(--admin-border);
            background: var(--admin-surface);
        }

        .user-table thead .action-column {
            z-index: 4;
            background: var(--admin-nav);
        }

        .user-table tbody tr:hover .action-column {
            background: #f7f8fa;
        }

        .status-marker {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
            font-size: 12px;
            font-weight: 700;
        }

        .status-marker::before {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--admin-navy);
            content: "";
        }

        .status-marker--locked {
            color: var(--admin-danger-dark);
        }

        .status-marker--locked::before {
            background: var(--admin-danger);
        }

        .account-action {
            min-width: 92px;
            min-height: 32px;
            padding: 6px 10px;
            border: 1px solid var(--admin-danger);
            border-radius: var(--admin-radius-sm);
            color: var(--admin-danger-dark);
            background: #fff;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: color 120ms ease, background-color 120ms ease;
        }

        .account-action:hover {
            color: #fff;
            background: var(--admin-danger);
        }

        .account-action--unlock {
            border-color: var(--admin-navy);
            color: #fff;
            background: var(--admin-navy);
        }

        .account-action--unlock:hover {
            background: var(--admin-nav-hover);
        }

        .account-action:disabled {
            border-color: var(--admin-border);
            color: var(--admin-muted);
            background: #f7f8fa;
            cursor: not-allowed;
        }

        .empty-state {
            padding: 28px 16px !important;
            color: var(--admin-muted);
            text-align: center !important;
        }

        .retry-link {
            color: var(--admin-nav);
            font-weight: 700;
        }

        .modal-backdrop[hidden] {
            display: none;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 100;
            display: grid;
            place-items: center;
            padding: 16px;
            background: var(--admin-scrim);
            opacity: 1;
            transition: opacity 140ms ease;
        }

        .account-modal {
            width: min(400px, 100%);
            max-height: calc(100vh - 32px);
            overflow: auto;
            border-radius: var(--admin-radius-md);
            background: var(--admin-surface);
            box-shadow: var(--admin-shadow-modal);
        }

        .account-modal__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 20px 0;
        }

        .account-modal__title {
            margin: 0;
            font-size: 20px;
            line-height: 1.2;
        }

        .account-modal__close {
            width: 32px;
            height: 32px;
            padding: 0;
            border: 0;
            color: var(--admin-muted);
            background: transparent;
            font-size: 25px;
            line-height: 1;
            cursor: pointer;
        }

        .account-modal__body {
            padding: 14px 20px 20px;
        }

        .account-modal__message {
            margin: 0 0 14px;
            color: var(--admin-muted);
            font-size: 14px;
        }

        .account-modal__target {
            margin: 0 0 16px;
            padding: 9px 10px;
            border-left: 3px solid var(--admin-navy);
            color: var(--admin-text);
            background: #f7f8fa;
            font-size: 13px;
        }

        .reason-field {
            display: grid;
            gap: 7px;
            margin-bottom: 18px;
        }

        .reason-field[hidden] {
            display: none;
        }

        .reason-field label {
            font-size: 13px;
            font-weight: 700;
        }

        .reason-field textarea {
            width: 100%;
            min-height: 96px;
            resize: vertical;
            padding: 10px;
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius-sm);
            color: var(--admin-text);
            background: #fff;
        }

        .account-modal__actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .modal-button {
            min-height: 40px;
            padding: 9px 15px;
            border-radius: var(--admin-radius-sm);
            font-weight: 700;
            cursor: pointer;
        }

        .modal-button--cancel {
            border: 1px solid var(--admin-border);
            color: var(--admin-text);
            background: #fff;
        }

        .modal-button--confirm {
            border: 1px solid var(--admin-danger);
            color: #fff;
            background: var(--admin-danger);
        }

        .modal-button--confirm.is-unlock {
            border-color: var(--admin-navy);
            background: var(--admin-navy);
        }

        .modal-button:disabled {
            opacity: 0.65;
            cursor: wait;
        }

        @media (max-width: 820px) {
            .admin-mobile-header {
                position: fixed;
                inset: 0 0 auto;
                z-index: 30;
                display: flex;
                align-items: center;
                justify-content: space-between;
                height: 56px;
                padding: 0 16px;
                color: #fff;
                background: var(--admin-navy);
            }

            .admin-mobile-header strong {
                font-size: 16px;
            }

            .admin-mobile-header button {
                min-width: 44px;
                min-height: 40px;
                border: 1px solid rgba(255, 255, 255, 0.5);
                border-radius: var(--admin-radius-sm);
                color: #fff;
                background: transparent;
                cursor: pointer;
            }

            .admin-sidebar {
                top: 56px;
                right: auto;
                bottom: 0;
                left: -240px;
                width: 220px;
                transition: left 140ms ease;
            }

            .admin-sidebar.is-open {
                left: 0;
            }

            .admin-nav-backdrop {
                position: fixed;
                inset: 56px 0 0;
                z-index: 19;
                display: none;
                background: rgba(11, 30, 69, 0.35);
            }

            .admin-nav-backdrop.is-visible {
                display: block;
            }

            .admin-main {
                margin-left: 0;
                padding: 76px 14px 30px;
            }

            .admin-topbar {
                grid-template-columns: 1fr auto;
            }

            .admin-topbar__logo {
                display: none;
            }

            .admin-topbar__logout {
                grid-column: 2;
                grid-row: 1;
            }

            .admin-page-intro {
                align-items: flex-start;
                flex-direction: column;
            }

            .account-census {
                overflow-x: auto;
            }

            .census-item {
                min-width: 145px;
            }

            .user-table th,
            .user-table td {
                padding: 11px 10px;
            }

            .account-action {
                min-height: 44px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body>
    <header class="admin-mobile-header">
        <strong>Quản trị</strong>
        <button type="button" id="mobileMenuButton" aria-expanded="false" aria-controls="adminSidebar">Menu</button>
    </header>

    <aside class="admin-sidebar" id="adminSidebar">
        <h2>Quản trị</h2>
        <div class="admin-sidebar__status">
            <span class="admin-sidebar__status-dot" aria-hidden="true"></span>
            <span>Trực tuyến</span>
        </div>
        <nav aria-label="Điều hướng quản trị">
            <ul>
                <li><a href="#">Ảnh trang chủ</a></li>
                <li><a href="user_table.php" aria-current="page">Người dùng</a></li>
                <li><a href="jobpending.php">Duyệt tin tuyển dụng</a></li>
                <li><a href="user_table.php">Duyệt người dùng</a></li>
                <li><a href="feedbackcheck.php">Phản hồi</a></li>
            </ul>
        </nav>
    </aside>
    <div class="admin-nav-backdrop" id="adminNavBackdrop" hidden></div>

    <main class="admin-main">
        <div class="admin-topbar">
            <h1>Quản lý người dùng</h1>
            <img class="admin-topbar__logo" src="../image/logo.png" alt="JobHive">
            <form method="post" action="adminlogout.php">
                <button type="submit" name="admin_logout" class="admin-topbar__logout">Đăng xuất</button>
            </form>
        </div>

        <div class="admin-page-intro">
            <h2>Danh sách tài khoản</h2>
            <a class="back-link" href="admindashboard.php">← Quay lại trang chủ</a>
        </div>

        <?php if (is_array($flash) && !empty($flash['message'])): ?>
            <div class="flash-message <?= ($flash['type'] ?? '') === 'error' ? 'flash-message--error' : '' ?>" role="status" aria-live="polite">
                <span><?= h($flash['message']) ?></span>
                <button type="button" class="flash-message__close" aria-label="Đóng thông báo">&times;</button>
            </div>
        <?php endif; ?>

        <section class="account-census" aria-label="Tổng quan tài khoản">
            <div class="census-item">
                <strong><?= $totalUsers ?></strong>
                <span>Tất cả</span>
            </div>
            <div class="census-item">
                <strong><?= $totalEmployers ?></strong>
                <span>Nhà tuyển dụng</span>
            </div>
            <div class="census-item">
                <strong><?= $totalJobseekers ?></strong>
                <span>Ứng viên</span>
            </div>
        </section>

        <div class="table-shell">
            <table class="user-table">
                <thead>
                    <tr>
                        <th scope="col">Mã</th>
                        <th scope="col">Tên tài khoản</th>
                        <th scope="col">Loại người dùng</th>
                        <th scope="col">Email</th>
                        <th scope="col">Số điện thoại</th>
                        <th scope="col">Ngày tạo</th>
                        <th scope="col">Email đã xác minh</th>
                        <th scope="col">Cập nhật lần cuối</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col" class="action-column">Khoá tài khoản</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($listError !== ''): ?>
                        <tr>
                            <td class="empty-state" colspan="10">
                                <?= h($listError) ?>
                                <a class="retry-link" href="user_table.php">Tải lại trang</a>
                            </td>
                        </tr>
                    <?php elseif (count($users) === 0): ?>
                        <tr>
                            <td class="empty-state" colspan="10">Chưa có tài khoản người dùng.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user):
                            $userId = (int) ($user['id'] ?? 0);
                            $username = (string) ($user['username'] ?? '');
                            $status = ($user['status'] ?? 'unlocked') === 'locked' ? 'locked' : 'unlocked';
                            $isCurrentAdmin = $currentAdminId > 0 && $userId === $currentAdminId;
                        ?>
                            <tr>
                                <td class="numeric"><?= $userId ?></td>
                                <td class="username"><?= h($username) ?></td>
                                <td><?= h(formatUserType((string) ($user['user_type'] ?? ''))) ?></td>
                                <td><?= h($user['email'] ?? '—') ?></td>
                                <td class="muted"><?= h($user['phone_number'] ?: '—') ?></td>
                                <td class="numeric"><?= h(formatDateValue($user['created_at'] ?? null)) ?></td>
                                <td><?= h(formatVerificationStatus($user['verify_email'] ?? false)) ?></td>
                                <td class="numeric"><?= h(formatDateValue($user['updated_at'] ?? null)) ?></td>
                                <td>
                                    <span class="status-marker <?= $status === 'locked' ? 'status-marker--locked' : '' ?>">
                                        <?= $status === 'locked' ? 'Đã khoá' : 'Đang hoạt động' ?>
                                    </span>
                                </td>
                                <td class="action-column">
                                    <?php if ($isCurrentAdmin && $status === 'unlocked'): ?>
                                        <button type="button" class="account-action" disabled title="Không thể tự khoá tài khoản quản trị hiện tại">Tài khoản hiện tại</button>
                                    <?php else: ?>
                                        <button
                                            type="button"
                                            class="account-action <?= $status === 'locked' ? 'account-action--unlock' : '' ?>"
                                            data-account-action="<?= $status === 'locked' ? 'unlock' : 'lock' ?>"
                                            data-user-id="<?= $userId ?>"
                                            data-username="<?= h($username) ?>">
                                            <?= $status === 'locked' ? 'Bỏ khoá' : 'Khoá' ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div class="modal-backdrop" id="accountModal" role="dialog" aria-modal="true" aria-labelledby="accountModalTitle" aria-describedby="accountModalMessage" hidden>
        <form class="account-modal" method="post" action="user_table.php" id="accountModalForm">
            <div class="account-modal__header">
                <h2 class="account-modal__title" id="accountModalTitle">Xác nhận</h2>
                <button type="button" class="account-modal__close" id="accountModalClose" aria-label="Đóng">&times;</button>
            </div>
            <div class="account-modal__body">
                <p class="account-modal__message" id="accountModalMessage">Bạn có chắc muốn khoá tài khoản này?</p>
                <p class="account-modal__target">Tài khoản: <strong id="accountModalUsername"></strong></p>
                <div class="reason-field" id="lockReasonField">
                    <label for="lockReason">Lý do khoá tài khoản</label>
                    <textarea id="lockReason" name="lock_reason" maxlength="2000" required></textarea>
                </div>
                <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                <input type="hidden" name="action" id="accountModalAction" value="lock">
                <input type="hidden" name="user_id" id="accountModalUserId" value="">
                <div class="account-modal__actions">
                    <button type="button" class="modal-button modal-button--cancel" id="accountModalCancel">Huỷ</button>
                    <button type="submit" class="modal-button modal-button--confirm" id="accountModalConfirm">Xác nhận</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('accountModal');
            const modalForm = document.getElementById('accountModalForm');
            const modalTitle = document.getElementById('accountModalTitle');
            const modalMessage = document.getElementById('accountModalMessage');
            const modalUsername = document.getElementById('accountModalUsername');
            const modalAction = document.getElementById('accountModalAction');
            const modalUserId = document.getElementById('accountModalUserId');
            const modalConfirm = document.getElementById('accountModalConfirm');
            const modalCancel = document.getElementById('accountModalCancel');
            const modalClose = document.getElementById('accountModalClose');
            const lockReasonField = document.getElementById('lockReasonField');
            const lockReason = document.getElementById('lockReason');
            const actionButtons = Array.from(document.querySelectorAll('[data-account-action]'));
            let lastTrigger = null;

            function openModal(trigger) {
                const action = trigger.dataset.accountAction === 'unlock' ? 'unlock' : 'lock';
                const username = trigger.dataset.username || '';
                lastTrigger = trigger;
                modalAction.value = action;
                modalUserId.value = trigger.dataset.userId || '';
                modalUsername.textContent = username;

                if (action === 'unlock') {
                    modalTitle.textContent = 'Xác nhận bỏ khoá tài khoản';
                    modalMessage.textContent = 'Bạn có chắc muốn bỏ khoá tài khoản này không?';
                    lockReasonField.hidden = true;
                    lockReason.required = false;
                    lockReason.disabled = true;
                    modalConfirm.classList.add('is-unlock');
                } else {
                    modalTitle.textContent = 'Xác nhận khoá tài khoản';
                    modalMessage.textContent = 'Bạn có chắc muốn khoá tài khoản này?';
                    lockReasonField.hidden = false;
                    lockReason.required = true;
                    lockReason.disabled = false;
                    lockReason.value = '';
                    modalConfirm.classList.remove('is-unlock');
                }

                modalConfirm.textContent = 'Xác nhận';
                modalConfirm.disabled = false;
                modal.hidden = false;
                document.body.classList.add('modal-open');
                modalCancel.focus();
            }

            function closeModal() {
                modal.hidden = true;
                document.body.classList.remove('modal-open');
                modalForm.reset();
                modalAction.value = 'lock';
                lockReasonField.hidden = false;
                lockReason.required = true;
                lockReason.disabled = false;
                modalConfirm.classList.remove('is-unlock');
                if (lastTrigger) {
                    lastTrigger.focus();
                }
            }

            function trapFocus(event) {
                if (event.key !== 'Tab' || modal.hidden) return;
                const focusable = Array.from(modal.querySelectorAll('button:not([disabled]), textarea:not([disabled]), input:not([disabled])'));
                if (focusable.length === 0) return;
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }

            actionButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    openModal(button);
                });
            });

            modalCancel.addEventListener('click', closeModal);
            modalClose.addEventListener('click', closeModal);
            modal.addEventListener('click', function (event) {
                if (event.target === modal) closeModal();
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.hidden) {
                    closeModal();
                }
                trapFocus(event);
            });
            modalForm.addEventListener('submit', function () {
                modalConfirm.disabled = true;
                modalConfirm.textContent = 'Đang xử lý...';
            });

            document.querySelectorAll('.flash-message__close').forEach(function (button) {
                button.addEventListener('click', function () {
                    const message = button.closest('.flash-message');
                    if (message) message.remove();
                });
            });

            const mobileMenuButton = document.getElementById('mobileMenuButton');
            const sidebar = document.getElementById('adminSidebar');
            const navBackdrop = document.getElementById('adminNavBackdrop');

            function setMobileMenu(open) {
                sidebar.classList.toggle('is-open', open);
                navBackdrop.classList.toggle('is-visible', open);
                navBackdrop.hidden = !open;
                mobileMenuButton.setAttribute('aria-expanded', String(open));
            }

            mobileMenuButton.addEventListener('click', function () {
                setMobileMenu(!sidebar.classList.contains('is-open'));
            });
            navBackdrop.addEventListener('click', function () {
                setMobileMenu(false);
            });
            sidebar.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    setMobileMenu(false);
                });
            });
        });
    </script>
</body>

</html>
