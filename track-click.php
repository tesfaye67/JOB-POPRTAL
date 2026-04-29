<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$jobId = (int)($_GET['id'] ?? 0);

$job = $jobId > 0 ? fetch_row_safe(
    $pdo,
    "SELECT id, application_link
     FROM jobs
     WHERE id = :id
       AND " . public_job_where_sql('jobs') . "
     LIMIT 1",
    ['id' => $jobId]
) : null;

if (!$job || empty($job['application_link'])) {
    redirect_to('jobs.php');
}

track_job_event($pdo, (int)$job['id'], 'click');
header('Location: ' . $job['application_link']);
exit;
