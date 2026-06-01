<?php
include '../includes/header.php';
include '../includes/sidebar.php';

$pdo = Database::connection();
require_once '../app/controllers/ClassificationController.php';

$controller = new ClassificationController($pdo);
$message = '';
$messageType = '';
$results = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $message = 'Invalid form token. Please refresh and try again.';
        $messageType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'classify_all') {
            $allProducts = isset($_POST['all_products']) ? true : false;
            $limit = (int)($_POST['limit'] ?? 100);
            $results = $controller->classifyProducts($allProducts, $limit);
            
            if ($results['success']) {
                $message = "Classification complete! Processed: {$results['total_classified']}, Auto-assigned: {$results['auto_assigned']}";
                $messageType = 'success';
            } else {
                $message = 'Error: ' . $results['error'];
                $messageType = 'danger';
            }
        } elseif ($action === 'train') {
            $trainResults = $controller->trainClassifier();
            $message = "Training complete! Processed: {$trainResults['processed']} products";
            $messageType = 'success';
        } elseif ($action === 'classify_single') {
            $productId = (int)($_POST['product_id'] ?? 0);
            $results = $controller->classifyProduct($productId);
            
            if ($results['success']) {
                $message = "Product classified successfully";
                $messageType = 'success';
            } else {
                $message = 'Error: ' . $results['error'];
                $messageType = 'danger';
            }
        } elseif ($action === 'apply_classification') {
            $productId = (int)($_POST['product_id'] ?? 0);
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $applyResult = $controller->applyClassification($productId, $categoryId);
            
            if ($applyResult['success']) {
                $message = $applyResult['message'];
                $messageType = 'success';
            } else {
                $message = 'Error: ' . $applyResult['error'];
                $messageType = 'danger';
            }
        }
    }
}

// Get products for single classification dropdown
$productsStmt = $pdo->query('SELECT id, name FROM products ORDER BY name LIMIT 100');
$products = $productsStmt->fetchAll();

// Get categories for dropdown
$categoriesStmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
$categories = $categoriesStmt->fetchAll();
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">AI Product Classification</h1>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Classify All Products -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Classify Products</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="classify_all">
                        
                        <div class="form-group">
                            <label for="all_products">Classification Scope</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="all_products" id="unclassified" value="0" checked>
                                <label class="form-check-label" for="unclassified">
                                    Only Unclassified Products
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="all_products" id="allProducts" value="1">
                                <label class="form-check-label" for="allProducts">
                                    All Products (Re-classify)
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="limit">Batch Size (max)</label>
                            <input type="number" class="form-control" id="limit" name="limit" value="100" min="1" max="1000">
                            <small class="form-text text-muted">Number of products to process</small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-magic"></i> Start Classification
                        </button>
                    </form>

                    <hr>

                    <form method="POST" style="margin-top: 20px;">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="train">
                        <p class="text-sm text-muted">Train classifier on existing categorized products to improve accuracy.</p>
                        <button type="submit" class="btn btn-info btn-block">
                            <i class="fas fa-brain"></i> Train Classifier
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Single Product Classification -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Classify Single Product</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="classify_single">
                        
                        <div class="form-group">
                            <label for="product_id">Select Product</label>
                            <select class="form-control" id="product_id" name="product_id" required>
                                <option value="">-- Choose a product --</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo (int)$product['id']; ?>">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Classify Product
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Classification Results -->
    <?php if (!empty($results) && isset($results['predictions'])): ?>
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Classification Results</h6>
                    </div>
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($results['product_name']); ?></h5>
                        
                        <?php if (!empty($results['predictions'])): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Predicted Category</th>
                                            <th>Confidence</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results['predictions'] as $prediction): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($prediction['category']); ?></td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar <?php echo ($prediction['confidence'] >= 70) ? 'bg-success' : (($prediction['confidence'] >= 50) ? 'bg-warning' : 'bg-danger'); ?>" 
                                                             role="progressbar" 
                                                             style="width: <?php echo (int)$prediction['confidence']; ?>%">
                                                            <?php echo (int)$prediction['confidence']; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <?php csrf_field(); ?>
                                                        <input type="hidden" name="action" value="apply_classification">
                                                        <input type="hidden" name="product_id" value="<?php echo (int)$results['product_id']; ?>">
                                                        <input type="hidden" name="category_id" value="<?php 
                                                            $catId = 0;
                                                            foreach ($categories as $cat) {
                                                                if (strtolower($cat['name']) === strtolower($prediction['category'])) {
                                                                    $catId = (int)$cat['id'];
                                                                    break;
                                                                }
                                                            }
                                                            echo $catId;
                                                        ?>">
                                                        <button type="submit" class="btn btn-sm btn-success">Apply</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No predictions available for this product.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Batch Results -->
    <?php if (!empty($results) && isset($results['total_classified'])): ?>
        <div class="row">
            <div class="col-lg-12 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Batch Classification Results</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <h5 class="text-primary"><?php echo (int)$results['total_classified']; ?></h5>
                                <p class="text-muted">Products Classified</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <h5 class="text-success"><?php echo (int)$results['auto_assigned']; ?></h5>
                                <p class="text-muted">Auto-Assigned</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <h5 class="text-warning"><?php echo (int)($results['total_classified'] - $results['auto_assigned']); ?></h5>
                                <p class="text-muted">Need Manual Review</p>
                            </div>
                        </div>

                        <hr>

                        <h6 class="font-weight-bold mb-3">Detailed Results:</h6>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Top Prediction</th>
                                        <th>Confidence</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results['results'] as $result): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($result['product_name']); ?></td>
                                            <td>
                                                <?php 
                                                if (!empty($result['predictions'])) {
                                                    echo htmlspecialchars($result['predictions'][0]['category']);
                                                } else {
                                                    echo '<span class="text-muted">No match</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if (!empty($result['predictions'])) {
                                                    $conf = (int)$result['predictions'][0]['confidence'];
                                                    $badgeClass = ($conf >= 70) ? 'badge-success' : (($conf >= 50) ? 'badge-warning' : 'badge-danger');
                                                    echo '<span class="badge ' . $badgeClass . '">' . $conf . '%</span>';
                                                } else {
                                                    echo '<span class="badge badge-secondary">N/A</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Info Section -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">How AI Classification Works</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Classification Process</h6>
                            <ul>
                                <li>Analyzes product name, description, brand, color, and size</li>
                                <li>Uses keyword matching and pattern recognition</li>
                                <li>Generates confidence scores for each category</li>
                                <li>Auto-assigns categories with >70% confidence</li>
                                <li>Flags products needing manual review</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Supported Categories</h6>
                            <p>
                                <?php 
                                $categories_list = ['Men', 'Women', 'Footwear', 'Accessories', 'Electronics', 'Beauty', 'Sports', 'Formal', 'Casual', 'Premium'];
                                echo implode(', ', $categories_list);
                                ?>
                            </p>
                            <p class="text-sm text-muted">The classifier learns from your existing product categorizations to improve accuracy over time.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
