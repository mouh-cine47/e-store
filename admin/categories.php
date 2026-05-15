<?php
include '../includes/header.php';
include '../includes/sidebar.php';
$pdo = Database::connection();

$tableStmt = $pdo->query("SHOW TABLES LIKE 'categories'");
$hasCategories = (bool)$tableStmt->fetch();

$success = '';
$error = '';

function slugify($text)
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text === '' ? 'category' : $text;
}

function uniqueSlug(PDO $pdo, $slug)
{
    $base = $slug;
    $suffix = 1;

    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $suffix++;
        $slug = $base . '-' . $suffix;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $hasCategories) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $error = 'Category name is required.';
        } else {
            $slug = uniqueSlug($pdo, slugify($name));
            $stmt = $pdo->prepare('INSERT INTO categories (name, slug) VALUES (:name, :slug)');
            $stmt->execute([
                'name' => $name,
                'slug' => $slug,
            ]);
            $success = 'Category added.';
        }
    }

    if ($action === 'delete') {
        $categoryId = (int)($_POST['category_id'] ?? 0);
        if ($categoryId > 0) {
            $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
            $stmt->execute(['id' => $categoryId]);
            $success = 'Category deleted.';
        }
    }
}

$categories = [];
if ($hasCategories) {
    $categoriesStmt = $pdo->query('SELECT id, name, slug FROM categories ORDER BY name');
    $categories = $categoriesStmt->fetchAll();
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Categories</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if (!$hasCategories): ?>
        <div class="alert alert-warning">Categories table is missing. Import database.sql to manage categories.</div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Add Category</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Categories</h6>
                </div>
                <div class="card-body">
                    <?php if (count($categories) === 0): ?>
                        <div class="alert alert-info mb-0">No categories yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?php echo (int)$category['id']; ?></td>
                                            <td><?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="category_id" value="<?php echo (int)$category['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
