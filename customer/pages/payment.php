<?php
session_start();
require_once "../../config.php";

if (!isset($_SESSION['cust_id'])) {
    header("Location: ../../auth/cust_login.php");
    exit();
}

$custId = (int) $_SESSION['cust_id'];
$serviceId = (int) ($_SESSION['service_id'] ?? $_GET['service_id'] ?? 0);

$booking = null;
if ($serviceId) {
    $stmt = $conn->prepare("
        SELECT s.*, v.vehicle_platenum, v.vehicle_brand, v.vehicle_model,
               p.package_name, p.package_price
        FROM service s
        JOIN vehicle v ON v.vehicle_id = s.vehicle_id
        JOIN package p ON p.package_id = s.package_id
        WHERE s.service_id = ? AND s.cust_id = ?
        LIMIT 1
    ");
    $stmt->execute([$serviceId, $custId]);
    $booking = $stmt->fetch();
}

$pageTitle = "Payment";
include "../includes/header.php";
?>

<style>
.payment-page {
    min-height: calc(100vh - 110px);
    padding: 64px 0 80px;
    background:
        radial-gradient(circle at 85% 15%, color-mix(in srgb, var(--success) 10%, transparent), transparent 24%),
        var(--bg);
}
.payment-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 350px;
    gap: 24px;
    align-items: start;
}
.confirmation-card { padding: 32px; }
.confirmation-icon {
    display: grid;
    width: 58px;
    height: 58px;
    place-items: center;
    border-radius: 18px;
    background: var(--success-soft);
    color: var(--success);
    font-size: 1.35rem;
}
.confirmation-card h1 {
    margin-top: 20px;
    color: var(--heading);
    font-family: 'Oswald', sans-serif;
    font-size: clamp(2rem, 4vw, 3rem);
    line-height: 1;
}
.confirmation-card > p { max-width: 650px; margin-top: 12px; color: var(--text-soft); }
.payment-notice {
    display: grid;
    grid-template-columns: 44px 1fr;
    gap: 13px;
    margin-top: 25px;
    padding: 17px;
    border: 1px solid var(--border);
    border-radius: 14px;
    background: var(--surface-2);
}
.payment-notice-icon {
    display: grid;
    width: 42px;
    height: 42px;
    place-items: center;
    border-radius: 12px;
    background: var(--primary-soft);
    color: var(--primary);
}
.payment-notice h3 { color: var(--heading); font-size: .92rem; }
.payment-notice p { margin-top: 3px; color: var(--text-soft); font-size: .8rem; }
.payment-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 25px; }

.booking-summary {
    position: sticky;
    top: 128px;
    padding: 24px;
}
.booking-summary h2 { color: var(--heading); font-size: 1.05rem; }
.summary-lines { display: grid; gap: 13px; margin-top: 18px; }
.summary-line {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
    font-size: .79rem;
}
.summary-line:last-child { border-bottom: 0; padding-bottom: 0; }
.summary-line span { color: var(--text-soft); }
.summary-line strong { max-width: 190px; color: var(--heading); text-align: right; }
.total-line {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 15px;
    margin-top: 20px;
    padding-top: 19px;
    border-top: 1px solid var(--border);
}
.total-line span { color: var(--text-soft); font-size: .78rem; font-weight: 700; }
.total-line strong { color: var(--heading); font-size: 1.45rem; }

@media (max-width: 820px) {
    .payment-grid { grid-template-columns: 1fr; }
    .booking-summary { position: static; }
}
@media (max-width: 520px) {
    .confirmation-card { padding: 24px; }
    .payment-actions { flex-direction: column; }
    .payment-actions .btn { width: 100%; }
}
</style>

<section class="payment-page">
    <div class="container payment-grid">
        <main class="card confirmation-card">
            <div class="confirmation-icon"><i class="fa-solid fa-check"></i></div>

            <?php if ($booking): ?>
                <span class="eyebrow" style="margin-top:18px;">Reservation #<?= (int)$booking['service_id'] ?></span>
                <h1>Your booking is saved.</h1>
                <p>
                    Your reservation is currently <strong><?= htmlspecialchars($booking['service_status']) ?></strong>.
                    The current Kamal Car Wash database does not yet support a complete online-payment transaction linked
                    directly to a service, so this page does not pretend to charge you online.
                </p>

                <div class="payment-notice">
                    <div class="payment-notice-icon"><i class="fa-solid fa-cash-register"></i></div>
                    <div>
                        <h3>Payment at the counter</h3>
                        <p>Pay when your service is processed. A receipt can be viewed after it is created by the business.</p>
                    </div>
                </div>

                <div class="payment-actions">
                    <a class="btn btn-primary" href="cust_home.php"><i class="fa-solid fa-house"></i> Back to dashboard</a>
                    <a class="btn btn-secondary" href="receipt.php"><i class="fa-solid fa-receipt"></i> View receipts</a>
                </div>
            <?php else: ?>
                <span class="eyebrow" style="margin-top:18px;">Payment</span>
                <h1>No active booking selected.</h1>
                <p>Create a reservation first and the booking summary will appear here.</p>
                <div class="payment-actions">
                    <a class="btn btn-primary" href="booking.php"><i class="fa-solid fa-calendar-plus"></i> Book a wash</a>
                </div>
            <?php endif; ?>
        </main>

        <?php if ($booking): ?>
            <aside class="card booking-summary">
                <h2>Booking summary</h2>
                <div class="summary-lines">
                    <div class="summary-line"><span>Package</span><strong><?= htmlspecialchars($booking['package_name']) ?></strong></div>
                    <div class="summary-line"><span>Vehicle</span><strong><?= htmlspecialchars($booking['vehicle_platenum']) ?><br><?= htmlspecialchars($booking['vehicle_brand'] . ' ' . $booking['vehicle_model']) ?></strong></div>
                    <div class="summary-line"><span>Date</span><strong><?= date('d M Y', strtotime($booking['service_date'])) ?></strong></div>
                    <div class="summary-line"><span>Time</span><strong><?= date('g:i A', strtotime($booking['service_time'])) ?></strong></div>
                    <div class="summary-line"><span>Status</span><strong><span class="status-badge status-<?= htmlspecialchars($booking['service_status']) ?>"><?= htmlspecialchars($booking['service_status']) ?></span></strong></div>
                </div>
                <div class="total-line">
                    <span>Amount due</span>
                    <strong>RM<?= number_format((float)$booking['package_price'], 2) ?></strong>
                </div>
            </aside>
        <?php endif; ?>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
