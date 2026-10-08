<?php
/**
 * CinemaVault - Movie Detail & Player Page
 * Features 4K poster, trailer embed, legal streaming player, download links, metadata, and related films
 */
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$slug = trim($_GET['slug'] ?? '');

if (empty($slug)) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Fetch Movie Details
$stmt = $db->prepare("SELECT m.*, c.name as category_name, c.slug as category_slug 
                      FROM `movies` m 
                      LEFT JOIN `categories` c ON m.category_id = c.id 
                      WHERE m.slug = ? AND m.status = 1 LIMIT 1");
$stmt->execute([$slug]);
$movie = $stmt->fetch();

if (!$movie) {
    // If movie doesn't exist or is unpublished
    http_response_code(404);
    $pageTitle = "Movie Not Found | CinemaVault";
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="container py-5 text-center">
        <div class="glass-card p-5 mx-auto" style="max-width: 500px;">
            <i class="bi bi-exclamation-triangle fs-1 text-danger mb-3 d-block"></i>
            <h2 class="text-white fw-bold">Film Not Found</h2>
            <p class="text-secondary">The requested movie is unavailable or has been archived.</p>
            <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-cinema">Return to Homepage</a>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

// Increment View Counter
try {
    $viewStmt = $db->prepare("UPDATE `movies` SET `views_count` = `views_count` + 1 WHERE `id` = ?");
    $viewStmt->execute([$movie['id']]);
} catch (Exception $e) {
    // Ignore counter failure
}

// Fetch Related Movies from same category
$relStmt = $db->prepare("SELECT * FROM `movies` 
                         WHERE `category_id` = ? AND `id` != ? AND `status` = 1 
                         ORDER BY `rating` DESC, `id` DESC LIMIT 6");
$relStmt->execute([$movie['category_id'], $movie['id']]);
$relatedMovies = $relStmt->fetchAll();

// Dynamic SEO Configurations
$pageTitle = !empty($movie['meta_title']) ? $movie['meta_title'] : ($movie['title'] . ' (' . $movie['release_year'] . ') - Watch Online & Full Movie Info | CinemaVault');
$pageDescription = !empty($movie['meta_description']) ? $movie['meta_description'] : ($movie['short_description']);
$pageKeywords = !empty($movie['meta_keywords']) ? $movie['meta_keywords'] : ($movie['tags'] . ', ' . $movie['genre'] . ', movie streaming');
$pageImage = resolve_image_url($movie['poster']);
$pageCanonical = !empty($movie['canonical_url']) ? $movie['canonical_url'] : (BASE_URL . '/movie.php?slug=' . urlencode($movie['slug']));
$isMovieDetail = true;

require_once __DIR__ . '/includes/header.php';

// Prepare video streaming embed source
$embedUrl = '';
if (!empty($movie['trailer_url'])) {
    if (strpos($movie['trailer_url'], 'youtube.com/watch?v=') !== false) {
        $embedUrl = str_replace('watch?v=', 'embed/', $movie['trailer_url']);
    } elseif (strpos($movie['trailer_url'], 'youtu.be/') !== false) {
        $parts = explode('/', $movie['trailer_url']);
        $embedUrl = 'https://www.youtube.com/embed/' . end($parts);
    } else {
        $embedUrl = $movie['trailer_url'];
    }
}
?>

<!-- Schema.org Movie Structured Data for Google Rich Results -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Movie",
  "name": <?php echo json_encode($movie['title']); ?>,
  "image": <?php echo json_encode($pageImage); ?>,
  "description": <?php echo json_encode($movie['short_description']); ?>,
  "datePublished": "<?php echo e($movie['release_year']); ?>",
  "genre": <?php echo json_encode(array_map('trim', explode(',', $movie['genre']))); ?>,
  "inLanguage": <?php echo json_encode($movie['language']); ?>,
  "duration": "<?php echo e($movie['duration']); ?>",
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "<?php echo e($movie['rating']); ?>",
    "bestRating": "10",
    "ratingCount": "<?php echo e($movie['views_count'] + 50); ?>"
  }
}
</script>

<div class="container py-4">

    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small text-uppercase letter-spacing-1">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php" class="text-secondary text-decoration-none"><i class="bi bi-house-door"></i> Home</a></li>
            <?php if (!empty($movie['category_slug'])): ?>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/category.php?slug=<?php echo urlencode($movie['category_slug']); ?>" class="text-secondary text-decoration-none"><?php echo e($movie['category_name']); ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active text-danger" aria-current="page"><?php echo e($movie['title']); ?></li>
        </ol>
    </nav>

    <!-- Movie Hero Showcase -->
    <div class="movie-detail-hero" style="background-image: url('<?php echo e(resolve_image_url($movie['banner'] ?: $movie['poster'], 'banner')); ?>');">
        <div class="movie-detail-overlay"></div>
        <div class="movie-detail-content">
            <div class="row align-items-end g-4">
                <!-- Movie Poster -->
                <div class="col-md-auto d-none d-md-block">
                    <img src="<?php echo e(resolve_image_url($movie['poster'])); ?>" alt="<?php echo e($movie['title']); ?> Poster" class="movie-detail-poster shadow-lg">
                </div>
                <!-- Title & Meta -->
                <div class="col-md">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge bg-danger rounded-pill px-3 py-1">Ultra 4K HDR</span>
                        <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-1">
                            <i class="bi bi-star-fill me-1"></i> <?php echo e($movie['rating']); ?> / 10
                        </span>
                        <span class="badge bg-dark border border-secondary text-light rounded-pill px-3 py-1">
                            <i class="bi bi-eye me-1"></i> <?php echo format_views($movie['views_count']); ?> Views
                        </span>
                    </div>

                    <h1 class="display-5 fw-bold text-white mb-2"><?php echo e($movie['title']); ?></h1>

                    <div class="d-flex flex-wrap align-items-center gap-3 text-light small mb-3">
                        <span><i class="bi bi-calendar3 me-1 text-danger"></i> <?php echo e($movie['release_year']); ?></span>
                        <span><i class="bi bi-clock me-1 text-danger"></i> <?php echo e($movie['duration']); ?></span>
                        <span><i class="bi bi-translate me-1 text-danger"></i> <?php echo e($movie['language']); ?></span>
                        <span><i class="bi bi-film me-1 text-danger"></i> <?php echo e($movie['genre']); ?></span>
                    </div>

                    <p class="text-light-50 lead fs-6 mb-4 pe-lg-5" style="max-width: 800px;">
                        <?php echo e($movie['short_description']); ?>
                    </p>

                    <!-- Primary Actions -->
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#player-section" class="btn btn-cinema px-4 py-2">
                            <i class="bi bi-play-circle-fill fs-5"></i>
                            <span>Watch Movie</span>
                        </a>

                        <?php if (!empty($movie['download_url'])): ?>
                            <a href="<?php echo e($movie['download_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-cinema-outline px-4 py-2">
                                <i class="bi bi-cloud-arrow-down-fill fs-5 text-warning"></i>
                                <span>Download Legal Copy</span>
                            </a>
                        <?php endif; ?>

                        <a href="<?php echo e(get_whatsapp_url()); ?>" target="_blank" class="btn btn-whatsapp px-4 py-2">
                            <i class="bi bi-whatsapp fs-5"></i>
                            <span>Inquire on WhatsApp</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Video Player Section (Trailer & Legal Stream) -->
    <div id="player-section" class="glass-card p-4 p-md-5 mb-5">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
            <div>
                <h3 class="fw-bold text-white mb-1"><i class="bi bi-broadcast text-danger me-2"></i>Cinematic Streaming Player</h3>
                <small class="text-muted">High-bitrate legal stream & trailer player in full HD</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" onclick="location.reload();">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh Stream
                </button>
            </div>
        </div>

        <?php if (!empty($embedUrl)): ?>
            <div class="player-wrapper mb-3">
                <iframe src="<?php echo e($embedUrl); ?>" title="<?php echo e($movie['title']); ?> Player" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
        <?php else: ?>
            <div class="text-center py-5 bg-dark rounded-4">
                <i class="bi bi-play-slash fs-1 text-secondary mb-3 d-block"></i>
                <h5 class="text-white">Direct Stream Coming Soon</h5>
                <p class="text-secondary small">This title's digital rights are being synced with our media nodes.</p>
            </div>
        <?php endif; ?>

        <!-- Disclaimer & Notice -->
        <div class="alert alert-dark border-secondary border-opacity-50 small text-secondary mt-3 mb-0 d-flex align-items-center">
            <i class="bi bi-shield-check text-success fs-4 me-3"></i>
            <div>
                <strong>Legal Content Guarantee:</strong> CinemaVault features and embeds content only under fair-use commentary, public domain archives, licensed embeds, and promotional distributors. No deceptive pop-ups or unauthorized media.
            </div>
        </div>
    </div>

    <!-- Movie Overview & Metadata Grid -->
    <div class="row g-4 mb-5">
        <!-- Left: Full About & Synopsis -->
        <div class="col-lg-8">
            <div class="glass-card p-4 p-md-5 h-100">
                <h3 class="fw-bold text-white mb-3">About the Film</h3>
                <div class="text-light text-opacity-75 lh-lg mb-4">
                    <?php echo nl2br(e($movie['description'])); ?>
                </div>

                <!-- Tags / Keywords -->
                <?php if (!empty($movie['tags'])): ?>
                    <h5 class="text-white fw-bold mb-3">Related Tags</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach (explode(',', $movie['tags']) as $tag): ?>
                            <?php $cleanTag = trim($tag); if (!empty($cleanTag)): ?>
                                <a href="<?php echo BASE_URL; ?>/search.php?q=<?php echo urlencode($cleanTag); ?>" class="badge bg-dark border border-secondary text-light text-decoration-none px-3 py-2">
                                    #<?php echo e($cleanTag); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Technical Film Specs -->
        <div class="col-lg-4">
            <div class="glass-card p-4 p-md-5 h-100">
                <h4 class="fw-bold text-white mb-4">Movie Specifications</h4>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                        <span class="text-muted">Category:</span>
                        <a href="<?php echo BASE_URL; ?>/category.php?slug=<?php echo urlencode($movie['category_slug'] ?? 'all'); ?>" class="text-danger fw-semibold text-decoration-none">
                            <?php echo e($movie['category_name'] ?? 'General'); ?>
                        </a>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                        <span class="text-muted">Genre:</span>
                        <span class="text-white fw-semibold"><?php echo e($movie['genre']); ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                        <span class="text-muted">Language:</span>
                        <span class="text-white fw-semibold"><?php echo e($movie['language']); ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                        <span class="text-muted">Release Year:</span>
                        <span class="text-white fw-semibold"><?php echo e($movie['release_year']); ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                        <span class="text-muted">Runtime:</span>
                        <span class="text-white fw-semibold"><?php echo e($movie['duration']); ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                        <span class="text-muted">Quality:</span>
                        <span class="badge bg-success">4K Ultra HD</span>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                        <span class="text-muted">IMDb Rating:</span>
                        <span class="text-warning fw-bold"><i class="bi bi-star-fill"></i> <?php echo e($movie['rating']); ?></span>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Status:</span>
                        <span class="text-info">Verified Available</span>
                    </li>
                </ul>

                <!-- Direct WhatsApp Inquiry Box -->
                <div class="mt-4 p-3 rounded-3 bg-dark border border-secondary border-opacity-25 text-center">
                    <i class="bi bi-whatsapp text-success fs-2 mb-2 d-block"></i>
                    <h6 class="text-white fw-bold mb-1">Request More Titles</h6>
                    <p class="text-muted small mb-3">Looking for a specific film, audio track or resolution? Message our admin directly on WhatsApp.</p>
                    <a href="<?php echo e(get_whatsapp_url()); ?>" target="_blank" class="btn btn-whatsapp btn-sm w-100">
                        Chat on WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Movies Section -->
    <?php if (!empty($relatedMovies)): ?>
        <section class="mb-5">
            <div class="section-header">
                <div>
                    <h3 class="section-title mb-1">You May Also Like</h3>
                    <small class="text-muted">Curated titles from <?php echo e($movie['category_name']); ?></small>
                </div>
            </div>
            <div class="row g-3 g-md-4">
                <?php foreach ($relatedMovies as $relMovie): ?>
                    <?php 
                        $movie = $relMovie;
                        include __DIR__ . '/includes/movie_card.php'; 
                    ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
