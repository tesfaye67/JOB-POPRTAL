<?php
$pageTitle = "Terms of Service | Tikuse Jobs";
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
      <h1>Terms of Service</h1>
      <p>Guidelines for using Tikuse Jobs and applying through listed opportunities.</p>
    </div>
  </section>

  <section class="section-block">
    <div class="container content-page">
      <section class="content-block">
        <h2>Use of the Site</h2>
        <p>Tikuse Jobs provides job listing information for browsing and discovery. Visitors are responsible for reviewing job details and applying through the official destination provided in each listing.</p>
      </section>

      <section class="content-block">
        <h2>Job Information</h2>
        <p>The site aims to present accurate and original summaries, but job details may change on employer websites. Always confirm requirements, deadlines, and application instructions on the official application page.</p>
      </section>

      <section class="content-block">
        <h2>No Guarantee of Employment</h2>
        <p>Using Tikuse Jobs does not guarantee interviews, employment, or employer responses. Hiring decisions are controlled by the companies or organizations receiving applications.</p>
      </section>

      <section class="content-block">
        <h2>Contact</h2>
        <p>For corrections, listing concerns, or support, use the contact page.</p>
      </section>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
