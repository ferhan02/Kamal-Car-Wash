<?php
session_start();

require_once __DIR__ . '/../config.php';

if (isset($_SESSION['cust_id'])) {
    header('Location: ../customer/pages/cust_home.php');
    exit();
}

$error = '';
$success = isset($_GET['registered'])
    ? 'Account created successfully. You can sign in now.'
    : '';

$loginValue = trim($_POST['login'] ?? $_GET['login'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if ($loginValue === '' || $password === '') {
        $error = 'Enter your username or email and password.';
    } else {
        $stmt = $conn->prepare(
            'SELECT cust_id, cust_name, cust_username, cust_email, cust_password
             FROM customer
             WHERE cust_username = ? OR cust_email = ?
             LIMIT 1'
        );
        $stmt->execute([$loginValue, $loginValue]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            $error = 'The username/email or password is incorrect.';
        } else {
            $storedPassword = (string) $customer['cust_password'];
            $validPassword = password_verify($password, $storedPassword);
            $legacyPlaintextMatch = !$validPassword
                && hash_equals($storedPassword, $password);

            if (!$validPassword && !$legacyPlaintextMatch) {
                $error = 'The username/email or password is incorrect.';
            } else {
                if (
                    $legacyPlaintextMatch
                    || password_needs_rehash($storedPassword, PASSWORD_DEFAULT)
                ) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upgrade = $conn->prepare(
                        'UPDATE customer SET cust_password = ? WHERE cust_id = ?'
                    );
                    $upgrade->execute([$newHash, (int) $customer['cust_id']]);
                }

                session_regenerate_id(true);

                $_SESSION['cust_id'] = (int) $customer['cust_id'];
                $_SESSION['cust_name'] = $customer['cust_name'];
                $_SESSION['cust_username'] = $customer['cust_username'];

                header('Location: ../customer/pages/cust_home.php');
                exit();
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
    <meta name="color-scheme" content="light dark">

    <title>Customer Sign In | Kamal Car Wash</title>

    <script>
        (() => {
            const saved = localStorage.getItem('kamal-theme');
            const dark = window.matchMedia
                && window.matchMedia('(prefers-color-scheme: dark)').matches;

            document.documentElement.dataset.theme = saved || (dark ? 'dark' : 'light');
        })();
    </script>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/ui-polish.css">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <style>
    .customer-login-page {
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
        border: 1px solid var(--border);
        border-radius: 34px;
        background: color-mix(in srgb, var(--surface) 88%, transparent);
        box-shadow: var(--shadow-lg);
        backdrop-filter: blur(26px) saturate(140%);
        -webkit-backdrop-filter: blur(26px) saturate(140%);
    }

    .login-visual {
        position: relative;
        min-height: 650px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 36px;
        color: #fff;
        background:
            linear-gradient(155deg, rgba(5, 42, 79, .92), rgba(36, 91, 149, .68)),
            url('../images/hero.jpg') center / cover;
    }

    .login-visual::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 45%, rgba(2, 12, 24, .42));
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
        color: #fff;
        text-decoration: none;
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
        color: rgba(255, 255, 255, .72);
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
        color: rgba(255, 255, 255, .78);
    }

    .visual-chip {
        align-self: flex-start;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        padding: 8px 12px;
        border: 1px solid rgba(255, 255, 255, .20);
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
        background: color-mix(in srgb, var(--surface) 84%, transparent);
    }

    .login-panel-inner {
        width: 100%;
        max-width: 430px;
        margin: auto;
    }

    .login-kicker {
        color: var(--primary);
        font-size: .73rem;
        font-weight: 850;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .login-panel h2 {
        margin: 8px 0 9px;
        color: var(--heading);
        font-size: 2rem;
        letter-spacing: -.045em;
    }

    .login-panel .lead {
        margin: 0 0 28px;
        color: var(--text-soft);
        font-size: .88rem;
    }

    .login-form {
        display: grid;
        gap: 16px;
    }

    .password-wrap {
        position: relative;
    }

    .password-wrap .input {
        padding-right: 48px;
    }

    .password-eye {
        position: absolute;
        top: 50%;
        right: 8px;
        width: 36px;
        height: 36px;
        display: grid;
        place-items: center;
        transform: translateY(-50%);
        border: 0;
        border-radius: 11px;
        background: transparent;
        color: var(--text-soft);
        cursor: pointer;
    }

    .password-eye:hover {
        background: var(--surface-2);
    }

    .login-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        color: var(--text-soft);
        font-size: .76rem;
    }

    .login-message {
        display: flex;
        gap: 9px;
        margin-bottom: 16px;
        padding: 12px 14px;
        border: 1px solid var(--border);
        border-radius: 16px;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        font-size: .78rem;
    }

    .login-message.error {
        border-color: color-mix(in srgb, var(--danger) 20%, var(--border));
        background: color-mix(in srgb, var(--danger-soft) 78%, transparent);
        color: var(--danger);
    }

    .login-message.success {
        border-color: color-mix(in srgb, var(--success) 20%, var(--border));
        background: color-mix(in srgb, var(--success-soft) 78%, transparent);
        color: var(--success);
    }

    .login-submit {
        width: 100%;
    }

    .login-switch {
        margin-top: 20px;
        color: var(--text-soft);
        text-align: center;
        font-size: .80rem;
    }

    .login-switch a {
        color: var(--primary);
        font-weight: 800;
        text-decoration: none;
    }

    .back-home {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 22px;
        color: var(--text-soft);
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

        .visual-chip {
            display: none;
        }
    }
    </style>
</head>
<body class="customer-login-page">

<main class="login-shell">
    <section class="login-visual">
        <a class="login-brand" href="../index.php">
            <img src="../images/logo.png" alt="Kamal Car Wash logo">

            <div>
                <strong>KAMAL CAR WASH</strong>
                <span>Customer Portal</span>
            </div>
        </a>

        <div class="visual-copy">
            <div class="visual-chip">
                <i class="fa-solid fa-sparkles"></i>
                Simple booking. Clean experience.
            </div>

            <h1>Your next wash, in one clean view.</h1>
            <p>
                Manage vehicles, choose a wash slot and keep your booking
                details together in the same polished portal.
            </p>
        </div>
    </section>

    <section class="login-panel">
        <div class="login-panel-inner">
            <span class="login-kicker">Customer access</span>
            <h2>Sign in</h2>
            <p class="lead">Use your username or registered email address.</p>

            <?php if ($success): ?>
                <div class="login-message success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="login-message error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form" autocomplete="on">
                <div class="field">
                    <label for="login">Username or email</label>
                    <input
                        class="input"
                        type="text"
                        id="login"
                        name="login"
                        value="<?= htmlspecialchars($loginValue) ?>"
                        required
                        autocomplete="username"
                        placeholder="Username or email"
                    >
                </div>

                <div class="field">
                    <label for="password">Password</label>

                    <div class="password-wrap">
                        <input
                            class="input"
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        >

                        <button
                            class="password-eye"
                            type="button"
                            data-password-toggle="password"
                            aria-label="Show password"
                        >
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="login-meta">
                    <span>
                        <i class="fa-solid fa-shield-halved"></i>
                        Secure customer access
                    </span>

                    <button
                        type="button"
                        class="icon-btn"
                        data-theme-toggle
                        aria-label="Toggle appearance"
                    >
                        <i class="fa-solid fa-moon"></i>
                    </button>
                </div>

                <button class="btn btn-primary login-submit" type="submit">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    Sign in
                </button>
            </form>

            <p class="login-switch">
                New customer?
                <a href="cust_register.php">Create an account</a>
            </p>

            <a class="back-home" href="../index.php">
                <i class="fa-solid fa-chevron-left"></i>
                Back to website
            </a>
        </div>
    </section>
</main>

<script src="../js/customer.js"></script>
</body>
</html>
