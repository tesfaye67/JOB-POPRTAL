<?php
$pageTitle = "Browse Jobs | Tikuse Jobs";
$bodyClass = "";

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

expire_old_jobs($pdo);
$siteSettings = get_settings($pdo);

$keyword = trim((string)($_GET['keyword'] ?? $_GET['q'] ?? ''));
$fieldId = (int)($_GET['field'] ?? 0);
$companyId = (int)($_GET['company'] ?? 0);
$location = trim((string)($_GET['location'] ?? ''));
$jobType = trim((string)($_GET['type'] ?? ''));

$conditions = [public_job_where_sql('j')];
$params = [];

if ($keyword !== '') {
    $conditions[] = "(j.title LIKE :keyword_title OR j.short_description LIKE :keyword_description OR c.name LIKE :keyword_company)";
    $keywordTerm = '%' . $keyword . '%';
    $params['keyword_title'] = $keywordTerm;
    $params['keyword_description'] = $keywordTerm;
    $params['keyword_company'] = $keywordTerm;
}

if ($fieldId > 0) {
    $conditions[] = "j.field_id = :field_id";
    $params['field_id'] = $fieldId;
}

if ($companyId > 0) {
    $conditions[] = "j.company_id = :company_id";
    $params['company_id'] = $companyId;
}

if ($location !== '') {
    $conditions[] = "j.location = :location";
    $params['location'] = $location;
}

if ($jobType !== '') {
    $conditions[] = "j.job_type = :job_type";
    $params['job_type'] = $jobType;
}

$whereSql = 'WHERE ' . implode(' AND ', $conditions);

$jobs = fetch_all_safe(
    $pdo,
    "SELECT
        j.id,
        j.title,
        j.slug,
        j.location,
        j.job_type,
        j.short_description,
        j.is_featured,
        j.posted_date,
        j.deadline,
        COALESCE(c.name, 'Unknown Company') AS company_name,
        c.logo AS company_logo,
        COALESCE(f.name, 'General') AS field_name,
        (SELECT COUNT(*) FROM job_views v WHERE v.job_id = j.id AND v.event_type = 'view') AS total_views
     FROM jobs j
     LEFT JOIN companies c ON c.id = j.company_id
     LEFT JOIN fields_of_study f ON f.id = j.field_id
     $whereSql
     ORDER BY j.is_featured DESC, COALESCE(j.posted_date, DATE(j.created_at)) DESC, j.id DESC
     LIMIT 60",
    $params
);

$categories = fetch_all_safe(
    $pdo,
    "SELECT f.id, f.name, COUNT(j.id) AS total_jobs
     FROM fields_of_study f
     LEFT JOIN jobs j ON j.field_id = f.id AND " . public_job_where_sql('j') . "
     GROUP BY f.id, f.name
     ORDER BY f.name ASC"
);

$companies = fetch_all_safe(
    $pdo,
    "SELECT c.id, c.name, COUNT(j.id) AS total_jobs
     FROM companies c
     LEFT JOIN jobs j ON j.company_id = c.id AND " . public_job_where_sql('j') . "
     GROUP BY c.id, c.name
     ORDER BY c.name ASC"
);

$locations = fetch_all_safe(
    $pdo,
    "SELECT DISTINCT location
     FROM jobs
     WHERE " . public_job_where_sql('jobs') . "
       AND location IS NOT NULL
       AND location != ''
     ORDER BY location ASC"
);

$jobTypes = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Freelance'];

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <section class="page-hero compact-hero">
    <div class="container">
      <h1>Browse Jobs</h1>
      <p>Search verified openings by keyword, category, company, location, and job type.</p>
    </div>
  </section>

  <section class="section-block">
    <div class="container listing-layout">
      <aside class="filter-sidebar">
        <form method="GET" class="filter-panel">
          <label>
            <span>Keyword</span>
            <input type="text" name="keyword" value="<?php echo e($keyword); ?>" placeholder="Title or company">
          </label>

          <label>
            <span>Category</span>
            <select name="field">
              <option value="">All categories</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo (int)$category['id']; ?>"<?php echo selected($fieldId, $category['id']); ?>>
                  <?php echo e($category['name']); ?> (<?php echo (int)$category['total_jobs']; ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            <span>Company</span>
            <select name="company">
              <option value="">All companies</option>
              <?php foreach ($companies as $company): ?>
                <option value="<?php echo (int)$company['id']; ?>"<?php echo selected($companyId, $company['id']); ?>>
                  <?php echo e($company['name']); ?> (<?php echo (int)$company['total_jobs']; ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            <span>Location</span>
            <select name="location">
              <option value="">All locations</option>
              <?php foreach ($locations as $row): ?>
                <option value="<?php echo e($row['location']); ?>"<?php echo selected($location, $row['location']); ?>>
                  <?php echo e($row['location']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            <span>Job Type</span>
            <select name="type">
              <option value="">All types</option>
              <?php foreach ($jobTypes as $type): ?>
                <option value="<?php echo e($type); ?>"<?php echo selected($jobType, $type); ?>><?php echo e($type); ?></option>
              <?php endforeach; ?>
            </select>
          </label>

          <button type="submit" class="btn btn-primary">Apply Filters</button>
          <a href="jobs.php" class="btn btn-secondary">Reset</a>
        </form>
      </aside>

      <div class="listing-main">
        <div class="section-title-row">
          <div>
            <h2><?php echo number_format(count($jobs)); ?> Jobs Found</h2>
            <p>Fresh opportunities from trusted employers</p>
          </div>
        </div>

        <div class="jobs-list">
          <?php if (!empty($jobs)): ?>
            <?php foreach ($jobs as $job): ?>
              <article class="job-list-card">
                <div class="job-logo">
                  <?php if (!empty($job['company_logo'])): ?>
                    <img src="<?php echo e(upload_public_path('logos', $job['company_logo'])); ?>" alt="<?php echo e($job['company_name']); ?>">
                  <?php else: ?>
                    <?php echo e(strtoupper(substr($job['company_name'], 0, 1))); ?>
                  <?php endif; ?>
                </div>

                <div>
                  <h3><?php echo e($job['title']); ?></h3>
                  <p><?php echo e($job['company_name']); ?></p>
                  <p><?php echo e($job['short_description']); ?></p>

                  <div class="job-tags">
                    <span class="pill"><?php echo e($job['location'] ?: 'Location not set'); ?></span>
                    <span class="pill"><?php echo e($job['job_type']); ?></span>
                    <span class="pill"><?php echo e($job['field_name']); ?></span>
                    <?php if ((int)$job['is_featured'] === 1): ?>
                      <span class="pill highlight-pill">Featured</span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="job-card-actions">
                  <small><?php echo number_format((int)$job['total_views']); ?> views</small>
                  <a href="job-details.php?slug=<?php echo urlencode($job['slug']); ?>" class="btn btn-primary btn-small">View Job</a>
                </div>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-public">
              <h3>No jobs found</h3>
              <p>Try changing your filters or check back soon for new openings.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
