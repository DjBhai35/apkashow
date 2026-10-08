<?php
/**
 * ApkaShow - Footer Template
 * Production Domain: apkashow.com
 */
$footerAd = get_setting('footer_ad_code', '');
$footerCopyright = get_setting('footer_copyright', '&copy; 2026 ApkaShow Elite Media. All rights reserved.');
$whatsAppUrl = get_whatsapp_url();
$siteName = get_setting('site_name', 'ApkaShow');
$siteLogoText = get_setting('site_logo_text', 'APKA<span class="text-gradient">SHOW</span>');
?>
</main> <!-- /main -->

<!-- Footer Ad Slot (Admin Managed) -->
<?php if (!empty($footerAd)): ?>
    <div class="container my-4">
        <?php echo $footerAd; ?>
    </div>
<?php endif; ?>

<!-- Floating WhatsApp Action Button for Mobile & Desktop -->
<a href="<?php echo e($whatsAppUrl); ?>" target="_blank" rel="noopener noreferrer" class="floating-whatsapp" title="Instant ApkaShow WhatsApp Support">
    <i class="bi bi-whatsapp"></i>
</a>

<!-- Premium Dark Footer -->
<footer class="footer-cinematic mt-5">
    <div class="container">
        <div class="row g-4 mb-4">
            <!-- Brand & Mission Column -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="fs-3">🎬</span>
                    <span class="brand-font fs-4 fw-bold text-white"><?php echo $siteLogoText; ?></span>
                </div>
                <p class="footer-text-muted small pe-lg-4">
                    The premier destination for motivational cinema, billionaire documentaries, forex trading thrillers, and blockbuster entertainment. Curated exclusively for ambitious high-performers on ApkaShow.
                </p>
                <div class="d-flex gap-3 mt-3">
                    <a href="<?php echo e($whatsAppUrl); ?>" target="_blank" class="text-light text-opacity-75 fs-5 footer-sublink" title="WhatsApp"><i class="bi bi-whatsapp text-success"></i></a>
                    <a href="#" class="text-light text-opacity-75 fs-5 footer-sublink" title="Telegram"><i class="bi bi-telegram text-info"></i></a>
                    <a href="#" class="text-light text-opacity-75 fs-5 footer-sublink" title="YouTube"><i class="bi bi-youtube text-danger"></i></a>
                    <a href="#" class="text-light text-opacity-75 fs-5 footer-sublink" title="Twitter / X"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-light text-opacity-75 fs-5 footer-sublink" title="Instagram"><i class="bi bi-instagram text-warning"></i></a>
                </div>
            </div>

            <!-- Curated Sections -->
            <?php
            // Query active categories if not already present
            if (!isset($navCategories) || empty($navCategories)) {
                try {
                    $db = getDB();
                    $navCategories = $db->query("SELECT * FROM `categories` WHERE `status` = 1 ORDER BY `display_order` ASC, `name` ASC")->fetchAll();
                } catch(Exception $e) { $navCategories = []; }
            }
            $footerCatsBySlug = [];
            foreach ($navCategories as $nc) {
                $footerCatsBySlug[$nc['slug']] = $nc;
            }
            ?>
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="footer-heading mb-3 text-uppercase letter-spacing-1">Themes</h6>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=millionaire" class="footer-link"><?php echo e($footerCatsBySlug['millionaire']['name'] ?? 'Millionaire Movies'); ?></a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=billionaire" class="footer-link"><?php echo e($footerCatsBySlug['billionaire']['name'] ?? 'Billionaire Movies'); ?></a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=mindset" class="footer-link"><?php echo e($footerCatsBySlug['mindset']['name'] ?? 'Mindset Movies'); ?></a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=business" class="footer-link"><?php echo e($footerCatsBySlug['business']['name'] ?? 'Business Movies'); ?></a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=forex" class="footer-link"><?php echo e($footerCatsBySlug['forex']['name'] ?? 'Forex Movies'); ?></a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=motivational" class="footer-link"><?php echo e($footerCatsBySlug['motivational']['name'] ?? 'Motivational Movies'); ?></a></li>
                </ul>
            </div>

            <!-- Popular Genres -->
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="footer-heading mb-3 text-uppercase letter-spacing-1">Genres</h6>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=action" class="footer-link"><?php echo e($footerCatsBySlug['action']['name'] ?? 'Action Movies'); ?></a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=thriller" class="footer-link"><?php echo e($footerCatsBySlug['thriller']['name'] ?? 'Thriller Movies'); ?></a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=bollywood" class="footer-link"><?php echo e($footerCatsBySlug['bollywood']['name'] ?? 'Bollywood Movies'); ?></a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=hindi" class="footer-link"><?php echo e($footerCatsBySlug['hindi']['name'] ?? 'Hindi Movies'); ?></a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=comedy" class="footer-link"><?php echo e($footerCatsBySlug['comedy']['name'] ?? 'Comedy Movies'); ?></a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=romantic" class="footer-link"><?php echo e($footerCatsBySlug['romantic']['name'] ?? 'Romantic Movies'); ?></a></li>
                </ul>
            </div>

            <!-- Quick Links & Contact -->
            <div class="col-lg-4 col-md-6">
                <h6 class="footer-heading mb-3 text-uppercase letter-spacing-1">Direct Assistance</h6>
                <p class="footer-text-muted mb-2">Have a question or content inquiry? Reach our concierge directly:</p>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-whatsapp text-success fs-5"></i>
                    <a href="<?php echo e($whatsAppUrl); ?>" target="_blank" class="footer-contact-link fw-semibold">
                        <?php echo e(get_setting('whatsapp_number', '+1234567890')); ?>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-envelope text-info fs-5"></i>
                    <a href="mailto:<?php echo e(get_setting('contact_email', 'contact@apkashow.com')); ?>" class="footer-contact-link">
                        <?php echo e(get_setting('contact_email', 'contact@apkashow.com')); ?>
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-2 pt-1">
                    <a href="<?php echo BASE_URL; ?>/about.php" class="btn btn-outline-light btn-sm px-3 footer-action-btn">About Us</a>
                    <a href="<?php echo BASE_URL; ?>/contact.php" class="btn btn-outline-light btn-sm px-3 footer-action-btn">Contact Us</a>
                    <a href="<?php echo BASE_URL; ?>/sitemap.xml" target="_blank" class="btn btn-outline-light btn-sm px-3 footer-action-btn">XML Sitemap</a>
                </div>
            </div>
        </div>

        <hr class="footer-divider my-4">

        <!-- Copyright & Disclaimers -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 footer-bottom-text small">
            <div><?php echo $footerCopyright; ?></div>
            <div class="d-flex gap-3">
                <a href="<?php echo BASE_URL; ?>/about.php" class="footer-sublink">Privacy & Terms</a>
                <a href="<?php echo BASE_URL; ?>/contact.php" class="footer-sublink">Legal & DMCA</a>
                <a href="<?php echo BASE_URL; ?>/admin/login.php" class="footer-sublink"><i class="bi bi-shield-lock"></i> Staff Login</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3.3 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Custom Cinematic Javascript -->
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>

</body>
</html>
