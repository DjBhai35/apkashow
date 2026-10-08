<?php
/**
 * Reusable Movie Card Component
 * Expects $movie array with keys: title, slug, poster, rating, release_year, duration, genre
 */
$moviePoster = resolve_image_url($movie['poster'] ?? '');
$movieRating = !empty($movie['rating']) ? number_format((float)$movie['rating'], 1) : '8.5';
$movieYear = !empty($movie['release_year']) ? $movie['release_year'] : '2024';
$movieDuration = !empty($movie['duration']) ? $movie['duration'] : '120 min';
$movieGenre = !empty($movie['genre']) ? explode(',', $movie['genre'])[0] : 'Drama';
$movieDetailUrl = BASE_URL . '/movie.php?slug=' . urlencode($movie['slug']);
?>
<div class="col-6 col-md-4 col-lg-3 col-xl-2">
    <div class="movie-card">
        <div class="movie-poster-box">
            <img src="<?php echo e($moviePoster); ?>" alt="<?php echo e($movie['title']); ?> Poster" class="movie-poster-img" loading="lazy">
            
            <!-- Quality Badge -->
            <span class="movie-badge-top">4K HDR</span>
            
            <!-- Rating Badge -->
            <span class="movie-rating-badge">
                <i class="bi bi-star-fill"></i> <?php echo e($movieRating); ?>
            </span>

            <!-- Interactive Hover Overlay with Watch Action -->
            <div class="movie-hover-overlay">
                <a href="<?php echo e($movieDetailUrl); ?>" class="movie-hover-play-btn text-decoration-none" title="Watch <?php echo e($movie['title']); ?>">
                    <i class="bi bi-play-fill"></i>
                </a>
                <div class="text-center text-white mb-1">
                    <small class="fw-bold d-block text-truncate"><?php echo e($movieGenre); ?></small>
                    <span class="badge bg-danger bg-opacity-75 rounded-pill px-2 py-1 small">Stream Now</span>
                </div>
            </div>
        </div>

        <div class="movie-info">
            <a href="<?php echo e($movieDetailUrl); ?>" class="movie-title" title="<?php echo e($movie['title']); ?>">
                <?php echo e($movie['title']); ?>
            </a>
            <div class="movie-meta">
                <span><i class="bi bi-calendar3 me-1"></i><?php echo e($movieYear); ?></span>
                <span><i class="bi bi-clock me-1"></i><?php echo e($movieDuration); ?></span>
            </div>
        </div>
    </div>
</div>
