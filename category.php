<?php
$fieldId = (int)($_GET['id'] ?? $_GET['field'] ?? 0);

if ($fieldId > 0) {
    header('Location: jobs.php?field=' . $fieldId);
    exit;
}

header('Location: categories.php');
exit;
