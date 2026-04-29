<?php
$adminName = $_SESSION['admin_full_name'] ?? 'Admin';
$adminRole = $_SESSION['admin_role'] ?? 'Admin';
$adminAvatar = $_SESSION['admin_avatar'] ?? '';
$adminInitial = strtoupper(substr($adminName, 0, 1));
$adminSubtitle = $adminSubtitle ?? 'Manage jobs, clicks, categories, and content.';
$siteSettings = $siteSettings ?? (isset($pdo) ? get_settings($pdo) : ['site_name' => 'Tikuse Jobs', 'site_logo' => null]);
?>
<header class="admin-topbar">
  <div class="admin-topbar-left">
    <button class="icon-btn admin-menu-toggle" id="adminMenuToggle" aria-label="Open sidebar">&#9776;</button>
    <div class="admin-topbar-logo"><?php echo brand_mark_html($siteSettings, '..'); ?></div>
    <div>
      <h1><?php echo e($pageTitle ?? 'Dashboard'); ?></h1>
      <p><?php echo e($adminSubtitle); ?></p>
    </div>
  </div>

  <div class="admin-topbar-right">
    <form class="admin-search" action="jobs.php" method="GET">
      <input type="text" name="q" placeholder="Search jobs..." value="<?php echo e($_GET['q'] ?? ''); ?>">
    </form>

    <button class="icon-btn theme-toggle" id="themeToggle" aria-label="Toggle theme">&#9728;</button>

    <div class="admin-user">
      <?php if (!empty($adminAvatar)): ?>
        <img src="../uploads/admins/<?php echo e($adminAvatar); ?>" alt="<?php echo e($adminName); ?>" class="admin-avatar-image">
      <?php else: ?>
        <div class="admin-avatar"><?php echo e($adminInitial); ?></div>
      <?php endif; ?>

      <div>
        <strong><?php echo e($adminName); ?></strong>
        <small><?php echo e(ucwords(str_replace('_', ' ', $adminRole))); ?></small>
      </div>
    </div>
  </div>
</header>
