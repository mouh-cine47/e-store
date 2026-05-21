<?php
/**
 * ClassificationController - Handle AI Product Classification Operations
 * 
 * Manages product classification requests, training, and results
 */
class ClassificationController
{
    private $pdo;
    private $classifier;

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
        require_once __DIR__ . '/../models/ProductClassifier.php';
        $this->classifier = new ProductClassifier($this->pdo);
    }

    /**
     * Classify a single product
     * 
     * @param int $productId Product ID to classify
     * @return array Classification result
     */
    public function classifyProduct($productId)
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT id, name, description, brand, color, size, collection_name FROM products WHERE id = ?'
            );
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product) {
                return ['success' => false, 'error' => 'Product not found'];
            }

            $predictions = $this->classifier->classifyProduct($product);
            return [
                'success' => true,
                'product_id' => $productId,
                'product_name' => $product['name'],
                'predictions' => $predictions
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Classify all unclassified or all products
     * 
     * @param bool $allProducts If true, classify all products. If false, only unclassified ones
     * @param int $limit Maximum number of products to classify
     * @return array Classification results
     */
    public function classifyProducts($allProducts = false, $limit = 100)
    {
        try {
            $query = 'SELECT id, name, description, brand, color, size, collection_name FROM products';
            if (!$allProducts) {
                $query .= ' WHERE category_id IS NULL OR category_id = 0';
            }
            $query .= ' LIMIT ' . (int)$limit;

            $stmt = $this->pdo->query($query);
            $products = $stmt->fetchAll();

            $results = [];
            $classified = 0;
            $updated = 0;

            foreach ($products as $product) {
                $predictions = $this->classifier->classifyProduct($product);
                
                $results[] = [
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'predictions' => $predictions
                ];

                // Auto-assign category if confidence is high enough (>70%)
                if (!empty($predictions) && $predictions[0]['confidence'] >= 70) {
                    $categoryName = $predictions[0]['category'];
                    $categoryId = $this->getCategoryIdByName($categoryName);
                    
                    if ($categoryId) {
                        $updateStmt = $this->pdo->prepare(
                            'UPDATE products SET category_id = ? WHERE id = ?'
                        );
                        if ($updateStmt->execute([$categoryId, $product['id']])) {
                            $updated++;
                        }
                    }
                }

                $classified++;
            }

            return [
                'success' => true,
                'total_classified' => $classified,
                'auto_assigned' => $updated,
                'results' => $results
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get or create category by name
     * 
     * @param string $categoryName Category name
     * @return int Category ID or 0 if not found
     */
    private function getCategoryIdByName($categoryName)
    {
        try {
            $stmt = $this->pdo->prepare('SELECT id FROM categories WHERE LOWER(name) = ?');
            $stmt->execute([strtolower($categoryName)]);
            $result = $stmt->fetch();
            return $result ? (int)$result['id'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Train classifier on existing categorized products
     * 
     * @return array Training results
     */
    public function trainClassifier()
    {
        return $this->classifier->trainOnExistingData();
    }

    /**
     * Get classification predictions for a product without saving
     * 
     * @param int $productId Product ID
     * @return array Predictions
     */
    public function getPredictions($productId)
    {
        $result = $this->classifyProduct($productId);
        return $result['success'] ? $result['predictions'] : [];
    }

    /**
     * Apply manual classification to a product
     * 
     * @param int $productId Product ID
     * @param int $categoryId Category ID
     * @return array Result
     */
    public function applyClassification($productId, $categoryId)
    {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE products SET category_id = ? WHERE id = ?'
            );
            $stmt->execute([$categoryId, $productId]);

            return [
                'success' => true,
                'message' => 'Product classified successfully'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get available classification categories
     * 
     * @return array Categories
     */
    public function getAvailableCategories()
    {
        return $this->classifier->getAvailableCategories();
    }

    /**
     * Get keywords for a category
     * 
     * @param string $category Category name
     * @return array Keywords
     */
    public function getKeywords($category)
    {
        return $this->classifier->getKeywordsForCategory($category);
    }

    /**
     * Add keywords to a category
     * 
     * @param string $category Category name
     * @param array $keywords Keywords to add
     * @return array Result
     */
    public function addKeywords($category, $keywords)
    {
        $this->classifier->updateCategoryKeywords($category, $keywords);
        return [
            'success' => true,
            'message' => 'Keywords updated',
            'keywords' => $this->classifier->getKeywordsForCategory($category)
        ];
    }
}
