<?php
require_once 'config.php';

// Get categories for display
$categories = $db->getAllCategories();

// Get recent ads
$recentAds = $db->getRecentAds(12);

// Handle search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : '';
$locationId = isset($_GET['location']) ? (int)$_GET['location'] : 0;
$locationFilter = '';
if ($locationId > 0) {
    $locationData = $db->getLocationById($locationId);
    if ($locationData) {
        $locationFilter = $locationData['name'];
    }
}
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

if ($search || $categoryFilter || $locationFilter) {
    $ads = $db->searchAds($search, $categoryFilter, $locationFilter, 12, $sortBy);
} else {
    $ads = $db->getRecentAds(12, $sortBy);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dagangin - Online Classifieds</title>
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
                    <?php if (isLoggedIn()): ?>
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
                                <li><a class="dropdown-item" href="edit-profile.php"><i class="bi bi-person"></i> Edit Profil</a></li>
                                <li><a class="dropdown-item" href="my-ad.php"><i class="bi bi-list-ul"></i> Iklan Saya</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Masuk
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="bi bi-person-plus"></i> Daftar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">Temukan Barang Impian Anda</h1>
            <p class="lead mb-4">Jual dan beli barang bekas dengan mudah dan aman</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <form method="GET" action="index.php" class="d-flex gap-2">
                        <input type="text" class="form-control" name="search" placeholder="Cari barang yang Anda inginkan..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-info" type="submit">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Kategori Populer</h2>
            <div class="row g-4">
                <?php foreach ($categories as $category): ?>
                    <div class="col-md-3 col-6">
                        <a href="index.php?category=<?php echo $category['id']; ?>" class="text-decoration-none">
                            <div class="card category-card text-center p-3">
                                <i class="bi <?php echo htmlspecialchars($category['icon']); ?> fs-1 text-primary mb-2"></i>
                                <h6><?php echo htmlspecialchars($category['name']); ?></h6>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Search & Filter Section -->
    <section class="search-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <form method="GET" action="index.php">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <select class="form-select" name="category" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $categoryFilter == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                <div class="col-md-3">
                    <form method="GET" action="index.php">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="category" value="<?php echo $categoryFilter; ?>">
                        <select class="form-select" name="location" onchange="this.form.submit()">
                            <option value="">Semua Lokasi</option>
                            <?php 
                            $locations = $db->getAllLocations();
                            foreach ($locations as $location): ?>
                                <option value="<?php echo $location['id']; ?>" <?php echo $locationFilter == $location['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($location['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                <div class="col-md-3">
                    <form method="GET" action="index.php">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="category" value="<?php echo $categoryFilter; ?>">
                        <input type="hidden" name="location" value="<?php echo $locationFilter; ?>">
                        <select class="form-select" name="sort" onchange="this.form.submit()">
                            <option value="">Urutkan</option>
                            <option value="price_asc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'price_asc' ? 'selected' : ''; ?>>Harga Terendah</option>
                            <option value="price_desc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'price_desc' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                            <option value="newest" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                        </select>
                    </form>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-info w-100" onclick="resetFilters()">
                        <i class="bi bi-arrow-clockwise"></i> Reset Filter
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest Ads Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="mb-4">
                <?php if ($search || $categoryFilter || $locationFilter): ?>
                    Hasil Pencarian
                <?php else: ?>
                    Iklan Terbaru
                <?php endif; ?>
            </h2>
            <div class="row g-4">
                <?php if (!empty($ads)): ?>
                    <?php foreach ($ads as $ad): ?>
                        <div class="col-md-3">
                            <a href="detail.php?id=<?php echo $ad['id']; ?>" class="text-decoration-none">
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
                                        <small class="text-muted"><?php echo timeAgo($ad['created_at']); ?></small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <?php if ($search || $categoryFilter || $locationFilter): ?>
                                Tidak ada iklan yang ditemukan untuk pencarian Anda.
                            <?php else: ?>
                                Belum ada iklan yang tersedia.
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Load More Button -->
            <?php if (!empty($ads) && count($ads) >= 12): ?>
            <div class="text-center mt-5">
                <button class="btn btn-outline-info btn-lg" onclick="loadMoreAds()">
                    <i class="bi bi-arrow-clockwise"></i> Muat Lebih Banyak
                </button>
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
        let currentPage = 1;
        
        function loadMoreAds() {
            currentPage++;
            // Implement AJAX load more functionality here
            console.log('Loading more ads, page:', currentPage);
            
            // For now, just show a message
            alert('Fitur load more akan segera tersedia!');
        }
        
        function resetFilters() {
            window.location.href = 'index.php';
        }
    </script>
</body>
</html>
