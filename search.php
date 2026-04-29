<?php
$query = http_build_query($_GET);
header('Location: jobs.php' . ($query ? '?' . $query : ''));
exit;
