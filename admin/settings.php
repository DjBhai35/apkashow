<?php
/**
 * CinemaVault - Admin System Settings
 * WhatsApp Number & Message, Site Branding, About & Contact Us content, Ad codes, SEO Defaults
 */
require_once __DIR__ . '/../includes/functions.php';
require_admin_auth();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Security token invalid. Please refresh.');
    } else {
        $allowedKeys = [
            'site_name',
            'site_tagline',
            'site_logo_text',
            'whatsapp_number',
            'whatsapp_message',
            'contact_email',
            'contact_phone',
            'contact_address',
            'about_hero_title',
            'about_hero_subtitle',
            'about_content',
            'header_ad_code',
            'footer_ad_code',
            'meta_title_default',
            'meta_description_default',
            'meta_keywords_default',
            'footer_copyright'
        ];

        foreach ($allowedKeys as $key) {
            if (isset($_POST[$key])) {
                set_setting($key, $_POST[$key]);
            }
        }

        set_flash('success', 'All system settings and WhatsApp configurations updated successfully.');
        header("Location: " . BASE_URL . "/admin/settings.php");
        exit();
    }
}

$settings = get_all_settings();

$adminTitle = "System & WhatsApp Settings - CinemaVault";
$pageHeading = "Platform & WhatsApp Settings";

require_once __DIR__ . '/header.php';
?>

<form method="POST" action="">
    <?php echo csrf_field(); ?>

    <div class="row g-4">
        <!-- Section 1: WhatsApp Configuration (Top Priority as requested) -->
        <div class="col-lg-6">
            <div class="glass-card p-4 mb-4 border-success border-opacity-50">
                <div class="d-flex align-items-center gap-2 mb-3 text-success">
                    <i class="bi bi-whatsapp fs-3"></i>
                    <h5 class="fw-bold text-white mb-0">WhatsApp Integration & Concierge</h5>
                </div>
                <p class="text-muted small mb-3">
                    Update the contact WhatsApp number below. Changes take effect instantly across all frontend header buttons, floating action triggers, and detail contact cards.
                </p>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">WhatsApp Phone Number *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-success"><i class="bi bi-telephone-fill"></i></span>
                        <input type="text" name="whatsapp_number" class="form-control bg-dark border-secondary text-white fw-bold" value="<?php echo e($settings['whatsapp_number'] ?? '+1234567890'); ?>" placeholder="+1234567890" required>
                    </div>
                    <div class="form-text text-muted small">Enter international format with country code (e.g. +1234567890, +919876543210).</div>
                </div>

                <div class="mb-0">
                    <label class="form-label text-light small fw-bold">Default Pre-filled WhatsApp Chat Message</label>
                    <textarea name="whatsapp_message" class="form-control bg-dark border-secondary text-white" rows="2" placeholder="Hello CinemaVault!"><?php echo e($settings['whatsapp_message'] ?? 'Hello CinemaVault!'); ?></textarea>
                </div>
            </div>

            <!-- Section 2: General Branding -->
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-palette text-danger me-2"></i>Branding & Identity</h5>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Website Name</label>
                    <input type="text" name="site_name" class="form-control bg-dark border-secondary text-white" value="<?php echo e($settings['site_name'] ?? 'CinemaVault Elite'); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Header HTML Logo Text</label>
                    <input type="text" name="site_logo_text" class="form-control bg-dark border-secondary text-white" value="<?php echo e($settings['site_logo_text'] ?? 'CINEMA<span class="text-gradient">VAULT</span>'); ?>" required>
                    <div class="form-text text-muted small">Supports HTML classes like <code>text-gradient</code> or <code>text-danger</code>.</div>
                </div>

                <div class="mb-0">
                    <label class="form-label text-light small fw-bold">Website Tagline</label>
                    <input type="text" name="site_tagline" class="form-control bg-dark border-secondary text-white" value="<?php echo e($settings['site_tagline'] ?? ''); ?>">
                </div>
            </div>

            <!-- Section 3: Ad Placements (User friendly & configurable) -->
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-badge-ad text-warning me-2"></i>Advertisement Slots</h5>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Header Advertisement Code / Banner HTML</label>
                    <textarea name="header_ad_code" class="form-control bg-dark border-secondary text-white font-monospace small" rows="3"><?php echo e($settings['header_ad_code'] ?? ''); ?></textarea>
                    <div class="form-text text-muted small">Shown below the navigation bar. Leave empty to hide.</div>
                </div>

                <div class="mb-0">
                    <label class="form-label text-light small fw-bold">Footer Advertisement Code / Banner HTML</label>
                    <textarea name="footer_ad_code" class="form-control bg-dark border-secondary text-white font-monospace small" rows="3"><?php echo e($settings['footer_ad_code'] ?? ''); ?></textarea>
                    <div class="form-text text-muted small">Shown right above the bottom footer. Leave empty to hide.</div>
                </div>
            </div>
        </div>

        <!-- Section 4: Contact, About & SEO (Right Column) -->
        <div class="col-lg-6">
            <!-- Contact Channels -->
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-geo-alt text-info me-2"></i>Contact Information</h5>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Contact Email</label>
                    <input type="email" name="contact_email" class="form-control bg-dark border-secondary text-white" value="<?php echo e($settings['contact_email'] ?? 'contact@cinemavault.com'); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Contact Phone Number</label>
                    <input type="text" name="contact_phone" class="form-control bg-dark border-secondary text-white" value="<?php echo e($settings['contact_phone'] ?? '+1 (800) 555-VAULT'); ?>">
                </div>

                <div class="mb-0">
                    <label class="form-label text-light small fw-bold">Studio Physical Address</label>
                    <textarea name="contact_address" class="form-control bg-dark border-secondary text-white" rows="2"><?php echo e($settings['contact_address'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- About Page Content -->
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-info-circle text-primary me-2"></i>About Us Content Editor</h5>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">About Hero Headline</label>
                    <input type="text" name="about_hero_title" class="form-control bg-dark border-secondary text-white" value="<?php echo e($settings['about_hero_title'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">About Hero Subtitle</label>
                    <textarea name="about_hero_subtitle" class="form-control bg-dark border-secondary text-white" rows="2"><?php echo e($settings['about_hero_subtitle'] ?? ''); ?></textarea>
                </div>

                <div class="mb-0">
                    <label class="form-label text-light small fw-bold">Full About Body HTML</label>
                    <textarea name="about_content" class="form-control bg-dark border-secondary text-white font-monospace small" rows="5"><?php echo e($settings['about_content'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Technical SEO Defaults -->
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold text-white mb-3"><i class="bi bi-search text-danger me-2"></i>Global SEO Metadata Defaults</h5>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Default Page Meta Title</label>
                    <input type="text" name="meta_title_default" class="form-control bg-dark border-secondary text-white" value="<?php echo e($settings['meta_title_default'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Default Meta Description</label>
                    <textarea name="meta_description_default" class="form-control bg-dark border-secondary text-white" rows="2"><?php echo e($settings['meta_description_default'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Default Meta Keywords</label>
                    <input type="text" name="meta_keywords_default" class="form-control bg-dark border-secondary text-white" value="<?php echo e($settings['meta_keywords_default'] ?? ''); ?>">
                </div>

                <div class="mb-0">
                    <label class="form-label text-light small fw-bold">Footer Copyright Notice</label>
                    <input type="text" name="footer_copyright" class="form-control bg-dark border-secondary text-white" value="<?php echo e($settings['footer_copyright'] ?? ''); ?>">
                </div>
            </div>

            <!-- Save Action Button -->
            <button type="submit" class="btn btn-cinema btn-lg w-100 py-3 shadow-lg">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i> Save All Platform Settings
            </button>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/footer.php'; ?>
