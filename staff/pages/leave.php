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
if (isset($_POST['submit'])) {
    $type = trim($_POST['leave_type'] ?? '');
    $start = $_POST['start_date'] ?? '';
    $end = $_POST['end_date'] ?? '';
    $reason = trim($_POST['reason'] ?? '');
    if ($type === '' || $start === '' || $end === '' || $end < $start) {
        $_SESSION['error'] = 'Check the leave type and date range.';
    } else{
        $stmt = $conn->prepare("INSERT INTO leave_application (staff_id,leave_type,start_date,end_date,reason,apply_date,status) VALUES (?,?,?,?,?,?,'Pending')");
        $stmt->execute([$staff_id, $type, $start, $end, $reason, date('Y-m-d')]);
        $_SESSION['success'] = 'Leave application submitted.';
    }
    header('Location: leave.php');
    exit();
}
$stmt = $conn->prepare('SELECT * FROM leave_application WHERE staff_id=? ORDER BY leave_id DESC');
$stmt->execute([$staff_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
$counts = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0];
foreach ($history as $r) {
    if (isset($counts[$r['status']]))$counts[$r['status']]++;
}
$root_prefix = '../../';
$page_title = 'Leave';
include __DIR__ . '/../includes/head.php';
?>
<style>
.leave-grid {
    display: grid;
    grid-template-columns: 390px 1fr;
    gap: 14px
}
.leave-form {
    display: grid;
    gap: 14px
}
.leave-side {
    position: sticky;
    top: 112px;
    align-self: start
}
.leave-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 12px
}
.leave-stat {
    padding: 15px;
    text-align: center
}
.leave-stat strong {
    display: block;
    font-size: 1.35rem
}
.leave-stat span {
    color: var(--staff-muted);
    font-size: .66rem;
    font-weight: 800
}
.leave-list {
    display: grid;
    gap: 9px
}
.leave-item {
    padding: 15px 16px;
    border: 1px solid var(--staff-border);
    border-radius: 18px;
    background: var(--staff-surface-solid)
}
.leave-top {
    display: flex;
    justify-content: space-between;
    gap: 12px
}
.leave-top strong {
    font-size: .86rem
}
.leave-date {
    margin-top: 3px;
    color: var(--staff-muted);
    font-size: .7rem
}
.leave-reason {
    margin: 12px 0 0;
    padding-top: 11px;
    border-top: 1px solid var(--staff-border);
    color: var(--staff-muted);
    font-size: .74rem;
    line-height: 1.5
}
.days-pill {
    display: inline-flex;
    margin-left: 6px;
    padding: 3px 7px;
    border-radius: 999px;
    background: var(--staff-surface-2);
    font-size: .63rem;
    color: var(--staff-muted)
}
@media (max-width: 900px) {
    .leave-grid {
        grid-template-columns: 1fr
    }
    .leave-side {
        position: static
    }
}
@media (max-width: 560px) {
    .leave-stats {
        grid-template-columns: 1fr 1fr 1fr
    }
    .leave-stat {
        padding: 12px 7px
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
                <span class="staff-eyebrow"><i class="fa-solid fa-plane-departure"></i> Time away</span>
                <h1>Leave</h1>
                <p>Request time away and keep track of approval status without digging through forms.</p>
            </div>
        </div>
        <section class="leave-grid">
            <article class="ios-card leave-side">
                <div class="ios-card-header">
                    <h2>Request leave</h2>
                    <i class="fa-solid fa-calendar-plus" style="color:var(--staff-blue)"></i>
                </div>
                <div class="ios-card-body">
                    <form method="POST" class="leave-form">
                        <div class="ios-field">
                            <label>Leave type</label>
                            <select class="ios-select" name="leave_type" required>
                                <option value="">Select type</option>
                                <option>Annual Leave</option>
                                <option>Medical Leave</option>
                                <option>Emergency Leave</option>
                                <option>Unpaid Leave</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="ios-field">
                            <label>Start date</label>
                            <input class="ios-input" type="date" name="start_date" required>
                        </div>
                        <div class="ios-field">
                            <label>End date</label>
                            <input class="ios-input" type="date" name="end_date" required>
                        </div>
                        <div class="ios-field">
                            <label>Reason</label>
                            <textarea class="ios-textarea" name="reason" placeholder="Add a short reason">
                            </textarea>
                        </div>
                        <button class="ios-btn ios-btn-primary" name="submit">
                            <i class="fa-solid fa-paper-plane"></i> Submit application</button>
                    </form>
                </div>
            </article>
            <div>
                <div class="leave-stats">
                    <article class="ios-card leave-stat">
                        <strong style="color:var(--staff-orange)"><?= $counts['Pending'] ?></strong>
                        <span>PENDING</span>
                    </article>
                    <article class="ios-card leave-stat">
                        <strong style="color:var(--staff-green)"><?= $counts['Approved'] ?></strong>
                        <span>APPROVED</span>
                    </article>
                    <article class="ios-card leave-stat">
                        <strong style="color:var(--staff-red)"><?= $counts['Rejected'] ?></strong>
                        <span>REJECTED</span>
                    </article>
                </div>
                <article class="ios-card">
                    <div class="ios-card-header">
                        <h2>Application history</h2>
                        <span class="ios-badge neutral">
                            <?= count($history) ?> total</span>
                        </div>
                        <div class="ios-card-body leave-list">
                            <?php if (!$history): ?>
                                <div class="ios-empty">
                                    <i class="fa-regular fa-calendar"></i>
                                    <strong>No leave applications yet</strong>
                                    <span>Your requests will appear here.</span>
                                </div>
                            <?php endif; ?>
                            <?php foreach ($history as $r):$days=(new DateTime($r['start_date']))->diff(new DateTime($r['end_date']))->days+1; ?>
                            <div class="leave-item">
                                <div class="leave-top">
                                    <div>
                                        <strong>
                                            <?= kcw_h($r['leave_type']) ?>
                                            <span class="days-pill"><?= $days ?> day<?= $days===1?'':'s' ?></span>
                                        </strong>
                                        <div class="leave-date">
                                            <?= date('d M Y',strtotime($r['start_date'])) ?> → <?= date('d M Y',strtotime($r['end_date'])) ?>
                                        </div>
                                    </div>
                                    <span class="ios-badge <?= kcw_status_class($r['status']) ?>">
                                        <?= kcw_h($r['status']) ?>
                                    </span>
                                </div>
                                <?php if ($r['reason']): ?>
                                    <p class="leave-reason"><?= kcw_h($r['reason']) ?></p>
                                <?php endif; ?>
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
