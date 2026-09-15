<?php
session_start();
require_once "../../config.php";

if (!isset($_SESSION['cust_id'])) {
    header("Location: ../../auth/cust_login.php");
    exit();
}

$custId = (int) $_SESSION['cust_id'];
$allowedSlots = ["08:00", "09:00", "10:00", "11:00", "12:00", "14:00", "15:00", "16:00", "17:00"];

$vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
$packageId = (int) ($_POST['package_id'] ?? 0);
$serviceDate = trim($_POST['service_date'] ?? '');
$serviceTime = substr(trim($_POST['service_time'] ?? ''), 0, 5);

if (!$vehicleId || !$packageId || !$serviceDate || !$serviceTime) {
    header("Location: booking.php");
    exit();
}

if ($serviceDate < date('Y-m-d') || date('N', strtotime($serviceDate)) === '7' || !in_array($serviceTime, $allowedSlots, true)) {
    $_SESSION['booking_error'] = 'The selected date or time is not available.';
    header("Location: booking.php");
    exit();
}

$vehicleStmt = $conn->prepare("SELECT * FROM vehicle WHERE vehicle_id = ? AND cust_id = ?");
$vehicleStmt->execute([$vehicleId, $custId]);
$vehicle = $vehicleStmt->fetch();

$packageStmt = $conn->prepare("SELECT * FROM package WHERE package_id = ?");
$packageStmt->execute([$packageId]);
$package = $packageStmt->fetch();

if (!$vehicle || !$package) {
    header("Location: booking.php");
    exit();
}

$error = '';

if (isset($_POST['confirm_booking'])) {
    $check = $conn->prepare("
        SELECT service_id
        FROM service
        WHERE service_date = ?
          AND service_time = ?
          AND service_status <> 'Cancelled'
        LIMIT 1
    ");
    $check->execute([$serviceDate, $serviceTime . ':00']);

    if ($check->fetch()) {
        $error = 'That time slot was just taken. Please choose another available time.';
    } else {
        $insert = $conn->prepare("
            INSERT INTO service
                (cust_id, package_id, vehicle_id, service_date, service_time, service_status)
            VALUES (?, ?, ?, ?, ?, 'Pending')
        ");
        $insert->execute([$custId, $packageId, $vehicleId, $serviceDate, $serviceTime . ':00']);

        $_SESSION['service_id'] = (int) $conn->lastInsertId();
        header("Location: payment.php");
        exit();
    }
}

$pageTitle = "Review Booking";
include "../includes/header.php";

function vehicleTypeName(string $type): string {
    return match ($type) {
        'A' => 'Motorcycle',
        'B' => 'Normal Car',
        'C' => '4x4 / Van',
        default => $type
    };
}
?>

<style>
.review-shell {
    min-height: calc(100vh - 110px);
    padding: 62px 0 80px;
    background:
        radial-gradient(circle at 85% 8%, color-mix(in srgb, var(--primary) 10%, transparent), transparent 26%),
        var(--bg);
}
.review-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 24px;
    align-items: start;
}
.review-card { padding: 30px; }
.review-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding-bottom: 23px;
    border-bottom: 1px solid var(--border);
}
.review-head h1 {
    color: var(--heading);
    font-family: 'Oswald', sans-serif;
    font-size: clamp(2rem, 4vw, 3rem);
    line-height: 1;
}
.review-head p { margin-top: 7px; color: var(--text-soft); font-size: .84rem; }

.detail-list { display: grid; margin-top: 8px; }
.detail-row {
    display: grid;
    grid-template-columns: 165px 1fr;
    gap: 22px;
    padding: 17px 0;
    border-bottom: 1px solid var(--border);
}
.detail-row:last-child { border-bottom: 0; }
.detail-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--text-soft);
    font-size: .8rem;
    font-weight: 700;
}
.detail-value { color: var(--heading); font-size: .92rem; font-weight: 700; }
.detail-value small { display: block; margin-top: 3px; color: var(--text-soft); font-weight: 500; }

.review-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 24px;
}

.summary-card {
    position: sticky;
    top: 128px;
    overflow: hidden;
}
.summary-visual {
    display: grid;
    min-height: 185px;
    place-items: center;
    padding: 20px;
    background: linear-gradient(145deg, var(--primary-soft), var(--surface));
}
.summary-visual img { width: 160px; filter: drop-shadow(0 14px 22px rgba(0,0,0,.13)); }
.summary-body { padding: 23px; }
.summary-body h2 { color: var(--heading); font-size: 1.05rem; }
.summary-body p { margin-top: 7px; color: var(--text-soft); font-size: .81rem; }
.price-line {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 15px;
    margin-top: 21px;
    padding-top: 18px;
    border-top: 1px solid var(--border);
}
.price-line span { color: var(--text-soft); font-size: .8rem; font-weight: 700; }
.price-line strong { color: var(--heading); font-size: 1.45rem; }

@media (max-width: 850px) {
    .review-grid { grid-template-columns: 1fr; }
    .summary-card { position: static; }
}
@media (max-width: 560px) {
    .review-card { padding: 22px; }
    .detail-row { grid-template-columns: 1fr; gap: 6px; }
    .review-actions .btn { width: 100%; }
}
</style>

<section class="review-shell">
    <div class="container review-grid">
        <main class="card review-card">
            <div class="review-head">
                <div>
                    <span class="eyebrow"><i class="fa-solid fa-clipboard-check"></i> Final check</span>
                    <h1>Review your booking</h1>
                    <p>Nothing is saved until you press Confirm booking.</p>
                </div>
                <span class="step-chip" style="display:inline-flex;padding:7px 11px;border-radius:999px;background:var(--primary-soft);color:var(--primary);font-size:.72rem;font-weight:800;">
                    Step 2 of 2
                </span>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-top:20px;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <div class="detail-list">
                <div class="detail-row">
                    <div class="detail-label"><i class="fa-solid fa-car-side"></i> Vehicle</div>
                    <div class="detail-value">
                        <?= htmlspecialchars($vehicle['vehicle_platenum']) ?>
                        <small><?= htmlspecialchars($vehicle['vehicle_brand'] . ' ' . $vehicle['vehicle_model'] . ' · ' . vehicleTypeName($vehicle['vehicle_type'])) ?></small>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label"><i class="fa-regular fa-calendar"></i> Date</div>
                    <div class="detail-value"><?= date('l, d F Y', strtotime($serviceDate)) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label"><i class="fa-regular fa-clock"></i> Time</div>
                    <div class="detail-value"><?= date('g:i A', strtotime($serviceTime)) ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label"><i class="fa-solid fa-spray-can-sparkles"></i> Package</div>
                    <div class="detail-value">
                        <?= htmlspecialchars($package['package_name']) ?>
                        <small>Package category <?= (int)$package['package_type'] ?></small>
                    </div>
                </div>
            </div>

            <form method="POST" class="review-actions">
                <input type="hidden" name="vehicle_id" value="<?= $vehicleId ?>">
                <input type="hidden" name="package_id" value="<?= $packageId ?>">
                <input type="hidden" name="service_date" value="<?= htmlspecialchars($serviceDate) ?>">
                <input type="hidden" name="service_time" value="<?= htmlspecialchars($serviceTime) ?>">

                <button class="btn btn-primary" type="submit" name="confirm_booking">
                    <i class="fa-solid fa-check"></i> Confirm booking
                </button>
                <a class="btn btn-secondary" href="booking.php?date=<?= urlencode($serviceDate) ?>">
                    <i class="fa-solid fa-arrow-left"></i> Change details
                </a>
            </form>
        </main>

        <aside class="card summary-card">
            <div class="summary-visual">
                <img src="../../images/reserve.png" alt="">
            </div>
            <div class="summary-body">
                <span class="eyebrow">Booking total</span>
                <h2><?= htmlspecialchars($package['package_name']) ?></h2>
                <p>The current database stores a single price per package, so this is the amount shown by the existing system.</p>
                <div class="price-line">
                    <span>Amount</span>
                    <strong>RM<?= number_format((float)$package['package_price'], 2) ?></strong>
                </div>
            </div>
        </aside>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
