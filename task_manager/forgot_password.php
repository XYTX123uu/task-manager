<?php
require_once 'includes/dbconnect.php';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $error = '请输入邮箱地址';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '邮箱格式不正确';
    } else {
        // 查找用户
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            $success = '如果该邮箱已注册，重置链接已发送。';
        } else {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $user['id']]);

            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/reset_password.php?token=" . $token;

            $success = "重置链接（演示模式）：<a href='$reset_link'>$reset_link</a><br>实际应用中会发送到您的邮箱。";
        }
    }
}
?>
<h2>忘记密码</h2>
<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="success"><?php echo $success; ?></div>
<?php else: ?>
    <form method="post">
        <div class="form-group">
            <label>注册邮箱</label>
            <input type="email" name="email" required>
        </div>
        <button type="submit">发送重置链接</button>
        <a href="login.php">返回登录</a>
    </form>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>