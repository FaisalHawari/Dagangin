<?php
/**
 * Database Configuration File
 * Dagangin - Online Classifieds Platform
 */

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'dagangin');
define('DB_USER', 'root');
define('DB_PASS', '');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Start session
session_start();

// Database connection using PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Application settings
define('SITE_NAME', 'OLC');
define('SITE_URL', 'http://localhost/OLC');
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Security settings
define('HASH_ALGO', PASSWORD_DEFAULT);
define('SESSION_NAME', 'olc_session');

// Helper functions
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserName() {
    return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
}

function getUserEmail() {
    return isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
}

function getUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
}

function logout() {
    // Destroy session
    session_destroy();
    
    // Clear session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Redirect to home page
    redirect('index.php');
}

// File upload helper
function uploadImage($file) {
    $uploadDir = 'uploads/ads/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return false;
        }
    }
    
    // Check if directory is writable
    if (!is_writable($uploadDir)) {
        return false;
    }
    
    // Check file size (5MB = 5 * 1024 * 1024 bytes)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return false;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'img_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return $filepath;
    }
    
    return false;
}

// Pagination helper
function getPagination($totalItems, $itemsPerPage = 10) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $currentPage = max(1, min($currentPage, $totalPages));
    
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'total_items' => $totalItems
    ];
}

// Format price helper
function formatPrice($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Time ago helper
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Baru saja';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' menit yang lalu';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' jam yang lalu';
    } elseif ($diff < 2592000) {
        return floor($diff / 86400) . ' hari yang lalu';
    } elseif ($diff < 31536000) {
        return floor($diff / 2592000) . ' bulan yang lalu';
    } else {
        return floor($diff / 31536000) . ' tahun yang lalu';
    }
}

// Database query helpers
class Database {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Users table operations
    public function createUser($name, $email, $password, $whatsapp = '') {
        $hashedPassword = password_hash($password, HASH_ALGO);
        $sql = "INSERT INTO users (name, email, password, whatsapp) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$name, $email, $hashedPassword, $whatsapp]);
    }
    
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Categories table operations
    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY id ASC"; 
    $stmt = $this->pdo->query($sql);
    return $stmt->fetchAll();
    }
    
    public function getCategoryById($id) {
        $sql = "SELECT * FROM categories WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Ads table operations
    public function createAd($userId, $categoryId, $title, $description, $price, $location) {
        $sql = "INSERT INTO ads (user_id, category_id, title, description, price, location, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $categoryId, $title, $description, $price, $location]);
        return $this->pdo->lastInsertId();
    }
    
    public function getAdById($id) {
        $sql = "SELECT a.*, u.name as user_name, u.email as user_email, u.whatsapp as user_whatsapp, c.name as category_name 
                FROM ads a 
                JOIN users u ON a.user_id = u.id 
                JOIN categories c ON a.category_id = c.id 
                WHERE a.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAllAds($limit = 10, $offset = 0, $categoryId = null, $search = null) {
        $sql = "SELECT a.*, u.name as user_name, c.name as category_name 
                FROM ads a 
                JOIN users u ON a.user_id = u.id 
                JOIN categories c ON a.category_id = c.id";
        
        $params = [];
        $conditions = [];
        
        if ($categoryId) {
            $conditions[] = "a.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($search) {
            $conditions[] = "(a.title LIKE ? OR a.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getAdsByUserId($userId, $limit = 10, $offset = 0) {
        $sql = "SELECT a.*, c.name as category_name 
                FROM ads a 
                JOIN categories c ON a.category_id = c.id 
                WHERE a.user_id = ? 
                ORDER BY a.created_at DESC 
                LIMIT ? OFFSET ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function updateAd($id, $title, $description, $price, $location, $categoryId) {
        $sql = "UPDATE ads SET title = ?, description = ?, price = ?, location = ?, category_id = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$title, $description, $price, $location, $categoryId, $id]);
    }

    // Update user
    public function updateUser($userId, $data) {
        $setClause = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $setClause[] = "$key = ?";
            $params[] = $value;
        }
        
        $params[] = $userId;
        
        $sql = "UPDATE users SET " . implode(', ', $setClause) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteAd($id) {
        // First delete related images
        $this->deleteAdImages($id);
        
        // Then delete the ad
        $sql = "DELETE FROM ads WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function getTotalAdsCount($categoryId = null, $search = null) {
        $sql = "SELECT COUNT(*) as count FROM ads a";
        $params = [];
        $conditions = [];
        
        if ($categoryId) {
            $conditions[] = "a.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($search) {
            $conditions[] = "(a.title LIKE ? OR a.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    // Ad images table operations
    public function addAdImage($adId, $imagePath) {
        $sql = "INSERT INTO ad_images (ad_id, image_path) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$adId, $imagePath]);
    }
    
    public function getAdImages($adId) {
        $sql = "SELECT * FROM ad_images WHERE ad_id = ? ORDER BY id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$adId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteAdImages($adId) {
        $sql = "DELETE FROM ad_images WHERE ad_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$adId]);
    }
    
    // Get recent ads
    public function getRecentAds($limit = 10, $sortBy = 'newest') {
        $sql = "SELECT a.*, u.name as user_name, c.name as category_name 
                FROM ads a 
                JOIN users u ON a.user_id = u.id 
                JOIN categories c ON a.category_id = c.id";
        
        // Add sorting
        switch ($sortBy) {
            case 'price_asc':
                $sql .= " ORDER BY a.price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY a.price DESC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY a.created_at DESC";
                break;
        }
        
        $sql .= " LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get featured ads
    public function getFeaturedAds($limit = 6) {
        $sql = "SELECT a.*, u.name as user_name, c.name as category_name 
                FROM ads a 
                JOIN users u ON a.user_id = u.id 
                JOIN categories c ON a.category_id = c.id 
                ORDER BY a.created_at DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search ads
    public function searchAds($search = '', $categoryId = 0, $locationId = 0, $limit = 10, $sortBy = 'newest') {
        $sql = "SELECT a.*, u.name as user_name, c.name as category_name 
                FROM ads a 
                JOIN users u ON a.user_id = u.id 
                JOIN categories c ON a.category_id = c.id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (a.title LIKE ? OR a.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($categoryId > 0) {
            $sql .= " AND a.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($locationId > 0) {
            $sql .= " AND a.location = ?";
            $params[] = $locationId;
        }
        
        // Add sorting
        switch ($sortBy) {
            case 'price_asc':
                $sql .= " ORDER BY a.price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY a.price DESC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY a.created_at DESC";
                break;
        }
        
        $sql .= " LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get ads by category (excluding current ad)
    public function getAdsByCategory($categoryId, $limit = 6, $excludeAdId = 0) {
        $sql = "SELECT a.*, u.name as user_name, c.name as category_name 
                FROM ads a 
                JOIN users u ON a.user_id = u.id 
                JOIN categories c ON a.category_id = c.id 
                WHERE a.category_id = ? AND a.id != ?
                ORDER BY a.created_at DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoryId, $excludeAdId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get ads by user
    public function getAdsByUser($userId, $limit = 50) {
        $sql = "SELECT a.*, u.name as user_name, c.name as category_name 
                FROM ads a 
                JOIN users u ON a.user_id = u.id 
                JOIN categories c ON a.category_id = c.id 
                WHERE a.user_id = ? 
                ORDER BY a.created_at DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteAdImage($imageId) {
        $sql = "DELETE FROM ad_images WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$imageId]);
    }
    
    // Locations table operations
    public function getAllLocations() {
        $stmt = $this->pdo->query("SELECT * FROM locations ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLocationById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM locations WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createLocation($name) {
        $stmt = $this->pdo->prepare("INSERT INTO locations (name) VALUES (?)");
        return $stmt->execute([$name]);
    }

    public function updateLocation($id, $name) {
        $stmt = $this->pdo->prepare("UPDATE locations SET name = ? WHERE id = ?");
        return $stmt->execute([$name, $id]);
    }

    public function deleteLocation($id) {
        $stmt = $this->pdo->prepare("DELETE FROM locations WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

// Initialize database class
$db = new Database($pdo);

// Helper function to initialize sample data
function initializeSampleData($db, $pdo) {
    // Check if categories exist
    $categories = $db->getAllCategories();
    if (empty($categories)) {
        // Insert sample categories
        $sampleCategories = [
            ['Mobil', 'bi-car-front'],
            ['Motor', 'bi-bicycle'],
            ['Properti', 'bi-house'],
            ['Elektronik', 'bi-phone'],
            ['Komputer', 'bi-laptop'],
            ['Fashion', 'bi-tshirt'],
            ['Hobi & Game', 'bi-controller'],
            ['Lainnya', 'bi-three-dots']
        ];
        
        foreach ($sampleCategories as $category) {
            $sql = "INSERT INTO categories (name, icon) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($category);
        }
    }
}

// Initialize sample data
initializeSampleData($db, $pdo);
?>
