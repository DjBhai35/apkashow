<?php
/**
 * ApkaShow - About Us Page
 * Content is fully managed from Admin Panel Settings
 * Domain: apkashow.com
 */
require_once __DIR__ . '/includes/functions.php';

$heroTitle = get_setting('about_hero_title', 'The World’s Premier Cinematic Hub for Visionaries');
$heroSubtitle = get_setting('about_hero_subtitle', 'Fueling ambition, financial mastery, and high-performance mindsets through elite storytelling and masterclass cinema on ApkaShow.');
$aboutContent = get_setting('about_content', '<p class="lead">Welcome to ApkaShow, an exclusive cinematic streaming platform.</p>');

$pageTitle = 'About Us | ApkaShow';
$pageDescription = 'Learn more about ApkaShow, our vision, curated cinematic masterclasses, and motivational storytelling archive.';
$pageCanonical = canonical_url_for('/about.php');

require_once __DIR__ . '/includes/header.php';
?>

<!-- Schema.org AboutPage & BreadcrumbList Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "<?php echo BASE_URL; ?>/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "About Us",
          "item": "<?php echo BASE_URL; ?>/about.php"
        }
      ]
    },
    {
      "@type": "AboutPage",
      "name": <?php echo json_encode($pageTitle); ?>,
      "description": <?php echo json_encode($pageDescription); ?>,
      "url": <?php echo json_encode($pageCanonical); ?>
    }
  ]
}
</script>

<div class="container py-4">

    <!-- About Hero -->
    <div class="glass-card p-4 p-md-5 mb-5 text-center position-relative overflow-hidden">
        <span class="badge bg-danger rounded-pill px-3 py-1 mb-3 text-uppercase letter-spacing-1">Our Philosophy</span>
        <h1 class="display-5 fw-bold text-white mb-3"><?php echo e($heroTitle); ?></h1>
        <p class="text-secondary lead fs-6 mx-auto mb-0" style="max-width: 750px;">
            <?php echo e($heroSubtitle); ?>
        </p>
    </div>

    <!-- About Content Grid -->
    <div class="row g-4 mb-5 align-items-center">
        <div class="col-lg-7">
            <div class="glass-card p-4 p-md-5">
                <div class="about-rendered-body text-light text-opacity-75">
                    <?php echo $aboutContent; ?>
                </div>

                <div class="row g-3 mt-4 pt-3 border-top border-secondary border-opacity-25 text-center">
                    <div class="col-4">
                        <h3 class="fw-bold text-white mb-0">12+</h3>
                        <small class="text-muted">Curated Genres</small>
                    </div>
                    <div class="col-4">
                        <h3 class="fw-bold text-white mb-0">4K HDR</h3>
                        <small class="text-muted">Master Bitrate</small>
                    </div>
                    <div class="col-4">
                        <h3 class="fw-bold text-white mb-0">100%</h3>
                        <small class="text-muted">Legal Streaming</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="glass-card p-4 p-md-5 text-center">
                <div class="p-4 rounded-4 bg-dark bg-opacity-50 border border-secondary border-opacity-25 mb-4">
                    <i class="bi bi-shield-check fs-1 text-danger mb-2 d-block"></i>
                    <h5 class="text-white fw-bold">Strict Compliance</h5>
                    <p class="text-muted small mb-0">Every film hosted or embedded complies strictly with legal distribution, licensed trailers, and archival distribution standards on ApkaShow.</p>
                </div>

                <div class="p-4 rounded-4 bg-dark bg-opacity-50 border border-secondary border-opacity-25">
                    <i class="bi bi-whatsapp fs-1 text-success mb-2 d-block"></i>
                    <h5 class="text-white fw-bold">Direct Communication</h5>
                    <p class="text-muted small mb-3">Questions about our curated list or enterprise collaborations? Connect directly on WhatsApp.</p>
                    <a href="<?php echo e(get_whatsapp_url()); ?>" target="_blank" class="btn btn-whatsapp w-100">
                        Chat on WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
