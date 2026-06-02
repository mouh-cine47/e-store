<?php

class AdminUsersController extends Controller
{
    public function index()
    {
        include project_path('includes/header.php');
        include project_path('includes/sidebar.php');
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
        $this->render('admin/users', get_defined_vars());
    }
}
