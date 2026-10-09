<?php
/**
 * ApkaShow - Header & Navigation Template
 * Production Domain: apkashow.com
 */
require_once __DIR__ . '/functions.php';

// Fetch Active Categories for Navigation Dropdown & Direct Nav items
try {
    $db = getDB();
    $catStmt = $db->query("SELECT * FROM `categories` WHERE `status` = 1 ORDER BY `display_order` ASC, `name` ASC");
    $navCategories = $catStmt->fetchAll();
} catch (Exception $e) {
    $navCategories = [];
}

$siteName = get_setting('site_name', 'ApkaShow');
$siteLogoText = get_setting('site_logo_text', 'APKA<span class="text-gradient">SHOW</span>');
$whatsAppUrl = get_whatsapp_url();
$headerAd = get_setting('header_ad_code', '');

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$currentSlug = $_GET['slug'] ?? '';
$searchQuery = $_GET['q'] ?? '';

// SEO Metadata Defaults
$metaTitle = $pageTitle ?? get_setting('meta_title_default', 'ApkaShow | Watch Premium Movies, Billionaire Mindset & Cinema Hub');
$metaDescription = $pageDescription ?? get_setting('meta_description_default', 'Explore curated high-definition cinema, motivational billionaire stories, Wall Street dramas, and blockbuster movies on ApkaShow.');
$metaKeywords = $pageKeywords ?? get_setting('meta_keywords_default', 'apkashow, apka show, movies, billionaire movies, forex cinema, mindset films, motivational movies, bollywood, hollywood, streaming');
$metaImage = $pageImage ?? (BASE_URL . '/assets/images/og-preview.jpg');
$canonicalUrl = $pageCanonical ?? canonical_url_for(ltrim($_SERVER['REQUEST_URI'] ?? '', '/'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($metaTitle); ?></title>
    
    <!-- Preconnect & DNS-Prefetch for Core Web Vitals & Performance -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
    <link rel="dns-prefetch" href="https://images.unsplash.com">

    <!-- Technical SEO Metadata (Google Search Best Practices) -->
    <meta name="description" content="<?php echo e($metaDescription); ?>">
    <meta name="keywords" content="<?php echo e($metaKeywords); ?>">
    <meta name="robots" content="<?php echo e($robotsDirectives ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'); ?>">
    <link rel="canonical" href="<?php echo e($canonicalUrl); ?>">
    
    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="<?php echo isset($isMovieDetail) ? 'video.movie' : 'website'; ?>">
    <meta property="og:title" content="<?php echo e($metaTitle); ?>">
    <meta property="og:description" content="<?php echo e($metaDescription); ?>">
    <meta property="og:url" content="<?php echo e($canonicalUrl); ?>">
    <meta property="og:image" content="<?php echo e($metaImage); ?>">
    <meta property="og:image:alt" content="<?php echo e($metaTitle); ?>">
    <meta property="og:site_name" content="<?php echo e($siteName); ?>">

    <!-- Twitter / X Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@apkashow">
    <meta name="twitter:title" content="<?php echo e($metaTitle); ?>">
    <meta name="twitter:description" content="<?php echo e($metaDescription); ?>">
    <meta name="twitter:image" content="<?php echo e($metaImage); ?>">
    <meta name="twitter:image:alt" content="<?php echo e($metaTitle); ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎬</text></svg>">

    <!-- Bootstrap 5.3.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Premium Cinematic Theme CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/cinematic.css">

    <script>
        window.BASE_URL = "<?php echo BASE_URL; ?>";
    </script>

    <?php if ($currentPage === 'index.php' && empty($currentSlug)): ?>
    <!-- Schema.org WebSite Structured Data with Sitelinks Searchbox -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": <?php echo json_encode($siteName); ?>,
      "url": <?php echo json_encode(CANONICAL_DOMAIN); ?>,
      "description": <?php echo json_encode($metaDescription); ?>,
      "potentialAction": {
        "@type": "SearchAction",
        "target": {
          "@type": "EntryPoint",
          "urlTemplate": <?php echo json_encode(CANONICAL_DOMAIN . '/search.php?q={search_term_string}'); ?>
        },
        "query-input": "required name=search_term_string"
      }
    }
    </script>
    <?php endif; ?>

    <?php if (!empty($pageJsonLd)): ?>
        <?php echo $pageJsonLd; ?>
    <?php endif; ?>
</head>
<body class="d-flex flex-column min-vh-100">



<!-- Top Cinematic Navbar -->
<nav class="navbar navbar-expand-xl glass-nav sticky-top py-2">
    <div class="container-fluid px-lg-4">
        <!-- Logo / Brand -->
        <a class="navbar-brand me-3" href="<?php echo BASE_URL; ?>/index.php">
            <span class="fs-4">🎬</span>
            <span><?php echo $siteLogoText; ?></span>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0 text-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#apkaNavbar" aria-controls="apkaNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-1 text-light"></i>
        </button>

        <!-- Navbar Links & Search -->
        <div class="collapse navbar-collapse" id="apkaNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-xl-0 align-items-xl-center">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'index.php' && empty($currentSlug)) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>
                
                <!-- Mandatory High-Priority Category Links (Dynamic from database or admin edits) -->
                <?php
                // Map active categories by slug for instant dynamic naming
                $catsBySlug = [];
                foreach ($navCategories as $nc) {
                    $catsBySlug[$nc['slug']] = $nc;
                }
                ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentSlug === 'millionaire') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/category.php?slug=millionaire">
                        <i class="bi bi-currency-dollar me-1 text-warning"></i> <?php echo e($catsBySlug['millionaire']['name'] ?? 'Millionaire Movies'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentSlug === 'billionaire') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/category.php?slug=billionaire">
                        <i class="bi bi-gem me-1 text-info"></i> <?php echo e($catsBySlug['billionaire']['name'] ?? 'Billionaire Movies'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentSlug === 'mindset') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/category.php?slug=mindset">
                        <i class="bi bi-lightning-charge me-1 text-warning"></i> <?php echo e($catsBySlug['mindset']['name'] ?? 'Mindset Movies'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentSlug === 'business') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/category.php?slug=business">
                        <i class="bi bi-briefcase me-1 text-light"></i> <?php echo e($catsBySlug['business']['name'] ?? 'Business Movies'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentSlug === 'forex') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/category.php?slug=forex">
                        <i class="bi bi-graph-up-arrow me-1 text-success"></i> <?php echo e($catsBySlug['forex']['name'] ?? 'Forex Movies'); ?>
                    </a>
                </li>

                <!-- Dynamic Categories Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-grid me-1"></i> Categories
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <?php foreach ($navCategories as $cat): ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center justify-content-between <?php echo ($currentSlug === $cat['slug']) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/category.php?slug=<?php echo urlencode($cat['slug']); ?>">
                                    <span><i class="bi <?php echo e($cat['icon'] ?: 'bi-film'); ?> me-2"></i><?php echo e($cat['name']); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'about.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/about.php">
                        About Us
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($currentPage === 'contact.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/contact.php">
                        Contact Us
                    </a>
                </li>
            </ul>

            <!-- Right Side: Search Form & Admin Controlled WhatsApp Button -->
            <div class="d-flex align-items-center flex-column flex-xl-row gap-2 mt-3 mt-xl-0">
                <!-- Live Search Form -->
                <form class="search-form-header w-100 position-relative" action="<?php echo BASE_URL; ?>/search.php" method="GET">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" id="siteSearchInput" name="q" class="form-control search-input-cinematic w-100" placeholder="Search movies, genre, tags..." value="<?php echo e($searchQuery); ?>" autocomplete="off" required>
                    <!-- Live Search Suggestions Container -->
                    <div id="searchSuggestBox" class="list-group position-absolute w-100 mt-1 d-none shadow-lg z-3"></div>
                </form>

                <!-- WhatsApp Button (Controlled Completely From Admin Panel) -->
                <a href="<?php echo e($whatsAppUrl); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-whatsapp px-3 py-2 text-nowrap w-100 w-xl-auto text-center" title="Chat with ApkaShow on WhatsApp">
                    <i class="bi bi-whatsapp fs-5"></i>
                    <span>WhatsApp</span>
                </a>

                <?php if (is_admin_logged_in()): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/index.php" class="btn btn-outline-warning btn-sm ms-xl-1" title="Go to Admin Panel">
                        <i class="bi bi-speedometer2"></i> Admin
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Main Page Container Starts -->
<main class="flex-grow-1">

<?php
// Render configurable header/search ad slot if enabled
$headerAdHtml = render_ad_placement('header_search');
if (!empty($headerAdHtml)):
?>
    <div class="container mt-2">
        <?php echo $headerAdHtml; ?>
    </div>
<?php endif; ?>

