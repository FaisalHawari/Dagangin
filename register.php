<?php
require_once 'config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $whatsapp = sanitize($_POST['whatsapp']);
    $terms = isset($_POST['terms']);
    
    $errors = [];
    
    // Validation
    if (empty($name)) {
        $errors[] = "Nama harus diisi";
    }
    
    if (empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (empty($password)) {
        $errors[] = "Password harus diisi";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password minimal 8 karakter";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Konfirmasi password tidak cocok";
    }
    
    if (empty($whatsapp)) {
        $errors[] = "Nomor WhatsApp harus diisi";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $whatsapp)) {
        $errors[] = "Format nomor WhatsApp tidak valid";
    }
    
    if (!$terms) {
        $errors[] = "Anda harus menyetujui syarat dan ketentuan";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $existingUser = $db->getUserByEmail($email);
        if ($existingUser) {
            $errors[] = "Email sudah terdaftar";
        }
    }
    
    // If no errors, create user
    if (empty($errors)) {
        if ($db->createUser($name, $email, $password, $whatsapp)) {
            // Set session and redirect
            $user = $db->getUserByEmail($email);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            
            redirect('index.php');
        } else {
            $errors[] = "Terjadi kesalahan saat mendaftar. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dagangin - Daftar</title>
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
        .register-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #f1faee 0%, #e9f5f5 100%);
            padding-top: 20px;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 450px;
            width: 100%;
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header h2 {
            color: #457b9d;
            font-weight: bold;
        }
        .register-header p {
            color: #6c757d;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #457b9d;
            box-shadow: 0 0 0 0.2rem rgba(69, 123, 157, 0.25);
        }
        .btn-register {
            background-color: #457b9d;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-register:hover {
            background-color: #3d6b8a;
            color: white;
        }
        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #ddd;
        }
        .divider span {
            background-color: white;
            padding: 0 15px;
            position: relative;
            color: #6c757d;
        }
        .social-register {
            display: flex;
            gap: 10px;
        }
        .social-btn {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
        }
        .social-btn:hover {
            background-color: #f8f9fa;
            color: #333;
            text-decoration: none;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }
        .login-link a {
            color: #457b9d;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .password-strength {
            height: 5px;
            border-radius: 3px;
            margin-top: 5px;
            transition: all 0.3s;
        }
        .alert {
            border-radius: 8px;
            border: none;
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
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Masuk
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Register Section -->
    <section class="register-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="register-card mx-auto">
                        <div class="register-header">
                            <i class="bi bi-person-plus fs-1 text-primary mb-3"></i>
                            <h2>Buat Akun Baru</h2>
                            <p>Bergabung dengan OLC sekarang</p>
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

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-person text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" id="name" name="name" placeholder="Masukkan nama lengkap" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-envelope text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="masukkan@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="whatsapp" class="form-label">Nomor WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-whatsapp text-muted"></i>
                                    </span>
                                    <input type="tel" class="form-control border-start-0" id="whatsapp" name="whatsapp" placeholder="08123456789" value="<?php echo isset($_POST['whatsapp']) ? htmlspecialchars($_POST['whatsapp']) : ''; ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Minimal 8 karakter" required>
                                </div>
                                <div class="password-strength bg-light" id="passwordStrength"></div>
                            </div>

                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Konfirmasi Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-lock-fill text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0" id="confirm_password" name="confirm_password" placeholder="Masukkan ulang password" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" value="1" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?> required>
                                    <label class="form-check-label" for="terms">
                                        Saya menyetujui <a href="#" style="color: #457b9d;">Syarat & Ketentuan</a> dan <a href="#" style="color: #457b9d;">Kebijakan Privasi</a> Dagangin
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-register w-100">
                                Daftar Dagangin Sekarang
                            </button>
                        </form>

                        <div class="divider">
                            <span>atau daftar dengan</span>
                        </div>

                        <div class="social-register mb-3">
                            <a href="#" class="social-btn text-center">
                                <i class="bi bi-google text-danger"></i> Google
                            </a>
                            <a href="#" class="social-btn text-center">
                                <i class="bi bi-facebook text-primary"></i> Facebook
                            </a>
                        </div>

                        <div class="login-link">
                            <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
                        </div>
                    </div>
                </div>
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
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strength = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strength.style.width = '0%';
                strength.style.backgroundColor = '#e9ecef';
            } else if (password.length < 6) {
                strength.style.width = '25%';
                strength.style.backgroundColor = '#dc3545';
            } else if (password.length < 8) {
                strength.style.width = '50%';
                strength.style.backgroundColor = '#ffc107';
            } else if (password.match(/[a-z]/) && password.match(/[A-Z]/) && password.match(/[0-9]/)) {
                strength.style.width = '100%';
                strength.style.backgroundColor = '#28a745';
            } else {
                strength.style.width = '75%';
                strength.style.backgroundColor = '#17a2b8';
            }
        });
    </script>
</body>
</html>
