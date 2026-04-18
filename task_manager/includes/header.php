<?php session_start(); ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>任务管理器</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1><a href="index.php">任务管理器</a></h1>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-info">
                    欢迎，<?php echo htmlspecialchars($_SESSION['username']); ?>
                    <a href="logout.php" class="btn-logout">退出</a>
                </div>
            <?php endif; ?>
        </header>
        <main>