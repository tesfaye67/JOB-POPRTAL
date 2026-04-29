<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

expire_old_jobs($pdo);
$siteSettings = get_settings($pdo);

$slug = trim((string)($_GET['slug'] ?? ''));
$id = (int)($_GET['id'] ?? $_GET['company'] ?? 0);

$params = [];
$identifierSql = '';
if ($slug !== '') {
    $identifierSql = 'slug = :slug';
    $params['slug'] = $slug;
} elseif ($id > 0) {
    $identifierSql = 'id = :id';
    $params['id'] = $id;
} else {
    header('Location: companies.php');
    exit;
}

$company = fetch_row_safe($pdo, "SELECT * FROM companies WHERE $identifierSql LIMIT 1", $params);

if (!$company) {
    header('Location: companies.php');
    exit;
}

$pageTitle = $company['name'] . " Jobs | Tikuse Jobs";
$bodyClass = "";

$jobs = fetch_all_safe(
    $pdo,
    "SELECT j.*, COALESCE(f.name, 'General') AS field_name
     FROM jobs j
     LEFT JOIN fields_of_study f ON f.id = j.field_id
     WHERE " . public_job_where_sql('j') . "
       AND j.company_id = :company_id
     ORDER BY j.is_featured DESC, COALESCE(j.posted_date, DATE(j.created_at)) DESC",
    ['company_id' => $company['id']]
);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <section class="page-hero compact-hero">
    <div class="container company-profile-hero">
      <div class="job-logo large-logo">
        <?php if (!empty($company['logo'])): ?>
          <img src="<?php echo e(upload_public_path('logos', $company['logo'])); ?>" alt="<?php echo e($company['name']); ?>">
        <?php else: ?>
          <?php echo e(strtoupper(substr($company['name'], 0, 1))); ?>
        <?php endif; ?>
      </div>
      <div>
        <h1><?php echo e($company['name']); ?></h1>
        <p><?php echo e($company['description'] ?: 'Active jobs from this company are listed below.'); ?></p>
      </div>
    </div>
  </section>

  <section class="section-block">
    <div class="container">
      <div class="section-title-row">
        <div>
          <h2><?php echo number_format(count($jobs)); ?> Active Jobs</h2>
          <p>Open roles from <?php echo e($company['name']); ?></p>
        </div>
        <a href="jobs.php?company=<?php echo (int)$company['id']; ?>" class="btn btn-secondary btn-small">Use Filters</a>
      </div>

      <div class="jobs-list">
        <?php foreach ($jobs as $job): ?>
          <article class="job-list-card">
            <div class="job-logo">
              <?php if (!empty($company['logo'])): ?>
                <img src="<?php echo e(upload_public_path('logos', $company['logo'])); ?>" alt="<?php echo e($company['name']); ?>">
              <?php else: ?>
                <?php echo e(strtoupper(substr($company['name'], 0, 1))); ?>
              <?php endif; ?>
            </div>
            <div>
              <h3><?php echo e($job['title']); ?></h3>
              <p><?php echo e($job['short_description']); ?></p>
              <div class="job-tags">
                <span class="pill"><?php echo e($job['location'] ?: 'Location not set'); ?></span>
                <span class="pill"><?php echo e($job['job_type']); ?></span>
                <span class="pill"><?php echo e($job['field_name']); ?></span>
              </div>
            </div>
            <div class="job-card-actions">
              <a href="job-details.php?slug=<?php echo urlencode($job['slug']); ?>" class="btn btn-primary btn-small">View Job</a>
            </div>
          </article>
        <?php endforeach; ?>

        <?php if (empty($jobs)): ?>
          <div class="empty-public">
            <h3>No active jobs</h3>
            <p>This company does not have public openings right now.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
