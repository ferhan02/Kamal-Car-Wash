<?php
$root_prefix = $root_prefix ?? '../../';
$current_file = basename($_SERVER['PHP_SELF'] ?? '');
$staff_name = $staff['staff_name'] ?? ($_SESSION['staff_name'] ?? 'Staff');
$staff_job = $staff['staff_job'] ?? ($_SESSION['staff_job'] ?? 'Staff');
$staff_image = $staff['staff_image'] ?? 'uploads/default.png';
if (!$staff_image) $staff_image = 'uploads/default.png';
$staff_image_url = $root_prefix . 'images/' . ltrim($staff_image, '/');
$is_admin_role = in_array($staff_job, ['Owner', 'Manager', 'Supervisor'], true);
$nav = [
    ['staff_home.php', $root_prefix . 'staff/staff_home.php', 'fa-house', 'Home'],
    ['reservation.php', $root_prefix . 'staff/pages/reservation.php', 'fa-calendar-check', 'Reservations'],
    ['attendance.php', $root_prefix . 'staff/pages/attendance.php', 'fa-calendar-days', 'Attendance'],
    ['salary_summary.php', $root_prefix . 'staff/pages/salary_summary.php', 'fa-wallet', 'Salary'],
    ['overtime.php', $root_prefix . 'staff/pages/overtime.php', 'fa-clock', 'Overtime'],
    ['leave.php', $root_prefix . 'staff/pages/leave.php', 'fa-plane-departure', 'Leave'],
    ['salary_advance.php', $root_prefix . 'staff/pages/salary_advance.php', 'fa-hand-holding-dollar', 'Advance'],
];
?>
<div class="staff-system-bar" aria-hidden="true">
    <span class="staff-system-time" data-staff-clock><?= date('H:i') ?></span>
    <span class="staff-system-center"></span>
    <span class="staff-system-icons"><i class="fa-solid fa-signal"></i><i class="fa-solid fa-wifi"></i><i class="fa-solid fa-battery-three-quarters"></i></span>
</div>
<div class="staff-header-wrap">
<header class="staff-header">
    <div class="staff-header-row">
        <a class="staff-brand" href="<?= $root_prefix ?>staff/staff_home.php">
            <img src="<?= $root_prefix ?>images/logo.png" class="staff-brand-logo" alt="Kamal Car Wash logo">
            <span class="staff-brand-copy"><strong>KAMAL CAR WASH</strong><span>Staff Operations</span></span>
        </a>

        <nav class="staff-nav" data-staff-nav aria-label="Staff navigation">
            <?php foreach ($nav as [$file, $href, $icon, $label]): ?>
                <a href="<?= $href ?>" class="<?= $current_file === $file ? 'active' : '' ?>"><i class="fa-solid <?= $icon ?>"></i><span><?= $label ?></span></a>
            <?php endforeach; ?>
            <?php if ($is_admin_role): ?>
                <a href="<?= $root_prefix ?>staff/admin/staff_management.php" class="<?= str_contains($_SERVER['PHP_SELF'] ?? '', '/staff/admin/') ? 'active' : '' ?>"><i class="fa-solid fa-shield-halved"></i><span>Admin</span></a>
            <?php endif; ?>
        </nav>

        <div class="staff-actions">
            <button type="button" class="staff-icon-button" data-theme-toggle aria-label="Toggle dark mode" title="Toggle appearance"><i class="fa-solid fa-moon" data-theme-icon></i></button>
            <a href="<?= $root_prefix ?>staff/pages/update_profile.php" class="staff-profile-pill" title="Profile">
                <img src="<?= htmlspecialchars($staff_image_url) ?>" alt="<?= htmlspecialchars($staff_name) ?> profile photo" onerror="this.src='<?= $root_prefix ?>images/uploads/default.png'">
                <span><?= htmlspecialchars($staff_name) ?></span>
            </a>
            <button type="button" class="staff-icon-button staff-menu-toggle" data-staff-menu-toggle aria-label="Open navigation"><i class="fa-solid fa-bars"></i></button>
        </div>
    </div>
</header>
</div>

<nav class="staff-mobile-dock" aria-label="Quick navigation">
    <a href="<?= $root_prefix ?>staff/staff_home.php" class="<?= $current_file === 'staff_home.php' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i><span>Home</span></a>
    <a href="<?= $root_prefix ?>staff/pages/reservation.php" class="<?= $current_file === 'reservation.php' ? 'active' : '' ?>"><i class="fa-solid fa-calendar-check"></i><span>Bookings</span></a>
    <a href="<?= $root_prefix ?>staff/pages/attendance.php" class="<?= $current_file === 'attendance.php' ? 'active' : '' ?>"><i class="fa-solid fa-calendar-days"></i><span>Attendance</span></a>
    <a href="<?= $root_prefix ?>staff/pages/update_profile.php" class="<?= $current_file === 'update_profile.php' ? 'active' : '' ?>"><i class="fa-solid fa-user"></i><span>Profile</span></a>
</nav>
