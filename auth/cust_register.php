<?php
session_start();
require_once "../config.php";

if (isset($_SESSION['cust_id'])) {
    header("Location: ../customer/pages/cust_home.php");
    exit();
}

$error = '';
$duplicateEmail = '';
$states = [
    'Johor','Kedah','Kelantan','Melaka','Negeri Sembilan','Pahang','Penang','Perak',
    'Perlis','Sabah','Sarawak','Selangor','Terengganu','Kuala Lumpur','Putrajaya','Labuan'
];

if (isset($_POST['register'])) {
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

    if ($name === '' || $dob === '' || $phone === '' || $email === '' || $username === '' ||
        $state === '' || $plate === '' || $brand === '' || $model === '' ||
        !in_array((string)$gender, ['0', '1'], true) || !in_array($vehicleType, ['A', 'B', 'C'], true)) {
        $error = 'Please complete every required field.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $verify) {
        $error = 'The passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Use at least 8 characters for your password.';
    } elseif (strlen($name) > 50 || strlen($email) > 30 || strlen($username) > 50 ||
              strlen($state) > 30 || strlen($plate) > 20 || strlen($brand) > 15 || strlen($model) > 15) {
        $error = 'One or more values are longer than the current database allows.';
    } else {
        $emailCheck = $conn->prepare("SELECT cust_id FROM customer WHERE cust_email = ? LIMIT 1");
        $emailCheck->execute([$email]);

        $usernameCheck = $conn->prepare("SELECT cust_id FROM customer WHERE cust_username = ? LIMIT 1");
        $usernameCheck->execute([$username]);

        $plateCheck = $conn->prepare("SELECT vehicle_id FROM vehicle WHERE vehicle_platenum = ? LIMIT 1");
        $plateCheck->execute([$plate]);

        if ($emailCheck->fetch()) {
            $duplicateEmail = $email;
            $error = 'This email address is already registered.';
        } elseif ($usernameCheck->fetch()) {
            $error = 'That username is already taken. Please choose another username.';
        } elseif ($plateCheck->fetch()) {
            $error = 'That vehicle plate number is already registered.';
        } else {
            try {
                $conn->beginTransaction();

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insert = $conn->prepare("
                    INSERT INTO customer
                        (cust_name, cust_dob, cust_phonenum, cust_email, cust_username, cust_password, cust_gender, cust_state)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $insert->execute([$name, $dob, $phone, $email, $username, $hashedPassword, (int)$gender, $state]);

                $custId = (int)$conn->lastInsertId();

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
                $error = 'Registration could not be completed. Please try again.';
            }
        }
    }
}
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
          integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <script defer src="../js/customer.js"></script>

    <style>
    .register-page {
        min-height: 100vh;
        background:
            radial-gradient(circle at 8% 4%, color-mix(in srgb, var(--primary) 11%, transparent), transparent 20%),
            var(--bg);
    }
    .register-top {
        display: flex;
        min-height: 74px;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        border-bottom: 1px solid var(--border);
        background: color-mix(in srgb, var(--surface) 92%, transparent);
        backdrop-filter: blur(16px);
    }
    .register-top .container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }
    .register-brand {
        display: inline-flex;
        align-items: center;
        gap: 11px;
        text-decoration: none;
    }
    .register-brand img { width: 46px; height: 46px; object-fit: contain; }
    .register-brand strong { color: var(--heading); font-family: 'Oswald', sans-serif; letter-spacing: .04em; }
    .top-actions { display: flex; align-items: center; gap: 9px; }

    .register-shell { padding: 48px 0 70px; }
    .register-intro { max-width: 700px; margin-bottom: 27px; }
    .register-intro h1 {
        margin-top: 7px;
        color: var(--heading);
        font-family: 'Oswald', sans-serif;
        font-size: clamp(2.4rem, 5vw, 4rem);
        line-height: 1;
    }
    .register-intro p { margin-top: 12px; color: var(--text-soft); }

    .register-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 22px;
    }
    .register-card { padding: 27px; }
    .register-card-head {
        display: flex;
        align-items: center;
        gap: 13px;
        margin-bottom: 22px;
        padding-bottom: 17px;
        border-bottom: 1px solid var(--border);
    }
    .register-icon {
        display: grid;
        width: 44px;
        height: 44px;
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
        display: grid;
        width: 34px;
        height: 34px;
        transform: translateY(-50%);
        place-items: center;
        border: 0;
        border-radius: 9px;
        background: transparent;
        color: var(--text-soft);
        cursor: pointer;
    }
    .register-submit {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 22px 26px;
    }
    .register-submit p { color: var(--text-soft); font-size: .8rem; }
    .register-submit a { color: var(--primary); font-weight: 800; text-decoration: none; }
    .password-hint {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 7px;
        color: var(--text-soft);
        font-size: .72rem;
        line-height: 1.35;
    }
    .password-hint i { color: var(--primary); font-size: .65rem; }
    .existing-account-alert {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 20px;
        padding: 15px 16px;
        border: 1px solid color-mix(in srgb, var(--primary) 26%, var(--border));
        border-radius: 14px;
        background: color-mix(in srgb, var(--primary-soft) 72%, var(--surface));
    }
    .existing-account-copy {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        color: var(--text);
        font-size: .82rem;
    }
    .existing-account-copy i { margin-top: 3px; color: var(--primary); }
    .existing-account-copy strong { display: block; color: var(--heading); }
    .existing-account-copy span { display: block; margin-top: 2px; color: var(--text-soft); }
    .existing-account-alert .btn { flex: 0 0 auto; }

    @media (max-width: 850px) {
        .register-form { grid-template-columns: 1fr; }
        .register-submit { grid-column: auto; }
    }
    @media (max-width: 560px) {
        .register-shell { padding-top: 34px; }
        .register-card { padding: 21px; }
        .field-grid { grid-template-columns: 1fr; }
        .register-submit { align-items: stretch; flex-direction: column; }
        .register-submit .btn { width: 100%; }
        .existing-account-alert { align-items: stretch; flex-direction: column; }
        .existing-account-alert .btn { width: 100%; }
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
            <p>Your first vehicle is registered with the account so you can start booking immediately after signing in.</p>
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
                <a class="btn btn-primary" href="cust_login.php?login=<?= urlencode($duplicateEmail) ?>">
                    Sign in instead <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-error" style="margin-bottom:20px;">
                <i class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form class="register-form" method="POST">
            <section class="card register-card">
                <div class="register-card-head">
                    <div class="register-icon"><i class="fa-solid fa-user"></i></div>
                    <div><h2>Account details</h2><p>Your personal and login information</p></div>
                </div>

                <div class="fields">
                    <div class="field">
                        <label for="name">Full name</label>
                        <input class="input" id="name" name="name" maxlength="50" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="field-grid">
                        <div class="field">
                            <label for="dob">Date of birth</label>
                            <input class="input" id="dob" type="date" name="dob" value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>" required>
                        </div>
                        <div class="field">
                            <label for="gender">Gender</label>
                            <select class="select" id="gender" name="gender" required>
                                <option value="">Choose</option>
                                <option value="0" <?= ($_POST['gender'] ?? '') === '0' ? 'selected' : '' ?>>Male</option>
                                <option value="1" <?= ($_POST['gender'] ?? '') === '1' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="field-grid">
                        <div class="field">
                            <label for="phone">Phone number</label>
                            <input class="input" id="phone" name="phone" inputmode="numeric" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                        </div>
                        <div class="field">
                            <label for="state">State</label>
                            <select class="select" id="state" name="state" required>
                                <option value="">Choose state</option>
                                <?php foreach ($states as $state): ?>
                                    <option value="<?= htmlspecialchars($state) ?>" <?= ($_POST['state'] ?? '') === $state ? 'selected' : '' ?>><?= htmlspecialchars($state) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input class="input" id="email" type="email" name="email" maxlength="30"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="field">
                        <label for="username">Username</label>
                        <input class="input" id="username" name="username" maxlength="50"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    </div>

                    <div class="field-grid">
                        <div class="field">
                            <label for="password">Password</label>
                            <div class="password-wrap">
                                <input class="input" id="password" type="password" name="password" minlength="8" required>
                                <button class="password-eye" type="button" data-password-toggle="password" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
                            </div>
                            <div class="password-hint"><i class="fa-solid fa-lock"></i> At least 8 characters</div>
                        </div>
                        <div class="field">
                            <label for="verify_password">Confirm password</label>
                            <div class="password-wrap">
                                <input class="input" id="verify_password" type="password" name="verify_password" minlength="8" required>
                                <button class="password-eye" type="button" data-password-toggle="verify_password" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
                            </div>
                            <div class="password-hint"><i class="fa-solid fa-check"></i> Re-enter the same password</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card register-card">
                <div class="register-card-head">
                    <div class="register-icon"><i class="fa-solid fa-car-side"></i></div>
                    <div><h2>First vehicle</h2><p>Add the car or motorcycle you will book for</p></div>
                </div>

                <div class="fields">
                    <div class="field">
                        <label for="plate">Plate number</label>
                        <input class="input" id="plate" name="plate" maxlength="20" placeholder="NXX 1234"
                               value="<?= htmlspecialchars($_POST['plate'] ?? '') ?>" required>
                    </div>

                    <div class="field-grid">
                        <div class="field">
                            <label for="brand">Brand</label>
                            <input class="input" id="brand" name="brand" maxlength="15" placeholder="Perodua"
                                   value="<?= htmlspecialchars($_POST['brand'] ?? '') ?>" required>
                        </div>
                        <div class="field">
                            <label for="model">Model</label>
                            <input class="input" id="model" name="model" maxlength="15" placeholder="Myvi"
                                   value="<?= htmlspecialchars($_POST['model'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="vehicle_type">Vehicle type</label>
                        <select class="select" id="vehicle_type" name="vehicle_type" required>
                            <option value="">Choose type</option>
                            <option value="A" <?= ($_POST['vehicle_type'] ?? '') === 'A' ? 'selected' : '' ?>>Motorcycle</option>
                            <option value="B" <?= ($_POST['vehicle_type'] ?? '') === 'B' ? 'selected' : '' ?>>Normal Car</option>
                            <option value="C" <?= ($_POST['vehicle_type'] ?? '') === 'C' ? 'selected' : '' ?>>4x4 / Van</option>
                        </select>
                    </div>

                    <div style="margin-top:10px;padding:18px;border-radius:14px;background:var(--primary-soft);color:var(--text-soft);font-size:.8rem;">
                        <i class="fa-solid fa-circle-info" style="color:var(--primary);margin-right:7px;"></i>
                        You can add more vehicles later from Vehicle Management.
                    </div>

                    <img src="../images/reserve.png" alt="" style="width:min(260px,75%);margin:14px auto 0;filter:drop-shadow(0 15px 24px rgba(0,0,0,.12));">
                </div>
            </section>

            <div class="card register-submit">
                <p>Already registered? <a href="cust_login.php">Sign in instead</a>.</p>
                <button class="btn btn-primary" type="submit" name="register">
                    Create account <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </main>
</div>
</body>
</html>
