<?php

class ClassificationController
{
    private $pdo;
    private $categories = [];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->loadCategories();
    }

    private function loadCategories()
    {
        $stmt = $this->pdo->query('SELECT id, name FROM categories ORDER BY name');
        $this->categories = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function normalizeText($product)
    {
        $parts = [
            $product['name'] ?? '',
            $product['description'] ?? '',
            $product['brand'] ?? '',
            $product['color'] ?? '',
            $product['size'] ?? '',
        ];

        return strtolower(implode(' ', $parts));
    }

    private function predictCategory(array $product)
    {
        $text = $this->normalizeText($product);
        $predictions = [];

        foreach ($this->categories as $category) {
            $score = 0;
            $name = strtolower($category['name']);

            if ($name !== '' && strpos($text, $name) !== false) {
                $score += 70;
            }

            $keywords = array_filter(preg_split('/[^a-z0-9]+/', $name));
            foreach ($keywords as $keyword) {
                if ($keyword === '') {
                    continue;
                }
                if (strpos($text, $keyword) !== false) {
                    $score += 15;
                }
            }

            if ($score > 0) {
                $predictions[] = [
                    'category_id' => (int)$category['id'],
                    'category' => $category['name'],
                    'confidence' => min(100, $score),
                ];
            }
        }

        if (empty($predictions) && !empty($this->categories)) {
            $default = $this->categories[0];
            $predictions[] = [
                'category_id' => (int)$default['id'],
                'category' => $default['name'],
                'confidence' => 45,
            ];
        }

        usort($predictions, function ($a, $b) {
            return $b['confidence'] <=> $a['confidence'];
        });

        return $predictions[0] ?? ['category_id' => 0, 'category' => 'Uncategorized', 'confidence' => 0];
    }

    public function classifyProducts($allProducts = false, $limit = 100)
    {
        $sql = 'SELECT id, name, description, brand, color, size, category_id FROM products';
        $params = [];

        if (!$allProducts) {
            $sql .= ' WHERE category_id IS NULL OR category_id = 0';
        }

        $sql .= ' ORDER BY id DESC LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', max(1, min(1000, $limit)), PDO::PARAM_INT);
        $stmt->execute($params);

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $results = [];
        $totalClassified = 0;
        $autoAssigned = 0;

        foreach ($products as $product) {
            $prediction = $this->predictCategory($product);
            $totalClassified++;

            if ($prediction['confidence'] >= 70 && $prediction['category_id'] > 0) {
                $update = $this->pdo->prepare('UPDATE products SET category_id = :category_id WHERE id = :id');
                $update->execute([
                    'category_id' => $prediction['category_id'],
                    'id' => $product['id'],
                ]);
                $autoAssigned++;
            }

            $results[] = [
                'product_name' => $product['name'] ?? 'Unknown Product',
                'predictions' => [$prediction],
            ];
        }

        return [
            'success' => true,
            'total_classified' => $totalClassified,
            'auto_assigned' => $autoAssigned,
            'results' => $results,
        ];
    }

    public function trainClassifier()
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) AS total FROM products WHERE category_id IS NOT NULL AND category_id <> 0');
        $processed = (int)($stmt->fetchColumn() ?: 0);

        return [
            'processed' => $processed,
            'success' => true,
        ];
    }

    public function classifyProduct($productId)
    {
        $stmt = $this->pdo->prepare('SELECT id, name, description, brand, color, size FROM products WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return [
                'success' => false,
                'error' => 'Product not found.',
            ];
        }

        $prediction = $this->predictCategory($product);

        return [
            'success' => true,
            'product_id' => (int)$product['id'],
            'product_name' => $product['name'] ?? 'Unnamed Product',
            'predictions' => [$prediction],
        ];
    }

    public function applyClassification($productId, $categoryId)
    {
        if ($productId <= 0 || $categoryId <= 0) {
            return [
                'success' => false,
                'error' => 'Invalid product or category.',
            ];
        }

        $productStmt = $this->pdo->prepare('SELECT id FROM products WHERE id = :id LIMIT 1');
        $productStmt->execute(['id' => $productId]);
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return [
                'success' => false,
                'error' => 'Product not found.',
            ];
        }

        $categoryStmt = $this->pdo->prepare('SELECT id FROM categories WHERE id = :id LIMIT 1');
        $categoryStmt->execute(['id' => $categoryId]);
        $category = $categoryStmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            return [
                'success' => false,
                'error' => 'Category not found.',
            ];
        }

        $update = $this->pdo->prepare('UPDATE products SET category_id = :category_id WHERE id = :id');
        $update->execute([
            'category_id' => $categoryId,
            'id' => $productId,
        ]);

        return [
            'success' => true,
            'message' => 'Classification applied successfully.',
        ];
    }
}
