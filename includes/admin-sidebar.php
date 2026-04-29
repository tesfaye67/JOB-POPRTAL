<?php
$siteSettings = $siteSettings ?? (isset($pdo) ? get_settings($pdo) : ['site_name' => 'Tikuse Jobs', 'site_logo' => null]);
$siteName = $siteSettings['site_name'] ?? 'Tikuse Jobs';
$currentAdminPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$adminLinks = [
    ['href' => 'dashboard.php', 'label' => 'Dashboard', 'match' => ['dashboard.php']],
    ['href' => 'jobs.php', 'label' => 'Jobs', 'match' => ['jobs.php', 'edit-job.php', 'delete-job.php']],
    ['href' => 'add-job.php', 'label' => 'Add New Job', 'match' => ['add-job.php']],
    ['href' => 'categories.php', 'label' => 'Categories', 'match' => ['categories.php']],
    ['href' => 'companies.php', 'label' => 'Companies', 'match' => ['companies.php']],
    ['href' => 'applications.php', 'label' => 'Applications', 'match' => ['applications.php']],
    ['href' => 'analytics.php', 'label' => 'Clicks Analytics', 'match' => ['analytics.php']],
    ['href' => 'users.php', 'label' => 'Users', 'match' => ['users.php']],
    ['href' => 'pages.php', 'label' => 'Pages', 'match' => ['pages.php']],
    ['href' => 'settings.php', 'label' => 'Settings', 'match' => ['settings.php']],
];
?>
<aside class="admin-sidebar" id="adminSidebar">
  <div class="admin-brand-row">
    <a href="dashboard.php" class="brand admin-brand">
      <?php echo brand_mark_html($siteSettings, '..'); ?>
      <span><?php echo e($siteName); ?></span>
    </a>

    <button class="icon-btn admin-close-btn" id="adminCloseBtn" aria-label="Close sidebar">X</button>
  </div>

  <nav class="admin-menu">
    <?php foreach ($adminLinks as $link): ?>
      <a class="<?php echo in_array($currentAdminPage, $link['match'], true) ? 'active' : ''; ?>" href="<?php echo e($link['href']); ?>">
        <span><?php echo e($link['label']); ?></span>
      </a>
    <?php endforeach; ?>
    <a href="logout.php" class="danger-link"><span>Logout</span></a>
  </nav>
</aside>
