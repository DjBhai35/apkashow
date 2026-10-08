<?php
/**
 * ApkaShow - Admin Featured Banners Manager
 * Add, edit, delete, reorder hero carousel slides
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin_auth();

$db = getDB();

$editId = (int)($_GET['edit'] ?? 0);
$editBanner = null;

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $delId = (int)($_GET['id'] ?? 0);
    if ($delId > 0 && verify_csrf_token($_GET['token'] ?? '')) {
        $delStmt = $db->prepare("DELETE FROM `featured_banners` WHERE `id` = ?");
        $delStmt->execute([$delId]);
        set_flash('success', 'Banner deleted successfully.');
    }
    header("Location: " . BASE_URL . "/admin/banners.php");
    exit();
}

if ($editId > 0) {
    $stmt = $db->prepare("SELECT * FROM `featured_banners` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$editId]);
    $editBanner = $stmt->fetch();
}

// Fetch all movies for linking banner directly
$allMovies = $db->query("SELECT id, title, slug FROM movies WHERE status = 1 ORDER BY title ASC")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'CSRF validation error.';
    } else {
        $bannerId = (int)($_POST['banner_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $badge = trim($_POST['badge'] ?? 'FEATURED PREMIERE');
        $movieId = !empty($_POST['movie_id']) ? (int)$_POST['movie_id'] : null;
        $targetUrl = trim($_POST['target_url'] ?? '');
        $buttonText = trim($_POST['button_text'] ?? 'Watch Now');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        // Image Handling
        $bannerImage = trim($_POST['banner_image_url'] ?? '');
        if (!empty($_FILES['banner_image_file']['name'])) {
            $upRes = upload_image_file($_FILES['banner_image_file'], 'banners');
            if ($upRes['success']) {
                $bannerImage = $upRes['url'];
            } else {
                $errors[] = 'Banner image upload failed: ' . $upRes['error'];
            }
        } elseif (empty($bannerImage) && $editBanner) {
            $bannerImage = $editBanner['banner_image'];
        }

        if (empty($title)) {
            $errors[] = 'Banner title is required.';
        }
        if (empty($bannerImage)) {
            $errors[] = 'Banner backdrop image is required (URL or file upload).';
        }

        if (empty($errors)) {
            try {
                if ($bannerId > 0) {
                    $upStmt = $db->prepare("UPDATE `featured_banners` SET `title` = ?, `subtitle` = ?, `badge` = ?, `banner_image` = ?, `movie_id` = ?, `target_url` = ?, `button_text` = ?, `sort_order` = ?, `status` = ? WHERE `id` = ?");
                    $upStmt->execute([$title, $subtitle, $badge, $bannerImage, $movieId, $targetUrl, $buttonText, $sortOrder, $status, $bannerId]);
                    set_flash('success', 'Banner slide updated successfully.');
                } else {
                    $inStmt = $db->prepare("INSERT INTO `featured_banners` (`title`, `subtitle`, `badge`, `banner_image`, `movie_id`, `target_url`, `button_text`, `sort_order`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $inStmt->execute([$title, $subtitle, $badge, $bannerImage, $movieId, $targetUrl, $buttonText, $sortOrder, $status]);
                    set_flash('success', 'New hero banner added successfully.');
                }
                header("Location: " . BASE_URL . "/admin/banners.php");
                exit();
            } catch (Exception $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Fetch all banners
$bannersStmt = $db->query("SELECT b.*, m.title as movie_title FROM `featured_banners` b LEFT JOIN `movies` m ON b.movie_id = m.id ORDER BY b.sort_order ASC, b.id DESC");
$banners = $bannersStmt->fetchAll();

$adminTitle = "Hero Banners Manager - ApkaShow Studio";
$pageHeading = "Featured Hero Banner Carousel";

require_once __DIR__ . '/header.php';
?>

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
    <!-- Form (Add / Edit) -->
    <div class="col-lg-5">
        <div class="glass-card p-4">
            <h5 class="fw-bold text-white mb-3">
                <?php echo $editBanner ? 'Edit Hero Banner' : 'Create Hero Banner Slide'; ?>
            </h5>

            <form method="POST" action="" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="banner_id" value="<?php echo $editBanner['id'] ?? 0; ?>">

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Hero Title *</label>
                    <input type="text" name="title" class="form-control bg-dark border-secondary text-white" value="<?php echo e($editBanner['title'] ?? ''); ?>" placeholder="THE WOLF OF WALL STREET" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Subtitle / Teaser Hook</label>
                    <textarea name="subtitle" class="form-control bg-dark border-secondary text-white" rows="2" placeholder="Greed. Power. Colossal Wealth."><?php echo e($editBanner['subtitle'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Top Pill Badge</label>
                    <input type="text" name="badge" class="form-control bg-dark border-secondary text-white" value="<?php echo e($editBanner['badge'] ?? 'FEATURED PREMIERE'); ?>" placeholder="EXCLUSIVE PREMIERE">
                </div>

                <!-- Image -->
                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Backdrop Image URL (1600x800 recommended)</label>
                    <input type="text" name="banner_image_url" class="form-control bg-dark border-secondary text-white mb-2" value="<?php echo e($editBanner['banner_image'] ?? ''); ?>" placeholder="https://images.unsplash.com/...">
                    <label class="form-label text-light small fw-bold">Or Upload Backdrop File</label>
                    <input type="file" name="banner_image_file" class="form-control bg-dark border-secondary text-white" accept="image/*">
                    <?php if (!empty($editBanner['banner_image'])): ?>
                        <div class="mt-2">
                            <img src="<?php echo e(resolve_image_url($editBanner['banner_image'], 'banner')); ?>" class="w-100 rounded shadow" style="height: 100px; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Link to Existing Movie (Optional)</label>
                    <select name="movie_id" class="form-select bg-dark border-secondary text-white">
                        <option value="">-- None (Or use custom target URL below) --</option>
                        <?php foreach ($allMovies as $m): ?>
                            <option value="<?php echo $m['id']; ?>" <?php echo (isset($editBanner['movie_id']) && $editBanner['movie_id'] == $m['id']) ? 'selected' : ''; ?>>
                                <?php echo e($m['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Custom Destination URL</label>
                    <input type="text" name="target_url" class="form-control bg-dark border-secondary text-white" value="<?php echo e($editBanner['target_url'] ?? ''); ?>" placeholder="movie.php?slug=the-wolf-of-wall-street">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label text-light small fw-bold">Button Text</label>
                        <input type="text" name="button_text" class="form-control bg-dark border-secondary text-white" value="<?php echo e($editBanner['button_text'] ?? 'Watch Now'); ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label text-light small fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control bg-dark border-secondary text-white" value="<?php echo e($editBanner['sort_order'] ?? 1); ?>">
                    </div>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="status" id="bannerStatus" <?php echo (!isset($editBanner['status']) || $editBanner['status'] == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label text-light" for="bannerStatus">Slide Active / Visible on Home</label>
                </div>

                <button type="submit" class="btn btn-cinema w-100 py-2 mb-2">
                    <i class="bi bi-save me-1"></i> <?php echo $editBanner ? 'Update Banner' : 'Create Banner'; ?>
                </button>

                <?php if ($editBanner): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/banners.php" class="btn btn-outline-secondary w-100">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Right List -->
    <div class="col-lg-7">
        <div class="glass-card p-4">
            <h5 class="fw-bold text-white mb-3">Configured Carousel Slides</h5>

            <div class="table-responsive">
                <table class="table table-dark table-dark-custom align-middle mb-0">
                    <thead>
                        <tr class="text-muted small">
                            <th>Preview</th>
                            <th>Slide Details</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($banners as $b): ?>
                            <tr>
                                <td style="width: 120px;">
                                    <img src="<?php echo e(resolve_image_url($b['banner_image'], 'banner')); ?>" class="rounded shadow" style="width: 110px; height: 60px; object-fit: cover;">
                                </td>
                                <td>
                                    <div class="fw-bold text-white"><?php echo e($b['title']); ?></div>
                                    <small class="text-danger d-block"><?php echo e($b['badge']); ?></small>
                                    <small class="text-muted"><?php echo e($b['movie_title'] ? 'Linked: ' . $b['movie_title'] : 'Custom Link'); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-dark border border-secondary text-light">#<?php echo (int)$b['sort_order']; ?></span>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($b['status'] == 1) ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo ($b['status'] == 1) ? 'Active' : 'Hidden'; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="<?php echo BASE_URL; ?>/admin/banners.php?edit=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/admin/banners.php?action=delete&id=<?php echo $b['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this banner slide?');" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
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
