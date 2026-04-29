<?php
$pageTitle = "Categories | Tikuse Jobs";
$bodyClass = "";

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

expire_old_jobs($pdo);
$siteSettings = get_settings($pdo);

$categories = fetch_all_safe(
    $pdo,
    "SELECT
        f.id,
        f.name,
        COUNT(j.id) AS total_jobs,
        MAX(COALESCE(j.posted_date, DATE(j.created_at))) AS latest_post
     FROM fields_of_study f
     LEFT JOIN jobs j ON j.field_id = f.id AND " . public_job_where_sql('j') . "
     GROUP BY f.id, f.name
     ORDER BY total_jobs DESC, f.name ASC"
);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <section class="page-hero compact-hero">
    <div class="container">
      <h1>Job Categories</h1>
      <p>Choose a field of study and browse matching active jobs.</p>
    </div>
  </section>

  <section class="section-block">
    <div class="container">
      <div class="card-grid-4">
        <?php foreach ($categories as $category): ?>
          <a class="category-card category-card-wide" href="jobs.php?field=<?php echo (int)$category['id']; ?>">
            <div class="category-icon">#</div>
            <h3><?php echo e($category['name']); ?></h3>
            <span><?php echo number_format((int)$category['total_jobs']); ?> active jobs</span>
            <?php if (!empty($category['latest_post'])): ?>
              <small>Latest: <?php echo e($category['latest_post']); ?></small>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>

        <?php if (empty($categories)): ?>
          <div class="empty-public">
            <h3>No categories yet</h3>
            <p>Add categories from the admin dashboard to organize your jobs.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
