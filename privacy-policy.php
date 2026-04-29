<?php
$pageTitle = "Privacy Policy | Tikuse Jobs";
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
      <h1>Privacy Policy</h1>
      <p>How Tikuse Jobs handles contact messages, analytics, and site data.</p>
    </div>
  </section>

  <section class="section-block">
    <div class="container content-page">
      <section class="content-block">
        <h2>Information We Collect</h2>
        <p>When you use the contact form, we collect the name, email address, subject, and message you provide. When visitors open a job or click an application link, the site records basic analytics such as the job, event type, time, IP address, browser information, and referring page.</p>
      </section>

      <section class="content-block">
        <h2>How We Use Information</h2>
        <p>Contact form information is used to respond to messages. Job analytics are used to understand which listings perform well, improve the site experience, and help admins manage content quality.</p>
      </section>

      <section class="content-block">
        <h2>External Links</h2>
        <p>Job application buttons may send you to third-party websites. Those websites have their own privacy practices, and Tikuse Jobs is not responsible for their content or data handling.</p>
      </section>

      <section class="content-block">
        <h2>Contact</h2>
        <p>Questions about privacy can be sent through the contact page or to <?php echo e($siteSettings['contact_email'] ?: 'the site administrator'); ?>.</p>
      </section>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
