<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = (int)$_POST['category'];
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $location = sanitize($_POST['location']);
    $terms = isset($_POST['terms']);
    
    $errors = [];
    
    // Validation
    if (empty($categoryId)) {
        $errors[] = "Kategori harus dipilih";
    }
    
    if (empty($title)) {
        $errors[] = "Judul iklan harus diisi";
    } elseif (strlen($title) > 150) {
        $errors[] = "Judul iklan maksimal 150 karakter";
    }
    
    if (empty($description)) {
        $errors[] = "Deskripsi harus diisi";
    }
    
    if (empty($price) || $price <= 0) {
        $errors[] = "Harga harus lebih dari 0";
    }
    
    if (empty($location)) {
        $errors[] = "Lokasi harus dipilih";
    } else {
        // Get location name from database
        $locationData = $db->getLocationById($location);
        if ($locationData) {
            $location = $locationData['name']; // Store name instead of ID
        } else {
            $errors[] = "Lokasi tidak valid";
        }
    }
    
    if (!$terms) {
        $errors[] = "Anda harus menyetujui syarat dan ketentuan";
    }
    
    // Handle image uploads
    $uploadedImages = [];
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $key => $name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['images']['name'][$key],
                    'type' => $_FILES['images']['type'][$key],
                    'tmp_name' => $_FILES['images']['tmp_name'][$key],
                    'error' => $_FILES['images']['error'][$key],
                    'size' => $_FILES['images']['size'][$key]
                ];
                
                $imagePath = uploadImage($file);
                if ($imagePath) {
                    $uploadedImages[] = $imagePath;
                } else {
                    $errors[] = "File " . $file['name'] . " gagal diupload. Pastikan format JPG/PNG dan ukuran maksimal 5MB.";
                }
            }
        }
    }
    
    if (empty($uploadedImages)) {
        $errors[] = "Minimal upload 1 foto produk";
    }
    
    // If no errors, create ad
    if (empty($errors)) {
        $userId = getUserId();
        
        // Create ad
        $adId = $db->createAd($userId, $categoryId, $title, $description, $price, $location);
        
        if ($adId) {
            // Add images to database
            foreach ($uploadedImages as $imagePath) {
                $db->addAdImage($adId, $imagePath);
            }
            
            $_SESSION['success'] = "Iklan berhasil dipasang! Produk Anda sekarang tersedia di OLC.";
            echo "<script>
                alert('✅ Iklan Berhasil Dipasang!\\n\\nProduk Anda sekarang tersedia di OLC.\\n\\nTerima kasih telah menggunakan layanan kami.');
                window.location.href='index.php';
            </script>";
            exit();
        } else {
            $errors[] = "Terjadi kesalahan saat memasang iklan. Silakan coba lagi.";
        }
    }
}

// Get categories and locations for dropdown
$categories = $db->getAllCategories();
$locations = $db->getAllLocations();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dagangin - Buat Iklan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #a8dadc 0%, #457b9d 100%);
            color: white;
            padding: 60px 0;
        }
        .category-card {
            transition: transform 0.3s;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .category-card:hover {
            transform: translateY(-5px);
        }
        .ad-card {
            transition: transform 0.3s;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .ad-card:hover {
            transform: translateY(-3px);
        }
        .ad-image {
            height: 200px;
            object-fit: cover;
        }
        .price-tag {
            font-weight: bold;
            color: #457b9d;
            font-size: 1.2rem;
        }
        .location-tag {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .search-section {
            background-color: #f1faee;
            padding: 30px 0;
            margin-bottom: 30px;
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
        .text-decoration-none {
            color: inherit !important;
        }
        .text-decoration-none:hover {
            color: inherit !important;
        }
        .post-ad-section {
            min-height: 100vh;
            background: linear-gradient(135deg, #f1faee 0%, #e9f5f5 100%);
            padding: 40px 0;
        }
        .post-ad-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
        }
        .post-ad-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .post-ad-header h2 {
            color: #457b9d;
            font-weight: bold;
        }
        .post-ad-header p {
            color: #6c757d;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px;
            transition: border-color 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #457b9d;
            box-shadow: 0 0 0 0.2rem rgba(69, 123, 157, 0.25);
        }
        .btn-post-ad {
            background-color: #457b9d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-post-ad:hover {
            background-color: #3d6b8a;
            color: white;
        }
        .image-upload-area {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        .image-upload-area:hover {
            border-color: #457b9d;
        }
        .image-preview {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .preview-item {
            position: relative;
            width: 100px;
            height: 100px;
        }
        .preview-item img {
            width: 100%;
            height: 100%;
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
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }
        .step {
            display: flex;
            align-items: center;
            color: #6c757d;
        }
        .step.active {
            color: #457b9d;
        }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }
        .step.active .step-number {
            border-color: #457b9d;
            background-color: #457b9d;
            color: white;
        }
        .step-line {
            width: 50px;
            height: 2px;
            background-color: #ddd;
            margin: 0 10px;
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
                        <a class="nav-link" href="my-ad.php">
                            <i class="bi bi-list-ul"></i> Iklan Saya
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars(getUserName()); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="edit-profile.php"><i class="bi bi-person"></i> Edit Profil</a></li>
                            <li><a class="dropdown-item" href="my-ad.php"><i class="bi bi-list-ul"></i> Iklan Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Post Ad Section -->
    <section class="post-ad-section">
        <div class="container">
            <div class="post-ad-card">
                <div class="post-ad-header">
                    <i class="bi bi-plus-circle fs-1 text-primary mb-3"></i>
                    <h2>Pasang Iklan Baru</h2>
                    <p>Jual barang Anda dengan mudah dan cepat</p>
                </div>

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
                    <!-- Step 1: Basic Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required onchange="updateCategoryIcon(this)">
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" data-icon="<?php echo htmlspecialchars($category['icon']); ?>" <?php echo isset($_POST['category']) && $_POST['category'] == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="mt-2">
                                    <i id="categoryIcon" class="bi bi-three-dots text-muted"></i>
                                    <span id="categoryName" class="text-muted">Pilih kategori</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="condition" class="form-label">Kondisi <span class="text-danger">*</span></label>
                                <select class="form-select" id="condition" name="condition" required>
                                    <option value="">Pilih Kondisi</option>
                                    <option value="baru" <?php echo isset($_POST['condition']) && $_POST['condition'] == 'baru' ? 'selected' : ''; ?>>Baru</option>
                                    <option value="bekas" <?php echo isset($_POST['condition']) && $_POST['condition'] == 'bekas' ? 'selected' : ''; ?>>Bekas</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Iklan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Contoh: Honda Vario 125 2021" maxlength="150" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                        <small class="text-muted">Maksimal 150 karakter</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Harga <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control" id="price" name="price" placeholder="0" maxlength="11" value="<?php echo isset($_POST['price']) ? number_format($_POST['price'], 0, ',', '.') : ''; ?>" required onkeyup="formatRupiah(this)">
                                </div>
                                <small class="text-muted">Format otomatis Rupiah, maksimal 11 digit</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label">Lokasi <span class="text-danger">*</span></label>
                                <select class="form-select" id="location" name="location" required>
                                    <option value="">Pilih Lokasi</option>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?php echo $location['id']; ?>" <?php echo isset($_POST['location']) && $_POST['location'] == $location['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($location['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Details & Photos -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="5" placeholder="Jelaskan kondisi, spesifikasi, dan informasi penting lainnya tentang barang Anda..." required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        <small class="text-muted">Semakin detail deskripsi, semakin cepat barang terjual</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Foto Produk <span class="text-danger">*</span></label>
                        <div class="image-upload-area" onclick="document.getElementById('imageUpload').click()">
                            <i class="bi bi-cloud-upload fs-1 text-muted mb-3"></i>
                            <p class="mb-2">Klik untuk upload foto</p>
                            <small class="text-muted">Maksimal 5 foto, format JPG/PNG, maksimal 5MB per foto</small>
                            <input type="file" id="imageUpload" name="images[]" multiple accept="image/*" style="display: none;" onchange="previewImages(event)">
                        </div>
                        <div class="image-preview" id="imagePreview"></div>
                    </div>

                    <!-- Additional Fields -->
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tag</label>
                        <input type="text" class="form-control" id="tags" name="tags" placeholder="Contoh: motor, bekas, murah" value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>">
                        <small class="text-muted">Pisahkan dengan koma untuk membantu pembeli menemukan iklan Anda</small>
                    </div>

                    <!-- Terms and Submit -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" name="terms" value="1" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="terms">
                                Saya menyetujui <a href="#" style="color: #457b9d;">Syarat & Ketentuan</a> dan <a href="#" style="color: #457b9d;">Kebijakan Privasi</a> OLC
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="button" class="btn btn-outline-secondary flex-fill" onclick="window.history.back()">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </button>
                        <button type="submit" class="btn btn-post-ad flex-fill">
                            <i class="bi bi-send"></i> Pasang Iklan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="bi bi-shop"></i> Dagangin</h5>
                    <p>Platform jual beli online terpercaya di Indonesia. Mudah, aman, dan cepat.</p>
                </div>
                <div class="col-md-2">
                    <h6>Perusahaan</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light text-decoration-none">Tentang Kami</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Karir</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Hubungi Kami</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h6>Bantuan</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light text-decoration-none">FAQ</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Panduan</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Keamanan</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6>Ikuti Kami</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light fs-4"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light fs-4"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light fs-4"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light fs-4"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; 2024 Dagangin. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let uploadedImages = [];

        function updateCategoryIcon(select) {
            const selectedOption = select.options[select.selectedIndex];
            const icon = document.getElementById('categoryIcon');
            const name = document.getElementById('categoryName');
            
            if (selectedOption.value === '') {
                icon.className = 'bi bi-three-dots text-muted';
                name.textContent = 'Pilih kategori';
                name.className = 'text-muted';
            } else {
                icon.className = 'bi ' + selectedOption.getAttribute('data-icon') + ' text-primary';
                name.textContent = selectedOption.textContent;
                name.className = 'text-primary';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category');
            updateCategoryIcon(categorySelect);
        });

        function formatRupiah(input) {
            // Remove all non-digit characters
            let value = input.value.replace(/\D/g, '');
            
            // Format with thousand separator
            let formatted = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            
            // Update input value
            input.value = formatted;
            
            // Store raw value in data attribute for form submission
            input.setAttribute('data-raw-value', value);
        }

        function previewImages(event) {
            const files = event.target.files;
            const preview = document.getElementById('imagePreview');
            
            // Clear existing previews
            preview.innerHTML = '';
            uploadedImages = [];

            // Limit to 5 images
            const maxFiles = Math.min(files.length, 5);
            
            for (let i = 0; i < maxFiles; i++) {
                const file = files[i];
                
                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert(`File ${file.name} terlalu besar. Maksimal 5MB.`);
                    continue;
                }
                
                // Check file type
                if (!file.type.match('image.*')) {
                    alert(`File ${file.name} bukan gambar.`);
                    continue;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-btn" onclick="removeImage(this)">×</button>
                    `;
                    preview.appendChild(previewItem);
                    uploadedImages.push(file);
                };
                reader.readAsDataURL(file);
            }
        }

        function removeImage(button) {
            const previewItem = button.parentElement;
            previewItem.remove();
            
            // Update uploadedImages array
            const index = Array.from(previewItem.parentElement.children).indexOf(previewItem);
            uploadedImages.splice(index, 1);
        }

        function toggleFeaturedPrice() {
            const featuredPrice = document.getElementById('featuredPrice');
            const featured = document.getElementById('featured');
            featuredPrice.style.display = featured.checked ? 'block' : 'none';
        }

        // Character counter for title
        document.getElementById('title').addEventListener('input', function() {
            const maxLength = 150;
            const currentLength = this.value.length;
            if (currentLength >= maxLength) {
                this.value = this.value.substring(0, maxLength);
            }
        });

        // Handle form submission - convert formatted price back to number
        document.querySelector('form').addEventListener('submit', function(e) {
            const priceInput = document.getElementById('price');
            const rawValue = priceInput.getAttribute('data-raw-value') || priceInput.value.replace(/\D/g, '');
            priceInput.value = rawValue;
        });
    </script>
</body>
</html>
