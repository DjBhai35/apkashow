<?php
/**
 * ApkaShow - Admin Add & Edit Movie
 * Supports comprehensive fields: Slug, Poster Upload/URL, Banner Upload/URL, Video embed,
 * Legal stream, Legal download, Category, Genre, Language, SEO fields, status.
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin_auth();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$isEditing = ($id > 0);

$movie = [
    'title' => '',
    'slug' => '',
    'poster' => '',
    'banner' => '',
    'short_description' => '',
    'description' => '',
    'category_id' => 1,
    'genre' => 'Drama',
    'language' => 'English',
    'release_year' => date('Y'),
    'duration' => '120 min',
    'rating' => '8.0',
    'tags' => '',
    'trailer_url' => '',
    'watch_url' => '',
    'download_url' => '',
    'is_featured' => 0,
    'is_popular' => 0,
    'status' => 1,
    'meta_title' => '',
    'meta_description' => '',
    'meta_keywords' => '',
    'canonical_url' => ''
];

if ($isEditing) {
    $stmt = $db->prepare("SELECT * FROM `movies` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        set_flash('danger', 'Movie not found.');
        header("Location: " . BASE_URL . "/admin/movies.php");
        exit();
    }
    $movie = array_merge($movie, $existing);
}

// Fetch all available categories
$categories = $db->query("SELECT * FROM `categories` ORDER BY `name` ASC")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'CSRF validation failed. Please refresh and resubmit.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if (empty($slug) && !empty($title)) {
            $slug = slugify($title);
        } else {
            $slug = slugify($slug);
        }

        $categoryId = (int)($_POST['category_id'] ?? 1);
        $shortDesc = trim($_POST['short_description'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $genre = trim($_POST['genre'] ?? '');
        $language = trim($_POST['language'] ?? 'English');
        $releaseYear = (int)($_POST['release_year'] ?? date('Y'));
        $duration = trim($_POST['duration'] ?? '120 min');
        $rating = (float)($_POST['rating'] ?? 8.0);
        $tags = trim($_POST['tags'] ?? '');
        $trailerUrl = trim($_POST['trailer_url'] ?? '');
        $watchUrl = trim($_POST['watch_url'] ?? '');
        $downloadUrl = trim($_POST['download_url'] ?? '');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isPopular = isset($_POST['is_popular']) ? 1 : 0;
        $status = isset($_POST['status']) ? 1 : 0;

        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $metaKeywords = trim($_POST['meta_keywords'] ?? '');
        $canonicalUrl = trim($_POST['canonical_url'] ?? '');

        // Poster handling (URL or File Upload)
        $poster = trim($_POST['poster_url'] ?? '');
        if (!empty($_FILES['poster_file']['name'])) {
            $uploadRes = upload_image_file($_FILES['poster_file'], 'movies');
            if ($uploadRes['success']) {
                $poster = $uploadRes['url'];
            } else {
                $errors[] = 'Poster upload error: ' . $uploadRes['error'];
            }
        } elseif (empty($poster) && $isEditing) {
            $poster = $movie['poster'];
        }

        // Banner handling (URL or File Upload)
        $banner = trim($_POST['banner_url'] ?? '');
        if (!empty($_FILES['banner_file']['name'])) {
            $uploadRes = upload_image_file($_FILES['banner_file'], 'banners');
            if ($uploadRes['success']) {
                $banner = $uploadRes['url'];
            } else {
                $errors[] = 'Banner upload error: ' . $uploadRes['error'];
            }
        } elseif (empty($banner) && $isEditing) {
            $banner = $movie['banner'];
        }

        // Basic validations
        if (empty($title)) {
            $errors[] = 'Movie title is required.';
        }
        if (empty($poster)) {
            $errors[] = 'Movie poster is required (either via URL or local file upload).';
        }

        // Check for duplicate slug
        $chkStmt = $db->prepare("SELECT id FROM `movies` WHERE `slug` = ? AND `id` != ? LIMIT 1");
        $chkStmt->execute([$slug, $id]);
        if ($chkStmt->fetch()) {
            $slug .= '-' . rand(10, 99);
        }

        if (empty($errors)) {
            try {
                if ($isEditing) {
                    $updateSql = "UPDATE `movies` SET 
                        `title` = ?, `slug` = ?, `poster` = ?, `banner` = ?, 
                        `short_description` = ?, `description` = ?, `category_id` = ?, 
                        `genre` = ?, `language` = ?, `release_year` = ?, `duration` = ?, 
                        `rating` = ?, `tags` = ?, `trailer_url` = ?, `watch_url` = ?, 
                        `download_url` = ?, `is_featured` = ?, `is_popular` = ?, `status` = ?, 
                        `meta_title` = ?, `meta_description` = ?, `meta_keywords` = ?, 
                        `canonical_url` = ? 
                        WHERE `id` = ?";
                    $upStmt = $db->prepare($updateSql);
                    $upStmt->execute([
                        $title, $slug, $poster, $banner, 
                        $shortDesc, $description, $categoryId, 
                        $genre, $language, $releaseYear, $duration, 
                        $rating, $tags, $trailerUrl, $watchUrl, 
                        $downloadUrl, $isFeatured, $isPopular, $status, 
                        $metaTitle, $metaDescription, $metaKeywords, 
                        $canonicalUrl, $id
                    ]);
                    set_flash('success', "Movie '{$title}' updated successfully.");
                } else {
                    $insertSql = "INSERT INTO `movies` (
                        `title`, `slug`, `poster`, `banner`, 
                        `short_description`, `description`, `category_id`, 
                        `genre`, `language`, `release_year`, `duration`, 
                        `rating`, `tags`, `trailer_url`, `watch_url`, 
                        `download_url`, `is_featured`, `is_popular`, `status`, 
                        `meta_title`, `meta_description`, `meta_keywords`, 
                        `canonical_url`
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $inStmt = $db->prepare($insertSql);
                    $inStmt->execute([
                        $title, $slug, $poster, $banner, 
                        $shortDesc, $description, $categoryId, 
                        $genre, $language, $releaseYear, $duration, 
                        $rating, $tags, $trailerUrl, $watchUrl, 
                        $downloadUrl, $isFeatured, $isPopular, $status, 
                        $metaTitle, $metaDescription, $metaKeywords, 
                        $canonicalUrl
                    ]);
                    set_flash('success', "New movie '{$title}' created successfully.");
                }
                header("Location: " . BASE_URL . "/admin/movies.php");
                exit();
            } catch (Exception $e) {
                $errors[] = 'Database operation failed: ' . $e->getMessage();
            }
        }
    }
}

$adminTitle = ($isEditing ? "Edit Movie" : "Add New Movie") . " - ApkaShow";
$pageHeading = $isEditing ? "Edit Movie: " . $movie['title'] : "Add New Movie Title";

require_once __DIR__ . '/header.php';
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger mb-4">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?>
                <li><?php echo e($err); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

    <div class="row g-4">
        <!-- Main Information (Left Column) -->
        <div class="col-lg-8">
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold text-white mb-3">Primary Film Details</h5>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Movie Title *</label>
                    <input type="text" name="title" class="form-control bg-dark border-secondary text-white" value="<?php echo e($movie['title']); ?>" placeholder="e.g. The Wolf of Wall Street" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">URL Slug (leave blank to auto-generate)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-secondary small">/movie/</span>
                        <input type="text" name="slug" class="form-control bg-dark border-secondary text-white" value="<?php echo e($movie['slug']); ?>" placeholder="the-wolf-of-wall-street">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Short Synopsis / Card Teaser *</label>
                    <textarea name="short_description" class="form-control bg-dark border-secondary text-white" rows="2" placeholder="Brief 1-2 sentence hook for cards and search snippets..." required><?php echo e($movie['short_description']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Full Movie Description / About *</label>
                    <textarea name="description" class="form-control bg-dark border-secondary text-white" rows="6" placeholder="Detailed plot synopsis, themes, and background information..." required><?php echo e($movie['description']); ?></textarea>
                </div>
            </div>

            <!-- Media Streams & Legal URLs -->
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold text-white mb-3">Streaming & Legal Distribution Media</h5>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Trailer Embed / Video URL (YouTube, Vimeo, MP4)</label>
                    <input type="text" name="trailer_url" class="form-control bg-dark border-secondary text-white" value="<?php echo e($movie['trailer_url']); ?>" placeholder="https://www.youtube.com/embed/iszwuX1AK6A or watch URL">
                    <div class="form-text text-muted small">Supports YouTube links (automatically converted to player embed).</div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Legal Watch / Streaming Stream Link</label>
                    <input type="text" name="watch_url" class="form-control bg-dark border-secondary text-white" value="<?php echo e($movie['watch_url']); ?>" placeholder="https://authorized-stream.com/embed/movie-123">
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Legal Download File / Direct URL</label>
                    <input type="text" name="download_url" class="form-control bg-dark border-secondary text-white" value="<?php echo e($movie['download_url']); ?>" placeholder="https://archive.org/download/licensed-copy.mp4">
                    <div class="form-text text-muted small">Only provide links to legally authorized media or promotional downloads.</div>
                </div>
            </div>

            <!-- Technical Search Engine Optimization (SEO) -->
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-search text-danger me-2"></i>SEO & Metadata</h5>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Custom Meta Title</label>
                    <input type="text" name="meta_title" class="form-control bg-dark border-secondary text-white" value="<?php echo e($movie['meta_title']); ?>" placeholder="The Wolf of Wall Street (2013) - Full Movie Stream & Analysis">
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Meta Description</label>
                    <textarea name="meta_description" class="form-control bg-dark border-secondary text-white" rows="2" placeholder="Crawlable search engine description..."><?php echo e($movie['meta_description']); ?></textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-light small fw-bold">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control bg-dark border-secondary text-white" value="<?php echo e($movie['meta_keywords']); ?>" placeholder="wall street, stocks, millionaire, movie">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-light small fw-bold">Canonical URL (optional)</label>
                        <input type="text" name="canonical_url" class="form-control bg-dark border-secondary text-white" value="<?php echo e($movie['canonical_url']); ?>" placeholder="https://example.com/movie/slug">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side Settings & Poster Upload -->
        <div class="col-lg-4">
            <!-- Publishing & Status -->
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold text-white mb-3">Publishing Control</h5>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" <?php echo ($movie['status'] == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label text-light" for="statusSwitch">Published / Active</label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_featured" id="featuredSwitch" <?php echo ($movie['is_featured'] == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label text-light" for="featuredSwitch">Mark as Featured</label>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="is_popular" id="popularSwitch" <?php echo ($movie['is_popular'] == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label text-light" for="popularSwitch">Mark as Popular</label>
                </div>

                <button type="submit" class="btn btn-cinema w-100 py-2 mb-2">
                    <i class="bi bi-cloud-check-fill me-1"></i> <?php echo $isEditing ? 'Save Changes' : 'Publish Movie'; ?>
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/movies.php" class="btn btn-outline-secondary w-100">Cancel</a>
            </div>

            <!-- Category & Genres -->
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold text-white mb-3">Classification</h5>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Primary Category *</label>
                    <select name="category_id" class="form-select bg-dark border-secondary text-white" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($movie['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo e($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Genre(s) *</label>
                    <input type="text" name="genre" class="form-control bg-dark border-secondary text-white" value="<?php echo e($movie['genre']); ?>" placeholder="Action, Thriller, Drama" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Language *</label>
                    <input type="text" name="language" class="form-control bg-dark border-secondary text-white" value="<?php echo e($movie['language']); ?>" placeholder="English, Hindi, etc." required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label text-light small fw-bold">Year</label>
                        <input type="number" name="release_year" class="form-control bg-dark border-secondary text-white" value="<?php echo e($movie['release_year']); ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-light small fw-bold">Duration</label>
                        <input type="text" name="duration" class="form-control bg-dark border-secondary text-white" value="<?php echo e($movie['duration']); ?>" placeholder="125 min" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Rating (out of 10)</label>
                    <input type="number" step="0.1" min="1" max="10" name="rating" class="form-control bg-dark border-secondary text-white" value="<?php echo e($movie['rating']); ?>">
                </div>

                <div class="mb-0">
                    <label class="form-label text-light small fw-bold">Tags (comma-separated)</label>
                    <input type="text" name="tags" class="form-control bg-dark border-secondary text-white" value="<?php echo e($movie['tags']); ?>" placeholder="forex, trading, motivation">
                </div>
            </div>

            <!-- Poster & Banner Images -->
            <div class="glass-card p-4">
                <h5 class="fw-bold text-white mb-3">Poster & Backdrop</h5>

                <!-- Poster -->
                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Poster URL</label>
                    <input type="text" name="poster_url" class="form-control bg-dark border-secondary text-white mb-2" value="<?php echo e($movie['poster']); ?>" placeholder="https://... or upload below">
                    <label class="form-label text-light small fw-bold">Or Upload Poster Image (Max 5MB)</label>
                    <input type="file" name="poster_file" class="form-control bg-dark border-secondary text-white" accept="image/*">
                    <?php if (!empty($movie['poster'])): ?>
                        <div class="mt-2 text-center">
                            <img src="<?php echo e(resolve_image_url($movie['poster'])); ?>" class="rounded shadow" style="max-height: 120px; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                </div>

                <hr class="border-secondary border-opacity-25 my-3">

                <!-- Banner -->
                <div class="mb-0">
                    <label class="form-label text-light small fw-bold">Backdrop Banner URL</label>
                    <input type="text" name="banner_url" class="form-control bg-dark border-secondary text-white mb-2" value="<?php echo e($movie['banner']); ?>" placeholder="https://... or upload below">
                    <label class="form-label text-light small fw-bold">Or Upload Banner Image</label>
                    <input type="file" name="banner_file" class="form-control bg-dark border-secondary text-white" accept="image/*">
                    <?php if (!empty($movie['banner'])): ?>
                        <div class="mt-2 text-center">
                            <img src="<?php echo e(resolve_image_url($movie['banner'], 'banner')); ?>" class="rounded shadow w-100" style="max-height: 80px; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/footer.php'; ?>
