<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../includes/helpers.php';
if (!isset($_SESSION['staff_id'])) {
    header('Location: ../../auth/staff_login.php');
    exit();
}
$staff_id = (int)$_SESSION['staff_id'];
$stmt = $conn->prepare('SELECT * FROM staff WHERE staff_id=?');
$stmt->execute([$staff_id]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$staff) {
    session_destroy();
    header('Location: ../../auth/staff_login.php');
    exit();
}
$ot_rate = 10.00;
if (isset($_POST['submit'])) {
    $date = $_POST['overtime_date'] ?? '';
    $hours = (float)($_POST['hours'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    if ($date === '' || $hours <= 0 || $hours > 24) {
        $_SESSION['error'] = 'Enter a valid overtime date and hours.';
    } else{
        $total = $hours * $ot_rate;
        $stmt = $conn->prepare("INSERT INTO overtime (staff_id,overtime_date,hours,rate,total_amount,reason,status) VALUES (?,?,?,?,?,?,'Pending')");
        $stmt->execute([$staff_id, $date, $hours, $ot_rate, $total, $reason]);
        $_SESSION['success'] = 'Overtime request submitted.';
    }
    header('Location: overtime.php');
    exit();
}
$month = max(1, min(12, (int)($_GET['month'] ?? date('m'))));
$year = max(2020, min(2100, (int)($_GET['year'] ?? date('Y'))));
$stmt = $conn->prepare(
    'SELECT *
     FROM overtime
     WHERE staff_id = ?
       AND MONTH(overtime_date) = ?
       AND YEAR(overtime_date) = ?
     ORDER BY overtime_date DESC, overtime_id DESC'
);
$stmt->execute([$staff_id, $month, $year]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
$approvedHours = 0;
$approvedPay = 0;
foreach ($history as $r) {
    if ($r['status'] === 'Approved') {
        $approvedHours+=(float)$r['hours'];
        $approvedPay+=(float)$r['total_amount'];
    }
}
$root_prefix = '../../';
$page_title = 'Overtime';
include __DIR__ . '/../includes/head.php';
?>
<style>
.ot-grid {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 14px
}
.request-card {
    position: sticky;
    top: 112px;
    align-self: start
}
.request-form {
    display: grid;
    gap: 14px
}
.rate-pill {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px;
    border-radius: 15px;
    background: var(--staff-blue-soft);
    color: var(--staff-blue);
    font-size: .76rem
}
.mini-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 12px
}
.mini-stat {
    padding: 16px
}
.mini-stat span {
    color: var(--staff-muted);
    font-size: .68rem;
    font-weight: 800
}
.mini-stat strong {
    display: block;
    margin-top: 4px;
    font-size: 1.3rem
}
.history-list {
    display: grid;
    gap: 9px
}
.history-item {
    display: grid;
    grid-template-columns: 110px 1fr auto auto;
    gap: 14px;
    align-items: center;
    padding: 14px 16px;
    border: 1px solid var(--staff-border);
    border-radius: 17px;
    background: var(--staff-surface-solid)
}
.history-date strong {
    display: block;
    font-size: .8rem
}
.history-date span {
    color: var(--staff-muted);
    font-size: .67rem
}
.history-reason {
    min-width: 0
}
.history-reason strong {
    display: block;
    font-size: .8rem
}
.history-reason span {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--staff-muted);
    font-size: .7rem
}
.amount {
    font-size: .8rem;
    font-weight: 850;
    white-space: nowrap
}
@media (max-width: 900px) {
    .ot-grid {
        grid-template-columns: 1fr
    }
    .request-card {
        position: static
    }
    .history-item {
        grid-template-columns: 90px 1fr auto
    }
    .amount {
        display: none
    }
}
@media (max-width: 560px) {
    .history-item {
        grid-template-columns: 1fr auto
    }
    .history-reason {
        grid-column: 1/-1;
        grid-row: 2
    }
    .history-date span {
        display: inline;
        margin-left: 4px
    }
}
</style>
</head>
<body class="staff-body">
    <?php include __DIR__.'/../includes/header.php'; ?>
    <?php if (kcw_admin_role($staff['staff_job'])) include __DIR__.'/../includes/admin_toolbar.php'; ?>
    <main class="staff-main">
        <div class="staff-page-heading">
            <div>
                <span class="staff-eyebrow"><i class="fa-solid fa-clock"></i> Time & pay</span>
                <h1>Overtime</h1>
                <p>Submit overtime work and follow each request from pending to approval.</p>
            </div>
            <form method="GET" style="display:flex;gap:8px">
                <select class="ios-select" name="month" data-auto-submit>
                    <?php for ($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= $month===$m?'selected':'' ?>>
                            <?= date('M',mktime(0,0,0,$m,1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <select class="ios-select" name="year" data-auto-submit>
                    <?php for ($y=(int)date('Y')-1; $y<=(int)date('Y')+1; $y++): ?>
                        <option value="<?= $y ?>" <?= $year===$y?'selected':'' ?>>
                            <?= $y ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
        <section class="ot-grid">
            <article class="ios-card request-card">
                <div class="ios-card-header">
                    <h2>New request</h2>
                    <i class="fa-solid fa-plus" style="color:var(--staff-blue)"></i>
                </div>
                <div class="ios-card-body">
                    <form method="POST" class="request-form">
                        <div class="rate-pill">
                            <span>
                                <i class="fa-solid fa-bolt"></i> Current OT rate</span>
                            <strong>RM <?= number_format($ot_rate,2) ?>/hr</strong>
                        </div>
                        <div class="ios-field">
                            <label>Date</label>
                            <input class="ios-input" type="date" name="overtime_date" max="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="ios-field">
                            <label>Hours</label>
                            <input class="ios-input" type="number" name="hours" min="0.25" max="24" step="0.25" placeholder="e.g. 2.5" required>
                        </div>
                        <div class="ios-field">
                            <label>Reason</label>
                            <textarea class="ios-textarea" name="reason" placeholder="Briefly describe the overtime work">
                            </textarea>
                        </div>
                        <button class="ios-btn ios-btn-primary" name="submit">
                            <i class="fa-solid fa-paper-plane"></i> Submit request</button>
                    </form>
                </div>
            </article>
            <div>
                <div class="mini-stats">
                    <article class="ios-card mini-stat">
                        <span>APPROVED HOURS</span>
                        <strong>
                            <?= number_format($approvedHours,2) ?> h</strong>
                        </article>
                        <article class="ios-card mini-stat">
                            <span>APPROVED PAY</span>
                            <strong>RM <?= number_format($approvedPay,2) ?>
                            </strong>
                        </article>
                    </div>
                    <article class="ios-card">
                        <div class="ios-card-header">
                            <h2>Request history</h2>
                            <span class="ios-badge neutral">
                                <?= count($history) ?> items</span>
                            </div>
                            <div class="ios-card-body history-list">
                                <?php if (!$history): ?>
                                    <div class="ios-empty">
                                        <i class="fa-regular fa-clock"></i>
                                        <strong>No overtime requests</strong>
                                        <span>Requests for this month will appear here.</span>
                                    </div>
                                <?php endif; ?>
                                <?php foreach ($history as $r): ?>
                                    <div class="history-item">
                                        <div class="history-date">
                                            <strong><?= date('d M',strtotime($r['overtime_date'])) ?></strong>
                                            <span><?= number_format((float)$r['hours'],2) ?> h</span>
                                            </div>
                                            <div class="history-reason">
                                                <strong><?= kcw_h($r['reason']?:'Overtime work') ?></strong>
                                                <span>Rate RM <?= number_format((float)$r['rate'],2) ?>/hr</span>
                                            </div>
                                            <span class="amount">RM <?= number_format((float)$r['total_amount'],2) ?>
                                            </span>
                                            <span class="ios-badge <?= kcw_status_class($r['status']) ?>">
                                                <?= kcw_h($r['status']) ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </article>
                        </div>
                    </section>
                </main>
                <?php include __DIR__.'/../includes/footer.php'; ?>
            </body>
        </html>
