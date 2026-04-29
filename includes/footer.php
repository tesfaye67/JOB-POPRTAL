<?php
require_once __DIR__ . '/functions.php';

$siteSettings = $siteSettings ?? (isset($pdo) ? get_settings($pdo) : ['site_name' => 'Tikuse Jobs', 'telegram_link' => '#']);
$siteName = $siteSettings['site_name'] ?? 'Tikuse Jobs';
$telegramLink = $siteSettings['telegram_link'] ?: '#';
$footerAbout = $siteSettings['footer_about'] ?: 'Your global-style job search partner. Find. Apply. Succeed.';
?>
<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <div class="brand footer-brand">
        <?php echo brand_mark_html($siteSettings); ?>
        <span><?php echo e($siteName); ?></span>
      </div>
      <p class="footer-text"><?php echo e($footerAbout); ?></p>
    </div>

    <div>
      <h4>Quick Links</h4>
      <a href="index.php">Home</a>
      <a href="jobs.php">Jobs</a>
      <a href="categories.php">Categories</a>
      <a href="companies.php">Companies</a>
    </div>

    <div>
      <h4>For Job Seekers</h4>
      <a href="jobs.php">Browse Jobs</a>
      <a href="jobs.php?location=Remote">Remote Jobs</a>
      <a href="jobs.php?type=Internship">Internships</a>
      <a href="contact.php">Request Support</a>
    </div>

    <div>
      <h4>Company</h4>
      <a href="about.php">About Us</a>
      <a href="contact.php">Contact</a>
      <a href="privacy-policy.php">Privacy Policy</a>
      <a href="terms.php">Terms of Service</a>
    </div>
  </div>

  <div class="container footer-bottom">
    <p>&copy; <?php echo date('Y'); ?> <?php echo e($siteName); ?>. All rights reserved.</p>
    <a href="<?php echo e($telegramLink); ?>" class="btn btn-primary btn-small">Join Telegram</a>
  </div>
</footer>

<script src="assets/js/script.js"></script>
</body>
</html>
