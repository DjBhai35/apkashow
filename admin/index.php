<?php
/**
 * CinemaVault - Admin Control Center Dashboard
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin_auth();

$db = getDB();

// Quick Aggregates
$totalMovies = (int)$db->query("SELECT COUNT(*) FROM movies")->fetchColumn();
$publishedMovies = (int)$db->query("SELECT COUNT(*) FROM movies WHERE status = 1")->fetchColumn();
$totalCategories = (int)$db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalBanners = (int)$db->query("SELECT COUNT(*) FROM featured_banners WHERE status = 1")->fetchColumn();
$totalViews = (int)$db->query("SELECT COALESCE(SUM(views_count), 0) FROM movies")->fetchColumn();
$totalMessages = (int)$db->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();

// Recent Inquiries
$recentMessagesStmt = $db->query("SELECT * FROM contact_messages ORDER BY id DESC LIMIT 5");
$recentMessages = $recentMessagesStmt->fetchAll();

// Top Streamed Movies
$topMoviesStmt = $db->query("SELECT m.*, c.name as category_name FROM movies m LEFT JOIN categories c ON m.category_id = c.id ORDER BY m.views_count DESC LIMIT 5");
$topMovies = $topMoviesStmt->fetchAll();

$adminTitle = "Dashboard - CinemaVault Studio";
$pageHeading = "Executive Overview";

require_once __DIR__ . '/header.php';
?>

<!-- Statistics Overview Cards -->
<div class="row g-4 mb-5">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase">Total Catalog</span>
                <span class="badge bg-danger bg-opacity-25 text-danger fs-6"><i class="bi bi-film"></i></span>
            </div>
            <h2 class="fw-bold text-white mb-1"><?php echo number_format($totalMovies); ?></h2>
            <div class="text-success small fw-semibold">
                <i class="bi bi-check-circle me-1"></i> <?php echo number_format($publishedMovies); ?> Published Active
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase">Total Video Streams</span>
                <span class="badge bg-warning bg-opacity-25 text-warning fs-6"><i class="bi bi-eye"></i></span>
            </div>
            <h2 class="fw-bold text-white mb-1"><?php echo format_views($totalViews); ?></h2>
            <div class="text-secondary small">
                High-bitrate audience engagement
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase">Dynamic Categories</span>
                <span class="badge bg-info bg-opacity-25 text-info fs-6"><i class="bi bi-grid-3x3-gap"></i></span>
            </div>
            <h2 class="fw-bold text-white mb-1"><?php echo number_format($totalCategories); ?></h2>
            <div class="text-info small fw-semibold">
                Billionaire, Forex, Mindset & More
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase">Hero Banners</span>
                <span class="badge bg-primary bg-opacity-25 text-primary fs-6"><i class="bi bi-images"></i></span>
            </div>
            <h2 class="fw-bold text-white mb-1"><?php echo number_format($totalBanners); ?></h2>
            <div class="text-secondary small">
                Active carousel spotlights
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Shortcuts -->
<div class="glass-card p-4 mb-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h5 class="fw-bold text-white mb-1">Quick Studio Actions</h5>
            <small class="text-muted">Direct links to primary administrative workflows</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo BASE_URL; ?>/admin/movie_edit.php" class="btn btn-cinema btn-sm px-3">
                <i class="bi bi-plus-lg me-1"></i> Add New Movie
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/categories.php" class="btn btn-cinema-outline btn-sm px-3">
                <i class="bi bi-folder-plus me-1"></i> Manage Categories
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/banners.php" class="btn btn-cinema-outline btn-sm px-3">
                <i class="bi bi-card-image me-1"></i> Add Hero Banner
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/settings.php" class="btn btn-cinema-outline btn-sm px-3">
                <i class="bi bi-whatsapp text-success me-1"></i> Change WhatsApp Number
            </a>
        </div>
    </div>
</div>

<!-- Top Movies & Inquiries Split View -->
<div class="row g-4">
    <!-- Left: Top Viewed Movies -->
    <div class="col-lg-7">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-white mb-0">Most Viewed Movies</h5>
                <a href="<?php echo BASE_URL; ?>/admin/movies.php" class="small text-danger text-decoration-none">View All Catalog &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-dark-custom align-middle mb-0">
                    <thead>
                        <tr class="text-muted small">
                            <th>Poster</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Views</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topMovies as $m): ?>
                            <tr>
                                <td style="width: 50px;">
                                    <img src="<?php echo e(resolve_image_url($m['poster'])); ?>" class="rounded" style="width: 36px; height: 50px; object-fit: cover;">
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/movie.php?slug=<?php echo urlencode($m['slug']); ?>" target="_blank" class="text-light fw-bold text-decoration-none d-block text-truncate" style="max-width: 200px;">
                                        <?php echo e($m['title']); ?>
                                    </a>
                                    <small class="text-muted"><?php echo e($m['release_year']); ?> &bull; <?php echo e($m['duration']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-dark border border-secondary text-secondary"><?php echo e($m['category_name'] ?? 'General'); ?></span>
                                </td>
                                <td>
                                    <span class="text-warning fw-semibold"><i class="bi bi-eye me-1"></i> <?php echo format_views($m['views_count']); ?></span>
                                </td>
                                <td class="text-end">
                                    <a href="<?php echo BASE_URL; ?>/admin/movie_edit.php?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-light py-1 px-2" title="Edit Movie">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right: Recent Inquiries -->
    <div class="col-lg-5">
        <div class="glass-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-white mb-0">User Messages</h5>
                <a href="<?php echo BASE_URL; ?>/admin/messages.php" class="small text-danger text-decoration-none">All Messages &rarr;</a>
            </div>
            <?php if (!empty($recentMessages)): ?>
                <div class="list-group list-group-flush bg-transparent">
                    <?php foreach ($recentMessages as $msg): ?>
                        <div class="list-group-item bg-transparent text-light border-secondary border-opacity-25 px-0 py-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="fw-bold text-white"><?php echo e($msg['name']); ?></span>
                                <small class="text-muted"><?php echo date('M d, H:i', strtotime($msg['created_at'])); ?></small>
                            </div>
                            <div class="small text-info mb-1"><?php echo e($msg['subject'] ?: 'Website Inquiry'); ?></div>
                            <p class="text-muted small mb-0 text-truncate"><?php echo e($msg['message']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                    No messages received yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
