<?php
// Kiểm tra token bảo mật
$secret = 'matkhaucucmanh2026';
if (!isset($_GET['token']) || $_GET['token'] !== $secret) {
    http_response_code(403);
    die("Access denied");
}

// KHÔNG dùng reset --hard nữa để bảo vệ file webhook.php và file .env (nếu có)
// Ta dùng lệnh stash tạm những thay đổi lặt vặt (nếu có), rồi pull về bình thường
$output = shell_exec('cd /www/wwwroot/viecnhe.io.vn && git stash && git pull origin main 2>&1');

// Ghi kết quả ra log và in ra màn hình
echo "<pre>";
echo "=== WEBHOOK AUTO-DEPLOY SUCCESS ===\n\n";
echo htmlspecialchars((string)$output);
echo "</pre>";
?>