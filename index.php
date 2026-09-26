<?php
error_reporting(E_ALL); // báo lỗi ra
ini_set('display_errors', 1); // đảm bảo lỗi hiển thị

$action = isset($_GET['action']) ? $_GET['action'] : 'home';

if ($action === 'search' || $action === 'results') {
    include_once(__DIR__ . '/app/controllers/JobControllers.php');
    $controller = new JobController();

    if ($action === 'search') {
        $controller->search();
    } elseif ($action === 'results') {
        $controller->searchResults();
    }

    exit;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Việc Nhẹ.vn</title>
    <link rel="icon" type="image/png" href="image/favicon.png">
</head>

<body>
    <?php
    include_once(__DIR__ . '/homepage/header.php');
    include_once(__DIR__ . '/homepage/body.php');
    ?>
</body>
<footer>
    <?php
    include_once(__DIR__ . '/homepage/footer.php');
    ?>
</footer>

</html>