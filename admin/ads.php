<?php
/**
 * ApkaShow - Ad Placements & Google AdSense Management
 * Supports official policy-compliant AdSense snippets & custom verified partner ads.
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin_auth();

$db = getDB();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Security token invalid. Please refresh and try again.');
    } else {
        $placements = $_POST['ads'] ?? [];
        
        foreach ($placements as $plKey => $data) {
            $adType = in_array($data['ad_type'] ?? '', ['disabled', 'custom', 'adsense']) ? $data['ad_type'] : 'disabled';
            $status = isset($data['status']) ? 1 : 0;
            $heading = trim($data['custom_heading'] ?? '');
            $subheading = trim($data['custom_subheading'] ?? '');
            $url = trim($data['custom_url'] ?? '');
            $btnText = trim($data['custom_button_text'] ?? 'Learn More');
            $whatsapp = trim($data['custom_whatsapp'] ?? '');
            $adsenseCode = trim($data['adsense_code'] ?? '');
            $customImage = trim($data['custom_image_url'] ?? '');

            // Handle file upload for custom image if provided
            if (!empty($_FILES['ads']['name'][$plKey]['custom_image_file'])) {
                $file = [
                    'name'     => $_FILES['ads']['name'][$plKey]['custom_image_file'],
                    'type'     => $_FILES['ads']['type'][$plKey]['custom_image_file'] ?? '',
                    'tmp_name' => $_FILES['ads']['tmp_name'][$plKey]['custom_image_file'],
                    'error'    => $_FILES['ads']['error'][$plKey]['custom_image_file'],
                    'size'     => $_FILES['ads']['size'][$plKey]['custom_image_file']
                ];
                $upRes = upload_image_file($file, 'banners');
                if ($upRes['success']) {
                    $customImage = $upRes['url'];
                }
            }

            // Update database record
            $upStmt = $db->prepare("UPDATE `advertisements` SET 
                `ad_type` = ?,
                `status` = ?,
                `custom_heading` = ?,
                `custom_subheading` = ?,
                `custom_image` = ?,
                `custom_url` = ?,
                `custom_button_text` = ?,
                `custom_whatsapp` = ?,
                `adsense_code` = ?
                WHERE `placement` = ?");
            $upStmt->execute([
                $adType,
                $status,
                $heading,
                $subheading,
                $customImage,
                $url,
                $btnText,
                $whatsapp,
                $adsenseCode,
                $plKey
            ]);
        }

        set_flash('success', 'Ad placements and AdSense settings updated successfully.');
        header("Location: " . BASE_URL . "/admin/ads.php");
        exit();
    }
}

// Fetch all ad placements
$adList = $db->query("SELECT * FROM `advertisements` ORDER BY `id` ASC")->fetchAll();

$adminTitle = "Ad Placements & AdSense Manager - ApkaShow";
$pageHeading = "Ad Placements & AdSense Configuration";

require_once __DIR__ . '/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold text-white mb-1"><i class="bi bi-badge-ad text-danger me-2"></i>Monetization & Ad Placement Manager</h4>
        <p class="text-secondary small mb-0">Configure policy-conscious ad zones across the site. Supports official Google AdSense code units and custom verified sponsor cards.</p>
    </div>
</div>

<div class="alert alert-dark border-secondary border-opacity-50 small text-secondary mb-4 d-flex align-items-center">
    <i class="bi bi-shield-check text-success fs-3 me-3"></i>
    <div>
        <strong class="text-white">Google AdSense Policy Compatibility:</strong> All ad placements are strictly labeled as "ADVERTISEMENT" or "SPONSORED SHOWCASE". No deceptive click triggers, pop-ups, or fake buttons are permitted. Paste official Google AdSense code units without alteration.
    </div>
</div>

<form method="POST" action="" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

    <div class="d-flex flex-column gap-4 mb-4">
        <?php foreach ($adList as $ad): ?>
            <?php 
                $plKey = $ad['placement'];
                $isAdsense = ($ad['ad_type'] === 'adsense');
                $isCustom = ($ad['ad_type'] === 'custom');
                $isDisabled = ($ad['ad_type'] === 'disabled' || empty($ad['status']));
            ?>
            <div class="glass-card p-4 border border-secondary border-opacity-25 position-relative">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 pb-3 border-bottom border-secondary border-opacity-25">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-danger rounded-pill px-3 py-1 text-uppercase"><?php echo e($ad['placement']); ?></span>
                            <h5 class="fw-bold text-white mb-0"><?php echo e($ad['title']); ?></h5>
                        </div>
                        <small class="text-secondary">Position: <code>render_ad_placement('<?php echo e($ad['placement']); ?>')</code></small>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="ads[<?php echo $plKey; ?>][status]" id="status_<?php echo $plKey; ?>" <?php echo ($ad['status'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label text-light small fw-bold" for="status_<?php echo $plKey; ?>">Enable Placement</label>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Choose Ad Type -->
                    <div class="col-md-3">
                        <label class="form-label text-light small fw-bold">Ad Provider / Format *</label>
                        <select name="ads[<?php echo $plKey; ?>][ad_type]" class="form-select bg-dark border-secondary text-white" onchange="toggleAdFields('<?php echo $plKey; ?>', this.value)">
                            <option value="disabled" <?php echo ($ad['ad_type'] === 'disabled') ? 'selected' : ''; ?>>Disabled / Hidden</option>
                            <option value="adsense" <?php echo ($ad['ad_type'] === 'adsense') ? 'selected' : ''; ?>>Google AdSense (Official Script)</option>
                            <option value="custom" <?php echo ($ad['ad_type'] === 'custom') ? 'selected' : ''; ?>>Custom Sponsor / Banner Card</option>
                        </select>
                        <small class="text-muted d-block mt-2">Select Google AdSense or upload custom promotional artwork.</small>
                    </div>

                    <!-- Google AdSense Code Box -->
                    <div class="col-md-9 ad-type-adsense-<?php echo $plKey; ?>" style="<?php echo ($ad['ad_type'] === 'adsense') ? '' : 'display:none;'; ?>">
                        <label class="form-label text-light small fw-bold">
                            <i class="bi bi-google text-warning me-1"></i> Paste Official Google AdSense Code Unit
                        </label>
                        <textarea name="ads[<?php echo $plKey; ?>][adsense_code]" class="form-control bg-dark border-secondary text-white font-monospace small" rows="5" placeholder="<script async src='https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-...' crossorigin='anonymous'></script>&#10;<ins class='adsbygoogle' style='display:block' data-ad-client='ca-pub-...' data-ad-slot='...' data-ad-format='auto'></ins>&#10;<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>"><?php echo htmlspecialchars($ad['adsense_code'] ?? '', ENT_QUOTES); ?></textarea>
                        <div class="form-text text-muted small">Paste your responsive AdSense unit snippet directly as provided in your Google AdSense account. No modifications will be made to the code.</div>
                    </div>

                    <!-- Custom Promotional Card Details -->
                    <div class="col-md-9 ad-type-custom-<?php echo $plKey; ?>" style="<?php echo ($ad['ad_type'] === 'custom') ? '' : 'display:none;'; ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-light small fw-bold">Custom Heading *</label>
                                <input type="text" name="ads[<?php echo $plKey; ?>][custom_heading]" class="form-control bg-dark border-secondary text-white" value="<?php echo e($ad['custom_heading'] ?? ''); ?>" placeholder="e.g. Exclusive Forex Trading Masterclass">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light small fw-bold">Custom Subtitle</label>
                                <input type="text" name="ads[<?php echo $plKey; ?>][custom_subheading]" class="form-control bg-dark border-secondary text-white" value="<?php echo e($ad['custom_subheading'] ?? ''); ?>" placeholder="e.g. Learn high-frequency currency trading from verified titans">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light small fw-bold">Destination URL (optional)</label>
                                <input type="text" name="ads[<?php echo $plKey; ?>][custom_url]" class="form-control bg-dark border-secondary text-white" value="<?php echo e($ad['custom_url'] ?? ''); ?>" placeholder="https://partner-brand.com/promo">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light small fw-bold">Action Button Label</label>
                                <input type="text" name="ads[<?php echo $plKey; ?>][custom_button_text]" class="form-control bg-dark border-secondary text-white" value="<?php echo e($ad['custom_button_text'] ?? 'Learn More'); ?>" placeholder="Learn More">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light small fw-bold">Direct WhatsApp Destination (optional)</label>
                                <input type="text" name="ads[<?php echo $plKey; ?>][custom_whatsapp]" class="form-control bg-dark border-secondary text-white" value="<?php echo e($ad['custom_whatsapp'] ?? ''); ?>" placeholder="+1234567890">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light small fw-bold">Image URL or Upload Below</label>
                                <input type="text" name="ads[<?php echo $plKey; ?>][custom_image_url]" class="form-control bg-dark border-secondary text-white mb-2" value="<?php echo e($ad['custom_image'] ?? ''); ?>" placeholder="https://... or upload">
                                <input type="file" name="ads[<?php echo $plKey; ?>][custom_image_file]" class="form-control bg-dark border-secondary text-white" accept="image/*">
                                <?php if (!empty($ad['custom_image'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo e(resolve_image_url($ad['custom_image'])); ?>" class="rounded shadow-sm" style="max-height: 50px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Save Button Floating/Sticky -->
    <div class="p-3 bg-dark border border-secondary border-opacity-50 rounded-3 text-end sticky-bottom shadow-lg mb-4">
        <button type="submit" class="btn btn-cinema px-5 py-2">
            <i class="bi bi-cloud-check-fill me-1"></i> Save All Ad Placements
        </button>
    </div>
</form>

<script>
function toggleAdFields(plKey, val) {
    const adsenseBox = document.querySelector('.ad-type-adsense-' + plKey);
    const customBox = document.querySelector('.ad-type-custom-' + plKey);
    if (val === 'adsense') {
        if (adsenseBox) adsenseBox.style.display = 'block';
        if (customBox) customBox.style.display = 'none';
    } else if (val === 'custom') {
        if (adsenseBox) adsenseBox.style.display = 'none';
        if (customBox) customBox.style.display = 'block';
    } else {
        if (adsenseBox) adsenseBox.style.display = 'none';
        if (customBox) customBox.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
