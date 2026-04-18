<?php
require_once 'includes/dbconnect.php';
require_once 'includes/header.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    $error = '无效的请求。';
} else {
    $stmt = $pdo->prepare("SELECT id, username, reset_expires FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if (!$user) {
        $error = '重置链接无效。';
    } elseif (strtotime($user['reset_expires']) < time()) {
        $error = '重置链接已过期，请重新申请。';
    } else {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            if (strlen($password) < 6) {
                $error = '密码至少6位';
            } elseif ($password !== $confirm) {
                $error = '两次密码输入不一致';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
                if ($stmt->execute([$hashed, $user['id']])) {
                    $success = '密码已重置成功！<a href="login.php">去登录</a>';
                } else {
                    $error = '重置失败，请重试';
                }
            }
        }
    }
}
?>
<h2>重置密码</h2>
<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="success"><?php echo $success; ?></div>
<?php else: ?>
    <?php if (!$error || strpos($error, '无效') === false): ?>
        <form method="post">
            <div class="form-group">
                <label>新密码（至少6位）</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>确认新密码</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit">重置密码</button>
        </form>
    <?php endif; ?>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>