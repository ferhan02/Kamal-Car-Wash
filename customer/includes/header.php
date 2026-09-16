<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$isLoggedIn = isset($_SESSION['cust_id']);

function customerNavActive(array $pages): string
{
    global $currentPage;
    return in_array($currentPage, $pages, true) ? ' active' : '';
}

function customerHeaderImageUrl(?string $value): string
{
    if (!$value) {
        return '../../images/uploads/default.png';
    }

    $value = trim($value);

    if (str_starts_with($value, 'uploads/')) {
        return '../../images/' . $value;
    }

    if (str_starts_with($value, 'images/')) {
        return '../../' . $value;
    }

    return '../../images/uploads/default.png';
}

$headerCustomerName = $_SESSION['cust_name'] ?? 'Customer';
$headerCustomerImage = '../../images/uploads/default.png';

if ($isLoggedIn) {
    require_once __DIR__ . '/../../config.php';

    $headerCustomerStmt = $conn->prepare(
        'SELECT cust_name, cust_image FROM customer WHERE cust_id = ? LIMIT 1'
    );
    $headerCustomerStmt->execute([(int) $_SESSION['cust_id']]);
    $headerCustomer = $headerCustomerStmt->fetch(PDO::FETCH_ASSOC);

    if ($headerCustomer) {
        $headerCustomerName = $headerCustomer['cust_name'] ?: $headerCustomerName;
        $headerCustomerImage = customerHeaderImageUrl($headerCustomer['cust_image'] ?? null);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#10243a">

    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>Kamal Car Wash</title>

    <script>
        (() => {
            const saved = localStorage.getItem('kamal-theme');
            const dark = window.matchMedia
                && window.matchMedia('(prefers-color-scheme: dark)').matches;

            document.documentElement.dataset.theme = saved || (dark ? 'dark' : 'light');
        })();
    </script>

    <link rel="stylesheet" href="../../css/style.css">
        <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    >

    <script defer src="../../js/customer.js"></script>
</head>
<body>

<div class="site-topbar">
    <div class="container">
        <span>
            <i class="fa-solid fa-clock"></i>
            Mon–Sat, 8:00 AM–6:00 PM
        </span>

        <span>
            <i class="fa-solid fa-sparkles"></i>
            More Than a Wash, It's a Revival
        </span>
    </div>
</div>

<header class="site-header">
    <div class="container site-header-inner">
        <a
            class="brand"
            href="<?= $isLoggedIn ? 'cust_home.php' : '../../index.php' ?>"
            aria-label="Kamal Car Wash home"
        >
            <img
                class="brand-logo"
                src="../../images/logo.png"
                alt="Kamal Car Wash logo"
            >

            <span class="brand-copy">
                <span class="brand-title">KAMAL CAR WASH</span>
                <span class="brand-subtitle">
                    <?= $isLoggedIn ? 'Customer Portal' : 'Professional Car Care' ?>
                </span>
            </span>
        </a>

        <button
            class="icon-btn nav-toggle"
            type="button"
            aria-label="Open navigation"
            aria-controls="customer-nav"
            aria-expanded="false"
            data-nav-toggle
        >
            <i class="fa-solid fa-bars"></i>
        </button>

        <nav class="site-nav" id="customer-nav" aria-label="Customer navigation">
            <?php if ($isLoggedIn): ?>
                <a
                    class="nav-link<?= customerNavActive(['cust_home.php']) ?>"
                    href="cust_home.php"
                >
                    <i class="fa-solid fa-house"></i>
                    Home
                </a>

                <a
                    class="nav-link<?= customerNavActive(['booking.php', 'booking_details.php', 'payment.php']) ?>"
                    href="booking.php"
                >
                    <i class="fa-solid fa-calendar-check"></i>
                    Book
                </a>

                <a
                    class="nav-link<?= customerNavActive(['vehicle.php']) ?>"
                    href="vehicle.php"
                >
                    <i class="fa-solid fa-car-side"></i>
                    Vehicles
                </a>

                <a
                    class="nav-link<?= customerNavActive(['receipt.php']) ?>"
                    href="receipt.php"
                >
                    <i class="fa-solid fa-receipt"></i>
                    Receipts
                </a>
            <?php endif; ?>

            <a
                class="nav-link<?= customerNavActive(['about.php']) ?>"
                href="about.php"
            >
                <i class="fa-solid fa-circle-info"></i>
                About
            </a>

            <a
                class="nav-link<?= customerNavActive(['contact.php']) ?>"
                href="contact.php"
            >
                <i class="fa-solid fa-envelope"></i>
                Contact
            </a>

            <?php if (!$isLoggedIn): ?>
                <a class="nav-link" href="../../auth/cust_login.php">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Login
                </a>
            <?php endif; ?>
        </nav>

        <div class="customer-header-actions">
            <button
                class="icon-btn theme-toggle"
                type="button"
                data-theme-toggle
                aria-label="Switch theme"
                title="Dark mode"
            >
                <i class="fa-solid fa-moon"></i>
            </button>

            <?php if ($isLoggedIn): ?>
                <div class="kcw-profile-menu" data-profile-menu-root>
                    <button
                        class="kcw-profile-trigger"
                        type="button"
                        aria-label="Open account menu"
                        aria-expanded="false"
                        data-profile-menu-toggle
                    >
                        <img
                            src="<?= htmlspecialchars($headerCustomerImage) ?>"
                            alt="<?= htmlspecialchars($headerCustomerName) ?> profile photo"
                            onerror="this.src='../../images/uploads/default.png'"
                        >
                    </button>

                    <div class="kcw-profile-dropdown" data-profile-menu>
                        <div class="kcw-profile-dropdown-head">
                            <img
                                src="<?= htmlspecialchars($headerCustomerImage) ?>"
                                alt=""
                                onerror="this.src='../../images/uploads/default.png'"
                            >

                            <div>
                                <strong><?= htmlspecialchars($headerCustomerName) ?></strong>
                                <span>Customer account</span>
                            </div>
                        </div>

                        <a href="profile.php">
                            <i class="fa-solid fa-user-gear"></i>
                            Profile
                        </a>

                        <a
                            class="logout"
                            href="../../auth/logout.php"
                            data-confirm-title="Sign out?"
                            data-confirm="You will need to sign in again to access your customer account."
                            data-confirm-text="Sign out"
                            data-confirm-tone="danger"
                        >
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            Sign out
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>
