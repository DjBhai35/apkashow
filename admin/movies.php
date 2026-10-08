<?php
/**
 * CinemaVault - Admin Movie Manager (List, Search, Status Toggle, Delete)
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin_auth();

$db = getDB();

// Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $deleteId = (int)($_GET['id'] ?? 0);
    if ($deleteId > 0 && verify_csrf_token($_GET['token'] ?? '')) {
        $delStmt = $db->prepare("DELETE FROM `movies` WHERE `id` = ?");
        $delStmt->execute([$deleteId]);
        set_flash('success', 'Movie deleted successfully from catalog.');
    } else {
        set_flash('danger', 'Invalid security token or movie ID.');
    }
    header("Location: " . BASE_URL . "/admin/movies.php");
    exit();
}

// Handle Status Toggle (Publish/Unpublish)
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status') {
    $toggleId = (int)($_GET['id'] ?? 0);
    if ($toggleId > 0 && verify_csrf_token($_GET['token'] ?? '')) {
        $togStmt = $db->prepare("UPDATE `movies` SET `status` = (CASE WHEN `status` = 1 THEN 0 ELSE 1 END) WHERE `id` = ?");
        $togStmt->execute([$toggleId]);
        set_flash('success', 'Movie status updated successfully.');
    }
    header("Location: " . BASE_URL . "/admin/movies.php");
    exit();
}

// Search and Filter
$q = trim($_GET['q'] ?? '');
$filterCat = (int)($_GET['cat'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = "WHERE 1=1";
$params = [];

if (!empty($q)) {
    $where .= " AND (m.title LIKE ? OR m.tags LIKE ? OR m.genre LIKE ?)";
    $term = '%' . $q . '%';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($filterCat > 0) {
    $where .= " AND m.category_id = ?";
    $params[] = $filterCat;
}

// Count Total
$countStmt = $db->prepare("SELECT COUNT(*) FROM `movies` m $where");
$countStmt->execute($params);
$totalMovies = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalMovies / $perPage);

// Fetch List
$listSql = "SELECT m.*, c.name as category_name 
            FROM `movies` m 
            LEFT JOIN `categories` c ON m.category_id = c.id 
            $where 
            ORDER BY m.id DESC 
            LIMIT $perPage OFFSET $offset";
$listStmt = $db->prepare($listSql);
$listStmt->execute($params);
$movies = $listStmt->fetchAll();

// All Categories for dropdown filter
$allCats = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

$adminTitle = "Movies Catalog Manager - CinemaVault";
$pageHeading = "Movies Catalog Management";

require_once __DIR__ . '/header.php';
?>

<div class="glass-card p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <!-- Search & Filter Controls -->
        <form method="GET" action="<?php echo BASE_URL; ?>/admin/movies.php" class="d-flex flex-wrap gap-2 flex-grow-1">
            <input type="text" name="q" class="form-control bg-dark border-secondary text-white" placeholder="Search by title, tags..." value="<?php echo e($q); ?>" style="max-width: 280px;">
            <select name="cat" class="form-select bg-dark border-secondary text-white" style="max-width: 200px;">
                <option value="0">All Categories</option>
                <?php foreach ($allCats as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo ($filterCat == $c['id']) ? 'selected' : ''; ?>>
                        <?php echo e($c['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary">Filter</button>
            <?php if (!empty($q) || $filterCat > 0): ?>
                <a href="<?php echo BASE_URL; ?>/admin/movies.php" class="btn btn-outline-secondary">Reset</a>
            <?php endif; ?>
        </form>

        <!-- Add Movie Button -->
        <div>
            <a href="<?php echo BASE_URL; ?>/admin/movie_edit.php" class="btn btn-cinema text-nowrap">
                <i class="bi bi-plus-circle-fill me-1"></i> Add New Movie
            </a>
        </div>
    </div>
</div>

<div class="glass-card p-4">
    <div class="table-responsive">
        <table class="table table-dark table-dark-custom align-middle mb-0">
            <thead>
                <tr class="text-muted small">
                    <th style="width: 50px;">Poster</th>
                    <th>Movie Details</th>
                    <th>Category</th>
                    <th>Specs</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($movies)): ?>
                    <?php foreach ($movies as $m): ?>
                        <tr>
                            <td>
                                <img src="<?php echo e(resolve_image_url($m['poster'])); ?>" class="rounded" style="width: 44px; height: 60px; object-fit: cover;">
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/movie.php?slug=<?php echo urlencode($m['slug']); ?>" target="_blank" class="fw-bold text-white text-decoration-none">
                                    <?php echo e($m['title']); ?>
                                </a>
                                <div class="text-muted small">
                                    Slug: <code><?php echo e($m['slug']); ?></code>
                                </div>
                                <div class="small text-secondary">
                                    <?php if ($m['is_featured']): ?><span class="badge bg-danger me-1">Featured</span><?php endif; ?>
                                    <?php if ($m['is_popular']): ?><span class="badge bg-warning text-dark me-1">Popular</span><?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-dark border border-secondary text-light"><?php echo e($m['category_name'] ?? 'Unassigned'); ?></span>
                            </td>
                            <td>
                                <div class="small text-light"><?php echo e($m['release_year']); ?> &bull; <?php echo e($m['duration']); ?></div>
                                <div class="small text-warning"><i class="bi bi-star-fill"></i> <?php echo e($m['rating']); ?></div>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/admin/movies.php?action=toggle_status&id=<?php echo $m['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm <?php echo ($m['status'] == 1) ? 'btn-success' : 'btn-secondary'; ?> py-0 px-2 rounded-pill small" title="Toggle Publish Status">
                                    <?php echo ($m['status'] == 1) ? 'Published' : 'Draft'; ?>
                                </a>
                            </td>
                            <td>
                                <span class="text-light small"><?php echo format_views($m['views_count']); ?></span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="<?php echo BASE_URL; ?>/movie.php?slug=<?php echo urlencode($m['slug']); ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Preview on Website">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/admin/movie_edit.php?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit Movie">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/admin/movies.php?action=delete&id=<?php echo $m['id']; ?>&token=<?php echo csrf_token(); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to permanently delete this movie?');" title="Delete Movie">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-film fs-2 d-block mb-2 text-secondary"></i>
                            No movies found matching criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <small class="text-muted">Showing page <?php echo $page; ?> of <?php echo $totalPages; ?> (Total <?php echo $totalMovies; ?> movies)</small>
            <ul class="pagination pagination-sm mb-0 gap-1">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                        <a class="page-link rounded <?php echo ($i === $page) ? 'bg-danger border-danger text-white' : 'bg-dark border-secondary text-light'; ?>" href="?q=<?php echo urlencode($q); ?>&cat=<?php echo $filterCat; ?>&page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
