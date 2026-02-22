<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=detail.php&id=' . (isset($_GET['id']) ? (int)$_GET['id'] : ''));
}

// Get ad ID from URL
$adId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get ad details
$ad = $db->getAdById($adId);

// If ad not found, redirect to home
if (!$ad) {
    redirect('index.php');
}

// Get ad images
$images = $db->getAdImages($adId);

// Get user details
$user = $db->getUserById($ad['user_id']);

// Get category details
$category = $db->getCategoryById($ad['category_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($ad['title']); ?> - Dagangin - Detail Iklan</title>
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
        .main-image {
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }
        .thumbnail {
            height: 80px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s;
        }
        .thumbnail:hover, .thumbnail.active {
            border-color: #457b9d;
        }
        .seller-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
        .contact-btn {
            background-color: #457b9d;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .contact-btn:hover {
            background-color: #3d6b8a;
            color: white;
        }
        .specification-table th {
            background-color: #f1faee;
            color: #457b9d;
            font-weight: 600;
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
                    <li class="nav-item">
                        <a class="nav-link" href="post-ad.php">
                            <i class="bi bi-plus-circle"></i> Buat Iklan
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" style="color: #457b9d;">Beranda</a></li>
                <li class="breadcrumb-item"><a href="index.php?category=<?php echo $category['id']; ?>" style="color: #457b9d;"><?php echo htmlspecialchars($category['name']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($ad['title']); ?></li>
            </ol>
        </nav>
    </div>

    <!-- Product Detail Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Product Images -->
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php 
                        $mainImage = !empty($images) ? $images[0]['image_path'] : 'https://placehold.co/600x400/png';
                        ?>
                        <img src="<?php echo htmlspecialchars($mainImage); ?>" class="main-image w-100" id="mainImage" alt="<?php echo htmlspecialchars($ad['title']); ?>">
                    </div>
                    <div class="d-flex gap-2">
                        <?php if (!empty($images)): ?>
                            <?php foreach ($images as $index => $image): ?>
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                     class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                     onclick="changeImage(this)" 
                                     alt="Thumbnail <?php echo $index + 1; ?>">
                            <?php endforeach; ?>
                        <?php else: ?>
                            <img src="https://placehold.co/600x400/png" class="thumbnail active" onclick="changeImage(this)" alt="Placeholder">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-md-6">
                    <h2 class="mb-3"><?php echo htmlspecialchars($ad['title']); ?></h2>
                    <div class="d-flex align-items-center mb-3">
                        <span class="price-tag me-3">Rp <?php echo number_format($ad['price'], 0, ',', '.'); ?></span>
                        <span class="badge bg-success">Tersedia</span>
                    </div>
                    
                    <div class="mb-4">
                        <p class="location-tag mb-2"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($ad['location']); ?></p>
                        <small class="text-muted"><i class="bi bi-clock"></i> Diposting <?php echo timeAgo($ad['created_at']); ?></small>
                    </div>

                    <div class="mb-4">
                        <h5>Deskripsi</h5>
                        <p><?php echo nl2br(htmlspecialchars($ad['description'])); ?></p>
                    </div>

                    <div class="mb-4">
                        <h5>Spesifikasi</h5>
                        <table class="table specification-table">
                            <tr>
                                <th>Kategori</th>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                            </tr>
                            <tr>
                                <th>Kondisi</th>
                                <td>Bekas</td>
                            </tr>
                        </table>
                    </div>

                    <div class="d-flex gap-3">
                        <?php if (!empty($user['whatsapp'])): ?>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $user['whatsapp']); ?>?text=Halo%20saya%20tertarik%20dengan%20iklan%20Anda%20<?php echo urlencode($ad['title']); ?>%20di%20Dagangin" 
                               class="contact-btn flex-fill text-decoration-none d-flex justify-content-center align-items-center">
                                <i class="bi bi-whatsapp me-2"></i> Hubungi Penjual
                            </a>
                        <?php else: ?>
                            <button class="contact-btn flex-fill d-flex justify-content-center align-items-center" disabled>
                                <i class="bi bi-whatsapp me-2"></i> Hubungi Penjual
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-outline-info flex-fill">
                            <i class="bi bi-heart"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Seller Info & Related Products -->
            <div class="row mt-5">
                <div class="col-md-4">
                    <div class="card-body">
                        <h5 class="mb-3">Informasi Penjual</h5>
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>" class="rounded-circle me-3" alt="Seller">
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($user['name']); ?></h6>
                                <small class="text-muted">Member sejak <?php echo date('Y', strtotime($user['created_at'])); ?></small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($ad['location']); ?></small>
                        </div>
                        <?php if (!empty($user['whatsapp'])): ?>
                        <div class="mb-3">
                            <small class="text-muted"><i class="bi bi-whatsapp"></i> <?php echo htmlspecialchars($user['whatsapp']); ?></small>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex gap-2 mb-3">
                            <span class="badge bg-info">Online</span>
                            <span class="badge bg-success">Verified</span>
                        </div>
                        <button class="contact-btn w-100 mb-2">
                            <?php if (!empty($user['whatsapp'])): ?>
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $user['whatsapp']); ?>?text=Halo%20saya%20tertarik%20dengan%20iklan%20Anda%20<?php echo urlencode($ad['title']); ?>%20di%20Dagangin" 
                                   class="text-decoration-none text-white">
                                    <i class="bi bi-chat-dots"></i> Kirim Pesan
                                </a>
                            <?php else: ?>
                                <i class="bi bi-chat-dots"></i> Kirim Pesan
                            <?php endif; ?>
                        </button>
                        <button class="btn btn-outline-info w-100">
                            <i class="bi bi-person"></i> Lihat Profil
                        </button>
                    </div>

                        <div class="mb-4">
                            <h5>Deskripsi</h5>
                            <p><?php echo nl2br(htmlspecialchars($ad['description'])); ?></p>
                        </div>

                        <div class="mb-4">
                            <h5>Spesifikasi</h5>
                            <table class="table specification-table">
                                <tr>
                                    <th>Kategori</th>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Kondisi</th>
                                    <td>Bekas</td>
                                </tr>
                            </table>
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
        function changeImage(thumbnail) {
            const mainImage = document.getElementById('mainImage');
            mainImage.src = thumbnail.src.replace('80x80', '600x400');
            
            // Remove active class from all thumbnails
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            
            // Add active class to clicked thumbnail
            thumbnail.classList.add('active');
        }
    </script>
</body>
</html>
