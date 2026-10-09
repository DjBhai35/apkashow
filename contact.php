<?php
/**
 * ApkaShow - Contact Us Page
 * Includes dynamic WhatsApp button, contact details from Admin Panel, and contact form
 * Domain: apkashow.com
 */
require_once __DIR__ . '/includes/functions.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errorMsg = 'Security validation failed (CSRF). Please refresh and try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($message)) {
            $errorMsg = 'Please fill out all required fields (Name, Email, and Message).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'Please provide a valid email address.';
        } else {
            try {
                $db = getDB();
                $stmt = $db->prepare("INSERT INTO `contact_messages` (`name`, `email`, `subject`, `message`) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $subject, $message]);
                $successMsg = 'Thank you! Your message has been safely received. The ApkaShow team will respond shortly.';
            } catch (Exception $e) {
                $errorMsg = 'Failed to submit your message. Please contact us directly via WhatsApp.';
            }
        }
    }
}

$whatsAppNumber = get_setting('whatsapp_number', '+1234567890');
$contactEmail = get_setting('contact_email', 'contact@apkashow.com');
$contactPhone = get_setting('contact_phone', '+1 (800) 555-SHOW');
$contactAddress = get_setting('contact_address', '100 Hollywood Blvd, Suite 880, Los Angeles, CA 90028');

$pageTitle = 'Contact Us & WhatsApp Concierge | ApkaShow';
$pageDescription = 'Contact ApkaShow staff for content requests, partnership inquiries, or WhatsApp direct messaging.';
$pageCanonical = canonical_url_for('/contact.php');

require_once __DIR__ . '/includes/header.php';
?>

<!-- Schema.org ContactPage & BreadcrumbList Structured Data -->
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
          "name": "Contact Us",
          "item": "<?php echo BASE_URL; ?>/contact.php"
        }
      ]
    },
    {
      "@type": "ContactPage",
      "name": <?php echo json_encode($pageTitle); ?>,
      "description": <?php echo json_encode($pageDescription); ?>,
      "url": <?php echo json_encode($pageCanonical); ?>
    }
  ]
}
</script>

<div class="container py-4">

    <!-- Contact Hero -->
    <div class="glass-card p-4 p-md-5 mb-5 text-center">
        <span class="badge bg-danger rounded-pill px-3 py-1 mb-3 text-uppercase letter-spacing-1">Direct Touchpoint</span>
        <h1 class="display-6 fw-bold text-white mb-2">Connect with ApkaShow</h1>
        <p class="text-secondary mx-auto mb-0" style="max-width: 600px;">
            Have inquiries regarding content licensing, feature additions, or private film curation? Our support channels are accessible 24/7.
        </p>
    </div>

    <div class="row g-4 mb-5">
        <!-- Left: Quick Info Cards & WhatsApp Spotlight -->
        <div class="col-lg-5">
            <!-- Dedicated WhatsApp Card (Controlled from Admin) -->
            <div class="glass-card p-4 mb-4 border-success border-opacity-25 bg-gradient">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-success text-white p-3 rounded-circle fs-3 shadow">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-white mb-0">Instant WhatsApp Chat</h5>
                        <small class="text-success fw-semibold">Fastest Response Time (< 10 mins)</small>
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    You can message our executive administrators directly on WhatsApp for instantaneous assistance.
                </p>
                <div class="p-2 px-3 rounded bg-dark border border-secondary border-opacity-25 text-white fw-bold mb-3 d-flex justify-content-between align-items-center">
                    <span><?php echo e($whatsAppNumber); ?></span>
                    <i class="bi bi-patch-check-fill text-success"></i>
                </div>
                <a href="<?php echo e(get_whatsapp_url()); ?>" target="_blank" class="btn btn-whatsapp w-100 py-2">
                    <i class="bi bi-whatsapp me-2"></i> Start WhatsApp Conversation
                </a>
            </div>

            <!-- Other Contact Channels -->
            <div class="glass-card p-4">
                <h5 class="fw-bold text-white mb-3">Office & Concierge</h5>

                <div class="d-flex align-items-start gap-3 mb-3">
                    <i class="bi bi-envelope-at fs-4 text-info mt-1"></i>
                    <div>
                        <small class="text-muted d-block">Official Email</small>
                        <a href="mailto:<?php echo e($contactEmail); ?>" class="text-light text-decoration-none fw-semibold"><?php echo e($contactEmail); ?></a>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3 mb-3">
                    <i class="bi bi-telephone-inbound fs-4 text-warning mt-1"></i>
                    <div>
                        <small class="text-muted d-block">Concierge Telephone</small>
                        <span class="text-light fw-semibold"><?php echo e($contactPhone); ?></span>
                    </div>
                </div>

                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-geo-alt fs-4 text-danger mt-1"></i>
                    <div>
                        <small class="text-muted d-block">Studio Headquarters</small>
                        <span class="text-light fw-semibold"><?php echo e($contactAddress); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Contact Form -->
        <div class="col-lg-7">
            <div class="glass-card p-4 p-md-5">
                <h3 class="fw-bold text-white mb-2">Send Us an Inquiry</h3>
                <p class="text-muted small mb-4">Leave your message below and our support team will reach you via email.</p>

                <?php if (!empty($successMsg)): ?>
                    <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 text-success mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i> <?php echo e($successMsg); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger mb-4">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i> <?php echo e($errorMsg); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo BASE_URL; ?>/contact.php">
                    <?php echo csrf_field(); ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-bold">Your Name *</label>
                            <input type="text" name="name" class="form-control bg-dark border-secondary text-white" placeholder="Johnathan Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-bold">Your Email Address *</label>
                            <input type="email" name="email" class="form-control bg-dark border-secondary text-white" placeholder="john@example.com" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-light small fw-bold">Subject</label>
                            <input type="text" name="subject" class="form-control bg-dark border-secondary text-white" placeholder="Movie Suggestion / Inquiry / Copyright">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-light small fw-bold">Message Details *</label>
                            <textarea name="message" rows="5" class="form-control bg-dark border-secondary text-white" placeholder="Please elaborate your message..." required></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-cinema px-4 py-2">
                                <i class="bi bi-send-fill me-1"></i> Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
