<?php
/**
 * ApkaShow - Dynamic XML Sitemap for Search Engines
 * Supports standard URLs & Google Image Sitemap extensions
 * Production Domain: apkashow.com
 */
require_once __DIR__ . '/includes/functions.php';

header("Content-Type: application/xml; charset=utf-8");

$db = getDB();

// Fetch all published movies with media assets
$moviesStmt = $db->query("SELECT title, slug, poster, banner, updated_at FROM movies WHERE status = 1 ORDER BY id DESC");
$movies = $moviesStmt->fetchAll();

// Fetch all published categories
$catsStmt = $db->query("SELECT name, slug, updated_at FROM categories WHERE status = 1 ORDER BY id ASC");
$categories = $catsStmt->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <!-- Static Main Pages -->
    <url>
        <loc><?php echo canonical_url_for('/'); ?></loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?php echo canonical_url_for('/about.php'); ?></loc>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    <url>
        <loc><?php echo canonical_url_for('/contact.php'); ?></loc>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <!-- Clean Category Pages -->
    <?php foreach ($categories as $cat): ?>
    <url>
        <loc><?php echo canonical_url_for('/category/' . urlencode($cat['slug'])); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($cat['updated_at'] ?? 'now')); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>

    <!-- Individual Clean Movie Detail Pages with Google Image Markup -->
    <?php foreach ($movies as $m): ?>
    <url>
        <loc><?php echo canonical_url_for('/movie/' . urlencode($m['slug'])); ?></loc>
        <lastmod><?php echo date('Y-m-d', strtotime($m['updated_at'] ?? 'now')); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
        <?php if (!empty($m['poster'])): ?>
        <image:image>
            <image:loc><?php echo htmlspecialchars(resolve_image_url($m['poster']), ENT_XML1, 'UTF-8'); ?></image:loc>
            <image:title><?php echo htmlspecialchars($m['title'] . ' Official Poster', ENT_XML1, 'UTF-8'); ?></image:title>
        </image:image>
        <?php endif; ?>
        <?php if (!empty($m['banner'])): ?>
        <image:image>
            <image:loc><?php echo htmlspecialchars(resolve_image_url($m['banner'], 'banner'), ENT_XML1, 'UTF-8'); ?></image:loc>
            <image:title><?php echo htmlspecialchars($m['title'] . ' Cinematic Backdrop', ENT_XML1, 'UTF-8'); ?></image:title>
        </image:image>
        <?php endif; ?>
    </url>
    <?php endforeach; ?>
</urlset>
