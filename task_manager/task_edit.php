<?php
require_once 'includes/dbconnect.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$task = $stmt->fetch();
if (!$task) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'pending';
    $start_date = !empty($_POST['start_date']) ? date('Y-m-d', strtotime(str_replace('/', '-', $_POST['start_date']))) : null;
    $due_date = !empty($_POST['due_date']) ? date('Y-m-d', strtotime(str_replace('/', '-', $_POST['due_date']))) : null;

    $attachment = $task['attachment']; 
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['attachment']['name']);
        $targetFile = $uploadDir . $fileName;
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        $fileType = mime_content_type($_FILES['attachment']['tmp_name']);
        if (in_array($fileType, $allowedTypes) && $_FILES['attachment']['size'] < 2 * 1024 * 1024) {
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
                if ($attachment && file_exists($attachment)) {
                    unlink($attachment); 
                }
                $attachment = $targetFile;
            } else {
                $error = '文件移动失败，请检查目录权限';
            }
        } else {
            $error = '文件类型只支持 JPG/PNG/PDF，大小不超过 2MB';
        }
    }

if (empty($title)) {
        $error = '任务标题不能为空';
    } else {
         $stmt = $pdo->prepare("UPDATE tasks SET title=?, description=?, status=?, start_date=?, due_date=? WHERE id=? AND user_id=?");
        if ($stmt->execute([$title, $description, $status, $start_date, $due_date, $id, $_SESSION['user_id']])) {
            $success = '任务更新成功！<a href="index.php">返回列表</a>';
            $task['title'] = $title;
            $task['description'] = $description;
            $task['status'] = $status;
            $task['start_date'] = $start_date;
            $task['due_date'] = $due_date;
            $task['attachment'] = $attachment;
        } else {
            $error = '更新失败，请重试';
        }
    }
}
?>
<h2>编辑任务</h2>
<?php if ($error): ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="success"><?php echo $success; ?></div>
<?php else: ?>
    <form method="post">
        <div class="form-group">
            <label>标题 *</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>
        </div>
        <div class="form-group">
            <label>描述</label>
            <textarea name="description" rows="4"><?php echo htmlspecialchars($task['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label>状态</label>
            <select name="status">
                <option value="pending" <?php echo $task['status']=='pending' ? 'selected' : ''; ?>>未开始</option>
                <option value="in_progress" <?php echo $task['status']=='in_progress' ? 'selected' : ''; ?>>进行中</option>
                <option value="completed" <?php echo $task['status']=='completed' ? 'selected' : ''; ?>>已完成</option>
            </select>
        </div>
<div class="form-group">
    <label>开始日期（年/月/日）</label>
    <div class="date-input-container">
        <input type="text" name="start_date" id="start_date" placeholder="年/月/日" value="<?php echo $task['start_date'] ? date('Y/m/d', strtotime($task['start_date'])) : ''; ?>">
        <i class="fas fa-calendar-alt calendar-icon" data-target="start_date"></i>
    </div>
</div>
<div class="form-group">
    <label>截止日期（年/月/日）</label>
    <div class="date-input-container">
        <input type="text" name="due_date" id="due_date" placeholder="年/月/日" value="<?php echo $task['due_date'] ? date('Y/m/d', strtotime($task['due_date'])) : ''; ?>">
        <i class="fas fa-calendar-alt calendar-icon" data-target="due_date"></i>
    </div>
</div>
<div class="form-group">
            <label>附件（JPG/PNG/PDF，≤2MB）</label>
            <input type="file" name="attachment" accept="image/jpeg,image/png,application/pdf">
            <?php if ($task['attachment']): ?>
                <p>当前附件：<a href="<?php echo htmlspecialchars($task['attachment']); ?>" target="_blank">查看/下载</a></p>
            <?php endif; ?>
        </div>

        <div class="form-actions">
        <button type="submit">保存修改</button>
        <a href="index.php" class="btn">取消</a>
    </form>
    <script>
    const zhLocale = {
        weekdays: { shorthand: ["日","一","二","三","四","五","六"], longhand: ["星期日","星期一","星期二","星期三","星期四","星期五","星期六"] },
        months: { shorthand: ["1月","2月","3月","4月","5月","6月","7月","8月","9月","10月","11月","12月"], longhand: ["一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"] },
        firstDayOfWeek: 1,
        today: "今天",
        clear: "清除",
        ok: "确定"
    };
    flatpickr("#start_date", { dateFormat: "Y/m/d", allowInput: true, locale: zhLocale });
    flatpickr("#due_date", { dateFormat: "Y/m/d", allowInput: true, locale: zhLocale });
    document.querySelectorAll('.calendar-icon').forEach(icon => {
            icon.addEventListener('click', function() {
                const input = document.getElementById(this.getAttribute('data-target'));
                if (input && input._flatpickr) input._flatpickr.open();
            });
        });
    </script>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>