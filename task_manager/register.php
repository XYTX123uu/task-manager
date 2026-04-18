<?php
require_once 'includes/dbconnect.php';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = '所有字段都是必填的';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '邮箱格式不正确';
    } elseif (strlen($password) < 6) {
        $error = '密码至少6位';
    } elseif ($password !== $confirm) {
        $error = '两次密码输入不一致';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = '用户名或邮箱已被注册';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed])) {
                $success = '注册成功！请 <a href="login.php">登录</a>';
            } else {
                $error = '注册失败，请重试';
            }
        }
    }
}
?>
<h2>用户注册</h2>
<?php if ($error): ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="success"><?php echo $success; ?></div>
<?php else: ?>
    <form method="post">
        <div class="form-group">
            <label>用户名</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>邮箱</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>密码（至少6位）</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>确认密码</label>
            <input type="password" name="confirm_password" required>
        </div>
        <button type="submit">注册</button>
    </form>
    <p>已有账号？<a href="login.php">去登录</a></p>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>