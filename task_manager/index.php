<?php
require_once 'includes/dbconnect.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? '';          
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  
$limit = 5;                                       
$offset = ($page - 1) * $limit;                   

$sql = "SELECT * FROM tasks WHERE user_id = :user_id";
$params = [':user_id' => $user_id];

if (in_array($status_filter, ['pending', 'in_progress', 'completed'])) {
    $sql .= " AND status = :status";
    $params[':status'] = $status_filter;
}

$countSql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalRows = $stmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);          

$sql .= " ORDER BY due_date ASC, created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$tasks = $stmt->fetchAll();
?>

<div class="actions">
    <a href="task_create.php" class="btn btn-create">+ 新建任务</a>
    <div class="filter">
        <a href="?">全部</a>
        <a href="?status=pending">未开始</a>
        <a href="?status=in_progress">进行中</a>
        <a href="?status=completed">已完成</a>
    </div>
</div>

<div class="task-list">
    <?php if (count($tasks) == 0): ?>
        <p>暂无任务，点击“新建任务”开始吧！</p>
    <?php else: ?>
        <?php foreach ($tasks as $task): ?>
            <div class="task-card">
                <div class="task-info">
                    <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                    <div>
                        <span class="task-status status-<?php echo $task['status']; ?>">
                            <?php 
                                $status_map = ['pending'=>'未开始', 'in_progress'=>'进行中', 'completed'=>'已完成'];
                                echo $status_map[$task['status']];
                            ?>
                        </span>
                        <?php if ($task['start_date']): ?>
                            <span>开始：<?php echo date('Y年m月d日', strtotime($task['start_date'])); ?></span>
                        <?php endif; ?>
                        <?php if ($task['due_date']): ?>
                            <span>截止：<?php echo date('Y年m月d日', strtotime($task['due_date'])); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($task['attachment'])): ?>
                            <span><a href="<?php echo htmlspecialchars($task['attachment']); ?>" target="_blank">📎 下载附件</a></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="task-actions">
                    <a href="task_edit.php?id=<?php echo $task['id']; ?>" class="btn btn-edit">编辑</a>
                    <a href="task_delete.php?id=<?php echo $task['id']; ?>" class="btn btn-delete">删除</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page-1])); ?>">&laquo; 上一页</a>
    <?php endif; ?>
    
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page+1])); ?>">下一页 &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>