<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get current user data
$userId = getUserId();
$user = $db->getUserById($userId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $whatsapp = sanitize($_POST['whatsapp']);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
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
    
    // Check if email already exists (excluding current user)
    if ($email !== $user['email']) {
        $existingUser = $db->getUserByEmail($email);
        if ($existingUser && $existingUser['id'] != $userId) {
            $errors[] = "Email sudah digunakan oleh user lain";
        }
    }
    
    if (empty($whatsapp)) {
        $errors[] = "Nomor WhatsApp harus diisi";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $whatsapp)) {
        $errors[] = "Format nomor WhatsApp tidak valid";
    }
    
    // Password change validation
    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        if (empty($currentPassword)) {
            $errors[] = "Password saat ini harus diisi untuk mengubah password";
        }
        
        if (empty($newPassword)) {
            $errors[] = "Password baru harus diisi";
        } elseif (strlen($newPassword) < 8) {
            $errors[] = "Password baru minimal 8 karakter";
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = "Konfirmasi password baru tidak cocok";
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            $errors[] = "Password saat ini tidak cocok";
        }
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        $updateData = [
            'name' => $name,
            'email' => $email,
            'whatsapp' => $whatsapp
        ];
        
        // Update password if provided
        if (!empty($newPassword)) {
            $updateData['password'] = password_hash($newPassword, HASH_ALGO);
        }
        
        if ($db->updateUser($userId, $updateData)) {
            // Update session if name or email changed
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            redirect('edit-profile.php?updated=1');
        } else {
            $errors[] = "Terjadi kesalahan saat memperbarui profil. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dagangin - Edit Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #457b9d;
            --secondary-color: #a8dadc;
            --accent-color: #f1faee;
            --dark-color: #1d3557;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: white;
            padding: 60px 0;
            min-height: 300px;
            display: flex;
            align-items: center;
        }
        
        .profile-section {
            background-color: var(--accent-color);
            padding: 40px 0;
            min-height: 100vh;
        }
        
        .profile-card {
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
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(69, 123, 157, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--dark-color) 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, var(--dark-color) 0%, var(--primary-color) 100%);
            color: white;
        }
        
        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary-color);
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .navbar-brand, .navbar-nav .nav-link {
            color: white !important;
        }
        
        .navbar-brand:hover, .navbar-nav .nav-link:hover {
            color: var(--accent-color) !important;
        }
        
        .navbar-toggler {
            border-color: white !important;
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.75%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
        }
        
        .password-strength {
            height: 5px;
            border-radius: 3px;
            margin-top: 5px;
            transition: all 0.3s;
        }
        
        .strength-weak { background-color: #dc3545; width: 33%; }
        .strength-medium { background-color: #ffc107; width: 66%; }
        .strength-strong { background-color: #28a745; width: 100%; }
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
                            <li><a class="dropdown-item active" href="edit-profile.php"><i class="bi bi-person"></i> Edit Profil</a></li>
                            <li><a class="dropdown-item" href="my-ad.php"><i class="bi bi-list-ul"></i> Iklan Saya</a></li>
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
            <h1 class="display-4 fw-bold mb-4">Edit Profil</h1>
            <p class="lead mb-4">Perbarui informasi profil Anda</p>
        </div>
    </section>

    <!-- Profile Section -->
    <section class="profile-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="profile-card">
                        <?php if (isset($_GET['updated'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle"></i> Profil berhasil diperbarui!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

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
                            <div class="text-center mb-4">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&size=120" 
                                     class="avatar-preview" alt="Profile Avatar">
                                <h5 class="mt-3 mb-1"><?php echo htmlspecialchars($user['name']); ?></h5>
                                <p class="text-muted">Member sejak <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nama Lengkap *</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="bi bi-person text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0" id="name" name="name" 
                                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="bi bi-envelope text-muted"></i>
                                            </span>
                                            <input type="email" class="form-control border-start-0" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="whatsapp" class="form-label">Nomor WhatsApp *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-whatsapp text-muted"></i>
                                    </span>
                                    <input type="tel" class="form-control border-start-0" id="whatsapp" name="whatsapp" 
                                           value="<?php echo htmlspecialchars($user['whatsapp']); ?>" required>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">Ubah Password</h5>
                            <p class="text-muted small mb-4">Kosongkan jika tidak ingin mengubah password</p>

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-lock text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0" id="current_password" name="current_password">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">Password Baru</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="bi bi-lock-fill text-muted"></i>
                                            </span>
                                            <input type="password" class="form-control border-start-0" id="new_password" name="new_password">
                                        </div>
                                        <div class="password-strength" id="passwordStrength"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0">
                                                <i class="bi bi-lock-fill text-muted"></i>
                                            </span>
                                            <input type="password" class="form-control border-start-0" id="confirm_password" name="confirm_password">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="bi bi-check-circle"></i> Perbarui Profil
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
        // Password strength checker
        document.getElementById('new_password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthBar.className = 'password-strength';
                return;
            }
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.className = 'password-strength';
            
            if (strength <= 1) {
                strengthBar.classList.add('strength-weak');
            } else if (strength === 2) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const currentPassword = document.getElementById('current_password').value;
            
            if (newPassword || confirmPassword || currentPassword) {
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('Password baru dan konfirmasi password harus cocok!');
                    return false;
                }
            }
        });
    </script>
</body>
</html>
