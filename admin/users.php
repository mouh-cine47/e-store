<?php
include '../includes/header.php';
include '../includes/sidebar.php';
$pdo = Database::connection();

$search = trim($_GET['search'] ?? '');
$params = [];
$filters = ["role = 'client'"];

$tableStmt = $pdo->query("SHOW TABLES LIKE 'users'");
$hasUsers = (bool)$tableStmt->fetch();

if ($search !== '') {
    $filters[] = '(name LIKE :search_name OR email LIKE :search_email)';
    $params['search_name'] = '%' . $search . '%';
    $params['search_email'] = '%' . $search . '%';
}

$whereSql = '';
if (count($filters) > 0) {
    $whereSql = 'WHERE ' . implode(' AND ', $filters);
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $hasUsers) {
    if (!csrf_validate()) {
        $error = 'Invalid form token. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $userId = (int)($_POST['user_id'] ?? 0);

        if ($action === 'delete' && $userId > 0) {
            if ($userId === (int)($_SESSION['user_id'] ?? 0)) {
                $error = 'You cannot delete your own account.';
            } else {
                $deleteStmt = $pdo->prepare('DELETE FROM users WHERE id = :id AND role = :role');
                $deleteStmt->execute([
                    'id' => $userId,
                    'role' => 'client',
                ]);
                $success = 'Client account removed.';
            }
        }
    }
}

$users = [];

if ($hasUsers) {
    $usersStmt = $pdo->prepare(
        'SELECT id, name, email, created_at '
        . 'FROM users '
        . $whereSql . ' '
        . 'ORDER BY created_at DESC'
    );
    $usersStmt->execute($params);
    $users = $usersStmt->fetchAll();
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Clients</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if (!$hasUsers): ?>
        <div class="alert alert-warning">Users table is missing. Import database.sql to enable client management.</div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Clients</h6>
            <form action="" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-sm btn-primary">Search</button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo (int)$user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this client?');">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!$hasUsers): ?>
                <div class="alert alert-info mb-0">Users table is missing.</div>
            <?php elseif (count($users) === 0): ?>
                <div class="alert alert-info mb-0">No clients found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
