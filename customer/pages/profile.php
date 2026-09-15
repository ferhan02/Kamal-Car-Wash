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

$stmt = $conn->prepare("SELECT * FROM customer WHERE cust_id = ?");
$stmt->execute([$custId]);
$customer = $stmt->fetch();

if (!$customer) {
    session_destroy();
    header("Location: ../../auth/cust_login.php");
    exit();
}

if (isset($_POST['update_profile'])) {
    $name = trim($_POST['cust_name'] ?? '');
    $dob = trim($_POST['cust_dob'] ?? '');
    $phone = trim($_POST['cust_phonenum'] ?? '');
    $email = strtolower(trim($_POST['cust_email'] ?? ''));
    $username = trim($_POST['cust_username'] ?? '');
    $gender = $_POST['cust_gender'] ?? '';
    $state = trim($_POST['cust_state'] ?? '');

    if ($name === '' || $dob === '' || $phone === '' || $email === '' || $username === '' || $state === '' || !in_array((string)$gender, ['0', '1'], true)) {
        $error = 'Please complete every required profile field.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($name) > 50 || strlen($email) > 30 || strlen($username) > 50 || strlen($state) > 30) {
        $error = 'One or more values are longer than the current database allows.';
    } else {
        $duplicate = $conn->prepare("
            SELECT cust_id
            FROM customer
            WHERE (cust_email = ? OR cust_username = ?)
              AND cust_id <> ?
            LIMIT 1
        ");
        $duplicate->execute([$email, $username, $custId]);

        if ($duplicate->fetch()) {
            $error = 'That email address or username is already used by another customer.';
        } else {
            $update = $conn->prepare("
                UPDATE customer
                SET cust_name = ?, cust_dob = ?, cust_phonenum = ?, cust_email = ?,
                    cust_username = ?, cust_gender = ?, cust_state = ?
                WHERE cust_id = ?
            ");
            $update->execute([$name, $dob, $phone, $email, $username, (int)$gender, $state, $custId]);

            $_SESSION['cust_name'] = $name;
            $success = 'Profile updated successfully.';

            $stmt->execute([$custId]);
            $customer = $stmt->fetch();
        }
    }
}

$vehicleStmt = $conn->prepare("SELECT COUNT(*) FROM vehicle WHERE cust_id = ?");
$vehicleStmt->execute([$custId]);
$vehicleCount = (int)$vehicleStmt->fetchColumn();

$bookingStmt = $conn->prepare("SELECT COUNT(*) FROM service WHERE cust_id = ?");
$bookingStmt->execute([$custId]);
$bookingCount = (int)$bookingStmt->fetchColumn();

$imagePath = '../../images/uploads/default.png';
if (!empty($customer['cust_image'])) {
    $candidate = trim($customer['cust_image']);
    if (str_starts_with($candidate, 'uploads/')) {
        $imagePath = '../../images/' . $candidate;
    } elseif (str_starts_with($candidate, 'images/')) {
        $imagePath = '../../' . $candidate;
    }
}

$states = [
    'Johor','Kedah','Kelantan','Melaka','Negeri Sembilan','Pahang','Penang','Perak',
    'Perlis','Sabah','Sarawak','Selangor','Terengganu','Kuala Lumpur','Putrajaya','Labuan'
];

$pageTitle = "Profile";
include "../includes/header.php";
?>

<style>
.profile-hero {
    padding: 54px 0 42px;
    border-bottom: 1px solid var(--border);
    background:
        radial-gradient(circle at 82% 10%, color-mix(in srgb, var(--primary) 13%, transparent), transparent 28%),
        var(--surface);
}
.profile-layout {
    display: grid;
    grid-template-columns: 310px minmax(0, 1fr);
    gap: 24px;
    align-items: start;
}
.profile-summary {
    position: sticky;
    top: 128px;
    overflow: hidden;
}
.profile-cover {
    height: 95px;
    background:
        linear-gradient(135deg, rgba(36,91,149,.92), rgba(76,132,184,.72)),
        url("../../images/hero.jpg") center/cover;
}
.profile-body { padding: 0 22px 24px; text-align: center; }
.profile-avatar {
    width: 88px;
    height: 88px;
    margin: -44px auto 13px;
    border: 5px solid var(--surface);
    border-radius: 24px;
    object-fit: cover;
    background: var(--surface-2);
    box-shadow: var(--shadow-sm);
}
.profile-body h2 { color: var(--heading); font-size: 1.08rem; }
.profile-body p { margin-top: 3px; color: var(--text-soft); font-size: .8rem; }
.profile-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 18px;
}
.profile-stat {
    padding: 12px 8px;
    border-radius: 12px;
    background: var(--surface-2);
}
.profile-stat strong { display: block; color: var(--heading); font-size: 1.08rem; }
.profile-stat span { color: var(--text-soft); font-size: .67rem; font-weight: 700; text-transform: uppercase; }
.profile-links { display: grid; gap: 8px; margin-top: 17px; }
.profile-links .btn { width: 100%; }

.profile-form-card { padding: 28px; }
.profile-form-head {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
}
.profile-form-head h2 { color: var(--heading); font-size: 1.2rem; }
.profile-form-head p { margin-top: 4px; color: var(--text-soft); font-size: .82rem; }
.profile-form { display: grid; gap: 17px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.profile-form-actions {
    display: flex;
    gap: 10px;
    margin-top: 4px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

@media (max-width: 850px) {
    .profile-layout { grid-template-columns: 1fr; }
    .profile-summary { position: static; }
}
@media (max-width: 600px) {
    .profile-form-card { padding: 21px; }
    .form-grid { grid-template-columns: 1fr; }
    .profile-form-actions { flex-direction: column; }
    .profile-form-actions .btn { width: 100%; }
}
</style>

<section class="profile-hero">
    <div class="container">
        <span class="eyebrow"><i class="fa-solid fa-user-gear"></i> Account</span>
        <h1 class="section-heading">Profile settings</h1>
        <p class="section-copy" style="margin-top:10px;">Keep the personal information used by your Kamal Car Wash account accurate and up to date.</p>
    </div>
</section>

<section class="section">
    <div class="container profile-layout">
        <aside class="card profile-summary">
            <div class="profile-cover"></div>
            <div class="profile-body">
                <img class="profile-avatar" src="<?= htmlspecialchars($imagePath) ?>" alt="Customer profile image"
                     onerror="this.src='../../images/uploads/default.png'">
                <h2><?= htmlspecialchars($customer['cust_name']) ?></h2>
                <p>@<?= htmlspecialchars($customer['cust_username']) ?></p>

                <div class="profile-stats">
                    <div class="profile-stat"><strong><?= $vehicleCount ?></strong><span>Vehicles</span></div>
                    <div class="profile-stat"><strong><?= $bookingCount ?></strong><span>Bookings</span></div>
                </div>

                <div class="profile-links">
                    <a class="btn btn-secondary" href="change_password.php"><i class="fa-solid fa-lock"></i> Change password</a>
                    <a class="btn btn-secondary" href="vehicle.php"><i class="fa-solid fa-car-side"></i> Manage vehicles</a>
                </div>
            </div>
        </aside>

        <main class="card profile-form-card">
            <div class="profile-form-head">
                <div>
                    <h2>Personal information</h2>
                    <p>These fields map directly to the existing customer table.</p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom:18px;"><i class="fa-solid fa-circle-check"></i><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom:18px;"><i class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form class="profile-form" method="POST">
                <div class="form-grid">
                    <div class="field">
                        <label for="cust_name">Full name</label>
                        <input class="input" id="cust_name" name="cust_name" maxlength="50"
                               value="<?= htmlspecialchars($customer['cust_name']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="cust_dob">Date of birth</label>
                        <input class="input" id="cust_dob" type="date" name="cust_dob"
                               value="<?= htmlspecialchars($customer['cust_dob']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="cust_phonenum">Phone number</label>
                        <input class="input" id="cust_phonenum" name="cust_phonenum" inputmode="numeric"
                               value="<?= htmlspecialchars((string)$customer['cust_phonenum']) ?>" required>
                        <span class="helper-text">The current database still stores this as an integer.</span>
                    </div>
                    <div class="field">
                        <label for="cust_email">Email address</label>
                        <input class="input" id="cust_email" type="email" name="cust_email" maxlength="30"
                               value="<?= htmlspecialchars($customer['cust_email']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="cust_username">Username</label>
                        <input class="input" id="cust_username" name="cust_username" maxlength="50"
                               value="<?= htmlspecialchars($customer['cust_username']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="cust_gender">Gender</label>
                        <select class="select" id="cust_gender" name="cust_gender" required>
                            <option value="0" <?= (int)$customer['cust_gender'] === 0 ? 'selected' : '' ?>>Male</option>
                            <option value="1" <?= (int)$customer['cust_gender'] === 1 ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="cust_state">State</label>
                        <select class="select" id="cust_state" name="cust_state" required>
                            <?php foreach ($states as $state): ?>
                                <option value="<?= htmlspecialchars($state) ?>" <?= $customer['cust_state'] === $state ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($state) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="profile-form-actions">
                    <button class="btn btn-primary" type="submit" name="update_profile">
                        <i class="fa-solid fa-floppy-disk"></i> Save changes
                    </button>
                    <a class="btn btn-secondary" href="cust_home.php">Cancel</a>
                </div>
            </form>
        </main>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
