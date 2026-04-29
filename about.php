<?php
$pageTitle = "About | Tikuse Jobs";
$bodyClass = "";

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$siteSettings = get_settings($pdo);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <section class="page-hero compact-hero">
    <div class="container">
      <h1>About Tikuse Jobs</h1>
      <p>A clean job portal built to help job seekers discover verified opportunities faster.</p>
    </div>
  </section>

  <section class="section-block">
    <div class="container content-page">
      <section class="content-block">
        <h2>What We Do</h2>
        <p>Tikuse Jobs organizes job opportunities from trusted companies into a simple browsing experience. The goal is to make openings easier to search, compare, and apply for without confusing layouts or misleading buttons.</p>
      </section>

      <section class="content-block">
        <h2>How Jobs Are Presented</h2>
        <p>Each listing is summarized in clear language, connected to a company, category, location, and job type, then linked to the official application destination. Expired jobs are hidden from public pages while remaining available for admin records.</p>
      </section>

      <section class="content-block">
        <h2>For Employers and Visitors</h2>
        <p>Visitors can browse by category, company, and search filters. Employers and site admins can publish jobs, upload logos and vacancy images, and review views and application clicks from the dashboard.</p>
      </section>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
