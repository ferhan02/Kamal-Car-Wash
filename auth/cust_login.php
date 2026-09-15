<?php
session_start();
require_once "../config.php";

if (isset($_SESSION['cust_id'])) {
    header("Location: ../customer/pages/cust_home.php");
    exit();
}

$error = '';
$loginValue = trim($_POST['login'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if ($loginValue === '' || $password === '') {
        $error = 'Enter your username or email and password.';
    } else {
        $stmt = $conn->prepare("
            SELECT cust_id, cust_name, cust_username, cust_email, cust_password
            FROM customer
            WHERE cust_username = ? OR cust_email = ?
            LIMIT 1
        ");
        $stmt->execute([$loginValue, $loginValue]);
        $customer = $stmt->fetch();

        if (!$customer || !password_verify($password, $customer['cust_password'])) {
            $error = 'The username/email or password is incorrect.';
        } else {
            session_regenerate_id(true);
            $_SESSION['cust_id'] = (int)$customer['cust_id'];
            $_SESSION['cust_name'] = $customer['cust_name'];
            $_SESSION['cust_username'] = $customer['cust_username'];

            header("Location: ../customer/pages/cust_home.php");
            exit();
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
    <title>Customer Login | Kamal Car Wash</title>

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
    .auth-page {
        min-height: 100vh;
        display: grid;
        grid-template-columns: minmax(360px, .9fr) minmax(480px, 1.1fr);
        background: var(--bg);
    }
    .auth-visual {
        position: relative;
        display: grid;
        align-items: end;
        min-height: 100vh;
        overflow: hidden;
        padding: 54px;
        background:
            linear-gradient(180deg, rgba(7,21,36,.18), rgba(7,21,36,.88)),
            url("../images/hero.jpg") center/cover no-repeat;
        color: #fff;
    }
    .auth-visual-content { position: relative; z-index: 2; max-width: 560px; }
    .auth-brand {
        position: absolute;
        top: 32px;
        left: 40px;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        gap: 11px;
        color: #fff;
        text-decoration: none;
    }
    .auth-brand img { width: 48px; height: 48px; object-fit: contain; }
    .auth-brand strong { font-family: 'Oswald', sans-serif; letter-spacing: .04em; }
    .auth-visual h1 {
        font-family: 'Oswald', sans-serif;
        font-size: clamp(2.8rem, 6vw, 5rem);
        line-height: .98;
    }
    .auth-visual p { max-width: 520px; margin-top: 17px; color: rgba(255,255,255,.7); }

    .auth-main {
        position: relative;
        display: grid;
        place-items: center;
        min-height: 100vh;
        padding: 48px 28px;
    }
    .auth-tools {
        position: absolute;
        top: 22px;
        right: 24px;
        display: flex;
        gap: 8px;
    }
    .auth-card { width: min(100%, 470px); }
    .auth-card-head h2 {
        color: var(--heading);
        font-family: 'Oswald', sans-serif;
        font-size: 2.3rem;
        line-height: 1;
    }
    .auth-card-head p { margin-top: 9px; color: var(--text-soft); font-size: .88rem; }
    .auth-form { display: grid; gap: 16px; margin-top: 28px; }
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
    .password-eye:hover { background: var(--surface-2); }
    .auth-submit { width: 100%; margin-top: 3px; }
    .auth-switch {
        margin-top: 22px;
        color: var(--text-soft);
        text-align: center;
        font-size: .83rem;
    }
    .auth-switch a { color: var(--primary); font-weight: 800; text-decoration: none; }
    .auth-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text-soft);
        font-size: .8rem;
        font-weight: 700;
        text-decoration: none;
    }

    @media (max-width: 860px) {
        .auth-page { grid-template-columns: 1fr; }
        .auth-visual { display: none; }
        .auth-main { min-height: 100vh; }
    }
    @media (max-width: 520px) {
        .auth-main { padding-inline: 20px; }
        .auth-tools { top: 16px; right: 16px; }
    }
    </style>
</head>
<body>
<div class="auth-page">
    <aside class="auth-visual">
        <a class="auth-brand" href="../index.php">
            <img src="../images/logo.png" alt="Kamal Car Wash logo">
            <strong>KAMAL CAR WASH</strong>
        </a>
        <div class="auth-visual-content">
            <span class="eyebrow" style="color:#b9d5ee;"><i class="fa-solid fa-sparkles"></i> Customer portal</span>
            <h1>Your next wash starts here.</h1>
            <p>Sign in to manage your vehicles, see available booking times and keep your customer information in one place.</p>
        </div>
    </aside>

    <main class="auth-main">
        <div class="auth-tools">
            <button class="icon-btn" type="button" data-theme-toggle aria-label="Switch theme"><i class="fa-solid fa-moon"></i></button>
        </div>

        <div class="auth-card">
            <a class="auth-back" href="../index.php"><i class="fa-solid fa-arrow-left"></i> Back to home</a>

            <div class="auth-card-head" style="margin-top:28px;">
                <span class="eyebrow">Welcome back</span>
                <h2>Customer login</h2>
                <p>Use your username or registered email address.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-top:20px;">
                    <i class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST">
                <div class="field">
                    <label for="login">Username or email</label>
                    <input class="input" id="login" name="login" value="<?= htmlspecialchars($loginValue) ?>"
                           autocomplete="username" placeholder="Enter username or email" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="password-wrap">
                        <input class="input" id="password" type="password" name="password"
                               autocomplete="current-password" placeholder="Enter your password" required>
                        <button class="password-eye" type="button" data-password-toggle="password" aria-label="Show password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button class="btn btn-primary auth-submit" type="submit">
                    Sign in <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <p class="auth-switch">New customer? <a href="cust_register.php">Create an account</a></p>
        </div>
    </main>
</div>
</body>
</html>
