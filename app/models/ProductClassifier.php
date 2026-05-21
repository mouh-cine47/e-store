<?php
/**
 * ProductClassifier - AI-based Product Classification Module
 * 
 * Uses keyword analysis and pattern matching to automatically categorize products
 * based on their name, description, brand, color, size, and other attributes.
 */
class ProductClassifier
{
    private $pdo;
    private $keywords = [];
    private $categoryMappings = [];

    public function __construct($pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
        $this->initializeKeywords();
        $this->loadCategoryMappings();
    }

    /**
     * Initialize keyword database for classification
     */
    private function initializeKeywords()
    {
        $this->keywords = [
            'men' => [
                'shirt', 'tshirt', 't-shirt', 'polo', 'blazer', 'jacket', 'coat', 'pants', 'jeans',
                'trousers', 'shorts', 'sweater', 'hoodie', 'suit', 'tie', 'shoe', 'sneaker', 'boot',
                'loafer', 'oxford', 'belt', 'wallet', 'watch', 'sunglasses', 'cap', 'hat', 'beard',
                'grooming', 'aftershave', 'cologne', 'razor', 'men\'s', 'male', 'masculine', 'gentleman'
            ],
            'women' => [
                'dress', 'skirt', 'blouse', 'top', 'tank', 'bra', 'panties', 'leggings', 'yoga',
                'jeans', 'pants', 'shorts', 'heels', 'pumps', 'stiletto', 'sandal', 'flip-flop',
                'handbag', 'purse', 'clutch', 'makeup', 'lipstick', 'mascara', 'eyeshadow', 'nail',
                'perfume', 'women\'s', 'female', 'feminine', 'lady', 'elegant', 'glamour'
            ],
            'footwear' => [
                'shoe', 'sneaker', 'boot', 'sandal', 'slipper', 'loafer', 'oxford', 'heel',
                'pump', 'stiletto', 'flip-flop', 'flip flop', 'canvas', 'leather shoe', 'athletic',
                'running', 'casual', 'formal', 'dress shoe', 'work boot', 'hiking'
            ],
            'accessories' => [
                'bag', 'purse', 'wallet', 'belt', 'watch', 'jewelry', 'necklace', 'bracelet',
                'ring', 'earring', 'scarf', 'glove', 'hat', 'cap', 'sunglasses', 'glasses', 'tie',
                'bow tie', 'handkerchief', 'brooch', 'pin', 'pendant'
            ],
            'electronics' => [
                'phone', 'smartphone', 'tablet', 'laptop', 'computer', 'headphone', 'speaker',
                'camera', 'gadget', 'charger', 'cable', 'router', 'modem', 'screen', 'monitor',
                'keyboard', 'mouse', 'smartwatch', 'wearable', 'earbuds', 'airpods'
            ],
            'beauty' => [
                'makeup', 'skincare', 'cosmetic', 'lipstick', 'mascara', 'eyeshadow', 'foundation',
                'concealer', 'blush', 'powder', 'cream', 'serum', 'lotion', 'shampoo', 'conditioner',
                'perfume', 'cologne', 'fragrance', 'nail', 'polish', 'salon'
            ],
            'sports' => [
                'sport', 'athletic', 'running', 'yoga', 'gym', 'training', 'workout', 'fitness',
                'ball', 'racket', 'tennis', 'badminton', 'soccer', 'football', 'basketball',
                'cycling', 'skateboard', 'roller', 'swimming'
            ],
            'formal' => [
                'suit', 'blazer', 'formal', 'dress', 'tuxedo', 'evening', 'cocktail', 'gown',
                'elegance', 'professional', 'business', 'office', 'tie', 'cufflink'
            ],
            'casual' => [
                'casual', 'relaxed', 'comfortable', 'everyday', 't-shirt', 'jeans', 'hoodie',
                'sweatshirt', 'lounge', 'weekend', 'laid-back', 'street'
            ],
            'premium' => [
                'luxury', 'premium', 'designer', 'exclusive', 'high-end', 'luxury brand',
                'couture', 'elite', 'prestigious', 'signature', 'limited edition'
            ]
        ];
    }

    /**
     * Load existing category mappings from database
     */
    private function loadCategoryMappings()
    {
        try {
            $stmt = $this->pdo->query('SELECT id, name FROM categories LIMIT 50');
            while ($row = $stmt->fetch()) {
                $this->categoryMappings[strtolower($row['name'])] = $row['id'];
            }
        } catch (Exception $e) {
            // Categories table may not exist yet
        }
    }

    /**
     * Classify a product and return predicted categories with confidence scores
     * 
     * @param array $productData Product information (name, description, brand, color, size, collection)
     * @return array Array of predicted categories with confidence scores
     */
    public function classifyProduct($productData)
    {
        $text = $this->prepareText($productData);
        $scores = [];

        // Calculate TF-IDF like scores for each category
        foreach ($this->keywords as $category => $keywords) {
            $score = $this->calculateCategoryScore($text, $keywords);
            if ($score > 0) {
                $scores[$category] = $score;
            }
        }

        // Sort by score descending
        arsort($scores);

        // Normalize scores to 0-100 range
        $maxScore = max($scores) ?: 1;
        $results = [];
        foreach ($scores as $category => $score) {
            $confidence = (int)(($score / $maxScore) * 100);
            if ($confidence >= 30) { // Only include predictions with 30%+ confidence
                $results[] = [
                    'category' => $category,
                    'confidence' => $confidence
                ];
            }
        }

        return $results;
    }

    /**
     * Classify multiple products at once
     * 
     * @param array $products Array of product data
     * @return array Predictions for each product
     */
    public function classifyMultiple($products)
    {
        $results = [];
        foreach ($products as $product) {
            $results[$product['id']] = $this->classifyProduct($product);
        }
        return $results;
    }

    /**
     * Prepare text for classification by cleaning and normalizing
     * 
     * @param array $productData Product information
     * @return string Concatenated and cleaned text
     */
    private function prepareText($productData)
    {
        $parts = [
            $productData['name'] ?? '',
            $productData['description'] ?? '',
            $productData['brand'] ?? '',
            $productData['color'] ?? '',
            $productData['size'] ?? '',
            $productData['collection_name'] ?? ''
        ];

        $text = implode(' ', array_filter($parts));
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Calculate confidence score for a category based on keyword matches
     * 
     * @param string $text Prepared product text
     * @param array $keywords Keywords for category
     * @return float Confidence score
     */
    private function calculateCategoryScore($text, $keywords)
    {
        $score = 0;
        $textWords = explode(' ', $text);
        $textWordsCount = count($textWords);

        foreach ($keywords as $keyword) {
            // Exact match gets higher score
            $exactCount = substr_count($text, $keyword);
            $score += $exactCount * 2;

            // Partial/word match
            foreach ($textWords as $word) {
                if (strlen($word) >= 3 && strlen($keyword) >= 3) {
                    $similarity = levenshtein($word, $keyword);
                    if ($similarity <= 1) { // Allow 1 character difference
                        $score += 0.5;
                    }
                }
            }
        }

        return $score;
    }

    /**
     * Get all available keywords for a category
     * 
     * @param string $category Category name
     * @return array Keywords for category
     */
    public function getKeywordsForCategory($category)
    {
        return $this->keywords[$category] ?? [];
    }

    /**
     * Get all available categories that can be predicted
     * 
     * @return array List of categories
     */
    public function getAvailableCategories()
    {
        return array_keys($this->keywords);
    }

    /**
     * Update or add keywords for a category
     * 
     * @param string $category Category name
     * @param array $keywords Keywords to add
     */
    public function updateCategoryKeywords($category, $keywords)
    {
        if (!isset($this->keywords[$category])) {
            $this->keywords[$category] = [];
        }
        $this->keywords[$category] = array_unique(array_merge($this->keywords[$category], $keywords));
    }

    /**
     * Train classifier on manually categorized products
     * This learns from product-category mappings in the database
     * 
     * @return array Training results
     */
    public function trainOnExistingData()
    {
        $results = [
            'processed' => 0,
            'errors' => 0,
            'summary' => []
        ];

        try {
            $stmt = $this->pdo->query(
                'SELECT p.id, p.name, p.description, p.brand, p.color, p.size, p.collection_name, c.name as category_name '
                . 'FROM products p '
                . 'LEFT JOIN categories c ON p.category_id = c.id '
                . 'WHERE c.name IS NOT NULL '
                . 'LIMIT 1000'
            );

            $categoryKeywords = [];

            while ($product = $stmt->fetch()) {
                $categoryName = strtolower($product['category_name']);
                $text = $this->prepareText($product);
                $words = array_filter(explode(' ', $text), function ($w) {
                    return strlen($w) > 2;
                });

                if (!isset($categoryKeywords[$categoryName])) {
                    $categoryKeywords[$categoryName] = [];
                }
                $categoryKeywords[$categoryName] = array_unique(
                    array_merge($categoryKeywords[$categoryName], $words)
                );

                $results['processed']++;
            }

            // Update keywords based on training
            foreach ($categoryKeywords as $category => $keywords) {
                if (isset($this->keywords[$category])) {
                    $this->keywords[$category] = array_unique(
                        array_merge($this->keywords[$category], $keywords)
                    );
                    $results['summary'][$category] = count($this->keywords[$category]) . ' keywords';
                }
            }
        } catch (Exception $e) {
            $results['errors']++;
        }

        return $results;
    }
}
