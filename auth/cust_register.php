<?php
session_start();
require_once "../config.php";

if (isset($_SESSION['cust_id'])) {
    header("Location: ../customer/pages/cust_home.php");
    exit();
}

$error = '';
$duplicateEmail = '';
$fieldErrors = [];
$states = [
    'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Penang', 'Perak',
    'Perlis', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur', 'Putrajaya', 'Labuan'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $username = trim($_POST['username'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $state = trim($_POST['state'] ?? '');
    $password = $_POST['password'] ?? '';
    $verify = $_POST['verify_password'] ?? '';
    $plate = strtoupper(trim($_POST['plate'] ?? ''));
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $vehicleType = trim($_POST['vehicle_type'] ?? '');

    if ($name === '') $fieldErrors['name'] = 'Enter your full name.';
    if ($dob === '') $fieldErrors['dob'] = 'Choose your date of birth.';
    if ($phone === '') $fieldErrors['phone'] = 'Enter your phone number.';
    if ($email === '') {
        $fieldErrors['email'] = 'Enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['email'] = 'Enter a valid email address, such as name@example.com.';
    }
    if ($username === '') $fieldErrors['username'] = 'Choose a username.';
    if (!in_array((string) $gender, ['0', '1'], true)) $fieldErrors['gender'] = 'Choose one gender option.';
    if (!in_array($state, $states, true)) $fieldErrors['state'] = 'Choose a state from the list.';
    if (strlen($password) < 8) $fieldErrors['password'] = 'Use at least 8 characters.';
    if ($verify === '') {
        $fieldErrors['verify_password'] = 'Re-enter your password.';
    } elseif ($password !== $verify) {
        $fieldErrors['verify_password'] = 'The passwords do not match.';
    }

    if ($plate === '') $fieldErrors['plate'] = 'Enter the vehicle plate number.';
    if ($brand === '') $fieldErrors['brand'] = 'Enter the vehicle brand.';
    if ($model === '') $fieldErrors['model'] = 'Enter the vehicle model.';
    if (!in_array($vehicleType, ['A', 'B', 'C'], true)) $fieldErrors['vehicle_type'] = 'Choose a vehicle type.';

    if (strlen($name) > 50) $fieldErrors['name'] = 'Full name must be 50 characters or fewer.';
    if (strlen($email) > 30) $fieldErrors['email'] = 'Email must be 30 characters or fewer for the current database.';
    if (strlen($username) > 50) $fieldErrors['username'] = 'Username must be 50 characters or fewer.';
    if (strlen($plate) > 20) $fieldErrors['plate'] = 'Plate number must be 20 characters or fewer.';
    if (strlen($brand) > 15) $fieldErrors['brand'] = 'Brand must be 15 characters or fewer.';
    if (strlen($model) > 15) $fieldErrors['model'] = 'Model must be 15 characters or fewer.';

    if (!$fieldErrors) {
        $emailCheck = $conn->prepare("SELECT cust_id FROM customer WHERE cust_email = ? LIMIT 1");
        $emailCheck->execute([$email]);
        $usernameCheck = $conn->prepare("SELECT cust_id FROM customer WHERE cust_username = ? LIMIT 1");
        $usernameCheck->execute([$username]);
        $plateCheck = $conn->prepare("SELECT vehicle_id FROM vehicle WHERE vehicle_platenum = ? LIMIT 1");
        $plateCheck->execute([$plate]);

        if ($emailCheck->fetch()) {
            $duplicateEmail = $email;
            $fieldErrors['email'] = 'This email is already registered.';
        } elseif ($usernameCheck->fetch()) {
            $fieldErrors['username'] = 'That username is already taken.';
        } elseif ($plateCheck->fetch()) {
            $fieldErrors['plate'] = 'That plate number is already registered.';
        } else {
            try {
                $conn->beginTransaction();
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insert = $conn->prepare("
                    INSERT INTO customer
                        (cust_name, cust_dob, cust_phonenum, cust_email, cust_username, cust_password, cust_gender, cust_state)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $insert->execute([$name, $dob, $phone, $email, $username, $hashedPassword, (int) $gender, $state]);

                $custId = (int) $conn->lastInsertId();
                $vehicle = $conn->prepare("
                    INSERT INTO vehicle
                        (cust_id, vehicle_platenum, vehicle_brand, vehicle_model, vehicle_type)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $vehicle->execute([$custId, $plate, $brand, $model, $vehicleType]);
                $conn->commit();

                header("Location: cust_login.php?registered=1");
                exit();
            } catch (Throwable $e) {
                if ($conn->inTransaction()) $conn->rollBack();
                $error = 'We could not create your account. Please try again.';
            }
        }
    }
}

$accountErrorFields = ['name', 'dob', 'phone', 'email', 'username', 'gender', 'state', 'password', 'verify_password'];
$vehicleErrorFields = ['plate', 'brand', 'model', 'vehicle_type'];
$initialStep = array_intersect(array_keys($fieldErrors), $accountErrorFields)
    ? 1
    : (array_intersect(array_keys($fieldErrors), $vehicleErrorFields) ? 2 : 1);
if ($error !== '' && !$fieldErrors) $initialStep = 3;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#10243a">
    <title>Create Account | Kamal Car Wash</title>
    <script>
        (() => {
            const saved = localStorage.getItem('kamal-theme');
            const dark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.dataset.theme = saved || (dark ? 'dark' : 'light');
        })();
    </script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/ui-polish.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
          integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <script defer src="../js/customer.js"></script>
    <style>
    .register-page {
        min-height: 100vh;
        background: radial-gradient(circle at 8% 4%, color-mix(in srgb, var(--primary) 8%, transparent), transparent 22%);
    }
    .register-top {
        min-height: 74px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid var(--border);
        background: color-mix(in srgb, var(--surface) 92%, transparent);
        backdrop-filter: blur(16px);
    }
    .register-top .container, .top-actions, .register-brand {
        display: flex;
        align-items: center;
    }
    .register-top .container { justify-content: space-between; gap: 20px; }
    .register-brand { gap: 11px; text-decoration: none; }
    .register-brand img { width: 46px; height: 46px; object-fit: contain; }
    .register-brand strong { color: var(--heading); font-family: 'Oswald', sans-serif; letter-spacing: .04em; }
    .top-actions { gap: 9px; }
    .register-shell { width: min(920px, calc(100% - 40px)); padding: 48px 0 70px; }
    .register-intro { max-width: 720px; margin-bottom: 24px; }
    .register-intro h1 {
        margin-top: 7px;
        color: var(--heading);
        font-family: 'Oswald', sans-serif;
        font-size: clamp(2.4rem, 5vw, 4rem);
        line-height: 1;
    }
    .register-intro p { margin-top: 12px; color: var(--text-soft); }
    .register-progress {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-bottom: 18px;
    }
    .progress-step {
        min-height: 48px;
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 12px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: color-mix(in srgb, var(--surface) 92%, transparent);
        color: var(--text-soft);
        font-size: .76rem;
        font-weight: 800;
    }
    .progress-step span {
        width: 26px;
        height: 26px;
        display: grid;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 50%;
        background: var(--surface-2);
    }
    .progress-step.active, .progress-step.complete { color: var(--primary); border-color: color-mix(in srgb, var(--primary) 32%, var(--border)); }
    .progress-step.active span, .progress-step.complete span { background: var(--primary); color: #fff; }
    .register-step[hidden] { display: none; }
    .register-card { padding: 28px; }
    .register-card-head {
        display: flex;
        align-items: center;
        gap: 13px;
        margin-bottom: 22px;
        padding-bottom: 17px;
        border-bottom: 1px solid var(--border);
    }
    .register-icon {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: var(--primary-soft);
        color: var(--primary);
    }
    .register-card-head h2 { color: var(--heading); font-size: 1.05rem; }
    .register-card-head p { color: var(--text-soft); font-size: .74rem; }
    .fields { display: grid; gap: 15px; }
    .field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .password-wrap { position: relative; }
    .password-wrap .input { padding-right: 45px; }
    .password-eye {
        position: absolute;
        top: 50%;
        right: 7px;
        width: 34px;
        height: 34px;
        display: grid;
        transform: translateY(-50%);
        place-items: center;
        border: 0;
        border-radius: 9px;
        background: transparent;
        color: var(--text-soft);
        cursor: pointer;
    }
    .password-hint { margin-top: 6px; color: var(--text-soft); font-size: .72rem; }
    .step-actions {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }
    .step-actions .right { margin-left: auto; }
    .existing-account-alert {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
        padding: 15px 16px;
        border: 1px solid color-mix(in srgb, var(--primary) 26%, var(--border));
        border-radius: 14px;
        background: color-mix(in srgb, var(--primary-soft) 72%, var(--surface));
    }
    .existing-account-copy { display: flex; gap: 11px; color: var(--text); font-size: .82rem; }
    .existing-account-copy i { margin-top: 3px; color: var(--primary); }
    .existing-account-copy strong { display: block; color: var(--heading); }
    .existing-account-copy span { display: block; margin-top: 2px; color: var(--text-soft); }
    .review-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .review-item { padding: 13px 14px; border-radius: 14px; background: var(--surface-2); }
    .review-item span { display: block; color: var(--text-soft); font-size: .68rem; font-weight: 800; text-transform: uppercase; }
    .review-item strong { display: block; margin-top: 3px; color: var(--heading); font-size: .84rem; }
    .review-note { margin-top: 14px; color: var(--text-soft); font-size: .8rem; }
    @media (max-width: 650px) {
        .register-shell { width: min(100% - 28px, 920px); padding-top: 34px; }
        .register-progress { grid-template-columns: 1fr; }
        .progress-step { min-height: 42px; }
        .register-card { padding: 21px; }
        .field-grid, .review-grid { grid-template-columns: 1fr; }
        .step-actions { align-items: stretch; flex-direction: column-reverse; }
        .step-actions .right { margin-left: 0; }
        .step-actions .btn { width: 100%; }
        .existing-account-alert { align-items: stretch; flex-direction: column; }
    }
    </style>
</head>
<body>
<div class="register-page">
    <header class="register-top">
        <div class="container">
            <a class="register-brand" href="../index.php">
                <img src="../images/logo.png" alt="Kamal Car Wash logo">
                <strong>KAMAL CAR WASH</strong>
            </a>
            <div class="top-actions">
                <a class="btn btn-secondary" href="cust_login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
                <button class="icon-btn" type="button" data-theme-toggle aria-label="Switch theme"><i class="fa-solid fa-moon"></i></button>
            </div>
        </div>
    </header>

    <main class="container register-shell">
        <div class="register-intro">
            <span class="eyebrow"><i class="fa-solid fa-user-plus"></i> New customer</span>
            <h1>Create your customer account.</h1>
            <p>We split registration into three short steps so you only focus on one group of details at a time.</p>
        </div>

        <div class="register-progress" aria-label="Registration progress">
            <div class="progress-step" data-progress-step="1"><span>1</span> Account</div>
            <div class="progress-step" data-progress-step="2"><span>2</span> Vehicle</div>
            <div class="progress-step" data-progress-step="3"><span>3</span> Review</div>
        </div>

        <?php if ($duplicateEmail): ?>
            <div class="existing-account-alert">
                <div class="existing-account-copy">
                    <i class="fa-solid fa-circle-info"></i>
                    <div>
                        <strong>Email already registered</strong>
                        <span><?= htmlspecialchars($duplicateEmail) ?> already has a Kamal Car Wash account.</span>
                    </div>
                </div>
                <a class="btn btn-primary" href="cust_login.php?login=<?= urlencode($duplicateEmail) ?>">Sign in instead <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom:18px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" id="registration-form" novalidate>
            <section class="card register-card register-step" data-step="1">
                <div class="register-card-head">
                    <div class="register-icon"><i class="fa-solid fa-user"></i></div>
                    <div><h2>Account details</h2><p>Your personal and login information</p></div>
                </div>
                <div class="fields">
                    <div class="field<?= isset($fieldErrors['name']) ? ' has-error' : '' ?>" data-field="name">
                        <label for="name">Full name</label>
                        <input class="input" id="name" name="name" maxlength="50" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        <span class="kcw-field-error" data-error-for="name"><?= htmlspecialchars($fieldErrors['name'] ?? '') ?></span>
                    </div>
                    <div class="field-grid">
                        <div class="field<?= isset($fieldErrors['dob']) ? ' has-error' : '' ?>" data-field="dob">
                            <label for="dob">Date of birth</label>
                            <input class="input" id="dob" type="date" name="dob" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>" required>
                            <span class="kcw-field-error" data-error-for="dob"><?= htmlspecialchars($fieldErrors['dob'] ?? '') ?></span>
                        </div>
                        <div class="field<?= isset($fieldErrors['gender']) ? ' has-error' : '' ?>" data-field="gender">
                            <label>Gender</label>
                            <div class="kcw-choice-grid">
                                <label class="kcw-choice">
                                    <input type="radio" name="gender" value="0" <?= ($_POST['gender'] ?? '') === '0' ? 'checked' : '' ?> required>
                                    <span><i class="fa-solid fa-person"></i> Male</span>
                                </label>
                                <label class="kcw-choice">
                                    <input type="radio" name="gender" value="1" <?= ($_POST['gender'] ?? '') === '1' ? 'checked' : '' ?>>
                                    <span><i class="fa-solid fa-person-dress"></i> Female</span>
                                </label>
                            </div>
                            <span class="kcw-field-error" data-error-for="gender"><?= htmlspecialchars($fieldErrors['gender'] ?? '') ?></span>
                        </div>
                    </div>
                    <div class="field-grid">
                        <div class="field<?= isset($fieldErrors['phone']) ? ' has-error' : '' ?>" data-field="phone">
                            <label for="phone">Phone number</label>
                            <input class="input" id="phone" name="phone" inputmode="numeric" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                            <span class="kcw-field-error" data-error-for="phone"><?= htmlspecialchars($fieldErrors['phone'] ?? '') ?></span>
                        </div>
                        <div class="field<?= isset($fieldErrors['state']) ? ' has-error' : '' ?>" data-field="state">
                            <label for="state">State</label>
                            <input
                                class="input"
                                id="state"
                                name="state"
                                list="state-options"
                                autocomplete="address-level1"
                                placeholder="Type or choose a state"
                                value="<?= htmlspecialchars($_POST['state'] ?? '') ?>"
                                required
                            >
                            <datalist id="state-options">
                                <?php foreach ($states as $state): ?><option value="<?= htmlspecialchars($state) ?>"></option><?php endforeach; ?>
                            </datalist>
                            <span class="helper-text">Type to search or open the list and scroll.</span>
                            <span class="kcw-field-error" data-error-for="state"><?= htmlspecialchars($fieldErrors['state'] ?? '') ?></span>
                        </div>
                    </div>
                    <div class="field<?= isset($fieldErrors['email']) ? ' has-error' : '' ?>" data-field="email">
                        <label for="email">Email</label>
                        <input
                            class="input"
                            id="email"
                            type="email"
                            name="email"
                            maxlength="30"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            placeholder="name@example.com"
                            required
                        >
                        <span class="kcw-field-error" data-error-for="email"><?= htmlspecialchars($fieldErrors['email'] ?? '') ?></span>
                    </div>
                    <div class="field<?= isset($fieldErrors['username']) ? ' has-error' : '' ?>" data-field="username">
                        <label for="username">Username</label>
                        <input class="input" id="username" name="username" maxlength="50" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                        <span class="kcw-field-error" data-error-for="username"><?= htmlspecialchars($fieldErrors['username'] ?? '') ?></span>
                    </div>
                    <div class="field-grid">
                        <div class="field<?= isset($fieldErrors['password']) ? ' has-error' : '' ?>" data-field="password">
                            <label for="password">Password</label>
                            <div class="password-wrap">
                                <input class="input" id="password" type="password" name="password" minlength="8" required>
                                <button class="password-eye" type="button" data-password-toggle="password" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
                            </div>
                            <div class="password-hint"><i class="fa-solid fa-lock"></i> At least 8 characters</div>
                            <span class="kcw-field-error" data-error-for="password"><?= htmlspecialchars($fieldErrors['password'] ?? '') ?></span>
                        </div>
                        <div class="field<?= isset($fieldErrors['verify_password']) ? ' has-error' : '' ?>" data-field="verify_password">
                            <label for="verify_password">Confirm password</label>
                            <div class="password-wrap">
                                <input class="input" id="verify_password" type="password" name="verify_password" minlength="8" required>
                                <button class="password-eye" type="button" data-password-toggle="verify_password" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
                            </div>
                            <div class="password-hint"><i class="fa-solid fa-check"></i> Re-enter the same password</div>
                            <span class="kcw-field-error" data-error-for="verify_password"><?= htmlspecialchars($fieldErrors['verify_password'] ?? '') ?></span>
                        </div>
                    </div>
                </div>
                <div class="step-actions">
                    <a class="btn btn-secondary" href="cust_login.php">Already have an account?</a>
                    <button class="btn btn-primary right" type="button" data-next-step="2">Continue <i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </section>

            <section class="card register-card register-step" data-step="2" hidden>
                <div class="register-card-head">
                    <div class="register-icon"><i class="fa-solid fa-car-side"></i></div>
                    <div><h2>First vehicle</h2><p>Add the vehicle you will book for</p></div>
                </div>
                <div class="fields">
                    <div class="field<?= isset($fieldErrors['plate']) ? ' has-error' : '' ?>" data-field="plate">
                        <label for="plate">Plate number</label>
                        <input class="input" id="plate" name="plate" maxlength="20" placeholder="NXX 1234" value="<?= htmlspecialchars($_POST['plate'] ?? '') ?>" required>
                        <span class="kcw-field-error" data-error-for="plate"><?= htmlspecialchars($fieldErrors['plate'] ?? '') ?></span>
                    </div>
                    <div class="field-grid">
                        <div class="field<?= isset($fieldErrors['brand']) ? ' has-error' : '' ?>" data-field="brand">
                            <label for="brand">Brand</label>
                            <input class="input" id="brand" name="brand" maxlength="15" placeholder="Perodua" value="<?= htmlspecialchars($_POST['brand'] ?? '') ?>" required>
                            <span class="kcw-field-error" data-error-for="brand"><?= htmlspecialchars($fieldErrors['brand'] ?? '') ?></span>
                        </div>
                        <div class="field<?= isset($fieldErrors['model']) ? ' has-error' : '' ?>" data-field="model">
                            <label for="model">Model</label>
                            <input class="input" id="model" name="model" maxlength="15" placeholder="Myvi" value="<?= htmlspecialchars($_POST['model'] ?? '') ?>" required>
                            <span class="kcw-field-error" data-error-for="model"><?= htmlspecialchars($fieldErrors['model'] ?? '') ?></span>
                        </div>
                    </div>
                    <div class="field<?= isset($fieldErrors['vehicle_type']) ? ' has-error' : '' ?>" data-field="vehicle_type">
                        <label>Vehicle type</label>
                        <div class="kcw-choice-grid three">
                            <label class="kcw-choice">
                                <input type="radio" name="vehicle_type" value="A" <?= ($_POST['vehicle_type'] ?? '') === 'A' ? 'checked' : '' ?> required>
                                <span><i class="fa-solid fa-motorcycle"></i> Motorcycle</span>
                            </label>
                            <label class="kcw-choice">
                                <input type="radio" name="vehicle_type" value="B" <?= ($_POST['vehicle_type'] ?? '') === 'B' ? 'checked' : '' ?>>
                                <span><i class="fa-solid fa-car-side"></i> Car</span>
                            </label>
                            <label class="kcw-choice">
                                <input type="radio" name="vehicle_type" value="C" <?= ($_POST['vehicle_type'] ?? '') === 'C' ? 'checked' : '' ?>>
                                <span><i class="fa-solid fa-truck-pickup"></i> 4x4 / Van</span>
                            </label>
                        </div>
                        <span class="kcw-field-error" data-error-for="vehicle_type"><?= htmlspecialchars($fieldErrors['vehicle_type'] ?? '') ?></span>
                    </div>
                    <div class="alert alert-success" style="margin-top:4px;">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>You can add more vehicles later from Vehicle Management.</span>
                    </div>
                </div>
                <div class="step-actions">
                    <button class="btn btn-secondary" type="button" data-prev-step="1"><i class="fa-solid fa-arrow-left"></i> Back</button>
                    <button class="btn btn-primary right" type="button" data-next-step="3">Review details <i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </section>

            <section class="card register-card register-step" data-step="3" hidden>
                <div class="register-card-head">
                    <div class="register-icon"><i class="fa-solid fa-clipboard-check"></i></div>
                    <div><h2>Review your details</h2><p>Make sure everything looks right before creating the account</p></div>
                </div>
                <div class="review-grid">
                    <div class="review-item"><span>Name</span><strong data-review="name">—</strong></div>
                    <div class="review-item"><span>Email</span><strong data-review="email">—</strong></div>
                    <div class="review-item"><span>Username</span><strong data-review="username">—</strong></div>
                    <div class="review-item"><span>State</span><strong data-review="state">—</strong></div>
                    <div class="review-item"><span>Vehicle</span><strong data-review="vehicle">—</strong></div>
                    <div class="review-item"><span>Vehicle type</span><strong data-review="vehicle_type">—</strong></div>
                </div>
                <p class="review-note">Your password is intentionally hidden here.</p>
                <div class="step-actions">
                    <button class="btn btn-secondary" type="button" data-prev-step="2"><i class="fa-solid fa-arrow-left"></i> Back</button>
                    <button class="btn btn-primary right" type="submit" name="register">Create account <i class="fa-solid fa-check"></i></button>
                </div>
            </section>
        </form>
    </main>
</div>

<script>
(() => {
    const form = document.getElementById('registration-form');
    if (!form) return;

    const validStates = <?= json_encode($states, JSON_UNESCAPED_UNICODE) ?>;
    let currentStep = <?= (int) $initialStep ?>;

    const messages = {
        name: 'Enter your full name.',
        dob: 'Choose your date of birth.',
        phone: 'Enter your phone number.',
        email: 'Enter a valid email address.',
        username: 'Choose a username.',
        gender: 'Choose one gender option.',
        state: 'Choose a state from the list.',
        password: 'Use at least 8 characters.',
        verify_password: 'Re-enter the same password.',
        plate: 'Enter the vehicle plate number.',
        brand: 'Enter the vehicle brand.',
        model: 'Enter the vehicle model.',
        vehicle_type: 'Choose a vehicle type.'
    };

    function setError(name, message = '') {
        const field = form.querySelector(`[data-field="${name}"]`);
        const error = form.querySelector(`[data-error-for="${name}"]`);
        if (!field || !error) return;
        field.classList.toggle('has-error', Boolean(message));
        error.textContent = message;
    }

    function value(name) {
        const checked = form.querySelector(`[name="${name}"]:checked`);
        if (checked) return checked.value;
        return form.elements[name]?.value?.trim() || '';
    }

    function validateField(name) {
        const element = form.elements[name];
        let message = '';
        if (name === 'gender' || name === 'vehicle_type') {
            if (!form.querySelector(`[name="${name}"]:checked`)) message = messages[name];
        } else if (!element?.value?.trim()) {
            message = messages[name];
        } else if (name === 'email' && !element.validity.valid) {
            message = messages.email;
        } else if (name === 'password' && element.value.length < 8) {
            message = messages.password;
        } else if (name === 'verify_password' && element.value !== form.elements.password.value) {
            message = 'The passwords do not match.';
        } else if (name === 'state' && !validStates.includes(element.value.trim())) {
            message = messages.state;
        }
        setError(name, message);
        return !message;
    }

    function validateStep(step) {
        const names = step === 1
            ? ['name', 'dob', 'gender', 'phone', 'state', 'email', 'username', 'password', 'verify_password']
            : ['plate', 'brand', 'model', 'vehicle_type'];
        const valid = names.map(validateField).every(Boolean);
        if (!valid) form.querySelector(`[data-step="${step}"] .has-error`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return valid;
    }

    function refreshReview() {
        const labels = { A: 'Motorcycle', B: 'Car', C: '4x4 / Van' };
        const plate = value('plate');
        const brand = value('brand');
        const model = value('model');
        const review = {
            name: value('name'),
            email: value('email'),
            username: value('username'),
            state: value('state'),
            vehicle: [plate, brand, model].filter(Boolean).join(' · '),
            vehicle_type: labels[value('vehicle_type')] || '—'
        };
        Object.entries(review).forEach(([key, text]) => {
            const target = form.querySelector(`[data-review="${key}"]`);
            if (target) target.textContent = text || '—';
        });
    }

    function showStep(step) {
        currentStep = step;
        form.querySelectorAll('[data-step]').forEach(section => {
            section.hidden = Number(section.dataset.step) !== step;
        });
        document.querySelectorAll('[data-progress-step]').forEach(item => {
            const number = Number(item.dataset.progressStep);
            item.classList.toggle('active', number === step);
            item.classList.toggle('complete', number < step);
        });
        if (step === 3) refreshReview();
    }

    form.addEventListener('click', event => {
        const next = event.target.closest('[data-next-step]');
        const prev = event.target.closest('[data-prev-step]');
        if (next) {
            if (validateStep(currentStep)) showStep(Number(next.dataset.nextStep));
        } else if (prev) {
            showStep(Number(prev.dataset.prevStep));
        }
    });

    form.addEventListener('input', event => {
        const name = event.target.name;
        if (name && messages[name]) validateField(name);
    });
    form.addEventListener('change', event => {
        const name = event.target.name;
        if (name && messages[name]) validateField(name);
    });
    form.addEventListener('submit', event => {
        if (currentStep < 3) {
            event.preventDefault();
            if (validateStep(currentStep)) showStep(currentStep + 1);
            return;
        }

        const accountValid = validateStep(1);
        const vehicleValid = validateStep(2);
        if (!accountValid || !vehicleValid) {
            event.preventDefault();
            showStep(accountValid ? 2 : 1);
        }
    });

    showStep(currentStep);
})();
</script>
</body>
</html>
