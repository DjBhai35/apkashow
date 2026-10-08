<?php
/**
 * CinemaVault - Admin Contact Messages / Inquiries Manager
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin_auth();

$db = getDB();

// Handle Delete Message
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $delId = (int)($_GET['id'] ?? 0);
    if ($delId > 0 && verify_csrf_token($_GET['token'] ?? '')) {
        $delStmt = $db->prepare("DELETE FROM `contact_messages` WHERE `id` = ?");
        $delStmt->execute([$delId]);
        set_flash('success', 'Inquiry deleted successfully.');
    }
    header("Location: " . BASE_URL . "/admin/messages.php");
    exit();
}

// Handle Mark as Read / Unread
if (isset($_GET['action']) && $_GET['action'] === 'toggle_read') {
    $msgId = (int)($_GET['id'] ?? 0);
    if ($msgId > 0 && verify_csrf_token($_GET['token'] ?? '')) {
        $togStmt = $db->prepare("UPDATE `contact_messages` SET `is_read` = (CASE WHEN `is_read` = 1 THEN 0 ELSE 1 END) WHERE `id` = ?");
        $togStmt->execute([$msgId]);
        set_flash('success', 'Message status updated.');
    }
    header("Location: " . BASE_URL . "/admin/messages.php");
    exit();
}

// Fetch all messages
$stmt = $db->query("SELECT * FROM `contact_messages` ORDER BY `id` DESC");
$messages = $stmt->fetchAll();

$adminTitle = "User Inquiries - CinemaVault Studio";
$pageHeading = "User Contact Messages & Inquiries";

require_once __DIR__ . '/header.php';
?>

<div class="glass-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-white mb-0">Received Contact Inquiries</h5>
        <span class="badge bg-danger bg-opacity-25 text-danger"><?php echo count($messages); ?> Total Messages</span>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-dark-custom align-middle mb-0">
            <thead>
                <tr class="text-muted small">
                    <th>Sender</th>
                    <th>Subject</th>
                    <th>Message Details</th>
                    <th>Received At</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($messages)): ?>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-white"><?php echo e($msg['name']); ?></div>
                                <a href="mailto:<?php echo e($msg['email']); ?>" class="text-info small text-decoration-none">
                                    <?php echo e($msg['email']); ?>
                                </a>
                            </td>
                            <td>
                                <span class="text-light fw-semibold small"><?php echo e($msg['subject'] ?: 'General'); ?></span>
                            </td>
                            <td style="max-width: 320px;">
                                <div class="text-light text-opacity-75 small lh-base"><?php echo nl2br(e($msg['message'])); ?></div>
                            </td>
                            <td>
                                <span class="text-muted small"><?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></span>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/admin/messages.php?action=toggle_read&id=<?php echo $msg['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm <?php echo ($msg['is_read'] == 1) ? 'btn-outline-secondary' : 'btn-success'; ?> py-0 px-2 rounded-pill small">
                                    <?php echo ($msg['is_read'] == 1) ? 'Read' : 'New'; ?>
                                </a>
                            </td>
                            <td class="text-end">
                                <a href="mailto:<?php echo e($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject']); ?>" class="btn btn-sm btn-outline-info" title="Reply via Email">
                                    <i class="bi bi-reply-fill"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/admin/messages.php?action=delete&id=<?php echo $msg['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this message permanently?');" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                            No contact inquiries found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
