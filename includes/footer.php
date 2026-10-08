<?php
/**
 * CinemaVault - Footer Template
 */
$footerAd = get_setting('footer_ad_code', '');
$footerCopyright = get_setting('footer_copyright', '&copy; 2026 CinemaVault Elite Media. All rights reserved.');
$whatsAppUrl = get_whatsapp_url();
$siteName = get_setting('site_name', 'CinemaVault Elite');
$siteLogoText = get_setting('site_logo_text', 'CINEMA<span class="text-gradient">VAULT</span>');
?>
</main> <!-- /main -->

<!-- Footer Ad Slot (Admin Managed) -->
<?php if (!empty($footerAd)): ?>
    <div class="container my-4">
        <?php echo $footerAd; ?>
    </div>
<?php endif; ?>

<!-- Floating WhatsApp Action Button for Mobile & Desktop -->
<a href="<?php echo e($whatsAppUrl); ?>" target="_blank" rel="noopener noreferrer" class="floating-whatsapp" title="Instant WhatsApp Support">
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
                <p class="text-muted small pe-lg-4">
                    The premier destination for motivational cinema, billionaire documentaries, forex trading thrillers, and blockbuster entertainment. Curated exclusively for ambitious high-performers.
                </p>
                <div class="d-flex gap-3 text-secondary mt-3">
                    <a href="<?php echo e($whatsAppUrl); ?>" target="_blank" class="text-muted text-hover-light fs-5"><i class="bi bi-whatsapp"></i></a>
                    <a href="#" class="text-muted text-hover-light fs-5"><i class="bi bi-telegram"></i></a>
                    <a href="#" class="text-muted text-hover-light fs-5"><i class="bi bi-youtube"></i></a>
                    <a href="#" class="text-muted text-hover-light fs-5"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="text-muted text-hover-light fs-5"><i class="bi bi-instagram"></i></a>
                </div>
            </div>

            <!-- Curated Sections -->
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="text-white fw-bold mb-3 text-uppercase letter-spacing-1">Themes</h6>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=millionaire" class="footer-link">Millionaire</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=billionaire" class="footer-link">Billionaire</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=mindset" class="footer-link">Mindset</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=business" class="footer-link">Business</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=forex" class="footer-link">Forex & Trading</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=motivational" class="footer-link">Motivational</a></li>
                </ul>
            </div>

            <!-- Popular Genres -->
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="text-white fw-bold mb-3 text-uppercase letter-spacing-1">Genres</h6>
                <ul class="list-unstyled">
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=action" class="footer-link">Action</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=thriller" class="footer-link">Thriller</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=bollywood" class="footer-link">Bollywood</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=hindi" class="footer-link">Hindi Dubbed</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=comedy" class="footer-link">Comedy</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/category.php?slug=romantic" class="footer-link">Romantic</a></li>
                </ul>
            </div>

            <!-- Quick Links & Contact -->
            <div class="col-lg-4 col-md-6">
                <h6 class="text-white fw-bold mb-3 text-uppercase letter-spacing-1">Direct Assistance</h6>
                <p class="text-muted small mb-2">Have a question or content inquiry? Reach our concierge directly:</p>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-whatsapp text-success fs-5"></i>
                    <a href="<?php echo e($whatsAppUrl); ?>" target="_blank" class="text-light text-decoration-none fw-semibold">
                        <?php echo e(get_setting('whatsapp_number', '+1234567890')); ?>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-envelope text-info fs-5"></i>
                    <span class="text-muted small"><?php echo e(get_setting('contact_email', 'contact@cinemavault.com')); ?></span>
                </div>
                <div class="d-flex flex-wrap gap-2 pt-1">
                    <a href="<?php echo BASE_URL; ?>/about.php" class="btn btn-outline-secondary btn-sm">About Us</a>
                    <a href="<?php echo BASE_URL; ?>/contact.php" class="btn btn-outline-secondary btn-sm">Contact Us</a>
                    <a href="<?php echo BASE_URL; ?>/sitemap.xml" target="_blank" class="btn btn-outline-secondary btn-sm">XML Sitemap</a>
                </div>
            </div>
        </div>

        <hr class="border-secondary border-opacity-25 my-4">

        <!-- Copyright & Disclaimers -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 text-muted small">
            <div><?php echo $footerCopyright; ?></div>
            <div class="d-flex gap-3">
                <a href="<?php echo BASE_URL; ?>/about.php" class="text-muted text-decoration-none">Privacy & Terms</a>
                <a href="<?php echo BASE_URL; ?>/contact.php" class="text-muted text-decoration-none">Legal & DMCA</a>
                <a href="<?php echo BASE_URL; ?>/admin/login.php" class="text-muted text-decoration-none"><i class="bi bi-shield-lock"></i> Staff Login</a>
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
