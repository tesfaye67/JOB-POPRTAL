<?php
require_once __DIR__ . '/functions.php';

$siteSettings = $siteSettings ?? (isset($pdo) ? get_settings($pdo) : ['site_name' => 'Tikuse Jobs', 'telegram_link' => '#', 'site_logo' => null]);
$siteName = $siteSettings['site_name'] ?? 'Tikuse Jobs';
$telegramLink = $siteSettings['telegram_link'] ?: '#';
?>
<header class="site-header">
  <div class="container nav-wrap">
    <a href="index.php" class="brand">
      <?php echo brand_mark_html($siteSettings); ?>
      <span><?php echo e($siteName); ?></span>
    </a>

    <nav class="nav-links" id="navMenu">
      <a href="index.php">Home</a>
      <a href="jobs.php">Jobs</a>
      <a href="companies.php">Companies</a>
      <a href="categories.php">Categories</a>
      <a href="about.php">About</a>
      <a href="contact.php">Contact</a>
    </nav>

    <div class="nav-actions">
      <button class="icon-btn theme-toggle" id="themeToggle" aria-label="Toggle theme">&#9728;</button>
      <a href="jobs.php" class="btn btn-secondary">Browse Jobs</a>
      <a href="<?php echo e($telegramLink); ?>" class="btn btn-primary">Join Telegram</a>
      <button class="icon-btn mobile-toggle" id="mobileToggle" aria-label="Menu">&#9776;</button>
    </div>
  </div>
</header>
