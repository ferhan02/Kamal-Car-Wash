<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$isLoggedIn = isset($_SESSION['cust_id']);
function customerNavActive(array $pages): string { global $currentPage; return in_array($currentPage, $pages, true) ? ' active' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#10243a">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>Kamal Car Wash</title>
    <script>(()=>{const saved=localStorage.getItem('kamal-theme');const dark=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches;document.documentElement.dataset.theme=saved||(dark?'dark':'light');})();</script>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script defer src="../../js/customer.js"></script>
</head>
<body>
<div class="site-topbar"><div class="container"><span><i class="fa-solid fa-clock"></i> Mon–Sat, 8:00 AM–6:00 PM</span><span><i class="fa-solid fa-sparkles"></i> More Than a Wash, It's a Revival</span></div></div>
<header class="site-header"><div class="container site-header-inner">
    <a class="brand" href="<?= $isLoggedIn ? 'cust_home.php' : '../../index.php' ?>" aria-label="Kamal Car Wash home"><img class="brand-logo" src="../../images/logo.png" alt="Kamal Car Wash logo"><span class="brand-copy"><span class="brand-title">KAMAL CAR WASH</span><span class="brand-subtitle"><?= $isLoggedIn ? 'Customer Portal' : 'Professional Car Care' ?></span></span></a>
    <button class="icon-btn nav-toggle" type="button" aria-label="Open navigation" aria-controls="customer-nav" aria-expanded="false" data-nav-toggle><i class="fa-solid fa-bars"></i></button>
    <nav class="site-nav" id="customer-nav" aria-label="Customer navigation">
        <?php if ($isLoggedIn): ?>
            <a class="nav-link<?= customerNavActive(['cust_home.php']) ?>" href="cust_home.php"><i class="fa-solid fa-house"></i> Home</a>
            <a class="nav-link<?= customerNavActive(['booking.php','booking_details.php','payment.php']) ?>" href="booking.php"><i class="fa-solid fa-calendar-check"></i> Book</a>
            <a class="nav-link<?= customerNavActive(['vehicle.php']) ?>" href="vehicle.php"><i class="fa-solid fa-car-side"></i> Vehicles</a>
            <a class="nav-link<?= customerNavActive(['receipt.php']) ?>" href="receipt.php"><i class="fa-solid fa-receipt"></i> Receipts</a>
            <a class="nav-link<?= customerNavActive(['profile.php','change_password.php']) ?>" href="profile.php"><i class="fa-solid fa-user"></i> Profile</a>
        <?php endif; ?>
        <a class="nav-link<?= customerNavActive(['about.php']) ?>" href="about.php"><i class="fa-solid fa-circle-info"></i> About</a>
        <a class="nav-link<?= customerNavActive(['contact.php']) ?>" href="contact.php"><i class="fa-solid fa-envelope"></i> Contact</a>
        <?php if ($isLoggedIn): ?>
            <a class="nav-link logout-link" href="../../auth/logout.php" data-confirm-title="Sign out?" data-confirm="You will need to sign in again to access your customer account." data-confirm-text="Sign out" data-confirm-tone="danger"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        <?php else: ?>
            <a class="nav-link" href="../../auth/cust_login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
        <?php endif; ?>
    </nav>
    <button class="icon-btn theme-toggle" type="button" data-theme-toggle aria-label="Switch theme" title="Dark mode"><i class="fa-solid fa-moon"></i></button>
</div></header>
