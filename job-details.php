<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

expire_old_jobs($pdo);
$siteSettings = get_settings($pdo);

$slug = trim((string)($_GET['slug'] ?? ''));
$id = (int)($_GET['id'] ?? 0);

$params = [];
$identifierSql = '';

if ($slug !== '') {
    $identifierSql = 'j.slug = :slug';
    $params['slug'] = $slug;
} elseif ($id > 0) {
    $identifierSql = 'j.id = :id';
    $params['id'] = $id;
} else {
    $identifierSql = '1 = 0';
}

$job = fetch_row_safe(
    $pdo,
    "SELECT
        j.*,
        COALESCE(c.name, 'Unknown Company') AS company_name,
        c.logo AS company_logo,
        c.website AS company_website,
        c.description AS company_description,
        c.slug AS company_slug,
        COALESCE(f.name, 'General') AS field_name,
        (SELECT COUNT(*) FROM job_views v WHERE v.job_id = j.id AND v.event_type = 'view') AS total_views,
        (SELECT COUNT(*) FROM job_views v WHERE v.job_id = j.id AND v.event_type = 'click') AS total_clicks
     FROM jobs j
     LEFT JOIN companies c ON c.id = j.company_id
     LEFT JOIN fields_of_study f ON f.id = j.field_id
     WHERE $identifierSql
       AND " . public_job_where_sql('j') . "
     LIMIT 1",
    $params
);

if ($job) {
    track_job_event($pdo, (int)$job['id'], 'view');
} else {
    http_response_code(404);
}

$pageTitle = $job ? $job['title'] . " | Tikuse Jobs" : "Job Not Found | Tikuse Jobs";
$bodyClass = "";

$relatedJobs = [];
if ($job) {
    $relatedJobs = fetch_all_safe(
        $pdo,
        "SELECT j.title, j.slug, j.location, j.job_type, COALESCE(c.name, 'Unknown Company') AS company_name
         FROM jobs j
         LEFT JOIN companies c ON c.id = j.company_id
         WHERE " . public_job_where_sql('j') . "
           AND j.id != :id
           AND (j.field_id = :field_id OR j.company_id = :company_id)
         ORDER BY COALESCE(j.posted_date, DATE(j.created_at)) DESC
         LIMIT 4",
        [
            'id' => $job['id'],
            'field_id' => $job['field_id'],
            'company_id' => $job['company_id'],
        ]
    );
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <?php if (!$job): ?>
    <section class="page-hero compact-hero">
      <div class="container">
        <h1>Job Not Found</h1>
        <p>The job may have expired or been removed.</p>
        <a href="jobs.php" class="btn btn-primary">Browse Jobs</a>
      </div>
    </section>
  <?php else: ?>
    <section class="job-detail-hero">
      <div class="container job-detail-grid">
        <div class="job-detail-main">
          <div class="job-title-row">
            <div class="job-logo large-logo">
              <?php if (!empty($job['company_logo'])): ?>
                <img src="<?php echo e(upload_public_path('logos', $job['company_logo'])); ?>" alt="<?php echo e($job['company_name']); ?>">
              <?php else: ?>
                <?php echo e(strtoupper(substr($job['company_name'], 0, 1))); ?>
              <?php endif; ?>
            </div>
            <div>
              <h1><?php echo e($job['title']); ?></h1>
              <p><?php echo e($job['company_name']); ?></p>
            </div>
          </div>

          <div class="job-tags detail-tags">
            <span class="pill"><?php echo e($job['location'] ?: 'Location not set'); ?></span>
            <span class="pill"><?php echo e($job['job_type']); ?></span>
            <span class="pill"><?php echo e($job['field_name']); ?></span>
            <?php if (!empty($job['deadline'])): ?>
              <span class="pill">Deadline: <?php echo e($job['deadline']); ?></span>
            <?php endif; ?>
          </div>

          <?php if (!empty($job['vacancy_image'])): ?>
            <img class="vacancy-image" src="<?php echo e(upload_public_path('jobs', $job['vacancy_image'])); ?>" alt="<?php echo e($job['title']); ?>">
          <?php endif; ?>

          <section class="content-block">
            <h2>Job Summary</h2>
            <p><?php echo nl2br(e($job['short_description'])); ?></p>
          </section>

          <?php if (!empty($job['full_description'])): ?>
            <section class="content-block">
              <h2>Description</h2>
              <p><?php echo nl2br(e($job['full_description'])); ?></p>
            </section>
          <?php endif; ?>

          <?php if (!empty($job['requirements'])): ?>
            <section class="content-block">
              <h2>Requirements</h2>
              <p><?php echo nl2br(e($job['requirements'])); ?></p>
            </section>
          <?php endif; ?>

          <?php if (!empty($job['application_process'])): ?>
            <section class="content-block">
              <h2>Application Process</h2>
              <p><?php echo nl2br(e($job['application_process'])); ?></p>
            </section>
          <?php endif; ?>
        </div>

        <aside class="job-detail-sidebar">
          <div class="detail-card">
            <h3>Apply for this job</h3>
            <p>Open the official application page. Your click is tracked for dashboard analytics.</p>
            <a href="track-click.php?id=<?php echo (int)$job['id']; ?>" class="btn btn-primary">Apply Now</a>
          </div>

          <div class="detail-card">
            <h3>Job Snapshot</h3>
            <div class="snapshot-list">
              <div><span>Posted</span><strong><?php echo e($job['posted_date'] ?: date('Y-m-d', strtotime($job['created_at']))); ?></strong></div>
              <div><span>Deadline</span><strong><?php echo e($job['deadline'] ?: 'Not set'); ?></strong></div>
              <div><span>Expires</span><strong><?php echo e($job['expiry_date'] ?: 'Not set'); ?></strong></div>
              <div><span>Views</span><strong><?php echo number_format((int)$job['total_views'] + 1); ?></strong></div>
              <div><span>Clicks</span><strong><?php echo number_format((int)$job['total_clicks']); ?></strong></div>
            </div>
          </div>

          <div class="detail-card">
            <h3>Company</h3>
            <p><?php echo e($job['company_description'] ?: 'Company details will be added soon.'); ?></p>
            <?php if (!empty($job['company_slug'])): ?>
              <a href="company.php?slug=<?php echo urlencode($job['company_slug']); ?>" class="btn btn-secondary btn-small">View Company Jobs</a>
            <?php endif; ?>
          </div>
        </aside>
      </div>
    </section>

    <?php if (!empty($relatedJobs)): ?>
      <section class="section-block">
        <div class="container">
          <div class="section-title-row">
            <div>
              <h2>Related Jobs</h2>
              <p>More openings you may want to inspect</p>
            </div>
          </div>

          <div class="card-grid-4">
            <?php foreach ($relatedJobs as $related): ?>
              <a href="job-details.php?slug=<?php echo urlencode($related['slug']); ?>" class="mini-job-card">
                <h3><?php echo e($related['title']); ?></h3>
                <p><?php echo e($related['company_name']); ?></p>
                <span><?php echo e($related['location'] ?: 'Location not set'); ?> / <?php echo e($related['job_type']); ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    <?php endif; ?>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
