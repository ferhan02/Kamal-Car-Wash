<?php
$root_prefix = $root_prefix ?? '../../';
$page_title = $page_title ?? 'Staff Portal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light dark">
<title><?= htmlspecialchars($page_title) ?> | Kamal Car Wash</title>
<script>
(function(){
    const saved = localStorage.getItem('kamal-theme');
    const theme = saved === 'dark' || saved === 'light'
        ? saved
        : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    document.documentElement.dataset.theme = theme;
})();
</script>
<link rel="stylesheet" href="<?= $root_prefix ?>css/style.css">
<link rel="stylesheet" href="<?= $root_prefix ?>css/staff.css">
<link rel="preconnect" href="https://cdnjs.cloudflare.com">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
