<?php
$pageTitle = "Companies | Tikuse Jobs";
$bodyClass = "";

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

expire_old_jobs($pdo);
$siteSettings = get_settings($pdo);

$companies = fetch_all_safe(
    $pdo,
    "SELECT
        c.id,
        c.name,
        c.slug,
        c.logo,
        c.description,
        cc.name AS category_name,
        COUNT(j.id) AS total_jobs
     FROM companies c
     LEFT JOIN company_categories cc ON cc.id = c.company_category_id
     LEFT JOIN jobs j ON j.company_id = c.id AND " . public_job_where_sql('j') . "
     GROUP BY c.id, c.name, c.slug, c.logo, c.description, cc.name
     ORDER BY total_jobs DESC, c.name ASC"
);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <section class="page-hero compact-hero">
    <div class="container">
      <h1>Companies</h1>
      <p>Browse employers and the active jobs they currently publish.</p>
    </div>
  </section>

  <section class="section-block">
    <div class="container">
      <div class="company-grid">
        <?php foreach ($companies as $company): ?>
          <a class="company-card" href="company.php?slug=<?php echo urlencode($company['slug']); ?>">
            <div class="job-logo large-logo">
              <?php if (!empty($company['logo'])): ?>
                <img src="<?php echo e(upload_public_path('logos', $company['logo'])); ?>" alt="<?php echo e($company['name']); ?>">
              <?php else: ?>
                <?php echo e(strtoupper(substr($company['name'], 0, 1))); ?>
              <?php endif; ?>
            </div>
            <h3><?php echo e($company['name']); ?></h3>
            <p><?php echo e($company['category_name'] ?: 'Company'); ?></p>
            <span><?php echo number_format((int)$company['total_jobs']); ?> active jobs</span>
          </a>
        <?php endforeach; ?>

        <?php if (empty($companies)): ?>
          <div class="empty-public">
            <h3>No companies yet</h3>
            <p>Add companies from the admin dashboard and connect them to jobs.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
