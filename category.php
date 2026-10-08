<?php
/**
 * ApkaShow - Dynamic Category Page
 * Supports pagination, SEO titles, descriptions, and grid filtering
 * Domain: apkashow.com
 */
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$slug = trim($_GET['slug'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$category = null;
$whereClause = "WHERE m.status = 1";
$params = [];

if (!empty($slug) && $slug !== 'all') {
    $catStmt = $db->prepare("SELECT * FROM `categories` WHERE `slug` = ? AND `status` = 1 LIMIT 1");
    $catStmt->execute([$slug]);
    $category = $catStmt->fetch();

    if (!$category) {
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }
    $whereClause .= " AND m.category_id = ?";
    $params[] = $category['id'];
}

// Count total movies for pagination
$countSql = "SELECT COUNT(*) FROM `movies` m $whereClause";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$totalMovies = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalMovies / $perPage);

// Fetch Paginated Movies
$moviesSql = "SELECT m.*, c.name as category_name, c.slug as category_slug 
              FROM `movies` m 
              LEFT JOIN `categories` c ON m.category_id = c.id 
              $whereClause 
              ORDER BY m.id DESC 
              LIMIT $perPage OFFSET $offset";
$movieStmt = $db->prepare($moviesSql);
$movieStmt->execute($params);
$movies = $movieStmt->fetchAll();

// Dynamic SEO Setup
if ($category) {
    $catName = $category['name'];
    $catDisplayTitle = (stripos($catName, 'Movies') !== false || stripos($catName, 'Films') !== false || stripos($catName, 'Cinema') !== false) ? $catName : ($catName . ' Movies');
    $pageTitle = !empty($category['meta_title']) ? $category['meta_title'] : ($catDisplayTitle . ' | Watch Online HD | ApkaShow');
    $pageDescription = !empty($category['meta_description']) ? $category['meta_description'] : ('Explore the best ' . $catDisplayTitle . ', complete cast info, synopsis, and legal streaming links on ApkaShow.');
    $catDesc = $category['description'];
    $catIcon = $category['icon'] ?: 'bi-film';
} else {
    $pageTitle = 'All Movies Archive | ApkaShow';
    $pageDescription = 'Browse the complete cinematic library of motivational, billionaire, action, thriller, and drama films on ApkaShow.';
    $catName = 'All Movies';
    $catDisplayTitle = 'All Movies';
    $catDesc = 'Browse the entire ApkaShow collection across all genres and languages.';
    $catIcon = 'bi-collection-play';
}

$pageCanonical = canonical_url_for('/category/' . urlencode($slug ?: 'all'));

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">

    <!-- Category Header Breadcrumb & Info Banner -->
    <div class="glass-card p-4 p-md-5 mb-5 position-relative overflow-hidden">
        <div class="position-relative z-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-2 text-uppercase letter-spacing-1">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php" class="text-secondary text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/category.php?slug=all" class="text-secondary text-decoration-none">Categories</a></li>
                    <li class="breadcrumb-item active text-danger" aria-current="page"><?php echo e($catName); ?></li>
                </ol>
            </nav>

            <div class="d-flex align-items-center gap-3">
                <div class="fs-1 text-danger">
                    <i class="bi <?php echo e($catIcon); ?>"></i>
                </div>
                <div>
                    <h1 class="display-6 fw-bold text-white mb-1"><?php echo e($catDisplayTitle); ?></h1>
                    <?php if (!empty($catDesc)): ?>
                        <p class="text-muted mb-0 lead fs-6"><?php echo e($catDesc); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Movie Grid -->
    <div class="row g-3 g-md-4 mb-5">
        <?php if (!empty($movies)): ?>
            <?php foreach ($movies as $movie): ?>
                <?php include __DIR__ . '/includes/movie_card.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 py-5 text-center">
                <div class="glass-card p-5 mx-auto" style="max-width: 500px;">
                    <i class="bi bi-film fs-1 text-muted mb-3 d-block"></i>
                    <h4 class="text-white fw-bold">No Movies In This Category Yet</h4>
                    <p class="text-secondary small mb-4">We are frequently curating fresh high-definition titles. Check back soon or explore other genres.</p>
                    <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-cinema px-4">Browse All Movies</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="d-flex justify-content-center">
            <ul class="pagination pagination-dark gap-2">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link rounded bg-dark border-secondary text-light px-3" href="?slug=<?php echo urlencode($slug); ?>&page=<?php echo $page - 1; ?>">
                            <i class="bi bi-chevron-left me-1"></i> Prev
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                        <a class="page-link rounded <?php echo ($i === $page) ? 'bg-danger border-danger text-white' : 'bg-dark border-secondary text-light'; ?> px-3" href="?slug=<?php echo urlencode($slug); ?>&page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link rounded bg-dark border-secondary text-light px-3" href="?slug=<?php echo urlencode($slug); ?>&page=<?php echo $page + 1; ?>">
                            Next <i class="bi bi-chevron-right ms-1"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
