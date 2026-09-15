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

if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $conn->prepare("SELECT cust_password FROM customer WHERE cust_id = ?");
    $stmt->execute([$custId]);
    $hash = $stmt->fetchColumn();

    if (!$hash || !password_verify($current, $hash)) {
        $error = 'Your current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'Use at least 8 characters for the new password.';
    } elseif ($new !== $confirm) {
        $error = 'The new passwords do not match.';
    } elseif (password_verify($new, $hash)) {
        $error = 'Choose a new password that is different from your current password.';
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE customer SET cust_password = ? WHERE cust_id = ?");
        $update->execute([$newHash, $custId]);
        $success = 'Password changed successfully.';
    }
}

$pageTitle = "Change Password";
include "../includes/header.php";
?>

<style>
.password-page {
    min-height: calc(100vh - 110px);
    display: grid;
    place-items: center;
    padding: 64px 0 80px;
    background:
        radial-gradient(circle at 20% 10%, color-mix(in srgb, var(--primary) 12%, transparent), transparent 24%),
        var(--bg);
}
.password-grid {
    display: grid;
    grid-template-columns: 300px minmax(0, 500px);
    gap: 22px;
    justify-content: center;
    align-items: stretch;
}
.password-visual {
    position: relative;
    overflow: hidden;
    padding: 28px;
    background:
        linear-gradient(160deg, rgba(13, 37, 62, .95), rgba(36, 91, 149, .82)),
        url("../../images/hero.jpg") center/cover;
    color: #fff;
}
.password-visual i { font-size: 2rem; color: #b8d6ef; }
.password-visual h1 {
    margin-top: 22px;
    font-family: 'Oswald', sans-serif;
    font-size: 2.15rem;
    line-height: 1;
}
.password-visual p { margin-top: 13px; color: rgba(255,255,255,.68); font-size: .84rem; }
.password-rules { display: grid; gap: 10px; margin-top: 25px; list-style: none; }
.password-rules li { display: flex; gap: 9px; color: rgba(255,255,255,.77); font-size: .78rem; }
.password-rules i { margin-top: 4px; font-size: .72rem; }

.password-card { padding: 29px; }
.password-card h2 { color: var(--heading); font-size: 1.25rem; }
.password-card > p { margin-top: 5px; color: var(--text-soft); font-size: .83rem; }
.password-form { display: grid; gap: 16px; margin-top: 22px; }
.password-wrap { position: relative; }
.password-wrap .input { padding-right: 46px; }
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
.password-eye:hover { background: var(--surface-2); color: var(--heading); }
.password-actions { display: flex; gap: 9px; margin-top: 5px; }

@media (max-width: 720px) {
    .password-grid { grid-template-columns: 1fr; }
    .password-visual { min-height: 220px; }
}
@media (max-width: 500px) {
    .password-card, .password-visual { padding: 22px; }
    .password-actions { flex-direction: column; }
}
</style>

<section class="password-page">
    <div class="container password-grid">
        <aside class="card password-visual">
            <i class="fa-solid fa-shield-halved"></i>
            <h1>Keep your account secure.</h1>
            <p>Your customer password is stored using PHP's password hashing system.</p>
            <ul class="password-rules">
                <li><i class="fa-solid fa-check"></i><span>Use at least 8 characters.</span></li>
                <li><i class="fa-solid fa-check"></i><span>Avoid reusing your current password.</span></li>
                <li><i class="fa-solid fa-check"></i><span>Keep your login details private.</span></li>
            </ul>
        </aside>

        <main class="card password-card">
            <span class="eyebrow">Security</span>
            <h2>Change password</h2>
            <p>Confirm your existing password before choosing a new one.</p>

            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-top:18px;"><i class="fa-solid fa-circle-check"></i><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-top:18px;"><i class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form class="password-form" method="POST">
                <div class="field">
                    <label for="current_password">Current password</label>
                    <div class="password-wrap">
                        <input class="input" id="current_password" type="password" name="current_password" autocomplete="current-password" required>
                        <button class="password-eye" type="button" data-password-toggle="current_password" aria-label="Show password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="field">
                    <label for="new_password">New password</label>
                    <div class="password-wrap">
                        <input class="input" id="new_password" type="password" name="new_password" minlength="8" autocomplete="new-password" required>
                        <button class="password-eye" type="button" data-password-toggle="new_password" aria-label="Show password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="field">
                    <label for="confirm_password">Confirm new password</label>
                    <div class="password-wrap">
                        <input class="input" id="confirm_password" type="password" name="confirm_password" minlength="8" autocomplete="new-password" required>
                        <button class="password-eye" type="button" data-password-toggle="confirm_password" aria-label="Show password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="password-actions">
                    <button class="btn btn-primary" type="submit" name="change_password">
                        <i class="fa-solid fa-key"></i> Update password
                    </button>
                    <a class="btn btn-secondary" href="profile.php">Back to profile</a>
                </div>
            </form>
        </main>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
