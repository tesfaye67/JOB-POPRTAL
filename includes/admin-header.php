<?php
require_once __DIR__ . '/functions.php';

if (!isset($pageTitle)) {
    $pageTitle = "Admin Dashboard | Tikuse Jobs";
}
if (!isset($bodyClass)) {
    $bodyClass = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo e($pageTitle); ?></title>
  <meta name="description" content="Tikuse Jobs admin dashboard">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="<?php echo e($bodyClass); ?>">
<div class="admin-layout">
