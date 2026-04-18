<?php
session_start();
require_once 'includes/dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    $stmt = $pdo->prepare("SELECT attachment FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $task = $stmt->fetch();
    if ($task && !empty($task['attachment']) && file_exists($task['attachment'])) {
        unlink($task['attachment']);
    }
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
}
header('Location: index.php');
exit;