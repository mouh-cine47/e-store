

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Add New Product</h1>

    <div class="card shadow mb-4 col-lg-6">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Product Details</h6>
        </div>
        <div class="card-body">
                        <?php if (!$hasCategories): ?>
                            <div class="alert alert-warning">
                                Categories table is missing. Import the updated database.sql to enable categories.
                            </div>
                        <?php endif; ?>
                        <?php if (!$hasProducts): ?>
                            <div class="alert alert-warning">
                                Products table is missing. Import database.sql to add products.
                            </div>
                        <?php endif; ?>
                        <?php if ($isLegacySchema): ?>
                            <div class="alert alert-warning">
                                Legacy product schema detected. Only name, category, price, and quantity will be saved.
                            </div>
                        <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <?php csrf_field(); ?>
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <?php if ($hasCategories && !$isLegacySchema): ?>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo (int)$category['id']; ?>">
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
                        <input type="text" name="category" class="form-control" required>
                    </div>
                <?php endif; ?>
                <?php if (!$isLegacySchema): ?>
                <div class="mb-3">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" class="form-control" placeholder="Brand">
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control" placeholder="Color">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Size</label>
                        <input type="text" name="size" class="form-control" placeholder="Size">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Collection</label>
                        <input type="text" name="collection_name" class="form-control" placeholder="Collection">
                    </div>
                </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price ($)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?php if ($isLegacySchema): ?>
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" value="0" min="0" required>
                        <?php else: ?>
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" value="0" min="0" required>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!$isLegacySchema): ?>
                <div class="mb-3">
                    <label class="form-label" for="image_file">Product Image</label>
                    <input type="file" name="image_file" id="image_file" class="form-control" accept="image/*">
                    <div class="form-text">JPG, PNG, WEBP, or GIF. Max 2MB.</div>
                </div>
                <div class="mb-3">
                    <div class="image-preview-card" id="imagePreviewCard">
                        <div class="image-preview-placeholder" id="imagePreviewPlaceholder">No image selected</div>
                        <img src="" alt="Selected product" class="image-preview-img" id="imagePreviewImg">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                    <label class="form-check-label" for="is_active">Active product</label>
                </div>
                <?php endif; ?>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save Product</button>
                    <a href="index.php" class="btn btn-secondary">Back to List</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const input = document.getElementById('image_file');
        const previewImg = document.getElementById('imagePreviewImg');
        const previewPlaceholder = document.getElementById('imagePreviewPlaceholder');

        if (!input || !previewImg || !previewPlaceholder) {
            return;
        }

        input.addEventListener('change', function (event) {
            const file = event.target.files && event.target.files[0];
            if (!file) {
                previewImg.src = '';
                previewImg.style.display = 'none';
                previewPlaceholder.style.display = 'block';
                return;
            }

            const objectUrl = URL.createObjectURL(file);
            previewImg.src = objectUrl;
            previewImg.style.display = 'block';
            previewPlaceholder.style.display = 'none';

            previewImg.onload = function () {
                URL.revokeObjectURL(objectUrl);
            };
        });
    })();
</script>

<?php include project_path('includes/footer.php'); ?>
