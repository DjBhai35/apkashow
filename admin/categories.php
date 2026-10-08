<?php
/**
 * ApkaShow - Admin Dynamic Categories Manager
 * Add, edit, delete categories, change slugs, customize icons and SEO metadata
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin_auth();

$db = getDB();

$editId = (int)($_GET['edit'] ?? 0);
$editCategory = null;

// Handle Delete Category
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $delId = (int)($_GET['id'] ?? 0);
    if ($delId > 0 && verify_csrf_token($_GET['token'] ?? '')) {
        $delStmt = $db->prepare("DELETE FROM `categories` WHERE `id` = ?");
        $delStmt->execute([$delId]);
        set_flash('success', 'Category deleted successfully.');
    }
    header("Location: " . BASE_URL . "/admin/categories.php");
    exit();
}

// Fetch Editing Category
if ($editId > 0) {
    $stmt = $db->prepare("SELECT * FROM `categories` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$editId]);
    $editCategory = $stmt->fetch();
}

$errors = [];

// Handle Form Submission (Create or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid. Please refresh.';
    } else {
        $catId = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-film');
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        if (empty($slug) && !empty($name)) {
            $slug = slugify($name);
        } else {
            $slug = slugify($slug);
        }

        if (empty($name)) {
            $errors[] = 'Category name is required.';
        }

        // Verify duplicate slug
        $chkStmt = $db->prepare("SELECT id FROM `categories` WHERE `slug` = ? AND `id` != ? LIMIT 1");
        $chkStmt->execute([$slug, $catId]);
        if ($chkStmt->fetch()) {
            $slug .= '-' . rand(10, 99);
        }

        if (empty($errors)) {
            try {
                if ($catId > 0) {
                    // Update
                    $upStmt = $db->prepare("UPDATE `categories` SET `name` = ?, `slug` = ?, `description` = ?, `icon` = ?, `meta_title` = ?, `meta_description` = ?, `display_order` = ?, `status` = ? WHERE `id` = ?");
                    $upStmt->execute([$name, $slug, $description, $icon, $metaTitle, $metaDescription, $displayOrder, $status, $catId]);
                    set_flash('success', "Category '{$name}' updated successfully.");
                } else {
                    // Insert
                    $inStmt = $db->prepare("INSERT INTO `categories` (`name`, `slug`, `description`, `icon`, `meta_title`, `meta_description`, `display_order`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $inStmt->execute([$name, $slug, $description, $icon, $metaTitle, $metaDescription, $displayOrder, $status]);
                    set_flash('success', "Category '{$name}' created successfully.");
                }
                header("Location: " . BASE_URL . "/admin/categories.php");
                exit();
            } catch (Exception $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Fetch all categories with movies count
$catsStmt = $db->query("SELECT c.*, COUNT(m.id) as movies_count 
                        FROM `categories` c 
                        LEFT JOIN `movies` m ON c.id = m.category_id 
                        GROUP BY c.id 
                        ORDER BY c.display_order ASC, c.name ASC");
$allCategories = $catsStmt->fetchAll();

$adminTitle = "Categories Manager - ApkaShow Studio";
$pageHeading = "Dynamic Category Management";

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
    <!-- Left Column: Add / Edit Category Form -->
    <div class="col-lg-4">
        <div class="glass-card p-4">
            <h5 class="fw-bold text-white mb-3">
                <?php echo $editCategory ? ('Edit: ' . e($editCategory['name'])) : 'Add New Category'; ?>
            </h5>

            <form method="POST" action="<?php echo BASE_URL; ?>/admin/categories.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="category_id" value="<?php echo $editCategory['id'] ?? 0; ?>">

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Category Name *</label>
                    <input type="text" name="name" class="form-control bg-dark border-secondary text-white" value="<?php echo e($editCategory['name'] ?? ''); ?>" placeholder="e.g. Billionaire, Forex, Action" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">URL Slug (leave blank to auto-generate)</label>
                    <input type="text" name="slug" class="form-control bg-dark border-secondary text-white" value="<?php echo e($editCategory['slug'] ?? ''); ?>" placeholder="e.g. billionaire">
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Icon (Bootstrap Icon Class)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi <?php echo e($editCategory['icon'] ?? 'bi-film'); ?>"></i></span>
                        <input type="text" name="icon" class="form-control bg-dark border-secondary text-white" value="<?php echo e($editCategory['icon'] ?? 'bi-film'); ?>" placeholder="bi-gem, bi-currency-dollar">
                    </div>
                    <small class="text-muted">Browse icons at <a href="https://icons.getbootstrap.com" target="_blank" class="text-danger">icons.getbootstrap.com</a></small>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Description / Overview</label>
                    <textarea name="description" class="form-control bg-dark border-secondary text-white" rows="3" placeholder="Category summary..."><?php echo e($editCategory['description'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">SEO Meta Title</label>
                    <input type="text" name="meta_title" class="form-control bg-dark border-secondary text-white" value="<?php echo e($editCategory['meta_title'] ?? ''); ?>" placeholder="Billionaire Movies | ApkaShow">
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">SEO Meta Description</label>
                    <textarea name="meta_description" class="form-control bg-dark border-secondary text-white" rows="2" placeholder="Search engine description..."><?php echo e($editCategory['meta_description'] ?? ''); ?></textarea>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label text-light small fw-bold">Display Order</label>
                        <input type="number" name="display_order" class="form-control bg-dark border-secondary text-white" value="<?php echo e($editCategory['display_order'] ?? 0); ?>">
                    </div>
                    <div class="col-6 d-flex align-items-end pb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="catStatusSwitch" <?php echo (!isset($editCategory['status']) || $editCategory['status'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label text-light small" for="catStatusSwitch">Active</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-cinema w-100 py-2 mb-2">
                    <i class="bi bi-check2-circle me-1"></i> <?php echo $editCategory ? 'Update Category' : 'Save Category'; ?>
                </button>

                <?php if ($editCategory): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/categories.php" class="btn btn-outline-secondary w-100">Cancel Editing</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Right Column: Categories List Table -->
    <div class="col-lg-8">
        <div class="glass-card p-4">
            <h5 class="fw-bold text-white mb-3">All Active & System Categories</h5>
            <div class="table-responsive">
                <table class="table table-dark table-dark-custom align-middle mb-0">
                    <thead>
                        <tr class="text-muted small">
                            <th>Icon</th>
                            <th>Name & Slug</th>
                            <th>Movies</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allCategories as $cat): ?>
                            <tr>
                                <td>
                                    <div class="p-2 rounded bg-dark border border-secondary text-center text-danger fs-5" style="width: 42px; height: 42px; line-height: 1;">
                                        <i class="bi <?php echo e($cat['icon'] ?: 'bi-film'); ?>"></i>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-white"><?php echo e($cat['name']); ?></div>
                                    <small class="text-muted">Slug: <code><?php echo e($cat['slug']); ?></code></small>
                                </td>
                                <td>
                                    <span class="badge bg-danger bg-opacity-25 text-danger"><?php echo (int)$cat['movies_count']; ?> titles</span>
                                </td>
                                <td>
                                    <span class="text-muted small"><?php echo (int)$cat['display_order']; ?></span>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($cat['status'] == 1) ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo ($cat['status'] == 1) ? 'Active' : 'Disabled'; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="<?php echo BASE_URL; ?>/category.php?slug=<?php echo urlencode($cat['slug']); ?>" target="_blank" class="btn btn-sm btn-outline-info" title="View on Site">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/admin/categories.php?edit=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit Category">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/admin/categories.php?action=delete&id=<?php echo $cat['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deleting category will detach associated movies. Proceed?');" title="Delete">
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
