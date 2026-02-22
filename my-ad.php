<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get user's ads
$userId = getUserId();
$ads = $db->getAdsByUser($userId);

// Handle ad deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $adId = (int)$_GET['delete'];
    
    // Verify ad belongs to current user
    $ad = $db->getAdById($adId);
    if ($ad && $ad['user_id'] == $userId) {
        // Delete ad images first
        $db->deleteAdImages($adId);
        
        // Delete ad
        $db->deleteAd($adId);
        
        // Redirect to refresh page
        redirect('my-ad.php?deleted=1');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dagangin - Iklan Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #a8dadc 0%, #457b9d 100%);
            color: white;
            padding: 60px 0;
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
        .contact-btn {
            background-color: #457b9d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .contact-btn:hover {
            background-color: #1d3557;
            color: white;
        }
        .contact-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars(getUserName()); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="my-ad.php"><i class="bi bi-list-ul"></i> Iklan Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Iklan Saya</h1>
            <p class="lead mb-4">Kelola semua iklan yang Anda posting</p>
            <a href="post-ad.php" class="btn btn-light btn-lg">
                <i class="bi bi-plus-circle"></i> Buat Iklan Baru
            </a>
        </div>
    </section>

    <!-- My Ads Section -->
    <section class="py-5">
        <div class="container">
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> Iklan berhasil dihapus!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> Iklan berhasil diperbarui!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($ads)): ?>
                <div class="row g-4">
                    <?php foreach ($ads as $ad): ?>
                        <div class="col-md-4">
                            <div class="card ad-card">
                                <?php 
                                // Get first image for this ad
                                $images = $db->getAdImages($ad['id']);
                                $imagePath = !empty($images) ? $images[0]['image_path'] : 'https://placehold.co/600x400/png';
                                ?>
                                <img src="<?php echo htmlspecialchars($imagePath); ?>" class="card-img-top ad-image" alt="<?php echo htmlspecialchars($ad['title']); ?>">
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($ad['title']); ?></h6>
                                    <p class="price-tag">Rp <?php echo number_format($ad['price'], 0, ',', '.'); ?></p>
                                    <p class="location-tag"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($ad['location']); ?></p>
                                    <small class="text-muted"><i class="bi bi-clock"></i> <?php echo timeAgo($ad['created_at']); ?></small>
                                    
                                    <div class="mt-3 d-flex gap-2">
                                        <a href="detail.php?id=<?php echo $ad['id']; ?>" class="btn btn-sm btn-outline-info flex-fill">
                                            <i class="bi bi-eye"></i> Lihat
                                        </a>
                                        <a href="edit-ad.php?id=<?php echo $ad['id']; ?>" class="btn btn-sm btn-outline-warning flex-fill">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $ad['id']; ?>)">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                    <h4 class="text-muted">Belum Ada Iklan</h4>
                    <p class="text-muted">Anda belum memposting iklan apa pun. Mulai jual barang Anda sekarang!</p>
                    <a href="post-ad.php" class="btn btn-info">
                        <i class="bi bi-plus-circle"></i> Buat Iklan Pertama
                    </a>
                </div>
            <?php endif; ?>
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
                        <li><a href="#" class="text-light text-decoration-none">Blog</a></li>
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
                <div class="col-md-2">
                    <h6>Ikuti Kami</h6>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light fs-4"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light fs-4"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light fs-4"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-light fs-4"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h6>Lainnya</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light text-decoration-none">Karir</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Blog</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Affiliate</a></li>
                    </ul>
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
        function confirmDelete(adId) {
            if (confirm('Apakah Anda yakin ingin menghapus iklan ini?')) {
                window.location.href = 'my-ad.php?delete=' + adId;
            }
        }
    </script>
</body>
</html>
