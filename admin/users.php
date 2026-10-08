<?php
/**
 * ApkaShow - Admin Accounts & Users Manager
 * Allows managing staff users, changing passwords with Bcrypt hashing
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin_auth();

$db = getDB();
$currentAdmin = get_current_admin();
$isForced = isset($_GET['force']) && $_GET['force'] == 1;

$errors = [];

// Handle Password Change for Current Admin or Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'CSRF validation failed.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'change_password') {
            $currentPass = trim($_POST['current_password'] ?? '');
            $newPass = trim($_POST['new_password'] ?? '');
            $confirmPass = trim($_POST['confirm_password'] ?? '');

            if (empty($currentPass) || empty($newPass)) {
                $errors[] = 'Current and new password are required.';
            } elseif ($newPass !== $confirmPass) {
                $errors[] = 'New password and confirmation do not match.';
            } elseif (strlen($newPass) < 8) {
                $errors[] = 'Security standard: New password must be at least 8 characters in length.';
            } else {
                $uStmt = $db->prepare("SELECT * FROM `users` WHERE `id` = ? LIMIT 1");
                $uStmt->execute([$currentAdmin['id']]);
                $user = $uStmt->fetch();

                if ($user && password_verify($currentPass, $user['password'])) {
                    $newHash = password_hash($newPass, PASSWORD_BCRYPT);
                    $upStmt = $db->prepare("UPDATE `users` SET `password` = ?, `force_password_change` = 0 WHERE `id` = ?");
                    $upStmt->execute([$newHash, $currentAdmin['id']]);
                    
                    // Update current session
                    $_SESSION[ADMIN_SESSION_KEY]['force_password_change'] = 0;

                    set_flash('success', 'Your password was securely updated. Account is fully active.');
                    header("Location: " . BASE_URL . "/admin/index.php");
                    exit();
                } else {
                    $errors[] = 'Incorrect current password.';
                }
            }
        } elseif ($action === 'create_user') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $fullName = trim($_POST['full_name'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $role = $_POST['role'] === 'editor' ? 'editor' : 'admin';

            if (empty($username) || empty($email) || empty($password)) {
                $errors[] = 'Username, email and password are required.';
            } elseif (strlen($password) < 8) {
                $errors[] = 'Temporary password must be at least 8 characters.';
            } else {
                // Check duplicate
                $dup = $db->prepare("SELECT id FROM `users` WHERE `username` = ? OR `email` = ?");
                $dup->execute([$username, $email]);
                if ($dup->fetch()) {
                    $errors[] = 'Username or email already exists.';
                } else {
                    $passHash = password_hash($password, PASSWORD_BCRYPT);
                    $ins = $db->prepare("INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`, `status`, `force_password_change`) VALUES (?, ?, ?, ?, ?, 1, 1)");
                    $ins->execute([$username, $email, $passHash, $fullName, $role]);
                    set_flash('success', "New user '{$username}' added successfully. They will be prompted to set a new password on first login.");
                    header("Location: " . BASE_URL . "/admin/users.php");
                    exit();
                }
            }
        }
    }
}

// Fetch all staff users
$users = $db->query("SELECT id, username, email, full_name, role, status, created_at FROM `users` ORDER BY id ASC")->fetchAll();

$adminTitle = "Admin Users - ApkaShow Studio";
$pageHeading = "Staff & Authentication Security";

require_once __DIR__ . '/header.php';
?>

<?php if ($isForced): ?>
    <div class="alert alert-warning bg-warning bg-opacity-15 border-warning text-warning mb-4">
        <i class="bi bi-shield-exclamation me-2 fs-5"></i>
        <strong>Action Required:</strong> You must change your temporary administrator password to a personal secure password (minimum 8 characters) before continuing.
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger mb-4">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?>
                <li><?php echo e($err); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left: Change Password Form -->
    <div class="col-lg-6">
        <div class="glass-card p-4 mb-4 border-primary border-opacity-25">
            <h5 class="fw-bold text-white mb-2"><i class="bi bi-key-fill text-warning me-2"></i>Change Your Password</h5>
            <p class="text-muted small mb-4">Updating password for logged-in user: <strong><?php echo e($currentAdmin['username']); ?></strong></p>

            <form method="POST" action="">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="change_password">

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Current Password</label>
                    <input type="password" name="current_password" class="form-control bg-dark border-secondary text-white" placeholder="••••••••" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">New Secure Password (min 8 chars)</label>
                    <input type="password" name="new_password" class="form-control bg-dark border-secondary text-white" placeholder="••••••••" required minlength="8">
                </div>

                <div class="mb-4">
                    <label class="form-label text-light small fw-bold">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control bg-dark border-secondary text-white" placeholder="••••••••" required minlength="8">
                </div>

                <button type="submit" class="btn btn-cinema w-100 py-2">
                    <i class="bi bi-shield-lock-fill me-1"></i> Update Password Now
                </button>
            </form>
        </div>

        <!-- Add Staff Account Form -->
        <div class="glass-card p-4">
            <h5 class="fw-bold text-white mb-2"><i class="bi bi-person-plus-fill text-info me-2"></i>Add Staff Member</h5>
            <p class="text-muted small mb-4">Grant administrative or editorial privileges to another team member.</p>

            <form method="POST" action="">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="create_user">

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Full Name</label>
                    <input type="text" name="full_name" class="form-control bg-dark border-secondary text-white" placeholder="Jane Miller" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Username</label>
                    <input type="text" name="username" class="form-control bg-dark border-secondary text-white" placeholder="janem" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Email Address</label>
                    <input type="email" name="email" class="form-control bg-dark border-secondary text-white" placeholder="jane@apkashow.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Temporary Password (min 8 chars)</label>
                    <input type="password" name="password" class="form-control bg-dark border-secondary text-white" placeholder="••••••••" required minlength="8">
                </div>

                <div class="mb-4">
                    <label class="form-label text-light small fw-bold">Role Assignment</label>
                    <select name="role" class="form-select bg-dark border-secondary text-white">
                        <option value="admin">Administrator (Full Access)</option>
                        <option value="editor">Editor (Content Only)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-cinema-outline w-100 py-2">
                    <i class="bi bi-person-check-fill me-1"></i> Create Staff Account
                </button>
            </form>
        </div>
    </div>

    <!-- Right: Users Table -->
    <div class="col-lg-6">
        <div class="glass-card p-4">
            <h5 class="fw-bold text-white mb-3">Active System Users</h5>

            <div class="table-responsive">
                <table class="table table-dark table-dark-custom align-middle mb-0">
                    <thead>
                        <tr class="text-muted small">
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-white"><?php echo e($u['full_name']); ?></div>
                                    <div class="text-muted small">@<?php echo e($u['username']); ?> &bull; <?php echo e($u['email']); ?></div>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($u['role'] === 'admin') ? 'bg-danger' : 'bg-info'; ?> text-uppercase">
                                        <?php echo e($u['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
