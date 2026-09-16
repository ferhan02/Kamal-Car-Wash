<?php
function kcw_h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function kcw_admin_role(string $role): bool { return in_array($role, ['Owner', 'Manager', 'Supervisor'], true); }
function kcw_flash(string $type, string $message): void { $_SESSION[$type] = $message; }
function kcw_status_class(?string $status): string {
$status = strtolower((string)$status);
return match ($status) {
'approved', 'confirmed', 'completed', 'present' => 'approved',
'rejected', 'cancelled', 'absent' => 'rejected',
'pending' => 'pending',
default => 'neutral',
};
}
function kcw_staff_image_url(array $staff, string $root_prefix): string {
$image = $staff['staff_image'] ?? 'uploads/default.png';
if (!$image) $image = 'uploads/default.png';
return $root_prefix . 'images/' . ltrim($image, '/');
}
