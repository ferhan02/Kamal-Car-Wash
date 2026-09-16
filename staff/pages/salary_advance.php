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
$salary=(float)$staff['staff_salary'];
$max=$salary*.5;
if(isset($_POST['submit'])) {
    $amount=(float)($_POST['amount']??0);
    $reason=trim($_POST['reason']??'');
    if($amount<=0) {
        $_SESSION['error']='Enter a valid advance amount.';
    } elseif($amount>$max) {
        $_SESSION['error']='The maximum advance is RM '.number_format($max,2).'.';
    } else {
        $stmt=$conn->prepare("INSERT INTO salary_advance (staff_id,amount,reason,request_date,status) VALUES (?,?,?,?, 'Pending')");
        $stmt->execute([$staff_id,$amount,$reason,date('Y-m-d')]);
        $_SESSION['success']='Salary advance request submitted.';
    }
    header('Location: salary_advance.php');
    exit();
}
$stmt=$conn->prepare('SELECT * FROM salary_advance WHERE staff_id=? ORDER BY advance_id DESC');
$stmt->execute([$staff_id]);
$history=$stmt->fetchAll(PDO::FETCH_ASSOC);
$root_prefix='../../';
$page_title='Salary Advance';
include __DIR__.'/../includes/head.php';
?>
<style>
.advance-grid {
    display:grid;
    grid-template-columns:390px 1fr;
    gap:14px
}
.advance-form {
    display:grid;
    gap:14px
}
.advance-side {
    position:sticky;
    top:112px;
    align-self:start
}
.limit-card {
    padding:18px;
    margin-bottom:12px;
    background:linear-gradient(135deg,var(--staff-blue-soft),transparent)
}
.limit-card span {
    color:var(--staff-muted);
    font-size:.7rem;
    font-weight:800
}
.limit-card strong {
    display:block;
    margin-top:4px;
    font-size:1.7rem;
    letter-spacing:-.04em
}
.progress {
    height:8px;
    margin-top:13px;
    border-radius:999px;
    background:var(--staff-surface-2);
    overflow:hidden
}
.progress i {
    display:block;
    width:50%;
    height:100%;
    border-radius:inherit;
    background:var(--staff-blue)
}
.advance-list {
    display:grid;
    gap:9px
}
.advance-item {
    display:grid;
    grid-template-columns:105px 1fr auto;
    align-items:center;
    gap:14px;
    padding:15px;
    border:1px solid var(--staff-border);
    border-radius:18px;
    background:var(--staff-surface-solid)
}
.advance-amount strong {
    font-size:.9rem
}
.advance-amount span,.advance-reason span {
    display:block;
    color:var(--staff-muted);
    font-size:.67rem
}
.advance-reason strong {
    display:block;
    font-size:.78rem;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis
}
@media(max-width:900px) {
    .advance-grid {
        grid-template-columns:1fr
    }
    .advance-side {
        position:static
    }
}
@media(max-width:560px) {
    .advance-item {
        grid-template-columns:1fr auto
    }
    .advance-reason {
        grid-column:1/-1;
        grid-row:2
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
                    <i class="fa-solid fa-hand-holding-dollar">
                    </i> Payroll request</span>
                    <h1>Salary advance</h1>
                    <p>Request up to half of your current basic salary and track the decision.</p>
                </div>
            </div>
            <section class="advance-grid">
                <div class="advance-side">
                    <article class="ios-card limit-card">
                        <span>YOUR CURRENT LIMIT</span>
                        <strong>RM <?= number_format($max,2) ?>
                        </strong>
                        <div class="progress">
                            <i>
                            </i>
                        </div>
                        <span style="display:block;margin-top:8px">50% of RM <?= number_format($salary,2) ?> basic salary</span>
                    </article>
                    <article class="ios-card">
                        <div class="ios-card-header">
                            <h2>New request</h2>
                        </div>
                        <div class="ios-card-body">
                            <form method="POST" class="advance-form">
                                <div class="ios-field">
                                    <label>Amount (RM)</label>
                                    <input class="ios-input" type="number" name="amount" min="1" max="<?= $max ?>" step="0.01" placeholder="0.00" required>
                                </div>
                                <div class="ios-field">
                                    <label>Reason</label>
                                    <textarea class="ios-textarea" name="reason" required placeholder="Brief reason for the advance">
                                    </textarea>
                                </div>
                                <button class="ios-btn ios-btn-primary" name="submit">
                                    <i class="fa-solid fa-paper-plane">
                                    </i> Submit request</button>
                                </form>
                            </div>
                        </article>
                    </div>
                    <article class="ios-card">
                        <div class="ios-card-header">
                            <h2>Request history</h2>
                            <span class="ios-badge neutral">
                                <?= count($history) ?> total</span>
                            </div>
                            <div class="ios-card-body advance-list">
                                <?php if(!$history): ?>
                                    <div class="ios-empty">
                                        <i class="fa-solid fa-money-bill-transfer">
                                        </i>
                                        <strong>No advance requests</strong>
                                        <span>Your requests will appear here.</span>
                                    </div>
                                <?php endif; ?>
                                <?php foreach($history as $r): ?>
                                    <div class="advance-item">
                                        <div class="advance-amount">
                                            <strong>RM <?= number_format((float)$r['amount'],2) ?>
                                            </strong>
                                            <span>
                                                <?= date('d M Y',strtotime($r['request_date'])) ?>
                                            </span>
                                        </div>
                                        <div class="advance-reason">
                                            <strong>
                                                <?= kcw_h($r['reason']) ?>
                                            </strong>
                                            <span>Salary advance request</span>
                                        </div>
                                        <span class="ios-badge <?= kcw_status_class($r['status']) ?>">
                                            <?= kcw_h($r['status']) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    </section>
                </main>
                <?php include __DIR__.'/../includes/footer.php'; ?>
            </body>
        </html>
