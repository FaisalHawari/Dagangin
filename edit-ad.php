<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get ad ID from URL
$adId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get ad details
$ad = $db->getAdById($adId);

// If ad not found or doesn't belong to current user, redirect
if (!$ad || $ad['user_id'] != getUserId()) {
    redirect('my-ad.php');
}

// Get categories for dropdown
$categories = $db->getAllCategories();

// Get current ad images
$images = $db->getAdImages($adId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $price = sanitize($_POST['price']);
    $price = str_replace(['Rp', '.', ' '], '', $price); // Remove formatting
    $price = (float)$price;
    $categoryId = (int)$_POST['category'];
    $location = sanitize($_POST['location']);
    
    $errors = [];
    
    // Validation
    if (empty($title)) {
        $errors[] = "Judul iklan harus diisi";
    }
    
    if (empty($description)) {
        $errors[] = "Deskripsi harus diisi";
    }
    
    if (empty($price) || $price <= 0) {
        $errors[] = "Harga harus lebih dari 0";
    }
    
    if ($categoryId <= 0) {
        $errors[] = "Pilih kategori yang valid";
    }
    
    if (empty($location)) {
        $errors[] = "Lokasi harus diisi";
    }
    
    // If no errors, update ad
    if (empty($errors)) {
        if ($db->updateAd($adId, $title, $description, $price, $location, $categoryId)) {
            // Handle image deletions
            if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $imageId) {
                    $db->deleteAdImage($imageId);
                }
            }

            // Handle new image uploads
            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if (!empty($_FILES['images']['name'][$key])) {
                        $imageFile = [
                            'name' => $_FILES['images']['name'][$key],
                            'type' => $_FILES['images']['type'][$key],
                            'tmp_name' => $_FILES['images']['tmp_name'][$key],
                            'error' => $_FILES['images']['error'][$key],
                            'size' => $_FILES['images']['size'][$key]
                        ];
                        
                        $imagePath = uploadImage($imageFile);
                        if ($imagePath) {
                            $db->addAdImage($adId, $imagePath);
                        }
                    }
                }
            }
            
            redirect('my-ad.php?updated=1');
        } else {
            $errors[] = "Terjadi kesalahan saat memperbarui iklan. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dagangin - Edit Iklan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #a8dadc 0%, #457b9d 100%);
            color: white;
            padding: 60px 0;
        }
        .post-ad-section {
            background-color: #f8f9fa;
            padding: 40px 0;
            min-height: 100vh;
        }
        .post-ad-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            border: none;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            padding: 12px 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #457b9d;
            box-shadow: 0 0 0 0.2rem rgba(69, 123, 157, 0.25);
        }
        .btn-post {
            background: linear-gradient(135deg, #457b9d 0%, #1d3557 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .btn-post:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #1d3557 0%, #457b9d 100%);
            color: white;
        }
        .image-preview {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        .image-preview:hover {
            border-color: #457b9d;
        }
        .preview-item {
            position: relative;
            display: inline-block;
            margin: 5px;
        }
        .preview-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .preview-item .remove-btn {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
        }
        .current-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        .image-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .image-item:hover {
            transform: translateY(-2px);
        }
        .current-image-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .image-item:hover .image-overlay {
            opacity: 1;
        }
        .delete-image-btn {
            padding: 8px 12px;
            border-radius: 5px;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        .upload-area:hover {
            border-color: #457b9d;
            background: #f1faee;
        }
        .upload-content {
            pointer-events: none;
        }
        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        .preview-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .preview-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        .preview-item .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .navbar-brand, .navbar-nav .nav-link {
            color: white !important;
        }
        .navbar-brand:hover, .navbar-nav .nav-link:hover {
            color: #f1faee !important;
        }
        .navbar-toggler {
            border-color: white !important;
        }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #457b9d;">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shop"></i> Dagangin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post-ad.php">
                            <i class="bi bi-plus-circle"></i> Buat Iklan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my-ad.php">
                            <i class="bi bi-list-ul"></i> Iklan Saya
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Edit Iklan</h1>
            <p class="lead mb-4">Perbarui informasi iklan Anda</p>
        </div>
    </section>

    <!-- Edit Ad Section -->
    <section class="post-ad-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="post-ad-card">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <div>
                                        <strong>Terjadi Kesalahan:</strong>
                                        <ul class="mb-0 mt-1">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo $error; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Judul Iklan *</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($ad['title']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Kategori *</label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                        <?php echo $ad['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi *</label>
                                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($ad['description']); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Harga *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control" id="price" name="price" 
                                                   value="<?php echo number_format($ad['price'], 0, ',', '.'); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="location" class="form-label">Lokasi *</label>
                                        <input type="text" class="form-control" id="location" name="location" 
                                               value="<?php echo htmlspecialchars($ad['location']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Foto Iklan</label>
                                <p class="text-muted small">Tambahkan atau hapus foto iklan. Maksimal 5 foto, masing-masing maksimal 5MB.</p>
                                
                                <!-- Current Images -->
                                <?php if (!empty($images)): ?>
                                    <div class="mb-4">
                                        <label class="form-label">Foto Saat Ini:</label>
                                        <div class="current-images-grid">
                                            <?php foreach ($images as $image): ?>
                                                <div class="image-item">
                                                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Current image" class="current-image-img">
                                                    <div class="image-overlay">
                                                        <button type="button" class="btn btn-danger btn-sm delete-image-btn" 
                                                                onclick="deleteImage(<?php echo $image['id']; ?>)">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                    <input type="hidden" name="delete_images[]" id="delete_<?php echo $image['id']; ?>" value="">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Add New Images -->
                                <div class="mb-3">
                                    <label class="form-label">Tambah Foto Baru:</label>
                                    <div class="upload-area" onclick="document.getElementById('images').click()">
                                        <div class="upload-content">
                                            <i class="bi bi-cloud-upload fs-1 text-muted mb-2"></i>
                                            <p class="mb-0 text-muted">Klik untuk upload foto atau drag and drop</p>
                                            <small class="text-muted">JPG, PNG maksimal 5MB per foto</small>
                                        </div>
                                    </div>
                                    <input type="file" class="form-control d-none" id="images" name="images[]" multiple accept="image/*">
                                    <div id="previewContainer" class="preview-grid"></div>
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-post flex-fill">
                                    <i class="bi bi-check-circle"></i> Perbarui Iklan
                                </button>
                                <a href="my-ad.php" class="btn btn-outline-secondary flex-fill">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Price formatting
        document.getElementById('price').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value) {
                e.target.value = parseInt(value).toLocaleString('id-ID');
            } else {
                e.target.value = '';
            }
        });

        // Image preview for new uploads
        document.getElementById('images').addEventListener('change', function(e) {
            const files = e.target.files;
            const previewContainer = document.getElementById('previewContainer');
            
            for (let i = 0; i < files.length; i++) {
                if (i >= 5) break; // Max 5 images
                
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'preview-item';
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" alt="Preview ${i + 1}">
                            <button type="button" class="remove-btn" onclick="removePreviewImage(this)">×</button>
                        `;
                        previewContainer.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        function removePreviewImage(btn) {
            btn.parentElement.remove();
        }

        // Delete existing image
        function deleteImage(imageId) {
            if (confirm('Apakah Anda yakin ingin menghapus foto ini?')) {
                const hiddenInput = document.getElementById('delete_' + imageId);
                const imageItem = hiddenInput.closest('.image-item');
                
                // Mark for deletion
                hiddenInput.value = imageId;
                
                // Hide from UI
                imageItem.style.opacity = '0.5';
                imageItem.style.pointerEvents = 'none';
                
                // Show deleted indicator
                const overlay = imageItem.querySelector('.image-overlay');
                overlay.innerHTML = '<span class="text-white"><i class="bi bi-check-circle"></i> Akan dihapus</span>';
                overlay.style.opacity = '1';
            }
        }

        // Form submission confirmation
        document.querySelector('form').addEventListener('submit', function(e) {
            const markedForDeletion = document.querySelectorAll('input[name="delete_images[]"][value!=""]').length;
            if (markedForDeletion > 0) {
                if (!confirm(`Anda akan menghapus ${markedForDeletion} foto. Lanjutkan?`)) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>
</html>
