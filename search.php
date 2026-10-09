<?php
/**
 * ApkaShow - Search System
 * Real database-powered search across title, description, category, genre, tags, and language
 * Domain: apkashow.com
 */
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$movies = [];
$totalMovies = 0;
$totalPages = 0;

if (!empty($q)) {
    // Exact Movie Title Match: Directly lead to that movie's detail page
    $exactStmt = $db->prepare("SELECT slug FROM `movies` WHERE status = 1 AND LOWER(TRIM(title)) = LOWER(TRIM(?)) LIMIT 1");
    $exactStmt->execute([$q]);
    $exactMovie = $exactStmt->fetch();
    if ($exactMovie && !empty($exactMovie['slug'])) {
        header("Location: " . BASE_URL . "/movie.php?slug=" . urlencode($exactMovie['slug']));
        exit();
    }

    // Split search query into multi-word tokens for broad semantic discovery
    $rawWords = preg_split('/\s+/', $q);
    $words = [];
    foreach ($rawWords as $w) {
        $w = trim($w);
        if (mb_strlen($w) >= 2) {
            $words[] = $w;
        }
    }
    if (empty($words)) {
        $words = [$q];
    }

    // Build multi-field tokenized query across title, descriptions, genre, tags, language, year, keywords, and category
    $whereParts = [];
    $params = [];
    foreach ($words as $word) {
        $wTerm = '%' . $word . '%';
        $whereParts[] = "(m.title LIKE ? OR m.short_description LIKE ? OR m.description LIKE ? OR m.genre LIKE ? OR m.tags LIKE ? OR m.language LIKE ? OR m.release_year LIKE ? OR m.meta_keywords LIKE ? OR c.name LIKE ?)";
        for ($k = 0; $k < 9; $k++) {
            $params[] = $wTerm;
        }
    }
    $whereSql = implode(' AND ', $whereParts);

    // Count Matching Rows
    $countSql = "SELECT COUNT(*) FROM `movies` m 
                 LEFT JOIN `categories` c ON m.category_id = c.id 
                 WHERE m.status = 1 AND ($whereSql)";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalMovies = (int)$countStmt->fetchColumn();
    $totalPages = (int)ceil($totalMovies / $perPage);

    // Fetch Matching Movies with Relevance Ordering & Pagination
    $fullTerm = '%' . $q . '%';
    $titleStartsTerm = $q . '%';
    $searchSql = "SELECT m.*, c.name as category_name, c.slug as category_slug 
                  FROM `movies` m 
                  LEFT JOIN `categories` c ON m.category_id = c.id 
                  WHERE m.status = 1 AND ($whereSql) 
                  ORDER BY 
                    (CASE 
                        WHEN LOWER(m.title) = LOWER(?) THEN 1 
                        WHEN m.title LIKE ? THEN 2 
                        WHEN m.title LIKE ? THEN 3 
                        ELSE 4 
                     END), 
                    m.views_count DESC, 
                    m.id DESC 
                  LIMIT $perPage OFFSET $offset";
    $searchStmt = $db->prepare($searchSql);
    $allParams = array_merge($params, [$q, $titleStartsTerm, $fullTerm]);
    $searchStmt->execute($allParams);
    $movies = $searchStmt->fetchAll();
}

$pageTitle = !empty($q) ? 'Search: "' . $q . '" - Full Movies & Trailers | ApkaShow' : 'Search Movies & Trailers | ApkaShow';
$pageDescription = !empty($q) ? 'Search results for "' . $q . '" on ApkaShow. Stream HD movies, mindsets, forex masterclasses, and billionaire films.' : 'Search the ApkaShow database for top movies, genres, mindsets, and billionaire biographies.';
$pageCanonical = canonical_url_for('/search.php?q=' . urlencode($q));

require_once __DIR__ . '/includes/header.php';
?>

<!-- Schema.org SearchResultsPage & BreadcrumbList Structured Data -->
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
          "name": "Search",
          "item": "<?php echo BASE_URL; ?>/search.php"
        }
        <?php if (!empty($q)): ?>,
        {
          "@type": "ListItem",
          "position": 3,
          "name": <?php echo json_encode('Results for "' . $q . '"'); ?>,
          "item": <?php echo json_encode($pageCanonical); ?>
        }
        <?php endif; ?>
      ]
    },
    {
      "@type": "SearchResultsPage",
      "name": <?php echo json_encode($pageTitle); ?>,
      "description": <?php echo json_encode($pageDescription); ?>,
      "url": <?php echo json_encode($pageCanonical); ?>,
      "mainEntity": {
        "@type": "ItemList",
        "numberOfItems": <?php echo (int)$totalMovies; ?>,
        "itemListElement": [
          <?php 
          $itemList = [];
          if (!empty($movies)) {
              foreach (array_slice($movies, 0, 10) as $idx => $mRow) {
                  $itemList[] = json_encode([
                      '@type' => 'Movie',
                      'position' => $idx + 1,
                      'name' => $mRow['title'],
                      'url' => BASE_URL . '/movie.php?slug=' . urlencode($mRow['slug']),
                      'image' => resolve_image_url($mRow['poster'])
                  ], JSON_UNESCAPED_SLASHES);
              }
          }
          echo implode(',', $itemList);
          ?>
        ]
      }
    }
  ]
}
</script>

<div class="container py-4">

    <!-- Search Hero & Input Box -->
    <div class="glass-card p-4 p-md-5 mb-5 text-center">
        <h1 class="display-6 fw-bold text-white mb-2">Search ApkaShow</h1>
        <p class="text-secondary mb-4">Discover motivational stories, forex documentaries, blockbuster action, and billionaire cinema.</p>
        
        <form action="<?php echo BASE_URL; ?>/search.php" method="GET" class="mx-auto" style="max-width: 650px;">
            <div class="input-group input-group-lg shadow-sm">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control bg-dark border-secondary text-white" placeholder="Type movie title, genre, keyword, language..." value="<?php echo e($q); ?>" required autofocus>
                <button class="btn btn-cinema px-4" type="submit">Search</button>
            </div>
        </form>
    </div>

    <!-- Results Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="section-title mb-1">
                <?php if (!empty($q)): ?>
                    Search Results for <span class="text-danger">"<?php echo e($q); ?>"</span>
                <?php else: ?>
                    All Movies
                <?php endif; ?>
            </h2>
            <small class="text-muted">Found <?php echo number_format($totalMovies); ?> movies matching your search query</small>
        </div>
    </div>

    <!-- Results Grid -->
    <div class="row g-3 g-md-4 mb-5">
        <?php if (!empty($movies)): ?>
            <?php foreach ($movies as $movie): ?>
                <?php include __DIR__ . '/includes/movie_card.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 py-5 text-center">
                <div class="glass-card p-5 mx-auto" style="max-width: 520px;">
                    <i class="bi bi-search fs-1 text-secondary mb-3 d-block"></i>
                    <h4 class="text-white fw-bold">No Movies Found</h4>
                    <p class="text-secondary small mb-4">
                        We couldn't find any movies matching "<strong><?php echo e($q); ?></strong>". Try searching for titles like "Wolf", "Social", "Forex", "John Wick", or check our categories.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-cinema">Return to Home</a>
                        <a href="<?php echo BASE_URL; ?>/category.php?slug=all" class="btn btn-cinema-outline">Explore All</a>
                    </div>
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
                        <a class="page-link rounded bg-dark border-secondary text-light px-3" href="?q=<?php echo urlencode($q); ?>&page=<?php echo $page - 1; ?>">
                            <i class="bi bi-chevron-left me-1"></i> Prev
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                        <a class="page-link rounded <?php echo ($i === $page) ? 'bg-danger border-danger text-white' : 'bg-dark border-secondary text-light'; ?> px-3" href="?q=<?php echo urlencode($q); ?>&page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link rounded bg-dark border-secondary text-light px-3" href="?q=<?php echo urlencode($q); ?>&page=<?php echo $page + 1; ?>">
                            Next <i class="bi bi-chevron-right ms-1"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
