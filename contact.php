<?php
$pageTitle = "Contact | Tikuse Jobs";
$bodyClass = "";

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$siteSettings = get_settings($pdo);
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $subject = trim((string)($_POST['subject'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));

        if ($name === '' || $email === '' || $message === '') {
            $error = 'Please fill in your name, email, and message.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, subject, message)
                VALUES (:name, :email, :subject, :message)
            ");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'subject' => $subject ?: null,
                'message' => $message,
            ]);
            $success = 'Your message was sent successfully.';
        }
    }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main>
  <section class="page-hero compact-hero">
    <div class="container">
      <h1>Contact</h1>
      <p>Send a message about listings, updates, partnerships, or site support.</p>
    </div>
  </section>

  <section class="section-block">
    <div class="container contact-layout">
      <form method="POST" class="public-form">
        <?php echo csrf_field(); ?>
        <h2>Send Message</h2>

        <?php if ($success): ?>
          <div class="public-alert success-alert"><?php echo e($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
          <div class="public-alert error-alert"><?php echo e($error); ?></div>
        <?php endif; ?>

        <label>
          <span>Name</span>
          <input type="text" name="name" required>
        </label>

        <label>
          <span>Email</span>
          <input type="email" name="email" required>
        </label>

        <label>
          <span>Subject</span>
          <input type="text" name="subject">
        </label>

        <label>
          <span>Message</span>
          <textarea name="message" rows="7" required></textarea>
        </label>

        <button type="submit" class="btn btn-primary">Send Message</button>
      </form>

      <aside class="detail-card contact-card">
        <h3>Contact Details</h3>
        <div class="snapshot-list">
          <div><span>Email</span><strong><?php echo e($siteSettings['contact_email'] ?: 'Not set'); ?></strong></div>
          <div><span>Phone</span><strong><?php echo e($siteSettings['contact_phone'] ?: 'Not set'); ?></strong></div>
          <div><span>Telegram</span><strong><a href="<?php echo e($siteSettings['telegram_link'] ?: '#'); ?>">Open Channel</a></strong></div>
        </div>
      </aside>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
