<?php
$root_prefix = $root_prefix ?? '../../';
$current_file = basename($_SERVER['PHP_SELF'] ?? '');
$items = [
    ['staff_management.php', 'fa-users-gear', 'Staff', $root_prefix . 'staff/admin/staff_management.php'],
    ['leave_management.php', 'fa-calendar-xmark', 'Leave', $root_prefix . 'staff/admin/leave_management.php'],
    ['overtime_management.php', 'fa-business-time', 'OT', $root_prefix . 'staff/admin/overtime_management.php'],
    ['salary_advance_management.php', 'fa-money-check-dollar', 'Advance', $root_prefix . 'staff/admin/salary_advance_management.php'],
];
?>
<nav class="staff-admin-dock" aria-label="Administration shortcuts">
    <?php foreach ($items as [$file, $icon, $label, $href]): ?>
        <a href="<?= $href ?>" class="<?= $current_file === $file ? 'active' : '' ?>" title="<?= htmlspecialchars($label) ?> management"><i class="fa-solid <?= $icon ?>"></i><span class="dock-label"><?= htmlspecialchars($label) ?></span></a>
    <?php endforeach; ?>
</nav>
