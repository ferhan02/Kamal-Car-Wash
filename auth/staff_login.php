<?php
session_start();

require_once __DIR__ . '/../config.php';

if (isset($_SESSION['staff_id'])) {
    header('Location: ../staff/staff_home.php');
    exit();
}

if (isset($_GET['check_email'])) {
    header('Content-Type: application/json');

    $email = trim($_GET['check_email']);
    $stmt = $conn->prepare(
        'SELECT staff_name, staff_job FROM staff WHERE staff_email = ? LIMIT 1'
    );
    $stmt->execute([$email]);
    $preview = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(
        $preview
            ? [
                'status' => 'found',
                'name' => $preview['staff_name'],
                'job' => $preview['staff_job']
            ]
            : ['status' => 'not_found']
    );
    exit();
}

$error = '';
$email = $_POST['staff_email'] ?? ($_COOKIE['staff_email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['staff_email'] ?? '');
    $password = $_POST['staff_password'] ?? '';

    $stmt = $conn->prepare('SELECT * FROM staff WHERE staff_email = ? LIMIT 1');
    $stmt->execute([$email]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    $valid = false;

    if ($staff) {
        $stored = (string) ($staff['staff_password'] ?? '');
        $info = password_get_info($stored);

        if (!empty($info['algo'])) {
            $valid = password_verify($password, $stored);
        } else {
            $valid = hash_equals($stored, $password);

            if ($valid) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upgrade = $conn->prepare(
                    'UPDATE staff SET staff_password = ? WHERE staff_id = ?'
                );
                $upgrade->execute([$newHash, $staff['staff_id']]);
            }
        }
    }

    if (!$staff || !$valid) {
        $error = 'The email or password is incorrect.';
    } else {
        session_regenerate_id(true);

        $_SESSION['staff_id'] = $staff['staff_id'];
        $_SESSION['staff_name'] = $staff['staff_name'];
        $_SESSION['staff_job'] = $staff['staff_job'];
        $_SESSION['login_success'] = 'Welcome back, ' . $staff['staff_name'] . '.';

        if (!empty($_POST['remember'])) {
            setcookie('staff_email', $email, [
                'expires' => time() + 86400 * 30,
                'path' => '/',
                'samesite' => 'Lax'
            ]);
        }

        header('Location: ../staff/staff_home.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">

    <title>Staff Sign In | Kamal Car Wash</title>

    <script>
        (() => {
            const saved = localStorage.getItem('kamal-theme');
            const dark = window.matchMedia
                && window.matchMedia('(prefers-color-scheme: dark)').matches;

            document.documentElement.dataset.theme = saved || (dark ? 'dark' : 'light');
        })();
    </script>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/staff.css">
    <link rel="stylesheet" href="../css/ui-polish.css">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <style>
    .staff-login-page {
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 34px 18px;
    }

    .login-shell {
        width: min(1050px, 100%);
        display: grid;
        grid-template-columns: .95fr 1.05fr;
        overflow: hidden;
        border: 1px solid var(--staff-border);
        border-radius: 34px;
        background: var(--staff-surface);
        box-shadow: var(--staff-shadow);
        backdrop-filter: blur(26px);
    }

    .login-visual {
        position: relative;
        min-height: 650px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 36px;
        color: white;
        background:
            linear-gradient(155deg, rgba(5, 42, 79, .92), rgba(10, 132, 255, .68)),
            url('../images/hero.jpg') center / cover;
    }

    .login-visual::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 45%, rgba(2, 12, 24, .40));
        pointer-events: none;
    }

    .login-visual > * {
        position: relative;
        z-index: 1;
    }

    .login-brand {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .login-brand img {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        object-fit: contain;
        background: rgba(255, 255, 255, .90);
        box-shadow: 0 8px 24px rgba(0, 0, 0, .16);
    }

    .login-brand strong {
        display: block;
        font-size: 1rem;
        letter-spacing: .01em;
    }

    .login-brand span {
        display: block;
        margin-top: 3px;
        color: rgba(255, 255, 255, .70);
        font-size: .73rem;
    }

    .visual-copy h1 {
        max-width: 500px;
        margin: 0 0 12px;
        font-size: clamp(2.3rem, 5vw, 4.2rem);
        line-height: .95;
        letter-spacing: -.055em;
        text-shadow: 0 4px 22px rgba(0, 0, 0, .25);
    }

    .visual-copy p {
        max-width: 460px;
        margin: 0;
        color: rgba(255, 255, 255, .76);
    }

    .visual-device {
        align-self: flex-start;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        padding: 8px 12px;
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 999px;
        background: rgba(255, 255, 255, .12);
        backdrop-filter: blur(14px);
        font-size: .76rem;
        font-weight: 750;
    }

    .login-panel {
        display: flex;
        align-items: center;
        padding: 58px clamp(28px, 6vw, 72px);
        background: color-mix(in srgb, var(--staff-surface-solid) 84%, transparent);
    }

    .login-panel-inner {
        width: 100%;
        max-width: 430px;
        margin: auto;
    }

    .login-kicker {
        color: var(--staff-blue);
        font-size: .73rem;
        font-weight: 850;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .login-panel h2 {
        margin: 8px 0 9px;
        font-size: 2rem;
        letter-spacing: -.045em;
    }

    .login-panel .lead {
        margin: 0 0 28px;
        color: var(--staff-muted);
        font-size: .88rem;
    }

    .login-form {
        display: grid;
        gap: 16px;
    }

    .password-wrap {
        position: relative;
    }

    .password-wrap .ios-input {
        padding-right: 48px;
    }

    .password-eye {
        position: absolute;
        top: 50%;
        right: 8px;
        width: 36px;
        height: 36px;
        transform: translateY(-50%);
        border: 0;
        border-radius: 11px;
        background: transparent;
        color: var(--staff-muted);
        cursor: pointer;
    }

    .remember-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        color: var(--staff-muted);
        font-size: .76rem;
    }

    .remember-row label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .preview-card {
        display: none;
        align-items: center;
        gap: 10px;
        margin-top: 2px;
        padding: 10px 12px;
        border-radius: 14px;
        background: var(--staff-blue-soft);
        color: var(--staff-blue);
        font-size: .78rem;
    }

    .preview-card.show {
        display: flex;
    }

    .login-error {
        display: flex;
        gap: 9px;
        margin-bottom: 16px;
        padding: 12px 14px;
        border: 1px solid color-mix(in srgb, var(--staff-red) 18%, var(--staff-border));
        border-radius: 16px;
        background: color-mix(in srgb, var(--staff-red-soft) 78%, transparent);
        color: var(--staff-red);
        backdrop-filter: blur(20px);
        font-size: .78rem;
    }

    .back-home {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 22px;
        color: var(--staff-muted);
        font-size: .76rem;
        font-weight: 700;
        text-decoration: none;
    }

    @media (max-width: 800px) {
        .login-shell {
            grid-template-columns: 1fr;
        }

        .login-visual {
            min-height: 270px;
            padding: 26px;
        }

        .visual-copy h1 {
            font-size: 2.6rem;
        }

        .login-panel {
            padding: 36px 24px;
        }

        .visual-device {
            display: none;
        }
    }
    </style>
</head>
<body class="staff-body staff-login-page">

<main class="login-shell">
    <section class="login-visual">
        <div class="login-brand">
            <img src="../images/logo.png" alt="Kamal Car Wash logo">

            <div>
                <strong>KAMAL CAR WASH</strong>
                <span>Staff Operations</span>
            </div>
        </div>

        <div class="visual-copy">
            <div class="visual-device">
                <i class="fa-solid fa-sparkles"></i>
                Focused. Fast. Familiar.
            </div>

            <h1>Your workday, in one clean view.</h1>
            <p>
                Reservations, attendance and staff operations arranged in a
                calm interface that keeps the important things close.
            </p>
        </div>
    </section>

    <section class="login-panel">
        <div class="login-panel-inner">
            <span class="login-kicker">Staff access</span>
            <h2>Sign in</h2>
            <p class="lead">Use the email assigned to your staff account.</p>

            <?php if ($error): ?>
                <div class="login-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form" autocomplete="on">
                <div class="ios-field">
                    <label for="staff_email">Email</label>
                    <input
                        class="ios-input"
                        type="email"
                        id="staff_email"
                        name="staff_email"
                        value="<?= htmlspecialchars($email) ?>"
                        required
                        autocomplete="username"
                        placeholder="name@example.com"
                    >

                    <div id="staffPreview" class="preview-card">
                        <i class="fa-solid fa-id-badge"></i>
                        <span></span>
                    </div>
                </div>

                <div class="ios-field">
                    <label for="staff_password">Password</label>

                    <div class="password-wrap">
                        <input
                            class="ios-input"
                            type="password"
                            id="staff_password"
                            name="staff_password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        >

                        <button
                            class="password-eye"
                            type="button"
                            data-password-toggle="staff_password"
                            aria-label="Show password"
                        >
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="remember-row">
                    <label>
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            <?= isset($_COOKIE['staff_email']) ? 'checked' : '' ?>
                        >
                        Remember my email
                    </label>

                    <button
                        type="button"
                        class="staff-icon-button"
                        data-theme-toggle
                        aria-label="Toggle appearance"
                    >
                        <i class="fa-solid fa-moon" data-theme-icon></i>
                    </button>
                </div>

                <button class="ios-btn ios-btn-primary" type="submit">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    Sign in
                </button>
            </form>

            <a class="back-home" href="../index.php">
                <i class="fa-solid fa-chevron-left"></i>
                Back to website
            </a>
        </div>
    </section>
</main>

<script src="../js/staff.js"></script>
<script>
const emailInput = document.getElementById('staff_email');
const preview = document.getElementById('staffPreview');
let timer;

function checkStaffEmail() {
    clearTimeout(timer);

    const value = emailInput.value.trim();

    if (!value) {
        preview.classList.remove('show');
        return;
    }

    timer = setTimeout(async () => {
        try {
            const response = await fetch(
                'staff_login.php?check_email=' + encodeURIComponent(value)
            );
            const data = await response.json();

            if (data.status === 'found') {
                preview.querySelector('span').textContent =
                    data.name + ' · ' + data.job;
                preview.classList.add('show');
            } else {
                preview.classList.remove('show');
            }
        } catch (error) {
            preview.classList.remove('show');
        }
    }, 300);
}

emailInput.addEventListener('input', checkStaffEmail);
checkStaffEmail();
</script>
</body>
</html>
