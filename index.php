<?php
$pageTitle = "Tikuse Jobs | Home";
$bodyClass = "";

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

expire_old_jobs($pdo);
$siteSettings = get_settings($pdo);

$totalJobs = fetch_value_safe(
    $pdo,
    "SELECT COUNT(*)
     FROM jobs
     WHERE status = 'published'
       AND (expiry_date IS NULL OR expiry_date >= CURDATE())"
);

$totalCompanies = fetch_value_safe($pdo, "SELECT COUNT(*) FROM companies");
$totalCategories = fetch_value_safe($pdo, "SELECT COUNT(*) FROM fields_of_study");
$totalClicks = fetch_value_safe($pdo, "SELECT COUNT(*) FROM job_views WHERE event_type = 'click'", [], 0);

$popularCategories = fetch_all_safe(
    $pdo,
    "SELECT
        fos.id,
        fos.name,
        COUNT(j.id) AS total_jobs
     FROM fields_of_study fos
     LEFT JOIN jobs j
        ON j.field_id = fos.id
       AND j.status = 'published'
       AND (j.expiry_date IS NULL OR j.expiry_date >= CURDATE())
     GROUP BY fos.id, fos.name
     ORDER BY total_jobs DESC, fos.name ASC
     LIMIT 8"
);

$latestJobs = fetch_all_safe(
    $pdo,
    "SELECT
        j.id,
        j.title,
        j.slug,
        j.location,
        j.job_type,
        j.short_description,
        j.is_featured,
        j.deadline,
        COALESCE(fos.name, 'General') AS field_name,
        COALESCE(c.name, 'Unknown Company') AS company_name,
        c.logo AS company_logo,
        COALESCE(j.posted_date, DATE(j.created_at)) AS post_date
     FROM jobs j
     LEFT JOIN companies c ON c.id = j.company_id
     LEFT JOIN fields_of_study fos ON fos.id = j.field_id
     WHERE j.status = 'published'
       AND (j.expiry_date IS NULL OR j.expiry_date >= CURDATE())
     ORDER BY j.is_featured DESC, COALESCE(j.posted_date, DATE(j.created_at)) DESC, j.id DESC
     LIMIT 6"
);

$heroPreviewJobs = array_slice($latestJobs, 0, 3);

$topCompanies = fetch_all_safe(
    $pdo,
    "SELECT
        c.id,
        c.name,
        c.slug,
        c.logo,
        COUNT(j.id) AS total_jobs
     FROM companies c
     LEFT JOIN jobs j
        ON j.company_id = c.id
       AND j.status = 'published'
       AND (j.expiry_date IS NULL OR j.expiry_date >= CURDATE())
     GROUP BY c.id, c.name, c.slug, c.logo
     ORDER BY total_jobs DESC, c.name ASC
     LIMIT 8"
);

$locations = fetch_all_safe(
    $pdo,
    "SELECT DISTINCT location
     FROM jobs
     WHERE status = 'published'
       AND (expiry_date IS NULL OR expiry_date >= CURDATE())
       AND location IS NOT NULL
       AND location != ''
     ORDER BY location ASC
     LIMIT 8"
);

$heroBadge = $siteSettings['hero_badge'] ?: 'Verified jobs from trusted employers';
$heroTitle = $siteSettings['hero_title'] ?: 'Find the right job faster';
$heroHighlight = $siteSettings['hero_highlight'] ?: 'with Tikuse Jobs';
$heroSubtitle = $siteSettings['hero_subtitle'] ?: 'Search fresh opportunities by title, category, company, location, and job type from one clean job portal.';
$primaryCtaLabel = $siteSettings['primary_cta_label'] ?: 'Browse Jobs';
$secondaryCtaLabel = $siteSettings['secondary_cta_label'] ?: 'View Companies';
$heroTelegramLabel = $siteSettings['hero_telegram_label'] ?: 'Join Telegram updates';
$statsJobsLabel = $siteSettings['stats_jobs_label'] ?: 'Active Jobs';
$statsCompaniesLabel = $siteSettings['stats_companies_label'] ?: 'Companies';
$statsCategoriesLabel = $siteSettings['stats_categories_label'] ?: 'Categories';
$statsClicksLabel = $siteSettings['stats_clicks_label'] ?: 'Apply Clicks';
$quickCategoriesLabel = $siteSettings['quick_categories_label'] ?: 'Explore all categories';
$viewAllJobsLabel = $siteSettings['view_all_jobs_label'] ?: 'View All Jobs';
$viewAllCategoriesLabel = $siteSettings['view_all_categories_label'] ?: 'View All';
$browseCompaniesLabel = $siteSettings['browse_companies_label'] ?: 'Browse Companies';
$previewClicksLabel = $siteSettings['preview_clicks_label'] ?: 'Tracked apply clicks';
$previewExpiryValue = $siteSettings['preview_expiry_value'] ?: '3 mo';
$previewExpiryLabel = $siteSettings['preview_expiry_label'] ?: 'Auto expiry window';
$searchPlaceholder = $siteSettings['search_placeholder'] ?: 'Job title, company, keywords';
$heroPreviewLabel = $siteSettings['hero_preview_label'] ?: 'Live Job Board';
$heroPreviewTitle = $siteSettings['hero_preview_title'] ?: 'Latest openings';
$homeAboutTitle = $siteSettings['home_about_title'] ?: 'Built for job seekers and growing employers';
$homeAboutText = $siteSettings['home_about_text'] ?: 'Tikuse Jobs helps visitors move from searching to applying with verified listings, clean filters, company pages, and tracked application clicks for better publishing decisions.';
$homeAboutCtaLabel = $siteSettings['home_about_cta_label'] ?: 'Learn About Us';
$homeAboutPoints = array_filter([
    $siteSettings['home_about_point_1'] ?: 'Verified job summaries',
    $siteSettings['home_about_point_2'] ?: 'Fast search and filters',
    $siteSettings['home_about_point_3'] ?: 'Admin analytics for every apply click',
]);
$latestJobsTitle = $siteSettings['latest_jobs_title'] ?: 'Latest Verified Jobs';
$latestJobsSubtitle = $siteSettings['latest_jobs_subtitle'] ?: 'Fresh openings published from the admin dashboard';
$categoriesTitle = $siteSettings['categories_title'] ?: 'Popular Categories';
$categoriesSubtitle = $siteSettings['categories_subtitle'] ?: 'Browse jobs by field and study direction';
$companiesTitle = $siteSettings['companies_title'] ?: 'Trusted Companies';
$companiesSubtitle = $siteSettings['companies_subtitle'] ?: 'Employers currently listed on Tikuse Jobs';
$telegramCardTitle = $siteSettings['telegram_card_title'] ?: 'Telegram Job Alerts';
$telegramCardText = $siteSettings['telegram_card_text'] ?: 'Share new jobs instantly and bring returning visitors back to the site.';
$telegramCardButtonLabel = $siteSettings['telegram_card_button_label'] ?: 'Join Now';
$whyTitle = $siteSettings['why_title'] ?: 'Why visitors stay';
$whyPoints = array_filter([
    $siteSettings['why_point_1'] ?: 'Search by keyword, category, company, and location',
    $siteSettings['why_point_2'] ?: 'Clean job detail pages with official apply links',
    $siteSettings['why_point_3'] ?: 'Expired jobs hidden automatically from public pages',
]);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <section class="hero-section professional-hero-section">
    <div class="container">
      <div class="hero-shell portal-hero-shell">
        <div class="hero-grid portal-hero-grid">
          <div class="hero-copy portal-hero-copy">
            <span class="hero-badge"><?php echo e($heroBadge); ?></span>
            <h1><?php echo e($heroTitle); ?> <span><?php echo e($heroHighlight); ?></span></h1>
            <p><?php echo e($heroSubtitle); ?></p>

            <form class="search-panel portal-search-panel" action="jobs.php" method="GET">
              <input type="text" name="keyword" placeholder="<?php echo e($searchPlaceholder); ?>">

              <select name="field">
                <option value="">All Categories</option>
                <?php foreach ($popularCategories as $category): ?>
                  <option value="<?php echo (int)$category['id']; ?>"><?php echo e($category['name']); ?></option>
                <?php endforeach; ?>
              </select>

              <select name="location">
                <option value="">All Locations</option>
                <?php foreach ($locations as $row): ?>
                  <option value="<?php echo e($row['location']); ?>"><?php echo e($row['location']); ?></option>
                <?php endforeach; ?>
              </select>

              <button type="submit" class="btn btn-primary">Search Jobs</button>
            </form>

            <div class="hero-actions">
              <a href="jobs.php" class="btn btn-primary"><?php echo e($primaryCtaLabel); ?></a>
              <a href="companies.php" class="btn btn-secondary"><?php echo e($secondaryCtaLabel); ?></a>
              <a href="<?php echo e($siteSettings['telegram_link'] ?: '#'); ?>" class="text-link"><?php echo e($heroTelegramLabel); ?></a>
            </div>

            <div class="hero-stats portal-hero-stats">
              <div class="metric-card">
                <strong><?php echo number_format((int)$totalJobs); ?>+</strong>
                <span><?php echo e($statsJobsLabel); ?></span>
              </div>
              <div class="metric-card">
                <strong><?php echo number_format((int)$totalCompanies); ?>+</strong>
                <span><?php echo e($statsCompaniesLabel); ?></span>
              </div>
              <div class="metric-card">
                <strong><?php echo number_format((int)$totalCategories); ?>+</strong>
                <span><?php echo e($statsCategoriesLabel); ?></span>
              </div>
              <div class="metric-card">
                <strong><?php echo number_format((int)$totalClicks); ?>+</strong>
                <span><?php echo e($statsClicksLabel); ?></span>
              </div>
            </div>
          </div>

          <aside class="portal-preview">
            <div class="portal-preview-header">
              <div>
                <span><?php echo e($heroPreviewLabel); ?></span>
                <strong><?php echo e($heroPreviewTitle); ?></strong>
              </div>
              <a href="jobs.php" class="btn btn-secondary btn-small">View All</a>
            </div>

            <div class="portal-preview-list">
              <?php if (!empty($heroPreviewJobs)): ?>
                <?php foreach ($heroPreviewJobs as $job): ?>
                  <a href="job-details.php?slug=<?php echo urlencode($job['slug']); ?>" class="preview-job-card">
                    <div class="job-logo">
                      <?php if (!empty($job['company_logo'])): ?>
                        <img src="<?php echo e(upload_public_path('logos', $job['company_logo'])); ?>" alt="<?php echo e($job['company_name']); ?>">
                      <?php else: ?>
                        <?php echo e(strtoupper(substr($job['company_name'], 0, 1))); ?>
                      <?php endif; ?>
                    </div>
                    <div>
                      <strong><?php echo e($job['title']); ?></strong>
                      <small><?php echo e($job['company_name']); ?></small>
                      <span><?php echo e($job['location'] ?: 'Location not set'); ?> / <?php echo e($job['job_type']); ?></span>
                    </div>
                  </a>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="preview-empty">
                  <strong>No published jobs yet</strong>
                  <span>Add and publish jobs from the admin dashboard.</span>
                </div>
              <?php endif; ?>
            </div>

            <div class="portal-preview-footer">
              <div>
                <strong><?php echo number_format((int)$totalClicks); ?></strong>
                <span><?php echo e($previewClicksLabel); ?></span>
              </div>
              <div>
                <strong><?php echo e($previewExpiryValue); ?></strong>
                <span><?php echo e($previewExpiryLabel); ?></span>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </div>
  </section>

  <section class="section-block">
    <div class="container">
      <div class="quick-filter-row">
        <?php foreach (array_slice($popularCategories, 0, 5) as $category): ?>
          <a href="jobs.php?field=<?php echo (int)$category['id']; ?>">
            <?php echo e($category['name']); ?>
            <span><?php echo number_format((int)$category['total_jobs']); ?></span>
          </a>
        <?php endforeach; ?>
        <a href="categories.php"><?php echo e($quickCategoriesLabel); ?> <span><?php echo number_format((int)$totalCategories); ?></span></a>
      </div>
    </div>
  </section>

  <section class="section-block">
    <div class="container">
      <div class="home-about-panel">
        <div>
          <span class="hero-badge">About <?php echo e($siteSettings['site_name']); ?></span>
          <h2><?php echo e($homeAboutTitle); ?></h2>
          <p><?php echo e($homeAboutText); ?></p>
          <a href="about.php" class="btn btn-secondary btn-small"><?php echo e($homeAboutCtaLabel); ?></a>
        </div>

        <div class="home-about-points">
          <?php foreach ($homeAboutPoints as $point): ?>
            <div>
              <strong><?php echo e($point); ?></strong>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="section-block">
    <div class="container">
      <div class="section-title-row">
        <div>
          <h2><?php echo e($latestJobsTitle); ?></h2>
          <p><?php echo e($latestJobsSubtitle); ?></p>
        </div>
        <a href="jobs.php" class="btn btn-secondary btn-small"><?php echo e($viewAllJobsLabel); ?></a>
      </div>

      <div class="latest-jobs-layout professional-jobs-layout">
        <div class="jobs-list">
          <?php if (!empty($latestJobs)): ?>
            <?php foreach ($latestJobs as $job): ?>
              <article class="job-list-card professional-job-card">
                <div class="job-logo">
                  <?php if (!empty($job['company_logo'])): ?>
                    <img src="<?php echo e(upload_public_path('logos', $job['company_logo'])); ?>" alt="<?php echo e($job['company_name']); ?>">
                  <?php else: ?>
                    <?php echo e(strtoupper(substr($job['company_name'], 0, 1))); ?>
                  <?php endif; ?>
                </div>

                <div>
                  <div class="job-card-heading">
                    <h3><?php echo e($job['title']); ?></h3>
                    <?php if ((int)$job['is_featured'] === 1): ?>
                      <span class="pill highlight-pill">Featured</span>
                    <?php endif; ?>
                  </div>
                  <p><?php echo e($job['company_name']); ?></p>
                  <p><?php echo e($job['short_description']); ?></p>

                  <div class="job-tags">
                    <span class="pill"><?php echo e($job['location'] ?: 'Location not set'); ?></span>
                    <span class="pill"><?php echo e($job['job_type']); ?></span>
                    <span class="pill"><?php echo e($job['field_name']); ?></span>
                    <?php if (!empty($job['deadline'])): ?>
                      <span class="pill">Deadline <?php echo e($job['deadline']); ?></span>
                    <?php endif; ?>
                  </div>
                </div>

                <div class="job-card-actions">
                  <small><?php echo e($job['post_date']); ?></small>
                  <a href="job-details.php?slug=<?php echo urlencode($job['slug']); ?>" class="btn btn-primary btn-small">View Job</a>
                </div>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-public">
              <h3>No published jobs yet</h3>
              <p>Add jobs from the admin panel and publish them to make this section live.</p>
            </div>
          <?php endif; ?>
        </div>

        <aside class="portal-side-stack">
          <div class="telegram-card professional-side-card">
            <div class="telegram-icon">T</div>
            <h3><?php echo e($telegramCardTitle); ?></h3>
            <p><?php echo e($telegramCardText); ?></p>
            <a href="<?php echo e($siteSettings['telegram_link'] ?: '#'); ?>" class="btn btn-primary"><?php echo e($telegramCardButtonLabel); ?></a>
          </div>

          <div class="professional-side-card">
            <h3><?php echo e($whyTitle); ?></h3>
            <div class="side-check-list">
              <?php foreach ($whyPoints as $point): ?>
                <span><?php echo e($point); ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </section>

  <section class="section-block">
    <div class="container">
      <div class="section-title-row">
        <div>
          <h2><?php echo e($categoriesTitle); ?></h2>
          <p><?php echo e($categoriesSubtitle); ?></p>
        </div>
        <a href="categories.php" class="btn btn-secondary btn-small"><?php echo e($viewAllCategoriesLabel); ?></a>
      </div>

      <div class="card-grid-6">
        <?php if (!empty($popularCategories)): ?>
          <?php foreach (array_slice($popularCategories, 0, 6) as $category): ?>
            <a class="category-card" href="jobs.php?field=<?php echo (int)$category['id']; ?>">
              <div class="category-icon">#</div>
              <h3><?php echo e($category['name']); ?></h3>
              <span><?php echo number_format((int)$category['total_jobs']); ?> Jobs</span>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="category-card">
            <h3>No categories yet</h3>
            <span>Add fields of study and published jobs in admin.</span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="section-block">
    <div class="container">
      <div class="section-title-row">
        <div>
          <h2><?php echo e($companiesTitle); ?></h2>
          <p><?php echo e($companiesSubtitle); ?></p>
        </div>
        <a href="companies.php" class="btn btn-secondary btn-small"><?php echo e($browseCompaniesLabel); ?></a>
      </div>

      <div class="company-strip professional-company-strip">
        <?php if (!empty($topCompanies)): ?>
          <?php foreach ($topCompanies as $company): ?>
            <a href="company.php?slug=<?php echo urlencode($company['slug']); ?>" class="company-strip-card">
              <div class="company-strip-logo">
                <?php if (!empty($company['logo'])): ?>
                  <img src="<?php echo e(upload_public_path('logos', $company['logo'])); ?>" alt="<?php echo e($company['name']); ?>">
                <?php else: ?>
                  <?php echo e(strtoupper(substr($company['name'], 0, 1))); ?>
                <?php endif; ?>
              </div>
              <strong><?php echo e($company['name']); ?></strong>
              <span><?php echo number_format((int)$company['total_jobs']); ?> jobs</span>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="company-strip-card">No companies yet</div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
