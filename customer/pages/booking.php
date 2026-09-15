<?php
session_start();
require_once "../../config.php";

if (!isset($_SESSION['cust_id'])) {
    header("Location: ../../auth/cust_login.php");
    exit();
}

$custId = (int) $_SESSION['cust_id'];
$slots = ["08:00", "09:00", "10:00", "11:00", "12:00", "14:00", "15:00", "16:00", "17:00"];
$selectedDate = trim($_GET['date'] ?? '');
$bookedSlots = [];
$dateError = '';

if ($selectedDate !== '') {
    $dateObject = DateTime::createFromFormat('Y-m-d', $selectedDate);
    if (!$dateObject || $dateObject->format('Y-m-d') !== $selectedDate) {
        $dateError = 'Please choose a valid booking date.';
        $selectedDate = '';
    } elseif ($selectedDate < date('Y-m-d')) {
        $dateError = 'Past dates cannot be booked.';
    } elseif (date('N', strtotime($selectedDate)) === '7') {
        $dateError = 'Kamal Car Wash is closed on Sundays. Please choose Monday to Saturday.';
    } else {
        $stmt = $conn->prepare("
            SELECT TIME_FORMAT(service_time, '%H:%i')
            FROM service
            WHERE service_date = ?
              AND service_status <> 'Cancelled'
        ");
        $stmt->execute([$selectedDate]);
        $bookedSlots = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

$vehicleStmt = $conn->prepare("SELECT * FROM vehicle WHERE cust_id = ? ORDER BY vehicle_platenum ASC");
$vehicleStmt->execute([$custId]);
$vehicles = $vehicleStmt->fetchAll();

$packageStmt = $conn->query("SELECT * FROM package ORDER BY package_type ASC, package_price ASC");
$packages = $packageStmt->fetchAll();

$groupedPackages = [1 => [], 2 => [], 3 => []];
foreach ($packages as $package) {
    $type = (int) $package['package_type'];
    if (!isset($groupedPackages[$type])) $groupedPackages[$type] = [];
    $groupedPackages[$type][] = $package;
}

$pageTitle = "Book a Wash";
include "../includes/header.php";
?>

<style>
.booking-hero {
    position: relative;
    min-height: 300px;
    display: grid;
    align-items: end;
    overflow: hidden;
    background:
        linear-gradient(90deg, rgba(8, 25, 42, .86), rgba(8, 25, 42, .46)),
        url("../../images/hero.jpg") center 58%/cover no-repeat;
    color: #fff;
}
.booking-hero .container { position: relative; z-index: 2; padding-bottom: 48px; }
.booking-hero h1 {
    margin-top: 9px;
    max-width: 700px;
    font-family: 'Oswald', sans-serif;
    font-size: clamp(2.5rem, 5vw, 4.4rem);
    line-height: 1;
}
.booking-hero p { max-width: 600px; margin-top: 12px; color: rgba(255,255,255,.72); }

.booking-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 24px;
    align-items: start;
}
.booking-card { padding: 28px; }
.booking-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
}
.booking-card-header h2 { color: var(--heading); font-size: 1.3rem; }
.booking-card-header p { margin-top: 5px; color: var(--text-soft); font-size: .85rem; }

.step-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 10px;
    border-radius: 999px;
    background: var(--primary-soft);
    color: var(--primary);
    font-size: .7rem;
    font-weight: 800;
    white-space: nowrap;
}
.date-form { display: grid; grid-template-columns: 1fr auto; gap: 10px; }
.booking-form { display: grid; gap: 18px; margin-top: 24px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.slot-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 9px;
    margin-top: 8px;
}
.slot-option { position: relative; }
.slot-option input { position: absolute; opacity: 0; pointer-events: none; }
.slot-label {
    display: flex;
    min-height: 45px;
    align-items: center;
    justify-content: center;
    gap: 7px;
    border: 1px solid var(--border);
    border-radius: 11px;
    background: var(--surface);
    color: var(--text);
    cursor: pointer;
    font-size: .82rem;
    font-weight: 700;
    transition: .18s ease;
}
.slot-option input:checked + .slot-label {
    border-color: var(--primary);
    background: var(--primary-soft);
    color: var(--primary);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 10%, transparent);
}
.slot-label:hover { border-color: var(--primary); }
.slot-option.unavailable .slot-label {
    cursor: not-allowed;
    opacity: .45;
    text-decoration: line-through;
}

.package-groups { display: grid; gap: 12px; }
.package-type-group {
    overflow: hidden;
    border: 1px solid var(--border);
    border-radius: 13px;
}
.package-type-label {
    padding: 9px 12px;
    background: var(--surface-2);
    color: var(--text-soft);
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .09em;
    text-transform: uppercase;
}
.package-radio {
    position: relative;
    display: block;
    border-top: 1px solid var(--border);
}
.package-radio:first-of-type { border-top: 0; }
.package-radio input { position: absolute; opacity: 0; pointer-events: none; }
.package-radio-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 13px 14px;
    cursor: pointer;
    transition: background-color .18s ease;
}
.package-radio-content:hover { background: var(--surface-2); }
.package-radio input:checked + .package-radio-content {
    background: var(--primary-soft);
    color: var(--primary);
}
.package-radio-title { font-size: .86rem; font-weight: 700; }
.package-radio-price { white-space: nowrap; font-size: .85rem; font-weight: 800; }

.booking-side {
    position: sticky;
    top: 128px;
    display: grid;
    gap: 14px;
}
.side-card { padding: 22px; }
.side-card h3 { color: var(--heading); font-size: 1rem; }
.side-card p { margin-top: 7px; color: var(--text-soft); font-size: .82rem; }
.side-list { display: grid; gap: 11px; margin-top: 17px; list-style: none; }
.side-list li { display: flex; gap: 10px; color: var(--text-soft); font-size: .81rem; }
.side-list i { margin-top: 4px; color: var(--primary); }
.reserve-visual {
    display: grid;
    place-items: center;
    min-height: 240px;
    padding: 20px;
    overflow: hidden;
    background: linear-gradient(145deg, var(--primary-soft), var(--surface));
}
.reserve-visual img { width: min(210px, 85%); filter: drop-shadow(0 15px 22px rgba(0,0,0,.12)); }

@media (max-width: 900px) {
    .booking-layout { grid-template-columns: 1fr; }
    .booking-side { position: static; grid-template-columns: 1fr 1fr; }
}
@media (max-width: 650px) {
    .booking-card { padding: 20px; }
    .date-form, .form-grid { grid-template-columns: 1fr; }
    .slot-grid { grid-template-columns: repeat(2, 1fr); }
    .booking-side { grid-template-columns: 1fr; }
    .reserve-visual { display: none; }
}
</style>

<section class="booking-hero">
    <div class="container">
        <span class="eyebrow" style="color:#b9d5ee;"><i class="fa-solid fa-calendar-check"></i> Online reservation</span>
        <h1>Pick a time that works for you.</h1>
        <p>Choose your registered vehicle, an available time slot and the wash package you want before confirming.</p>
    </div>
</section>

<section class="section">
    <div class="container booking-layout">
        <main class="card booking-card">
            <div class="booking-card-header">
                <div>
                    <h2>Reservation details</h2>
                    <p>Start by choosing the date. Available slots are checked against existing active bookings.</p>
                </div>
                <span class="step-chip"><i class="fa-solid fa-1"></i> Select date</span>
            </div>

            <?php if ($dateError): ?>
                <div class="alert alert-error" style="margin-bottom:16px;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($dateError) ?></span>
                </div>
            <?php endif; ?>

            <form class="date-form" method="GET">
                <div class="field">
                    <label for="date">Booking date</label>
                    <input class="input" id="date" type="date" name="date"
                           min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($selectedDate) ?>" required>
                </div>
                <button class="btn btn-secondary" type="submit" style="align-self:end;">
                    <i class="fa-solid fa-magnifying-glass"></i> Check slots
                </button>
            </form>

            <?php if ($selectedDate !== '' && !$dateError): ?>
                <?php if (!$vehicles): ?>
                    <div class="alert alert-warning" style="margin-top:22px;">
                        <i class="fa-solid fa-car-side"></i>
                        <div>
                            <strong>No registered vehicle found.</strong><br>
                            Add a vehicle before creating a reservation.
                            <a href="vehicle.php" style="font-weight:800;">Add vehicle</a>
                        </div>
                    </div>
                <?php else: ?>
                    <form class="booking-form" method="POST" action="booking_details.php">
                        <input type="hidden" name="service_date" value="<?= htmlspecialchars($selectedDate) ?>">

                        <div class="form-grid">
                            <div class="field">
                                <label for="vehicle_id">Vehicle</label>
                                <select class="select" id="vehicle_id" name="vehicle_id" required>
                                    <option value="">Select your vehicle</option>
                                    <?php foreach ($vehicles as $vehicle): ?>
                                        <option value="<?= (int)$vehicle['vehicle_id'] ?>">
                                            <?= htmlspecialchars($vehicle['vehicle_platenum'] . ' · ' . $vehicle['vehicle_brand'] . ' ' . $vehicle['vehicle_model']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="field">
                                <label>Available time</label>
                                <div class="helper-text">Closed from 1:00 PM–2:00 PM.</div>
                            </div>
                        </div>

                        <div class="slot-grid" aria-label="Available time slots">
                            <?php foreach ($slots as $slot): ?>
                                <?php $isBooked = in_array($slot, $bookedSlots, true); ?>
                                <label class="slot-option <?= $isBooked ? 'unavailable' : '' ?>">
                                    <input type="radio" name="service_time" value="<?= $slot ?>" <?= $isBooked ? 'disabled' : '' ?> required>
                                    <span class="slot-label">
                                        <i class="fa-regular fa-clock"></i>
                                        <?= date('g:i A', strtotime($slot)) ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="field">
                            <label>Package</label>
                            <div class="package-groups">
                                <?php
                                $typeNames = [1 => 'Basic', 2 => 'Deluxe', 3 => 'Premium'];
                                foreach ($typeNames as $typeId => $typeName):
                                    if (empty($groupedPackages[$typeId])) continue;
                                ?>
                                    <div class="package-type-group">
                                        <div class="package-type-label"><?= $typeName ?></div>
                                        <?php foreach ($groupedPackages[$typeId] as $package): ?>
                                            <label class="package-radio">
                                                <input type="radio" name="package_id" value="<?= (int)$package['package_id'] ?>" required>
                                                <span class="package-radio-content">
                                                    <span class="package-radio-title"><?= htmlspecialchars($package['package_name']) ?></span>
                                                    <span class="package-radio-price">RM<?= number_format((float)$package['package_price'], 2) ?></span>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit" style="justify-self:start;">
                            Review booking <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </main>

        <aside class="booking-side">
            <div class="card reserve-visual">
                <img src="../../images/reserve.png" alt="Car wash reservation illustration">
            </div>
            <div class="card side-card">
                <h3><i class="fa-solid fa-circle-info" style="color:var(--primary);"></i> Before you book</h3>
                <ul class="side-list">
                    <li><i class="fa-solid fa-check"></i><span>Only active bookings block a time slot.</span></li>
                    <li><i class="fa-solid fa-check"></i><span>Sunday is excluded because the business is closed.</span></li>
                    <li><i class="fa-solid fa-check"></i><span>You can review the full booking before it is saved.</span></li>
                </ul>
            </div>
        </aside>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
