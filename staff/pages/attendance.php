<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../includes/helpers.php';
if (!isset($_SESSION['staff_id'])) {
    header('Location: ../../auth/staff_login.php');
    exit();
}
$current_id = (int)$_SESSION['staff_id'];
$stmt = $conn->prepare('SELECT * FROM staff WHERE staff_id=?');
$stmt->execute([$current_id]);
$current_staff = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$current_staff) {
    session_destroy();
    header('Location: ../../auth/staff_login.php');
    exit();
}
$is_admin = kcw_admin_role($current_staff['staff_job']);
$staff_list = [];
if ($is_admin) {
    $staff_list = $conn->query('SELECT staff_id,staff_name FROM staff ORDER BY staff_name')->fetchAll(PDO::FETCH_ASSOC);
}
$selected_id = $is_admin ? max(1, (int)($_GET['staff_id'] ?? $current_id)) : $current_id;
$year = max(2020, min(2100, (int)($_GET['year'] ?? date('Y'))));
$stmt = $conn->prepare('SELECT * FROM staff WHERE staff_id=?');
$stmt->execute([$selected_id]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$staff) {
    $staff = $current_staff;
    $selected_id = $current_id;
}
$stmt = $conn->prepare('SELECT attendance_date,is_present FROM attendance WHERE staff_id=? AND YEAR(attendance_date)=? ORDER BY attendance_date');
$stmt->execute([$selected_id, $year]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$byMonth = array_fill(1, 12, []);
$present = 0;
$absent = 0;
foreach ($rows as $r) {
    $m = (int)date('n', strtotime($r['attendance_date']));
    $byMonth[$m][] = $r;
    (int)$r['is_present'] === 1 ? $present++: $absent++;
}
$root_prefix = '../../';
$page_title = 'Attendance';
include __DIR__ . '/../includes/head.php';
?>
<style>
.attendance-head {
    display: flex;
    gap: 10px;
    align-items: end;
    flex-wrap: wrap
}
.attendance-head form {
    display: flex;
    gap: 10px;
    align-items: end;
    flex-wrap: wrap
}
.summary-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 14px
}
.summary-card {
    padding: 18px
}
.summary-card span {
    color: var(--staff-muted);
    font-size: .72rem;
    font-weight: 800
}
.summary-card strong {
    display: block;
    margin-top: 4px;
    font-size: 1.7rem;
    letter-spacing: -.04em
}
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px
}
.month-card {
    padding: 17px
}
.month-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px
}
.month-title strong {
    font-size: .88rem
}
.month-title span {
    color: var(--staff-muted);
    font-size: .67rem
}
.attendance-list {
    display: grid;
    gap: 6px;
    max-height: 220px;
    overflow: auto
}
.attendance-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 10px;
    border-radius: 12px;
    background: var(--staff-surface-2);
    font-size: .72rem
}
.attendance-row.empty {
    justify-content: center;
    color: var(--staff-muted);
    min-height: 55px;
    background: transparent;
    border: 1px dashed var(--staff-border)
}
@media (max-width: 1000px) {
    .calendar-grid {
        grid-template-columns: repeat(2, 1fr)
    }
}
@media (max-width: 620px) {
    .summary-grid, .calendar-grid {
        grid-template-columns: 1fr
    }
    .attendance-head form {
        width: 100%
    }
    .attendance-head .ios-field {
        flex: 1
    }
}
</style>
</head>
<body class="staff-body">
    <?php include __DIR__.'/../includes/header.php'; ?>
    <?php if ($is_admin) include __DIR__.'/../includes/admin_toolbar.php'; ?>
    <main class="staff-main">
        <div class="staff-page-heading">
            <div>
                <span class="staff-eyebrow"><i class="fa-solid fa-calendar-days"></i> Attendance</span>
                <h1><?= $selected_id===$current_id?'Your attendance':kcw_h($staff['staff_name']).' attendance' ?></h1>
                <p>A clean yearly view of recorded attendance. Administrators can switch between staff members.</p>
            </div>
            <div class="attendance-head">
                <form method="GET">
                    <?php if ($is_admin): ?>
                        <div class="ios-field">
                            <label>Staff</label>
                            <select class="ios-select" name="staff_id" data-auto-submit>
                                <?php foreach ($staff_list as $s): ?>
                                    <option value="<?= $s['staff_id'] ?>" <?= $selected_id==(int)$s['staff_id']?'selected':'' ?>>
                                        <?= kcw_h($s['staff_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="ios-field">
                        <label>Year</label>
                        <select class="ios-select" name="year" data-auto-submit>
                            <?php for ($y=(int)date('Y')-2; $y<=(int)date('Y')+1; $y++): ?>
                                <option value="<?= $y ?>" <?= $year===$y?'selected':'' ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
        <section class="summary-grid">
            <article class="ios-card summary-card">
                <span>RECORDED DAYS</span>
                <strong><?= count($rows) ?></strong>
            </article>
            <article class="ios-card summary-card">
                <span>PRESENT</span>
                <strong style="color:var(--staff-green)"><?= $present ?></strong>
            </article>
            <article class="ios-card summary-card">
                <span>ABSENT</span>
                <strong style="color:var(--staff-red)"><?= $absent ?></strong>
            </article>
        </section>
        <section class="calendar-grid">
            <?php for ($m=1; $m<=12; $m++): ?>
                <article class="ios-card month-card">
                    <div class="month-title">
                        <strong><?= date('F',mktime(0,0,0,$m,1,$year)) ?></strong>
                        <span><?= count($byMonth[$m]) ?> records</span>
                        </div>
                        <div class="attendance-list">
                            <?php if (!$byMonth[$m]): ?>
                                <div class="attendance-row empty">No records</div>
                            <?php endif; ?>
                            <?php foreach ($byMonth[$m] as $r): ?>
                                <div class="attendance-row">
                                    <span><?= date('D, d M',strtotime($r['attendance_date'])) ?></span>
                                    <span class="ios-badge <?= (int)$r['is_present']===1?'present':'absent' ?>">
                                        <?= (int)$r['is_present']===1?'Present':'Absent' ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endfor; ?>
            </section>
        </main>
        <?php include __DIR__.'/../includes/footer.php'; ?>
    </body>
</html>
