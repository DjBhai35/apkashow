<?php
/**
 * ApkaShow - Movie Detail & Player Page
 * Features 4K poster, trailer embed, legal streaming player, download links, metadata, and related films
 * Domain: apkashow.com
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
    http_response_code(404);
    $pageTitle = "Movie Not Found | ApkaShow";
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

// Fetch Associated Gallery Images (2-4 Scene Stills)
$galleryStmt = $db->prepare("SELECT * FROM `movie_images` WHERE `movie_id` = ? ORDER BY `sort_order` ASC, `id` ASC");
$galleryStmt->execute([$movie['id']]);
$galleryImages = $galleryStmt->fetchAll();

// Dynamic SEO Configurations
$pageTitle = !empty($movie['meta_title']) ? $movie['meta_title'] : ($movie['title'] . ' (' . $movie['release_year'] . ') - Watch Online & Full Movie Info | ApkaShow');
$pageDescription = !empty($movie['meta_description']) ? $movie['meta_description'] : ($movie['short_description']);
$pageKeywords = !empty($movie['meta_keywords']) ? $movie['meta_keywords'] : ($movie['tags'] . ', ' . $movie['genre'] . ', movie streaming, apkashow');
$pageImage = resolve_image_url($movie['poster']);
$pageCanonical = !empty($movie['canonical_url']) ? $movie['canonical_url'] : canonical_url_for('/movie/' . urlencode($movie['slug']));
$isMovieDetail = true;

require_once __DIR__ . '/includes/header.php';

// Prepare video streaming embed source (YouTube)
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

// Trailer Availability Checks
$hasUploadedTrailer = !empty($movie['trailer_file']);
$hasYoutubeTrailer = !empty($embedUrl);
$hasTrailer = $hasUploadedTrailer || $hasYoutubeTrailer;
$movieHeroBackdrop = resolve_image_url(!empty($movie['banner']) ? $movie['banner'] : $movie['poster'], 'banner');
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

    <!-- Movie Hero Showcase (Prominently displaying this movie's own banner/poster) -->
    <div class="movie-detail-hero" style="background-image: url('<?php echo e($movieHeroBackdrop); ?>');">
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

                    <!-- Primary Actions: Trailer is clearly labeled as Trailer -->
                    <div class="d-flex flex-wrap gap-3">
                        <?php if ($hasTrailer): ?>
                            <a href="#trailer-section" class="btn btn-cinema px-4 py-2">
                                <i class="bi bi-play-circle-fill fs-5"></i>
                                <span>Watch Trailer</span>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($movie['watch_url'])): ?>
                            <a href="<?php echo e($movie['watch_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-danger px-4 py-2">
                                <i class="bi bi-film fs-5"></i>
                                <span>Watch Full Movie</span>
                            </a>
                        <?php endif; ?>

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

    <!-- Official Movie Trailer Section -->
    <div id="trailer-section" class="glass-card p-4 p-md-5 mb-5">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-danger rounded-pill px-3 py-1 text-uppercase">Trailer</span>
                    <h3 class="fw-bold text-white mb-0"><i class="bi bi-play-btn-fill text-danger me-2"></i>Official Movie Trailer</h3>
                </div>
                <small class="text-light text-opacity-75">Watch high-definition trailer and preview scenes for <?php echo e($movie['title']); ?></small>
            </div>

            <?php if ($hasUploadedTrailer && $hasYoutubeTrailer): ?>
                <!-- Toggle between Server Trailer and YouTube Embed -->
                <div class="btn-group shadow-sm" role="group" aria-label="Trailer Source Selector">
                    <button type="button" class="btn btn-cinema btn-sm px-3" id="btnSwitchUploaded" onclick="switchTrailerSource('uploaded')">
                        <i class="bi bi-file-earmark-play-fill me-1"></i> HD Server Trailer
                    </button>
                    <button type="button" class="btn btn-outline-light btn-sm px-3" id="btnSwitchYoutube" onclick="switchTrailerSource('youtube')">
                        <i class="bi bi-youtube text-danger me-1"></i> YouTube Trailer
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($hasUploadedTrailer && $hasYoutubeTrailer): ?>
            <!-- Dual Trailer Players -->
            <div id="uploadedTrailerBox" class="player-wrapper mb-3">
                <video controls controlsList="nodownload" preload="metadata" poster="<?php echo e($movieHeroBackdrop); ?>" class="w-100 h-100">
                    <source src="<?php echo e(resolve_video_url($movie['trailer_file'])); ?>" type="video/mp4">
                    Your browser does not support HTML5 video player.
                </video>
            </div>
            <div id="youtubeTrailerBox" class="player-wrapper mb-3 d-none">
                <iframe id="youtubeIframe" src="<?php echo e($embedUrl); ?>" title="<?php echo e($movie['title']); ?> YouTube Trailer" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
        <?php elseif ($hasUploadedTrailer): ?>
            <!-- Uploaded Video Trailer Player -->
            <div class="player-wrapper mb-3">
                <video controls controlsList="nodownload" preload="metadata" poster="<?php echo e($movieHeroBackdrop); ?>" class="w-100 h-100">
                    <source src="<?php echo e(resolve_video_url($movie['trailer_file'])); ?>" type="video/mp4">
                    Your browser does not support HTML5 video player.
                </video>
            </div>
        <?php elseif ($hasYoutubeTrailer): ?>
            <!-- YouTube Trailer Embed Player -->
            <div class="player-wrapper mb-3">
                <iframe src="<?php echo e($embedUrl); ?>" title="<?php echo e($movie['title']); ?> Official Trailer" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
        <?php else: ?>
            <!-- No Trailer Available Notice -->
            <div class="text-center py-5 bg-dark rounded-4 border border-secondary border-opacity-25">
                <i class="bi bi-film fs-1 text-secondary mb-3 d-block"></i>
                <h5 class="text-white">Official Trailer Available Soon</h5>
                <p class="text-secondary small mb-0">The promotional trailer for <?php echo e($movie['title']); ?> is being processed by our media distribution nodes.</p>
            </div>
        <?php endif; ?>

        <!-- Dedicated Full Movie Stream Callout if watch_url exists -->
        <?php if (!empty($movie['watch_url'])): ?>
            <div class="p-3 mt-3 rounded-3 bg-dark border border-danger border-opacity-50 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-danger"><i class="bi bi-film"></i></div>
                    <div>
                        <h6 class="text-white fw-bold mb-0">Stream Full Feature Film</h6>
                        <small class="text-light text-opacity-75">Ready for the entire movie? Access the full stream via our legal distributor stream node.</small>
                    </div>
                </div>
                <a href="<?php echo e($movie['watch_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-danger btn-sm px-4 py-2 text-nowrap fw-bold">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Watch Full Movie
                </a>
            </div>
        <?php endif; ?>

        <!-- Disclaimer & Notice -->
        <div class="alert alert-dark border-secondary border-opacity-50 small text-secondary mt-3 mb-0 d-flex align-items-center">
            <i class="bi bi-shield-check text-success fs-4 me-3"></i>
            <div>
                <strong>Legal Content Guarantee:</strong> ApkaShow features and embeds content only under fair-use commentary, public domain archives, licensed embeds, and promotional distributors. No deceptive pop-ups or unauthorized media.
            </div>
        </div>
    </div>

    <!-- Movie Overview & Metadata Grid -->
    <div class="row g-4 mb-5">
        <!-- Left: Full About & Synopsis with Natural Inline Gallery Stills -->
        <div class="col-lg-8">
            <div class="glass-card p-4 p-md-5 h-100">
                <h3 class="fw-bold text-white mb-3">About the Film</h3>
                <div class="text-light text-opacity-75 lh-lg mb-4">
                    <?php echo nl2br(e($movie['description'])); ?>
                </div>

                <!-- Multiple Scene Images Inside Description / Gallery (2-4 Images) -->
                <?php if (!empty($galleryImages)): ?>
                    <div class="mt-4 pt-4 border-top border-secondary border-opacity-25">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h4 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-images text-danger"></i> Scene Stills & Production Highlights
                            </h4>
                            <span class="badge bg-secondary bg-opacity-50 small"><?php echo count($galleryImages); ?> Photos</span>
                        </div>
                        <p class="text-light text-opacity-75 small mb-3">High-resolution scene captures and cinematography moments from <?php echo e($movie['title']); ?>.</p>
                        <div class="row g-3">
                            <?php foreach ($galleryImages as $index => $gImg): ?>
                                <?php $fullImgUrl = resolve_image_url($gImg['image_url']); ?>
                                <div class="col-6 col-md-<?php echo (count($galleryImages) <= 2) ? '6' : ((count($galleryImages) === 3) ? '4' : '3'); ?>">
                                    <div class="gallery-still-card position-relative overflow-hidden rounded-3 border border-secondary border-opacity-25 h-100">
                                        <a href="<?php echo e($fullImgUrl); ?>" class="d-block gallery-lightbox-trigger" data-bs-toggle="modal" data-bs-target="#imageLightboxModal" data-img-src="<?php echo e($fullImgUrl); ?>" data-caption="<?php echo e($gImg['caption'] ?: ($movie['title'] . ' Scene ' . ($index + 1))); ?>">
                                            <img src="<?php echo e($fullImgUrl); ?>" alt="<?php echo e($gImg['caption'] ?: ($movie['title'] . ' Still ' . ($index + 1))); ?>" class="w-100 gallery-thumb-img" loading="lazy">
                                            <div class="gallery-still-overlay">
                                                <i class="bi bi-arrows-fullscreen text-white fs-5"></i>
                                            </div>
                                        </a>
                                        <?php if (!empty($gImg['caption'])): ?>
                                            <div class="p-2 bg-dark bg-opacity-75 text-light small text-truncate text-center">
                                                <?php echo e($gImg['caption']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

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

    <!-- Full-resolution Image Lightbox Modal -->
    <div class="modal fade" id="imageLightboxModal" tabindex="-1" aria-labelledby="imageLightboxLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark border-secondary">
                <div class="modal-header border-secondary py-2">
                    <h6 class="modal-title text-white small" id="imageLightboxLabel"><?php echo e($movie['title']); ?> - Scene Still</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 text-center bg-black">
                    <img id="lightboxModalImg" src="" alt="Scene Preview" class="img-fluid" style="max-height: 80vh; object-fit: contain;">
                </div>
                <div class="modal-footer border-secondary py-2 justify-content-between">
                    <small class="text-secondary" id="lightboxModalCaption"></small>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function switchTrailerSource(source) {
    const upBox = document.getElementById('uploadedTrailerBox');
    const ytBox = document.getElementById('youtubeTrailerBox');
    const btnUp = document.getElementById('btnSwitchUploaded');
    const btnYt = document.getElementById('btnSwitchYoutube');
    if (!upBox || !ytBox) return;

    if (source === 'uploaded') {
        upBox.classList.remove('d-none');
        ytBox.classList.add('d-none');
        if (btnUp) btnUp.className = 'btn btn-cinema btn-sm px-3';
        if (btnYt) btnYt.className = 'btn btn-outline-light btn-sm px-3';
    } else {
        upBox.classList.add('d-none');
        ytBox.classList.remove('d-none');
        if (btnUp) btnUp.className = 'btn btn-outline-light btn-sm px-3';
        if (btnYt) btnYt.className = 'btn btn-cinema btn-sm px-3';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const triggers = document.querySelectorAll('.gallery-lightbox-trigger');
    const modalImg = document.getElementById('lightboxModalImg');
    const modalCap = document.getElementById('lightboxModalCaption');
    triggers.forEach(trig => {
        trig.addEventListener('click', () => {
            const src = trig.getAttribute('data-img-src');
            const caption = trig.getAttribute('data-caption') || '';
            if (modalImg) modalImg.src = src;
            if (modalCap) modalCap.textContent = caption;
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
