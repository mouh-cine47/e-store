<?php

class AdminCategoriesController extends Controller
{
    public function index()
    {
        include project_path('includes/header.php');
        include project_path('includes/sidebar.php');
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
            if (!csrf_validate()) {
                $error = 'Invalid form token. Please refresh and try again.';
            } else {
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
        }
        
        $categories = [];
        if ($hasCategories) {
            $categoriesStmt = $pdo->query('SELECT id, name, slug FROM categories ORDER BY name');
            $categories = $categoriesStmt->fetchAll();
        }
        $this->render('admin/categories', get_defined_vars());
    }
}
