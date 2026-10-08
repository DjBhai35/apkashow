<?php
/**
 * ApkaShow - Homepage
 * Production-ready dynamic homepage featuring banners, categories, and curated movie collections
 * Domain: apkashow.com
 */
require_once __DIR__ . '/includes/functions.php';

$db = getDB();

// 1. Fetch Active Featured Banners (Admin Configurable)
$bannerStmt = $db->query("SELECT b.*, m.slug as movie_slug, m.genre as movie_genre, m.rating as movie_rating 
                          FROM `featured_banners` b 
                          LEFT JOIN `movies` m ON b.movie_id = m.id 
                          WHERE b.status = 1 
                          ORDER BY b.sort_order ASC, b.id DESC");
$featuredBanners = $bannerStmt->fetchAll();

// 2. Fetch Latest Movies
$latestStmt = $db->query("SELECT * FROM `movies` WHERE `status` = 1 ORDER BY `id` DESC LIMIT 6");
$latestMovies = $latestStmt->fetchAll();

// 3. Fetch Popular Movies
$popularStmt = $db->query("SELECT * FROM `movies` WHERE `status` = 1 AND `is_popular` = 1 ORDER BY `views_count` DESC, `rating` DESC LIMIT 6");
$popularMovies = $popularStmt->fetchAll();

// 4. Fetch Motivational & Mindset Movies
$mindsetStmt = $db->query("SELECT m.* FROM `movies` m 
                           JOIN `categories` c ON m.category_id = c.id 
                           WHERE m.status = 1 AND c.slug IN ('mindset', 'motivational') 
                           ORDER BY m.rating DESC LIMIT 6");
$mindsetMovies = $mindsetStmt->fetchAll();

// 5. Fetch Business, Forex & Wealth Movies
$businessStmt = $db->query("SELECT m.* FROM `movies` m 
                            JOIN `categories` c ON m.category_id = c.id 
                            WHERE m.status = 1 AND c.slug IN ('business', 'forex', 'millionaire', 'billionaire') 
                            ORDER BY m.id DESC LIMIT 6");
$businessMovies = $businessStmt->fetchAll();

// 6. Fetch Category Pills for quick filtering
$categoriesStmt = $db->query("SELECT * FROM `categories` WHERE `status` = 1 ORDER BY `display_order` ASC, `name` ASC");
$allCategories = $categoriesStmt->fetchAll();

// Page SEO Meta
$pageTitle = get_setting('meta_title_default', 'ApkaShow | Watch Premium Movies, Billionaire Mindset & Cinema Hub');
$pageDescription = get_setting('meta_description_default', 'Stream high-definition cinema, motivational billionaire stories, Wall Street dramas, and forex mastery on ApkaShow.');
$pageCanonical = canonical_url_for('/');

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-3">

    <!-- Hero Featured Banners Carousel -->
    <?php if (!empty($featuredBanners)): ?>
        <section class="hero-carousel-section mb-5">
            <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
                <!-- Indicators -->
                <div class="carousel-indicators mb-3">
                    <?php foreach ($featuredBanners as $index => $banner): ?>
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo ($index === 0) ? 'active' : ''; ?>" aria-current="<?php echo ($index === 0) ? 'true' : 'false'; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>

                <div class="carousel-inner rounded-4 shadow-lg overflow-hidden">
                    <?php foreach ($featuredBanners as $index => $banner): ?>
                        <?php 
                            $bannerImg = resolve_image_url($banner['banner_image'], 'banner');
                            $targetUrl = !empty($banner['target_url']) ? $banner['target_url'] : (!empty($banner['movie_slug']) ? 'movie.php?slug=' . urlencode($banner['movie_slug']) : '#');
                        ?>
                        <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
                            <div class="hero-slide-item" style="background-image: url('<?php echo e($bannerImg); ?>');">
                                <div class="hero-overlay"></div>
                                <div class="hero-content">
                                    <?php if (!empty($banner['badge'])): ?>
                                        <div class="hero-badge">
                                            <i class="bi bi-stars"></i> <?php echo e($banner['badge']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <h1 class="hero-title"><?php echo e($banner['title']); ?></h1>
                                    <p class="hero-desc"><?php echo e($banner['subtitle']); ?></p>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a href="<?php echo e($targetUrl); ?>" class="btn btn-cinema px-4 py-2">
                                            <i class="bi bi-play-circle-fill fs-5"></i>
                                            <span><?php echo e($banner['button_text'] ?: 'Watch Now'); ?></span>
                                        </a>
                                        <a href="<?php echo e(get_whatsapp_url()); ?>" target="_blank" class="btn btn-cinema-outline px-4 py-2">
                                            <i class="bi bi-whatsapp text-success fs-5"></i>
                                            <span>WhatsApp Concierge</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Carousel Controls -->
                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon p-3 rounded-circle bg-dark bg-opacity-50" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon p-3 rounded-circle bg-dark bg-opacity-50" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </section>
    <?php endif; ?>

    <!-- Category Pills Quick Filter Bar -->
    <section class="mb-5 overflow-auto pb-2">
        <div class="d-flex align-items-center gap-2 flex-nowrap">
            <span class="text-white-50 small fw-bold text-uppercase letter-spacing-1 me-2 text-nowrap">Explore:</span>
            <?php foreach ($allCategories as $cat): ?>
                <a href="<?php echo BASE_URL; ?>/category.php?slug=<?php echo urlencode($cat['slug']); ?>" class="category-pill">
                    <i class="bi <?php echo e($cat['icon'] ?: 'bi-film'); ?>"></i>
                    <span><?php echo e($cat['name']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Section 1: Latest Cinema Releases -->
    <section class="mb-5">
        <div class="section-header">
            <div>
                <h2 class="section-title mb-1">Latest Movies</h2>
                <small class="text-muted">Fresh additions to the ApkaShow archive</small>
            </div>
            <a href="<?php echo BASE_URL; ?>/category.php?slug=all" class="text-secondary text-hover-light small fw-bold text-decoration-none">
                View All <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-3 g-md-4">
            <?php 
            if (!empty($latestMovies)) {
                foreach ($latestMovies as $movie) {
                    include __DIR__ . '/includes/movie_card.php';
                }
            } else {
                echo '<div class="col-12 text-muted py-4">No movies uploaded yet.</div>';
            }
            ?>
        </div>
    </section>

    <!-- Section 2: Popular & Trending Movies -->
    <section class="mb-5">
        <div class="section-header">
            <div>
                <h2 class="section-title mb-1">Popular & Trending</h2>
                <small class="text-muted">Most streamed and critically acclaimed masterworks on ApkaShow</small>
            </div>
            <a href="<?php echo BASE_URL; ?>/category.php?slug=action" class="text-secondary text-hover-light small fw-bold text-decoration-none">
                See More <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-3 g-md-4">
            <?php 
            if (!empty($popularMovies)) {
                foreach ($popularMovies as $movie) {
                    include __DIR__ . '/includes/movie_card.php';
                }
            }
            ?>
        </div>
    </section>

    <!-- Section 3: Motivational & Mindset Masterpieces -->
    <section class="mb-5">
        <div class="section-header">
            <div>
                <h2 class="section-title mb-1">Motivational & Mindset</h2>
                <small class="text-muted">High-performance psychology, stoicism, and relentless drive</small>
            </div>
            <a href="<?php echo BASE_URL; ?>/category.php?slug=mindset" class="text-secondary text-hover-light small fw-bold text-decoration-none">
                Explore Hub <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-3 g-md-4">
            <?php 
            if (!empty($mindsetMovies)) {
                foreach ($mindsetMovies as $movie) {
                    include __DIR__ . '/includes/movie_card.php';
                }
            }
            ?>
        </div>
    </section>

    <!-- Section 4: Business, Forex & Billionaire Empire -->
    <section class="mb-5">
        <div class="section-header">
            <div>
                <h2 class="section-title mb-1">Business, Forex & Wealth Cinema</h2>
                <small class="text-muted">Boardroom strategies, financial empires, and currency titans</small>
            </div>
            <a href="<?php echo BASE_URL; ?>/category.php?slug=business" class="text-secondary text-hover-light small fw-bold text-decoration-none">
                Explore Wealth <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-3 g-md-4">
            <?php 
            if (!empty($businessMovies)) {
                foreach ($businessMovies as $movie) {
                    include __DIR__ . '/includes/movie_card.php';
                }
            }
            ?>
        </div>
    </section>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
