<?php
session_start();
require_once "../../config.php";

if (!isset($_SESSION['cust_id'])) {
    header("Location: ../../auth/cust_login.php");
    exit();
}

$custId = (int) $_SESSION['cust_id'];

$stmt = $conn->prepare("SELECT cust_name FROM customer WHERE cust_id = ?");
$stmt->execute([$custId]);
$customer = $stmt->fetch();

if (!$customer) {
    session_destroy();
    header("Location: ../../auth/cust_login.php");
    exit();
}

$upcomingStmt = $conn->prepare("
    SELECT s.service_id, s.service_date, s.service_time, s.service_status,
           v.vehicle_platenum, v.vehicle_brand, v.vehicle_model,
           p.package_name, p.package_price
    FROM service s
    JOIN vehicle v ON v.vehicle_id = s.vehicle_id
    JOIN package p ON p.package_id = s.package_id
    WHERE s.cust_id = ?
      AND s.service_status <> 'Cancelled'
      AND s.service_date >= CURDATE()
    ORDER BY s.service_date ASC, s.service_time ASC
    LIMIT 3
");
$upcomingStmt->execute([$custId]);
$upcoming = $upcomingStmt->fetchAll();

$packageStmt = $conn->query("
    SELECT package_id, package_name, package_price, package_type
    FROM package
    ORDER BY package_type ASC, package_price ASC
    LIMIT 6
");
$packages = $packageStmt->fetchAll();

$pageTitle = "Home";
include "../includes/header.php";
?>

<style>
.home-hero {
    position: relative;
    min-height: 610px;
    display: grid;
    align-items: center;
    overflow: hidden;
    background:
        linear-gradient(90deg, rgba(9, 24, 40, .88) 0%, rgba(9, 24, 40, .67) 48%, rgba(9, 24, 40, .32) 100%),
        url("../../images/hero.jpg") center/cover no-repeat;
    color: #fff;
}
.home-hero::after {
    content: "";
    position: absolute;
    inset: auto 0 0;
    height: 140px;
    background: linear-gradient(to bottom, transparent, rgba(5, 16, 28, .28));
    pointer-events: none;
}
.hero-shell {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) minmax(320px, .55fr);
    gap: 64px;
    align-items: center;
}
.hero-copy { max-width: 720px; }
.hero-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 18px;
    padding: 7px 11px;
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 999px;
    background: rgba(255,255,255,.09);
    backdrop-filter: blur(8px);
    color: rgba(255,255,255,.88);
    font-size: .76rem;
    font-weight: 800;
    letter-spacing: .11em;
    text-transform: uppercase;
}
.hero-title {
    max-width: 760px;
    font-family: 'Oswald', sans-serif;
    font-size: clamp(3rem, 6vw, 5.7rem);
    line-height: .98;
    letter-spacing: -.03em;
    text-wrap: balance;
}
.hero-title span { color: #9fc5e8; }
.hero-subtitle {
    max-width: 620px;
    margin-top: 22px;
    color: rgba(255,255,255,.75);
    font-size: 1.02rem;
}
.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 30px;
}
.hero-actions .btn-secondary {
    border-color: rgba(255,255,255,.22);
    background: rgba(255,255,255,.09);
    color: #fff;
    box-shadow: none;
}
.hero-panel {
    padding: 24px;
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 22px;
    background: rgba(12, 29, 48, .72);
    backdrop-filter: blur(18px);
    box-shadow: 0 24px 55px rgba(0,0,0,.2);
}
.hero-panel-label {
    color: rgba(255,255,255,.58);
    font-size: .76rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.hero-panel h2 {
    margin-top: 4px;
    font-size: 1.3rem;
}
.hero-panel-list {
    display: grid;
    gap: 13px;
    margin-top: 20px;
    list-style: none;
}
.hero-panel-list li {
    display: flex;
    align-items: center;
    gap: 11px;
    color: rgba(255,255,255,.82);
    font-size: .88rem;
}
.hero-panel-list i {
    display: grid;
    width: 32px;
    height: 32px;
    place-items: center;
    border-radius: 9px;
    background: rgba(255,255,255,.09);
    color: #9fc5e8;
}

.home-strip {
    position: relative;
    z-index: 3;
    margin-top: -36px;
}
.home-strip-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    overflow: hidden;
    border: 1px solid var(--border);
    border-radius: 20px;
    background: var(--surface);
    box-shadow: var(--shadow-md);
}
.strip-item {
    display: flex;
    gap: 15px;
    padding: 23px;
}
.strip-item + .strip-item { border-left: 1px solid var(--border); }
.strip-icon {
    display: grid;
    width: 44px;
    height: 44px;
    flex: 0 0 auto;
    place-items: center;
    border-radius: 13px;
    background: var(--primary-soft);
    color: var(--primary);
}
.strip-item h3 { color: var(--heading); font-size: .95rem; }
.strip-item p { margin-top: 3px; color: var(--text-soft); font-size: .8rem; }

.dashboard-section { padding: 76px 0; }
.dashboard-head {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 30px;
    margin-bottom: 26px;
}
.welcome-copy h2 {
    margin-top: 5px;
    color: var(--heading);
    font-size: clamp(1.7rem, 3vw, 2.4rem);
}
.welcome-copy p { margin-top: 6px; color: var(--text-soft); }

.booking-list { display: grid; gap: 13px; }
.booking-card {
    display: grid;
    grid-template-columns: 86px 1fr auto;
    gap: 17px;
    align-items: center;
    padding: 18px;
}
.booking-date {
    display: grid;
    min-height: 72px;
    place-items: center;
    align-content: center;
    border-radius: 14px;
    background: var(--primary-soft);
    color: var(--primary);
    text-align: center;
}
.booking-date strong { font-size: 1.25rem; line-height: 1; }
.booking-date span { margin-top: 4px; font-size: .67rem; font-weight: 800; text-transform: uppercase; }
.booking-main h3 { color: var(--heading); font-size: .98rem; }
.booking-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 13px;
    margin-top: 7px;
    color: var(--text-soft);
    font-size: .78rem;
}
.booking-meta span { display: inline-flex; align-items: center; gap: 6px; }

.packages-section {
    padding: 70px 0 82px;
    background: var(--surface-2);
}
.package-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-top: 30px;
}
.package-card {
    position: relative;
    display: grid;
    gap: 14px;
    min-height: 215px;
    padding: 24px;
    overflow: hidden;
}
.package-card::before {
    content: "";
    position: absolute;
    width: 100px;
    height: 100px;
    top: -38px;
    right: -34px;
    border-radius: 50%;
    background: var(--primary-soft);
}
.package-icon {
    display: grid;
    width: 43px;
    height: 43px;
    place-items: center;
    border-radius: 12px;
    background: var(--primary-soft);
    color: var(--primary);
}
.package-card h3 { color: var(--heading); }
.package-card p { color: var(--text-soft); font-size: .84rem; }
.package-bottom {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 15px;
    margin-top: auto;
}
.package-price { color: var(--heading); font-size: 1.25rem; font-weight: 800; }
.package-price small { color: var(--text-soft); font-size: .69rem; font-weight: 600; }

@media (max-width: 900px) {
    .home-hero { min-height: 560px; }
    .hero-shell { grid-template-columns: 1fr; gap: 30px; }
    .hero-panel { display: none; }
    .home-strip-grid { grid-template-columns: 1fr; }
    .strip-item + .strip-item { border-left: 0; border-top: 1px solid var(--border); }
    .package-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 650px) {
    .home-hero { min-height: 520px; }
    .hero-title { font-size: clamp(2.7rem, 14vw, 4.2rem); }
    .home-strip { margin-top: -24px; }
    .dashboard-head { align-items: flex-start; flex-direction: column; }
    .booking-card { grid-template-columns: 70px 1fr; }
    .booking-card > .status-badge { grid-column: 2; justify-self: start; }
    .package-grid { grid-template-columns: 1fr; }
}
</style>

<section class="home-hero">
    <div class="container hero-shell">
        <div class="hero-copy">
            <div class="hero-kicker"><i class="fa-solid fa-droplet"></i> Customer car-care portal</div>
            <h1 class="hero-title">Clean car. <span>Zero hassle.</span></h1>
            <p class="hero-subtitle">
                Welcome back, <?= htmlspecialchars($customer['cust_name']) ?>. Reserve your preferred wash slot,
                keep your vehicles organised and track your latest bookings from one clean dashboard.
            </p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="booking.php"><i class="fa-solid fa-calendar-plus"></i> Book a wash</a>
                <a class="btn btn-secondary" href="vehicle.php"><i class="fa-solid fa-car-side"></i> My vehicles</a>
            </div>
        </div>

        <aside class="hero-panel" aria-label="Service highlights">
            <span class="hero-panel-label">Designed around your time</span>
            <h2>Simple from booking to shine.</h2>
            <ul class="hero-panel-list">
                <li><i class="fa-solid fa-clock"></i> Pick an available one-hour slot</li>
                <li><i class="fa-solid fa-layer-group"></i> Compare packages before booking</li>
                <li><i class="fa-solid fa-car"></i> Keep multiple vehicles under one account</li>
                <li><i class="fa-solid fa-receipt"></i> View generated service receipts</li>
            </ul>
        </aside>
    </div>
</section>

<div class="container home-strip">
    <div class="home-strip-grid">
        <div class="strip-item">
            <div class="strip-icon"><i class="fa-solid fa-bolt"></i></div>
            <div><h3>Fast booking</h3><p>Choose your car, package, date and available time without unnecessary steps.</p></div>
        </div>
        <div class="strip-item">
            <div class="strip-icon"><i class="fa-solid fa-shield-heart"></i></div>
            <div><h3>Trusted care</h3><p>Your reservation stays attached to the correct customer and registered vehicle.</p></div>
        </div>
        <div class="strip-item">
            <div class="strip-icon"><i class="fa-solid fa-wallet"></i></div>
            <div><h3>Clear pricing</h3><p>Package prices are shown before you confirm the reservation.</p></div>
        </div>
    </div>
</div>

<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-head">
            <div class="welcome-copy">
                <span class="eyebrow"><i class="fa-solid fa-calendar-day"></i> Your schedule</span>
                <h2>Upcoming reservations</h2>
                <p>Your nearest active bookings are shown first.</p>
            </div>
            <a class="btn btn-secondary" href="booking.php"><i class="fa-solid fa-plus"></i> New booking</a>
        </div>

        <?php if ($upcoming): ?>
            <div class="booking-list">
                <?php foreach ($upcoming as $booking): ?>
                    <?php $time = strtotime($booking['service_time']); ?>
                    <article class="card booking-card">
                        <div class="booking-date">
                            <strong><?= date('d', strtotime($booking['service_date'])) ?></strong>
                            <span><?= date('M Y', strtotime($booking['service_date'])) ?></span>
                        </div>
                        <div class="booking-main">
                            <h3><?= htmlspecialchars($booking['package_name']) ?></h3>
                            <div class="booking-meta">
                                <span><i class="fa-regular fa-clock"></i> <?= date('g:i A', $time) ?></span>
                                <span><i class="fa-solid fa-car"></i> <?= htmlspecialchars($booking['vehicle_platenum']) ?></span>
                                <span><?= htmlspecialchars($booking['vehicle_brand'] . ' ' . $booking['vehicle_model']) ?></span>
                                <span><i class="fa-solid fa-tag"></i> RM<?= number_format((float)$booking['package_price'], 2) ?></span>
                            </div>
                        </div>
                        <span class="status-badge status-<?= htmlspecialchars($booking['service_status']) ?>">
                            <?= htmlspecialchars($booking['service_status']) ?>
                        </span>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card empty-state">
                <i class="fa-regular fa-calendar-check"></i>
                <h3>No upcoming reservation</h3>
                <p>Your next wash can be booked in a few clicks.</p>
                <a class="btn btn-primary" href="booking.php">Book now</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="packages-section">
    <div class="container">
        <span class="eyebrow"><i class="fa-solid fa-spray-can-sparkles"></i> Packages</span>
        <h2 class="section-heading">Choose the care your car needs.</h2>
        <p class="section-copy" style="margin-top:12px;">Prices come directly from the current package records in your Kamal Car Wash database.</p>

        <div class="package-grid">
            <?php foreach ($packages as $package): ?>
                <?php
                    $typeName = match ((int)$package['package_type']) {
                        1 => 'Basic',
                        2 => 'Deluxe',
                        3 => 'Premium',
                        default => 'Car Wash'
                    };
                ?>
                <article class="card package-card">
                    <div class="package-icon"><i class="fa-solid fa-soap"></i></div>
                    <div>
                        <span class="eyebrow"><?= htmlspecialchars($typeName) ?></span>
                        <h3><?= htmlspecialchars($package['package_name']) ?></h3>
                        <p>Book this package with one of your registered vehicles and an available time slot.</p>
                    </div>
                    <div class="package-bottom">
                        <div class="package-price">RM<?= number_format((float)$package['package_price'], 2) ?> <small>/ service</small></div>
                        <a class="btn btn-secondary" href="booking.php"><i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
