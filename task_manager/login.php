<?php
require_once 'includes/dbconnect.php';
require_once 'includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = '用户名和密码都不能为空';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = '用户名或密码错误';
        }
    }
}
?>

<div class="login-wrapper">
    <div class="login-box">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <h2 class="login-title">任务管理器</h2>
            <p class="login-subtitle">高效管理你的日常任务</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label><i class="fas fa-user"></i> 用户名或邮箱</label>
                <input type="text" name="username" required placeholder="请输入用户名或邮箱">
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> 密码</label>
                <input type="password" name="password" required placeholder="请输入密码">
            </div>
            <button type="submit" class="login-btn">登录</button>
        </form>

        <div class="login-links">
            <a href="forgot_password.php">忘记密码？</a>
            <a href="register.php">立即注册</a>
        </div>

        <div class="login-features">
            <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>创建任务</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-edit"></i>
                <span>编辑更新</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-chart-line"></i>
                <span>跟踪进度</span>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>