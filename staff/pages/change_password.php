<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../includes/helpers.php';
if(!isset($_SESSION['staff_id'])) {
    header('Location: ../../auth/staff_login.php');
    exit();
}
$staff_id=(int)$_SESSION['staff_id'];
$stmt=$conn->prepare('SELECT * FROM staff WHERE staff_id=?');
$stmt->execute([$staff_id]);
$staff=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$staff) {
    session_destroy();
    header('Location: ../../auth/staff_login.php');
    exit();
}
if(isset($_POST['change_password'])) {
    $current=$_POST['current_password']??'';
    $new=$_POST['new_password']??'';
    $confirm=$_POST['confirm_password']??'';
    $stored=(string)$staff['staff_password'];
    $info=password_get_info($stored);
    $valid=!empty($info['algo'])?password_verify($current,$stored):hash_equals($stored,$current);
    if(!$valid) {
        $_SESSION['error']='Current password is incorrect.';
    } elseif(strlen($new)<8) {
        $_SESSION['error']='New password must contain at least 8 characters.';
    } elseif($new!==$confirm) {
        $_SESSION['error']='The new passwords do not match.';
    } elseif($new===$current) {
        $_SESSION['error']='Choose a password different from your current password.';
    } else {
        $hash=password_hash($new,PASSWORD_DEFAULT);
        $stmt=$conn->prepare('UPDATE staff SET staff_password=? WHERE staff_id=?');
        $stmt->execute([$hash,$staff_id]);
        $_SESSION['success']='Password changed successfully.';
    }
    header('Location: change_password.php');
    exit();
}
$root_prefix='../../';
$page_title='Change Password';
include __DIR__.'/../includes/head.php';
?>
<style>
.password-layout {
    display:grid;
    grid-template-columns:1fr 420px;
    gap:14px;
    max-width:980px;
    margin:0 auto
}
.security-card {
    padding:28px;
    background:linear-gradient(145deg,var(--staff-blue-soft),var(--staff-surface));
}
.security-icon {
    width:58px;
    height:58px;
    display:grid;
    place-items:center;
    border-radius:19px;
    background:var(--staff-blue);
    color:#fff;
    font-size:1.3rem;
    box-shadow:0 10px 24px rgba(10,132,255,.24)
}
.security-card h2 {
    margin:24px 0 8px;
    font-size:1.45rem;
    letter-spacing:-.035em
}
.security-card p {
    margin:0;
    color:var(--staff-muted);
    font-size:.82rem
}
.tips {
    display:grid;
    gap:9px;
    margin-top:24px
}
.tip {
    display:flex;
    gap:9px;
    color:var(--staff-muted);
    font-size:.74rem
}
.tip i {
    margin-top:4px;
    color:var(--staff-green)
}
.password-card {
    padding:24px
}
.password-form {
    display:grid;
    gap:15px
}
.password-wrap {
    position:relative
}
.password-wrap .ios-input {
    padding-right:50px
}
.password-eye {
    position:absolute;
    right:7px;
    top:50%;
    transform:translateY(-50%);
    width:36px;
    height:36px;
    border:0;
    border-radius:12px;
    background:transparent;
    color:var(--staff-muted);
    cursor:pointer
}
.password-actions {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:8px;
    margin-top:5px
}
@media(max-width:800px) {
    .password-layout {
        grid-template-columns:1fr
    }
    .security-card {
        order:2
    }
}
@media(max-width:500px) {
    .password-actions {
        grid-template-columns:1fr
    }
}
</style>
</head>
<body class="staff-body">
    <?php include __DIR__.'/../includes/header.php'; ?>
    <?php if(kcw_admin_role($staff['staff_job'])) include __DIR__.'/../includes/admin_toolbar.php'; ?>
    <main class="staff-main">
        <div class="staff-page-heading">
            <div>
                <span class="staff-eyebrow">
                    <i class="fa-solid fa-lock">
                    </i> Security</span>
                    <h1>Change password</h1>
                    <p>Use a longer password that you do not reuse elsewhere.</p>
                </div>
            </div>
            <section class="password-layout">
                <article class="ios-card security-card">
                    <div class="security-icon">
                        <i class="fa-solid fa-shield-halved">
                        </i>
                    </div>
                    <h2>Keep your staff account private.</h2>
                    <p>Your password protects access to reservations, staff records and payroll information.</p>
                    <div class="tips">
                        <div class="tip">
                            <i class="fa-solid fa-circle-check">
                            </i>
                            <span>Use at least 8 characters.</span>
                        </div>
                        <div class="tip">
                            <i class="fa-solid fa-circle-check">
                            </i>
                            <span>A mix of words, numbers and symbols is easier to make unique.</span>
                        </div>
                        <div class="tip">
                            <i class="fa-solid fa-circle-check">
                            </i>
                            <span>Do not share your password with other staff.</span>
                        </div>
                    </div>
                </article>
                <article class="ios-card password-card">
                    <form method="POST" class="password-form">
                        <div class="ios-field">
                            <label>Current password</label>
                            <div class="password-wrap">
                                <input class="ios-input" type="password" id="currentPassword" name="current_password" required autocomplete="current-password">
                                <button class="password-eye" type="button" data-password-toggle="currentPassword">
                                    <i class="fa-solid fa-eye">
                                    </i>
                                </button>
                            </div>
                        </div>
                        <div class="ios-field">
                            <label>New password</label>
                            <div class="password-wrap">
                                <input
                                    class="ios-input"
                                    type="password"
                                    id="newPassword"
                                    name="new_password"
                                    minlength="8"
                                    required
                                    autocomplete="new-password"
                                >
                                <button class="password-eye" type="button" data-password-toggle="newPassword">
                                    <i class="fa-solid fa-eye">
                                    </i>
                                </button>
                            </div>
                            <span class="ios-helper">At least 8 characters.</span>
                        </div>
                        <div class="ios-field">
                            <label>Confirm new password</label>
                            <div class="password-wrap">
                                <input
                                    class="ios-input"
                                    type="password"
                                    id="confirmPassword"
                                    name="confirm_password"
                                    minlength="8"
                                    required
                                    autocomplete="new-password"
                                >
                                <button class="password-eye" type="button" data-password-toggle="confirmPassword">
                                    <i class="fa-solid fa-eye">
                                    </i>
                                </button>
                            </div>
                        </div>
                        <div class="password-actions">
                            <a class="ios-btn ios-btn-secondary" href="update_profile.php">Cancel</a>
                            <button class="ios-btn ios-btn-primary" name="change_password">
                                <i class="fa-solid fa-check">
                                </i> Update password</button>
                            </div>
                        </form>
                    </article>
                </section>
            </main>
            <?php include __DIR__.'/../includes/footer.php'; ?>
        </body>
    </html>
