<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/storage.php';

if (!isAdmin()) {
    header('Location: ?page=login');
    exit;
}

$path = __DIR__ . '/../data/feedback.json';
$items = readJsonFile($path);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $items = array_values(array_filter($items, function($f) use ($id) { return ($f['id'] ?? '') !== $id; }));
        writeJsonFile($path, $items);
        header('Location: ?page=%40feedback');
        exit;
    }
}
?>
<section class="section">
    <h1>Quản lý Feedback</h1>
    <p><a class="btn" href="?page=admin&tab=feedback">Về Admin Feedback</a></p>
    <table class="table">
        <thead>
            <tr>
                <th>Tên</th>
                <th>Email</th>
                <th>Nội dung</th>
                <th>Ngày</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $f): ?>
            <tr>
                <td><?php echo htmlspecialchars($f['name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($f['email'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($f['content'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($f['createdAt'] ?? ''); ?></td>
                <td>
                    <form method="post" onsubmit="return confirm('Xoá phản hồi này?');" style="display:inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($f['id'] ?? ''); ?>">
                        <button class="btn">Xoá</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>


