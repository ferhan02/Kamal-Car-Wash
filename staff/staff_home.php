<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/helpers.php';
if (!isset($_SESSION['staff_id'])) { header('Location: ../auth/staff_login.php'); exit(); }
$staff_id = (int)$_SESSION['staff_id'];
$stmt = $conn->prepare('SELECT * FROM staff WHERE staff_id = ?'); $stmt->execute([$staff_id]); $staff = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$staff) { session_destroy(); header('Location: ../auth/staff_login.php'); exit(); }
$_SESSION['staff_name'] = $staff['staff_name']; $_SESSION['staff_job'] = $staff['staff_job'];
$today = date('Y-m-d');
$stmt=$conn->prepare('SELECT COUNT(*) FROM service WHERE service_date=?');$stmt->execute([$today]);$today_reservations=(int)$stmt->fetchColumn();
$stmt=$conn->query("SELECT COUNT(*) FROM service WHERE service_status='Pending'");$pending=(int)$stmt->fetchColumn();
if(isset($_POST['check_attendance'])){
    $stmt=$conn->prepare('SELECT attendance_id FROM attendance WHERE staff_id=? AND attendance_date=?');$stmt->execute([$staff_id,$today]);
    if(!$stmt->fetchColumn()){ $stmt=$conn->prepare('INSERT INTO attendance (staff_id,attendance_date,is_present) VALUES (?,?,1)');$stmt->execute([$staff_id,$today]); $_SESSION['success']='Attendance recorded for today.'; }
    header('Location: staff_home.php');exit();
}
$stmt=$conn->prepare('SELECT is_present FROM attendance WHERE staff_id=? AND attendance_date=?');$stmt->execute([$staff_id,$today]);$attendance=$stmt->fetch(PDO::FETCH_ASSOC);$attendance_status=($attendance && (int)$attendance['is_present']===1)?'Present':'Not checked in';
$salary=(float)$staff['staff_salary'];
$root_prefix='../';$page_title='Staff Dashboard'; include __DIR__.'/includes/head.php';
?>
<style>
.dashboard-hero{position:relative;overflow:hidden;display:grid;grid-template-columns:1.25fr .75fr;gap:24px;min-height:300px;padding:34px;background:linear-gradient(135deg,rgba(7,45,80,.93),rgba(10,132,255,.78)),url('../images/hero.jpg') center/cover;border-radius:32px;color:#fff;box-shadow:var(--staff-shadow)}
.dashboard-hero::after{content:"";position:absolute;width:260px;height:260px;border-radius:50%;right:-70px;top:-90px;background:rgba(255,255,255,.12);filter:blur(2px)}
.hero-copy{position:relative;z-index:1;align-self:center}.hero-copy .eyebrow{display:inline-flex;align-items:center;gap:8px;padding:7px 10px;border-radius:999px;background:rgba(255,255,255,.12);font-size:.72rem;font-weight:800}.hero-copy h1{max-width:760px;margin:15px 0 11px;font-size:clamp(2.5rem,5vw,4.7rem);line-height:.94;letter-spacing:-.06em}.hero-copy p{max-width:650px;margin:0;color:rgba(255,255,255,.78)}
.hero-profile{position:relative;z-index:1;align-self:center;justify-self:end;width:min(300px,100%);padding:18px;border:1px solid rgba(255,255,255,.18);border-radius:26px;background:rgba(255,255,255,.12);backdrop-filter:blur(20px)}.hero-profile-top{display:flex;align-items:center;gap:12px}.hero-profile img{width:56px;height:56px;border-radius:18px;object-fit:cover;border:1px solid rgba(255,255,255,.22)}.hero-profile strong{display:block}.hero-profile span{display:block;margin-top:2px;color:rgba(255,255,255,.68);font-size:.76rem}.hero-profile-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:16px}.hero-profile-actions a{min-height:38px;display:flex;align-items:center;justify-content:center;gap:7px;border-radius:14px;background:rgba(255,255,255,.13);color:#fff;text-decoration:none;font-size:.72rem;font-weight:800}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:16px}.stat-card{padding:20px}.stat-icon{width:42px;height:42px;display:grid;place-items:center;border-radius:14px;margin-bottom:18px;background:var(--staff-blue-soft);color:var(--staff-blue)}.stat-card.green .stat-icon{background:var(--staff-green-soft);color:var(--staff-green)}.stat-card.orange .stat-icon{background:var(--staff-orange-soft);color:var(--staff-orange)}.stat-card.purple .stat-icon{background:rgba(124,92,255,.12);color:var(--staff-purple)}.stat-label{color:var(--staff-muted);font-size:.73rem;font-weight:800}.stat-value{margin:5px 0 2px;font-size:1.75rem;font-weight:850;letter-spacing:-.04em}.stat-meta{color:var(--staff-muted);font-size:.7rem}.attendance-action{margin-top:12px}
.module-heading{margin:34px 0 14px;display:flex;align-items:end;justify-content:space-between;gap:16px}.module-heading h2{margin:0;font-size:1.25rem;letter-spacing:-.03em}.module-heading p{margin:3px 0 0;color:var(--staff-muted);font-size:.78rem}.modules-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.module-card{position:relative;overflow:hidden;min-height:168px;padding:20px;color:inherit;text-decoration:none;transition:transform .18s ease,box-shadow .18s ease}.module-card:hover{transform:translateY(-3px);box-shadow:var(--staff-shadow)}.module-icon{width:46px;height:46px;display:grid;place-items:center;border-radius:16px;background:var(--staff-blue-soft);color:var(--staff-blue);font-size:1.05rem}.module-card h3{margin:25px 0 5px;font-size:.98rem}.module-card p{margin:0;color:var(--staff-muted);font-size:.75rem}.module-arrow{position:absolute;right:18px;top:20px;color:var(--staff-muted)}
@media(max-width:1050px){.stats-grid,.modules-grid{grid-template-columns:repeat(2,1fr)}.dashboard-hero{grid-template-columns:1fr}.hero-profile{justify-self:start}}
@media(max-width:600px){.dashboard-hero{padding:24px;min-height:0}.hero-copy h1{font-size:2.7rem}.stats-grid,.modules-grid{grid-template-columns:1fr}.hero-profile{width:100%}}
</style>
</head><body class="staff-body">
<?php include __DIR__.'/includes/header.php'; ?>
<?php if(kcw_admin_role($staff['staff_job'])) include __DIR__.'/includes/admin_toolbar.php'; ?>
<main class="staff-main">
    <section class="dashboard-hero">
        <div class="hero-copy"><span class="eyebrow"><i class="fa-solid fa-sparkles"></i> <?= date('l, d F') ?></span><h1>Good to see you, <?= kcw_h(explode(' ',trim($staff['staff_name']))[0]) ?>.</h1><p>Everything you need for today’s car wash operations, arranged in one calm workspace.</p></div>
        <aside class="hero-profile"><div class="hero-profile-top"><img src="<?= kcw_h(kcw_staff_image_url($staff,$root_prefix)) ?>" alt="Profile"><div><strong><?= kcw_h($staff['staff_name']) ?></strong><span><?= kcw_h($staff['staff_job']) ?></span></div></div><div class="hero-profile-actions"><a href="pages/update_profile.php"><i class="fa-solid fa-user-pen"></i> Profile</a><a href="../auth/logout.php" data-confirm="Sign out from the staff portal?"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sign out</a></div></aside>
    </section>

    <section class="stats-grid" aria-label="Today overview">
        <article class="ios-card stat-card"><div class="stat-icon"><i class="fa-solid fa-car-side"></i></div><span class="stat-label">TODAY'S BOOKINGS</span><div class="stat-value"><?= $today_reservations ?></div><div class="stat-meta">Scheduled for <?= date('d M') ?></div></article>
        <article class="ios-card stat-card orange"><div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div><span class="stat-label">PENDING</span><div class="stat-value"><?= $pending ?></div><div class="stat-meta">Reservations awaiting action</div></article>
        <article class="ios-card stat-card green"><div class="stat-icon"><i class="fa-solid fa-fingerprint"></i></div><span class="stat-label">ATTENDANCE</span><div class="stat-value" style="font-size:1.25rem"><?= kcw_h($attendance_status) ?></div><?php if($attendance_status!=='Present'): ?><form method="POST" class="attendance-action"><button class="ios-btn ios-btn-primary ios-btn-sm" name="check_attendance"><i class="fa-solid fa-check"></i> Check in</button></form><?php else: ?><div class="stat-meta"><i class="fa-solid fa-circle-check"></i> Recorded today</div><?php endif; ?></article>
        <article class="ios-card stat-card purple"><div class="stat-icon"><i class="fa-solid fa-wallet"></i></div><span class="stat-label">BASIC SALARY</span><div class="stat-value">RM <?= number_format($salary,2) ?></div><div class="stat-meta">Current monthly base</div></article>
    </section>

    <div class="module-heading"><div><h2>Your workspace</h2><p>Jump straight into the task you need.</p></div></div>
    <section class="modules-grid">
        <a class="ios-card module-card" href="pages/reservation.php"><span class="module-arrow"><i class="fa-solid fa-chevron-right"></i></span><div class="module-icon"><i class="fa-solid fa-calendar-check"></i></div><h3>Reservations</h3><p>Search, confirm and manage wash appointments.</p></a>
        <a class="ios-card module-card" href="pages/attendance.php"><span class="module-arrow"><i class="fa-solid fa-chevron-right"></i></span><div class="module-icon"><i class="fa-solid fa-calendar-days"></i></div><h3>Attendance</h3><p>Review yearly attendance at a glance.</p></a>
        <a class="ios-card module-card" href="pages/overtime.php"><span class="module-arrow"><i class="fa-solid fa-chevron-right"></i></span><div class="module-icon"><i class="fa-solid fa-clock"></i></div><h3>Overtime</h3><p>Submit overtime and follow approval status.</p></a>
        <a class="ios-card module-card" href="pages/leave.php"><span class="module-arrow"><i class="fa-solid fa-chevron-right"></i></span><div class="module-icon"><i class="fa-solid fa-plane-departure"></i></div><h3>Leave</h3><p>Request leave and review your application history.</p></a>
        <a class="ios-card module-card" href="pages/salary_summary.php"><span class="module-arrow"><i class="fa-solid fa-chevron-right"></i></span><div class="module-icon"><i class="fa-solid fa-receipt"></i></div><h3>Salary summary</h3><p>See allowances, deductions and approved overtime.</p></a>
        <a class="ios-card module-card" href="pages/salary_advance.php"><span class="module-arrow"><i class="fa-solid fa-chevron-right"></i></span><div class="module-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div><h3>Salary advance</h3><p>Submit and track salary advance requests.</p></a>
        <a class="ios-card module-card" href="pages/update_profile.php"><span class="module-arrow"><i class="fa-solid fa-chevron-right"></i></span><div class="module-icon"><i class="fa-solid fa-user-pen"></i></div><h3>Profile</h3><p>Keep your contact and profile details current.</p></a>
        <?php if(kcw_admin_role($staff['staff_job'])): ?><a class="ios-card module-card" href="admin/staff_management.php"><span class="module-arrow"><i class="fa-solid fa-chevron-right"></i></span><div class="module-icon"><i class="fa-solid fa-shield-halved"></i></div><h3>Administration</h3><p>Manage staff and approval queues.</p></a><?php endif; ?>
    </section>
</main>
<?php include __DIR__.'/includes/footer.php'; ?>
</body></html>
