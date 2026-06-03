

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Product</h1>

    <div class="card shadow mb-4 col-lg-6">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Details: <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h6>
        </div>
        <div class="card-body">
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if (!$hasCategories): ?>
                <div class="alert alert-warning">
                    Categories table is missing. Import the updated database.sql to enable categories.
                </div>
            <?php endif; ?>
            <?php if ($isLegacySchema): ?>
                <div class="alert alert-warning">
                    Legacy product schema detected. Only name, category, price, and quantity will be saved.
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <?php csrf_field(); ?>
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <?php if ($hasCategories && !$isLegacySchema): ?>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo (int)$category['id']; ?>" <?php echo ((int)$product['category_id'] === (int)$category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Or add new category</label>
                        <input type="text" name="new_category" class="form-control" placeholder="New category">
                    </div>
                <?php endif; ?>
                <?php if ($isLegacySchema): ?>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($product['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                <?php endif; ?>
                <?php if (!$isLegacySchema): ?>
                <div class="mb-3">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" class="form-control" value="<?php echo htmlspecialchars($product['brand'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control" value="<?php echo htmlspecialchars($product['color'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Size</label>
                        <input type="text" name="size" class="form-control" value="<?php echo htmlspecialchars($product['size'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Collection</label>
                        <input type="text" name="collection_name" class="form-control" value="<?php echo htmlspecialchars($product['collection_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price ($)</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?php if ($isLegacySchema): ?>
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" value="<?php echo htmlspecialchars($product['quantity'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>" min="0" required>
                        <?php else: ?>
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" value="<?php echo htmlspecialchars($product['stock'], ENT_QUOTES, 'UTF-8'); ?>" min="0" required>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!$isLegacySchema): ?>
                <div class="mb-3">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                    <div class="form-text">JPG, PNG, WEBP, or GIF. Max 2MB.</div>
                    <label class="form-label mt-3" for="image_url">Or Image URL</label>
                    <input type="url" name="image_url" id="image_url" class="form-control" placeholder="https://example.com/product.jpg" value="<?php echo (!empty($product['image']) && preg_match('/^https?:\/\//i', $product['image'])) ? htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    <div class="form-text">Paste a new URL to replace the current image. Local upload is used first if both are provided.</div>
                    <?php if (!empty($product['image'])): ?>
                        <div class="mt-2">
                            <?php $currentImageSrc = preg_match('/^https?:\/\//i', $product['image']) ? $product['image'] : '../public/' . $product['image']; ?>
                            <img src="<?php echo htmlspecialchars($currentImageSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="Product image" style="max-width: 140px;" class="img-thumbnail">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($product['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?php echo ((int)$product['is_active'] === 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Active product</label>
                </div>
                <?php endif; ?>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="index.php" class="btn btn-secondary">Back to List</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include project_path('includes/footer.php'); ?>
