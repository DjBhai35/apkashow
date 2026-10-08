<?php
/**
 * ApkaShow - Admin Login
 * Domain: apkashow.com
 */
require_once __DIR__ . '/../includes/functions.php';

// If already authenticated, redirect to dashboard
if (is_admin_logged_in()) {
    header("Location: " . BASE_URL . "/admin/index.php");
    exit();
}

// Check if any admin account exists in database. If none, redirect to initial setup wizard
$needsSetup = false;
try {
    $db = getDB();
    $adminCount = (int)$db->query("SELECT COUNT(*) FROM `users` WHERE `role` = 'admin'")->fetchColumn();
    if ($adminCount === 0) {
        $needsSetup = true;
    }
} catch (Exception $e) {
    // Database connection or table missing
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            try {
                $db = getDB();
                $stmt = $db->prepare("SELECT * FROM `users` WHERE (`username` = ? OR `email` = ?) AND `status` = 1 LIMIT 1");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Password matches, regenerate session ID for protection against session fixation
                    session_regenerate_id(true);
                    $_SESSION[ADMIN_SESSION_KEY] = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'full_name' => $user['full_name'],
                        'role' => $user['role'],
                        'force_password_change' => (int)($user['force_password_change'] ?? 0)
                    ];
                    
                    if (!empty($user['force_password_change'])) {
                        header("Location: " . BASE_URL . "/admin/users.php?force=1");
                    } else {
                        header("Location: " . BASE_URL . "/admin/index.php");
                    }
                    exit();
                } else {
                    $error = 'Invalid credentials. Please verify your username and password.';
                }
            } catch (Exception $e) {
                $error = 'System authentication error. Please ensure database is configured.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApkaShow Admin Portal - Secure Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/cinematic.css">
    <style>
        body {
            background: radial-gradient(circle at center, #111624 0%, #05070a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #0e121b;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 18px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.9);
            width: 100%;
            max-width: 440px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="login-card mx-auto p-4 p-md-5">
        <div class="text-center mb-4">
            <span class="fs-1">🎬</span>
            <h3 class="fw-bold text-white mb-1">Apka<span class="text-danger">Show</span></h3>
            <small class="text-muted">Master Administration Suite &bull; apkashow.com</small>
        </div>

        <?php if ($needsSetup): ?>
            <div class="alert alert-warning bg-warning bg-opacity-10 border-warning text-warning small mb-4">
                <i class="bi bi-info-circle me-1"></i> No administrator account found. Please initialize your secure admin credentials using the <a href="<?php echo BASE_URL; ?>/install.php" class="text-white fw-bold">Setup Wizard</a>.
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger small mb-4">
                <i class="bi bi-shield-x me-1"></i> <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/admin/login.php">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label text-light small fw-bold">Admin Username or Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control bg-dark border-secondary text-white" placeholder="admin" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-light small fw-bold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control bg-dark border-secondary text-white" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-cinema w-100 py-2 mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Authenticate & Enter
            </button>

            <div class="text-center">
                <a href="<?php echo BASE_URL; ?>/index.php" class="text-secondary small text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Return to Live Website
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
