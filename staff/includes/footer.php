<?php
$root_prefix = $root_prefix ?? '../../';
$flash_success = $_SESSION['success'] ?? ($_SESSION['login_success'] ?? null);
$flash_error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['login_success'], $_SESSION['error']);
?>
<footer class="staff-footer">
    <div class="staff-footer-inner">
        <span>© <?= date('Y') ?> Kamal Car Wash · Staff Operations</span>
        <span>More than a wash, it’s a revival.</span>
    </div>
</footer>

<?php if ($flash_success): ?>
<div class="ios-toast success" role="status"><i class="fa-solid fa-circle-check"></i><div><strong>Done</strong><span><?= htmlspecialchars($flash_success) ?></span></div></div>
<?php endif; ?>
<?php if ($flash_error): ?>
<div class="ios-toast error" role="alert"><i class="fa-solid fa-circle-exclamation"></i><div><strong>Something needs attention</strong><span><?= htmlspecialchars($flash_error) ?></span></div></div>
<?php endif; ?>
<script src="<?= $root_prefix ?>js/staff.js"></script>
