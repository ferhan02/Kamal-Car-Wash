<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../includes/helpers.php';
if(!isset($_SESSION['staff_id'])) {
    header('Location: ../../auth/staff_login.php');
    exit();
}
$staff_id=(int)$_SESSION['staff_id'];
$month=max(1,min(12,(int)($_GET['month']??date('m'))));
$year=max(2020,min(2100,(int)($_GET['year']??date('Y'))));
$stmt=$conn->prepare('SELECT * FROM staff WHERE staff_id=?');
$stmt->execute([$staff_id]);
$staff=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$staff) {
    session_destroy();
    header('Location: ../../auth/staff_login.php');
    exit();
}
$stmt=$conn->prepare('SELECT * FROM salary_summary WHERE staff_id=? AND salary_month=? AND salary_year=?');
$stmt->execute([$staff_id,$month,$year]);
$salary=$stmt->fetch(PDO::FETCH_ASSOC);
$allowance=(float)($salary['allowance']??0);
$deduction=(float)($salary['deduction']??0);
$bonus=(float)($salary['bonus']??0);
$stmt = $conn->prepare(
    "SELECT
        COALESCE(SUM(total_amount), 0) AS overtime_pay,
        COALESCE(SUM(hours), 0) AS overtime_hours
     FROM overtime
     WHERE staff_id = ?
       AND MONTH(overtime_date) = ?
       AND YEAR(overtime_date) = ?
       AND status = 'Approved'"
);
$stmt->execute([$staff_id,$month,$year]);
$ot=$stmt->fetch(PDO::FETCH_ASSOC);
$overtime_pay=(float)$ot['overtime_pay'];
$overtime_hours=(float)$ot['overtime_hours'];
$basic=(float)$staff['staff_salary'];
$gross=$basic+$allowance+$overtime_pay;
$net=$gross-$deduction+$bonus;
$stmt = $conn->prepare(
    'SELECT
        COALESCE(SUM(is_present = 1), 0) AS present_days,
        COALESCE(SUM(is_present = 0), 0) AS absent_days
     FROM attendance
     WHERE staff_id = ?
       AND MONTH(attendance_date) = ?
       AND YEAR(attendance_date) = ?'
);
$stmt->execute([$staff_id,$month,$year]);
$att=$stmt->fetch(PDO::FETCH_ASSOC);
$present=(int)$att['present_days'];
$absent=(int)$att['absent_days'];
$root_prefix='../../';
$page_title='Salary Summary';
include __DIR__.'/../includes/head.php';
?>
<style>
.pay-hero {
    display:grid;
    grid-template-columns:1fr auto;
    gap:24px;
    padding:28px;
    margin-bottom:14px;
    background:linear-gradient(135deg,color-mix(in srgb,var(--staff-blue) 90%,#002f62),color-mix(in srgb,var(--staff-purple) 60%,var(--staff-blue)));
    color:#fff
}
.pay-hero span {
    color:rgba(255,255,255,.72);
    font-size:.75rem;
    font-weight:800
}
.pay-hero strong {
    display:block;
    margin-top:6px;
    font-size:clamp(2.2rem,5vw,4rem);
    letter-spacing:-.055em;
    line-height:1
}
.pay-period {
    align-self:center;
    display:flex;
    gap:8px
}
.pay-period select {
    border-color:rgba(255,255,255,.18);
    background:rgba(255,255,255,.13);
    color:#fff
}
.pay-period option {
    color:#111;
    background:#fff
}
.pay-grid {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px
}
.breakdown-list {
    display:grid;
    gap:3px
}
.breakdown-row {
    display:flex;
    justify-content:space-between;
    gap:20px;
    padding:13px 0;
    border-bottom:1px solid var(--staff-border)
}
.breakdown-row:last-child {
    border:0
}
.breakdown-row span {
    color:var(--staff-muted);
    font-size:.8rem
}
.breakdown-row strong {
    font-size:.84rem
}
.attendance-ring {
    display:grid;
    grid-template-columns:auto 1fr;
    gap:22px;
    align-items:center
}
.ring {
    width:112px;
    height:112px;
    border-radius:50%;
    display:grid;
    place-items:center;
    background:conic-gradient(var(--staff-green) 0 calc(var(--pct)*1%),var(--staff-surface-2) calc(var(--pct)*1%) 100%);
    position:relative
}
.ring::after {
    content:"";
    position:absolute;
    inset:12px;
    border-radius:50%;
    background:var(--staff-surface-solid)
}
.ring strong {
    position:relative;
    z-index:1;
    font-size:1.2rem
}
.att-legend {
    display:grid;
    gap:10px
}
.att-legend div {
    display:flex;
    justify-content:space-between;
    gap:16px;
    color:var(--staff-muted);
    font-size:.78rem
}
.att-legend strong {
    color:var(--staff-text)
}
@media(max-width:780px) {
    .pay-grid {
        grid-template-columns:1fr
    }
    .pay-hero {
        grid-template-columns:1fr
    }
    .pay-period {
        flex-wrap:wrap
    }
    .attendance-ring {
        grid-template-columns:1fr;
        justify-items:center
    }
    .att-legend {
        width:100%
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
                    <i class="fa-solid fa-wallet">
                    </i> Payroll</span>
                    <h1>Salary summary</h1>
                    <p>Your current pay breakdown based on the data recorded in the system.</p>
                </div>
            </div>
            <section class="ios-card pay-hero">
                <div>
                    <span>ESTIMATED NET PAY</span>
                    <strong>RM <?= number_format($net,2) ?>
                    </strong>
                    <span>
                        <?= date('F',mktime(0,0,0,$month,1)).' '.$year ?>
                    </span>
                </div>
                <form class="pay-period" method="GET">
                    <select class="ios-select" name="month" data-auto-submit>
                        <?php for($m=1;$m<=12;$m++): ?>
                            <option value="<?= $m ?>" <?= $month===$m?'selected':'' ?>>
                                <?= date('M',mktime(0,0,0,$m,1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select class="ios-select" name="year" data-auto-submit>
                        <?php for($y=(int)date('Y')-2;$y<=(int)date('Y')+1;$y++): ?>
                            <option value="<?= $y ?>" <?= $year===$y?'selected':'' ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </form>
            </section>
            <section class="pay-grid">
                <article class="ios-card">
                    <div class="ios-card-header">
                        <h2>Pay breakdown</h2>
                        <span class="ios-badge neutral">RM</span>
                    </div>
                    <div class="ios-card-body breakdown-list">
                        <div class="breakdown-row">
                            <span>Basic salary</span>
                            <strong>RM <?= number_format($basic,2) ?>
                            </strong>
                        </div>
                        <div class="breakdown-row">
                            <span>Allowance</span>
                            <strong>+ RM <?= number_format($allowance,2) ?>
                            </strong>
                        </div>
                        <div class="breakdown-row">
                            <span>Approved overtime (<?= number_format($overtime_hours,2) ?> h)</span>
                            <strong>+ RM <?= number_format($overtime_pay,2) ?>
                            </strong>
                        </div>
                        <div class="breakdown-row">
                            <span>Bonus</span>
                            <strong>+ RM <?= number_format($bonus,2) ?>
                            </strong>
                        </div>
                        <div class="breakdown-row">
                            <span>Deduction</span>
                            <strong style="color:var(--staff-red)">− RM <?= number_format($deduction,2) ?>
                            </strong>
                        </div>
                        <div class="breakdown-row">
                            <span>Gross pay</span>
                            <strong>RM <?= number_format($gross,2) ?>
                            </strong>
                        </div>
                    </div>
                </article>
                <article class="ios-card">
                    <div class="ios-card-header">
                        <h2>Attendance context</h2>
                    </div>
                    <div class="ios-card-body attendance-ring">@@KCWBLOCK2@@<div class="ring" style="--pct:<?= $pct ?>">
                        <strong>
                            <?= $pct ?>%</strong>
                        </div>
                        <div class="att-legend">
                            <div>
                                <span>Present days</span>
                                <strong>
                                    <?= $present ?>
                                </strong>
                            </div>
                            <div>
                                <span>Absent days</span>
                                <strong>
                                    <?= $absent ?>
                                </strong>
                            </div>
                            <div>
                                <span>Recorded days</span>
                                <strong>
                                    <?= $totalDays ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                </article>
            </section>
        </main>
        <?php include __DIR__.'/../includes/footer.php'; ?>
    </body>
</html>
