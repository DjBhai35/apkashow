<?php
/**
 * CinemaVault - Dynamic XML Sitemap for Search Engines
 */
require_once __DIR__ . '/includes/functions.php';

header("Content-Type: application/xml; charset=utf-8");

$db = getDB();

// Fetch all published movies
$moviesStmt = $db->query("SELECT slug, updated_at FROM movies WHERE status = 1 ORDER BY id DESC");
$movies = $moviesStmt->fetchAll();

// Fetch all published categories
$catsStmt = $db->query("SELECT slug, updated_at FROM categories WHERE status = 1 ORDER BY id ASC");
$categories = $catsStmt->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Static Main Pages -->
    <url>
        <loc><?php echo BASE_URL; ?>/index.php</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?php echo BASE_URL; ?>/about.php</loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <url>
        <loc><?php echo BASE_URL; ?>/contact.php</loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>

    <!-- Category Pages -->
    <?php foreach ($categories as $cat): ?>
    <url>
        <loc><?php echo BASE_URL; ?>/category.php?slug=<?php echo urlencode($cat['slug']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($cat['updated_at'] ?? 'now')); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>

    <!-- Individual Movie Detail Pages -->
    <?php foreach ($movies as $m): ?>
    <url>
        <loc><?php echo BASE_URL; ?>/movie.php?slug=<?php echo urlencode($m['slug']); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($m['updated_at'] ?? 'now')); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <?php endforeach; ?>
</urlset>
