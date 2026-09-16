<?php
session_start();
require_once "../../config.php";

if (!isset($_SESSION['cust_id'])) {
    header("Location: ../../auth/cust_login.php");
    exit();
}

$custId = (int) $_SESSION['cust_id'];
$success = '';
$error = '';

if (isset($_POST['add_vehicle'])) {
    $plate = strtoupper(trim($_POST['vehicle_platenum'] ?? ''));
    $brand = trim($_POST['vehicle_brand'] ?? '');
    $model = trim($_POST['vehicle_model'] ?? '');
    $type = trim($_POST['vehicle_type'] ?? '');

    if ($plate === '' || $brand === '' || $model === '' || !in_array($type, ['A', 'B', 'C'], true)) {
        $error = 'Please complete every vehicle field.';
    } elseif (strlen($plate) > 20 || strlen($brand) > 15 || strlen($model) > 15) {
        $error = 'One or more vehicle values are longer than the current database allows.';
    } else {
        $check = $conn->prepare("SELECT vehicle_id FROM vehicle WHERE vehicle_platenum = ? LIMIT 1");
        $check->execute([$plate]);

        if ($check->fetch()) {
            $error = 'That plate number is already registered.';
        } else {
            $insert = $conn->prepare("
                INSERT INTO vehicle (cust_id, vehicle_platenum, vehicle_brand, vehicle_model, vehicle_type)
                VALUES (?, ?, ?, ?, ?)
            ");
            $insert->execute([$custId, $plate, $brand, $model, $type]);
            $success = 'Vehicle added successfully.';
        }
    }
}

if (isset($_POST['delete_vehicle'])) {
    $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);

    $owned = $conn->prepare("SELECT vehicle_id FROM vehicle WHERE vehicle_id = ? AND cust_id = ?");
    $owned->execute([$vehicleId, $custId]);

    if (!$owned->fetch()) {
        $error = 'Vehicle not found.';
    } else {
        $history = $conn->prepare("SELECT service_id FROM service WHERE vehicle_id = ? LIMIT 1");
        $history->execute([$vehicleId]);

        if ($history->fetch()) {
            $error = 'This vehicle has booking history, so it cannot be removed without losing service references.';
        } else {
            $delete = $conn->prepare("DELETE FROM vehicle WHERE vehicle_id = ? AND cust_id = ?");
            $delete->execute([$vehicleId, $custId]);
            $success = 'Vehicle removed.';
        }
    }
}

$stmt = $conn->prepare("
    SELECT v.*,
           (SELECT COUNT(*) FROM service s WHERE s.vehicle_id = v.vehicle_id) AS booking_count
    FROM vehicle v
    WHERE v.cust_id = ?
    ORDER BY v.vehicle_id DESC
");
$stmt->execute([$custId]);
$vehicles = $stmt->fetchAll();

function typeLabel(string $type): string {
    return match ($type) {
        'A' => 'Motorcycle',
        'B' => 'Normal Car',
        'C' => '4x4 / Van',
        default => $type
    };
}

function typeIcon(string $type): string {
    return $type === 'A' ? 'fa-motorcycle' : ($type === 'C' ? 'fa-truck-pickup' : 'fa-car-side');
}

$pageTitle = "My Vehicles";
include "../includes/header.php";
?>

<style>
.vehicle-hero {
    padding: 58px 0 46px;
    border-bottom: 1px solid var(--border);
    background:
        radial-gradient(circle at 82% 12%, color-mix(in srgb, var(--primary) 14%, transparent), transparent 26%),
        var(--surface);
}
.vehicle-hero-row {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 30px;
}
.vehicle-hero h1 { margin-top: 7px; }
.vehicle-hero p { margin-top: 10px; }

.vehicle-layout {
    display: grid;
    grid-template-columns: 390px minmax(0, 1fr);
    gap: 24px;
    align-items: start;
}
.vehicle-form-card {
    position: sticky;
    top: 128px;
    padding: 26px;
}
.vehicle-form-card h2 { color: var(--heading); font-size: 1.15rem; }
.vehicle-form-card p { margin-top: 5px; color: var(--text-soft); font-size: .82rem; }
.vehicle-form { display: grid; gap: 15px; margin-top: 22px; }
.vehicle-form .btn { width: 100%; }

.vehicle-list { display: grid; gap: 14px; }
.vehicle-card {
    display: grid;
    grid-template-columns: 62px 1fr auto;
    gap: 17px;
    align-items: center;
    padding: 20px;
}
.vehicle-icon {
    display: grid;
    width: 58px;
    height: 58px;
    place-items: center;
    border-radius: 16px;
    background: var(--primary-soft);
    color: var(--primary);
    font-size: 1.35rem;
}
.vehicle-info h3 {
    color: var(--heading);
    font-family: 'Oswald', sans-serif;
    font-size: 1.2rem;
    letter-spacing: .03em;
}
.vehicle-info p { margin-top: 3px; color: var(--text-soft); font-size: .82rem; }
.vehicle-tags { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 9px; }
.vehicle-tag {
    padding: 4px 8px;
    border-radius: 999px;
    background: var(--surface-2);
    color: var(--text-soft);
    font-size: .7rem;
    font-weight: 700;
}
.vehicle-actions { display: grid; gap: 8px; justify-items: end; }
.vehicle-actions form { margin: 0; }

@media (max-width: 900px) {
    .vehicle-layout { grid-template-columns: 1fr; }
    .vehicle-form-card { position: static; }
}
@media (max-width: 620px) {
    .vehicle-hero-row { align-items: flex-start; flex-direction: column; }
    .vehicle-card { grid-template-columns: 52px 1fr; }
    .vehicle-icon { width: 50px; height: 50px; }
    .vehicle-actions { grid-column: 2; justify-items: start; }
}
</style>

<section class="vehicle-hero">
    <div class="container vehicle-hero-row">
        <div>
            <span class="eyebrow"><i class="fa-solid fa-warehouse"></i> Garage</span>
            <h1 class="section-heading">Your registered vehicles</h1>
            <p class="section-copy">Keep the vehicles you book for in one place. Vehicle details are shown exactly as they are stored in the current database.</p>
        </div>
        <a class="btn btn-primary" href="booking.php"><i class="fa-solid fa-calendar-plus"></i> Book a wash</a>
    </div>
</section>

<section class="section">
    <div class="container vehicle-layout">
        <aside class="card vehicle-form-card">
            <span class="eyebrow"><i class="fa-solid fa-car-on"></i> Add vehicle</span>
            <h2>Register another vehicle</h2>
            <p>Plate numbers are converted to uppercase to keep them consistent.</p>

            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-top:18px;"><i class="fa-solid fa-circle-check"></i><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-top:18px;"><i class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form class="vehicle-form" method="POST">
                <div class="field">
                    <label for="vehicle_platenum">Plate number</label>
                    <input class="input" id="vehicle_platenum" name="vehicle_platenum" maxlength="20" placeholder="NXX 1234" required>
                </div>
                <div class="field">
                    <label for="vehicle_brand">Brand</label>
                    <input class="input" id="vehicle_brand" name="vehicle_brand" maxlength="15" placeholder="Perodua" required>
                </div>
                <div class="field">
                    <label for="vehicle_model">Model</label>
                    <input class="input" id="vehicle_model" name="vehicle_model" maxlength="15" placeholder="Myvi" required>
                </div>
                <div class="field">
                    <label for="vehicle_type">Vehicle type</label>
                    <select class="select" id="vehicle_type" name="vehicle_type" required>
                        <option value="">Choose type</option>
                        <option value="A">Motorcycle</option>
                        <option value="B">Normal Car</option>
                        <option value="C">4x4 / Van</option>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit" name="add_vehicle">
                    <i class="fa-solid fa-plus"></i> Add vehicle
                </button>
            </form>
        </aside>

        <main>
            <?php if ($vehicles): ?>
                <div class="vehicle-list">
                    <?php foreach ($vehicles as $vehicle): ?>
                        <article class="card vehicle-card">
                            <div class="vehicle-icon"><i class="fa-solid <?= typeIcon($vehicle['vehicle_type']) ?>"></i></div>
                            <div class="vehicle-info">
                                <h3><?= htmlspecialchars($vehicle['vehicle_platenum']) ?></h3>
                                <p><?= htmlspecialchars($vehicle['vehicle_brand'] . ' ' . $vehicle['vehicle_model']) ?></p>
                                <div class="vehicle-tags">
                                    <span class="vehicle-tag"><?= htmlspecialchars(typeLabel($vehicle['vehicle_type'])) ?></span>
                                    <span class="vehicle-tag"><?= (int)$vehicle['booking_count'] ?> booking<?= (int)$vehicle['booking_count'] === 1 ? '' : 's' ?></span>
                                </div>
                            </div>
                            <div class="vehicle-actions">
                                <a class="btn btn-secondary" href="booking.php"><i class="fa-solid fa-calendar-check"></i> Book</a>
                                <?php if ((int)$vehicle['booking_count'] === 0): ?>
                                    <form method="POST">
                                        <input type="hidden" name="vehicle_id" value="<?= (int)$vehicle['vehicle_id'] ?>">
                                        <button class="btn btn-danger" type="submit" name="delete_vehicle"
                                                data-confirm="Remove this vehicle from your account?">
                                            <i class="fa-solid fa-trash"></i> Remove
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card empty-state">
                    <i class="fa-solid fa-car-side"></i>
                    <h3>No vehicles yet</h3>
                    <p>Add your first vehicle using the form, then you can create a reservation.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
