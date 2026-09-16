<?php
session_start();
require_once "../../config.php";

if (!isset($_SESSION['cust_id'])) {
    header("Location: ../../auth/cust_login.php");
    exit();
}

$custId = (int) $_SESSION['cust_id'];
$success = '';
$error = '';

$stmt = $conn->prepare("SELECT * FROM customer WHERE cust_id = ?");
$stmt->execute([$custId]);
$customer = $stmt->fetch();

if (!$customer) {
    session_destroy();
    header("Location: ../../auth/cust_login.php");
    exit();
}

function resolveCustomerImagePath(?string $value): string {
    if (!$value) return '../../images/uploads/default.png';
    $candidate = trim($value);
    if (str_starts_with($candidate, 'uploads/')) return '../../images/' . $candidate;
    if (str_starts_with($candidate, 'images/')) return '../../' . $candidate;
    return '../../images/uploads/default.png';
}

if (isset($_POST['update_profile'])) {
    $name = trim($_POST['cust_name'] ?? '');
    $dob = trim($_POST['cust_dob'] ?? '');
    $phone = trim($_POST['cust_phonenum'] ?? '');
    $email = strtolower(trim($_POST['cust_email'] ?? ''));
    $username = trim($_POST['cust_username'] ?? '');
    $gender = $_POST['cust_gender'] ?? '';
    $state = trim($_POST['cust_state'] ?? '');
    $croppedImage = trim($_POST['cropped_image'] ?? '');

    if ($name === '' || $dob === '' || $phone === '' || $email === '' || $username === '' || $state === '' || !in_array((string)$gender, ['0', '1'], true)) {
        $error = 'Please complete every required profile field.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($name) > 50 || strlen($email) > 30 || strlen($username) > 50 || strlen($state) > 30) {
        $error = 'One or more values are longer than the current database allows.';
    } else {
        $duplicate = $conn->prepare("\n            SELECT cust_id\n            FROM customer\n            WHERE (cust_email = ? OR cust_username = ?)\n              AND cust_id <> ?\n            LIMIT 1\n        ");
        $duplicate->execute([$email, $username, $custId]);

        if ($duplicate->fetch()) {
            $error = 'That email address or username is already used by another customer.';
        } else {
            $newImageValue = $customer['cust_image'] ?? null;
            $newImageAbsolutePath = null;
            $oldImageValue = $customer['cust_image'] ?? null;

            if ($croppedImage !== '') {
                if (strlen($croppedImage) > 4 * 1024 * 1024) {
                    $error = 'The cropped profile image is too large. Please choose another image.';
                } elseif (!preg_match('#^data:image/(jpeg|jpg|png|webp);base64,#i', $croppedImage, $matches)) {
                    $error = 'The selected profile image could not be processed.';
                } else {
                    $base64 = substr($croppedImage, strpos($croppedImage, ',') + 1);
                    $binary = base64_decode($base64, true);
                    $imageInfo = $binary !== false ? @getimagesizefromstring($binary) : false;

                    if ($binary === false || $imageInfo === false) {
                        $error = 'The selected profile image is invalid.';
                    } else {
                        $mimeToExtension = [
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/webp' => 'webp'
                        ];
                        $mime = $imageInfo['mime'] ?? '';

                        if (!isset($mimeToExtension[$mime])) {
                            $error = 'Please use a JPG, PNG, or WEBP image.';
                        } else {
                            $uploadDir = '../../images/uploads/customer/';
                            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                                $error = 'The profile image folder could not be created.';
                            } else {
                                $filename = 'customer_' . $custId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $mimeToExtension[$mime];
                                $newImageAbsolutePath = $uploadDir . $filename;
                                if (file_put_contents($newImageAbsolutePath, $binary) === false) {
                                    $newImageAbsolutePath = null;
                                    $error = 'The profile image could not be saved.';
                                } else {
                                    $newImageValue = 'uploads/customer/' . $filename;
                                }
                            }
                        }
                    }
                }
            }

            if ($error === '') {
                try {
                    $conn->beginTransaction();
                    $update = $conn->prepare("\n                        UPDATE customer\n                        SET cust_name = ?, cust_dob = ?, cust_phonenum = ?, cust_email = ?,\n                            cust_username = ?, cust_gender = ?, cust_state = ?, cust_image = ?\n                        WHERE cust_id = ?\n                    ");
                    $update->execute([$name, $dob, $phone, $email, $username, (int)$gender, $state, $newImageValue, $custId]);
                    $conn->commit();

                    if ($newImageAbsolutePath && $oldImageValue && str_starts_with($oldImageValue, 'uploads/customer/')) {
                        $oldAbsolute = '../../images/' . $oldImageValue;
                        if (is_file($oldAbsolute) && realpath($oldAbsolute) !== realpath($newImageAbsolutePath)) {
                            @unlink($oldAbsolute);
                        }
                    }

                    $_SESSION['cust_name'] = $name;
                    $success = $croppedImage !== '' ? 'Profile and profile picture updated successfully.' : 'Profile updated successfully.';

                    $stmt->execute([$custId]);
                    $customer = $stmt->fetch();
                } catch (Throwable $e) {
                    if ($conn->inTransaction()) $conn->rollBack();
                    if ($newImageAbsolutePath && is_file($newImageAbsolutePath)) @unlink($newImageAbsolutePath);
                    $error = 'Your profile could not be updated. Please try again.';
                }
            }
        }
    }
}

$vehicleStmt = $conn->prepare("SELECT COUNT(*) FROM vehicle WHERE cust_id = ?");
$vehicleStmt->execute([$custId]);
$vehicleCount = (int)$vehicleStmt->fetchColumn();

$bookingStmt = $conn->prepare("SELECT COUNT(*) FROM service WHERE cust_id = ?");
$bookingStmt->execute([$custId]);
$bookingCount = (int)$bookingStmt->fetchColumn();

$imagePath = resolveCustomerImagePath($customer['cust_image'] ?? null);

$states = [
    'Johor','Kedah','Kelantan','Melaka','Negeri Sembilan','Pahang','Penang','Perak',
    'Perlis','Sabah','Sarawak','Selangor','Terengganu','Kuala Lumpur','Putrajaya','Labuan'
];

$pageTitle = "Profile";
include "../includes/header.php";
?>

<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css');

.profile-hero {
    padding: 54px 0 42px;
    border-bottom: 1px solid var(--border);
    background:
        radial-gradient(circle at 82% 10%, color-mix(in srgb, var(--primary) 12%, transparent), transparent 28%),
        color-mix(in srgb, var(--surface) 90%, transparent);
}
.profile-layout {
    display: grid;
    grid-template-columns: 320px minmax(0, 1fr);
    gap: 26px;
    align-items: start;
}
.profile-summary {
    position: sticky;
    top: 128px;
    overflow: hidden;
}
.profile-cover {
    height: 102px;
    background:
        linear-gradient(135deg, rgba(36,91,149,.91), rgba(76,132,184,.72)),
        url("../../images/hero.jpg") center/cover;
}
.profile-body { padding: 0 22px 24px; text-align: center; }
.avatar-wrap {
    position: relative;
    width: 108px;
    height: 108px;
    margin: -54px auto 14px;
}
.profile-avatar {
    width: 108px;
    height: 108px;
    border: 5px solid var(--surface);
    border-radius: 50%;
    object-fit: cover;
    background: var(--surface-2);
    box-shadow: 0 12px 28px rgba(20, 38, 60, .18);
}
.avatar-edit-btn {
    position: absolute;
    right: -2px;
    bottom: 2px;
    display: grid;
    width: 36px;
    height: 36px;
    place-items: center;
    border: 3px solid var(--surface);
    border-radius: 50%;
    background: var(--primary);
    color: #fff;
    cursor: pointer;
    box-shadow: var(--shadow-sm);
    transition: transform .18s ease, background .18s ease;
}
.avatar-edit-btn:hover { transform: translateY(-1px) scale(1.03); background: var(--primary-strong); }
.profile-body h2 { color: var(--heading); font-size: 1.08rem; }
.profile-body p { margin-top: 3px; color: var(--text-soft); font-size: .8rem; }
.photo-note { margin-top: 9px !important; font-size: .72rem !important; line-height: 1.45; }
.profile-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 18px;
}
.profile-stat { padding: 12px 8px; border-radius: 12px; background: var(--surface-2); }
.profile-stat strong { display: block; color: var(--heading); font-size: 1.08rem; }
.profile-stat span { color: var(--text-soft); font-size: .67rem; font-weight: 700; text-transform: uppercase; }
.profile-links { display: grid; gap: 8px; margin-top: 17px; }
.profile-links .btn { width: 100%; }

.profile-form-card { padding: 30px; }
.profile-form-head {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
}
.profile-form-head h2 { color: var(--heading); font-size: 1.2rem; }
.profile-form-head p { margin-top: 4px; color: var(--text-soft); font-size: .82rem; }
.profile-form { display: grid; gap: 17px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.profile-form-actions {
    display: flex;
    gap: 10px;
    margin-top: 4px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.crop-modal {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: none;
    place-items: center;
    padding: 24px;
    background: rgba(7, 15, 25, .68);
    backdrop-filter: blur(10px);
}
.crop-modal.open { display: grid; }
.crop-dialog {
    width: min(760px, 100%);
    overflow: hidden;
    border: 1px solid var(--border);
    border-radius: 22px;
    background: var(--surface);
    box-shadow: var(--shadow-lg);
}
.crop-dialog-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 20px 22px;
    border-bottom: 1px solid var(--border);
}
.crop-dialog-head h3 { color: var(--heading); font-size: 1.06rem; }
.crop-dialog-head p { margin-top: 2px; color: var(--text-soft); font-size: .76rem; }
.crop-close {
    display: grid;
    width: 38px;
    height: 38px;
    place-items: center;
    border: 1px solid var(--border);
    border-radius: 11px;
    background: var(--surface-2);
    color: var(--heading);
    cursor: pointer;
}
.crop-stage {
    height: min(56vh, 470px);
    min-height: 320px;
    padding: 18px;
    background: #0b1420;
}
.crop-stage img { display: block; max-width: 100%; }
.cropper-view-box, .cropper-face { border-radius: 50%; }
.cropper-view-box { outline-color: rgba(255,255,255,.86); outline-width: 2px; }
.crop-tools {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}
.crop-tool {
    display: inline-flex;
    min-height: 38px;
    align-items: center;
    gap: 7px;
    padding: 8px 11px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: var(--surface-2);
    color: var(--heading);
    cursor: pointer;
    font-weight: 700;
    font-size: .76rem;
}
.crop-dialog-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 18px 20px;
}

@media (max-width: 850px) {
    .profile-layout { grid-template-columns: 1fr; }
    .profile-summary { position: static; }
}
@media (max-width: 600px) {
    .profile-form-card { padding: 21px; }
    .form-grid { grid-template-columns: 1fr; }
    .profile-form-actions, .crop-dialog-actions { flex-direction: column; }
    .profile-form-actions .btn, .crop-dialog-actions .btn { width: 100%; }
    .crop-modal { padding: 12px; }
    .crop-stage { min-height: 280px; height: 48vh; }
    .crop-tools { justify-content: center; }
}
</style>

<section class="profile-hero">
    <div class="container">
        <span class="eyebrow"><i class="fa-solid fa-user-gear"></i> Account</span>
        <h1 class="section-heading">Profile settings</h1>
        <p class="section-copy" style="margin-top:10px;">Keep your account details current and make your profile picture look exactly how you want it.</p>
    </div>
</section>

<section class="section">
    <div class="container profile-layout">
        <aside class="card profile-summary">
            <div class="profile-cover"></div>
            <div class="profile-body">
                <div class="avatar-wrap">
                    <img class="profile-avatar" id="profile-avatar-preview" src="<?= htmlspecialchars($imagePath) ?>" alt="Customer profile image"
                         onerror="this.src='../../images/uploads/default.png'">
                    <button class="avatar-edit-btn" type="button" id="profile-photo-button" aria-label="Change profile picture" title="Change profile picture">
                        <i class="fa-solid fa-camera"></i>
                    </button>
                </div>
                <input type="file" id="profile-photo-input" accept="image/jpeg,image/png,image/webp" hidden>

                <h2><?= htmlspecialchars($customer['cust_name']) ?></h2>
                <p>@<?= htmlspecialchars($customer['cust_username']) ?></p>
                <p class="photo-note">JPG, PNG or WEBP. Crop and reposition it before saving.</p>

                <div class="profile-stats">
                    <div class="profile-stat"><strong><?= $vehicleCount ?></strong><span>Vehicles</span></div>
                    <div class="profile-stat"><strong><?= $bookingCount ?></strong><span>Bookings</span></div>
                </div>

                <div class="profile-links">
                    <a class="btn btn-secondary" href="change_password.php"><i class="fa-solid fa-lock"></i> Change password</a>
                    <a class="btn btn-secondary" href="vehicle.php"><i class="fa-solid fa-car-side"></i> Manage vehicles</a>
                </div>
            </div>
        </aside>

        <main class="card profile-form-card">
            <div class="profile-form-head">
                <div>
                    <h2>Personal information</h2>
                    <p>Changes are saved securely to your customer profile.</p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom:18px;"><i class="fa-solid fa-circle-check"></i><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom:18px;"><i class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form class="profile-form" method="POST" id="profile-form">
                <input type="hidden" name="cropped_image" id="cropped-image-data" value="">

                <div class="form-grid">
                    <div class="field">
                        <label for="cust_name">Full name</label>
                        <input class="input" id="cust_name" name="cust_name" maxlength="50" value="<?= htmlspecialchars($customer['cust_name']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="cust_dob">Date of birth</label>
                        <input class="input" id="cust_dob" type="date" name="cust_dob" value="<?= htmlspecialchars($customer['cust_dob']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="cust_phonenum">Phone number</label>
                        <input class="input" id="cust_phonenum" name="cust_phonenum" inputmode="numeric" value="<?= htmlspecialchars((string)$customer['cust_phonenum']) ?>" required>
                        <span class="helper-text">The current database still stores this as an integer.</span>
                    </div>
                    <div class="field">
                        <label for="cust_email">Email address</label>
                        <input class="input" id="cust_email" type="email" name="cust_email" maxlength="30" value="<?= htmlspecialchars($customer['cust_email']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="cust_username">Username</label>
                        <input class="input" id="cust_username" name="cust_username" maxlength="50" value="<?= htmlspecialchars($customer['cust_username']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="cust_gender">Gender</label>
                        <select class="select" id="cust_gender" name="cust_gender" required>
                            <option value="0" <?= (int)$customer['cust_gender'] === 0 ? 'selected' : '' ?>>Male</option>
                            <option value="1" <?= (int)$customer['cust_gender'] === 1 ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="cust_state">State</label>
                        <select class="select" id="cust_state" name="cust_state" required>
                            <?php foreach ($states as $state): ?>
                                <option value="<?= htmlspecialchars($state) ?>" <?= $customer['cust_state'] === $state ? 'selected' : '' ?>><?= htmlspecialchars($state) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="profile-form-actions">
                    <button class="btn btn-primary" type="submit" name="update_profile">
                        <i class="fa-solid fa-floppy-disk"></i> Save changes
                    </button>
                    <a class="btn btn-secondary" href="cust_home.php">Cancel</a>
                </div>
            </form>
        </main>
    </div>
</section>

<div class="crop-modal" id="crop-modal" aria-hidden="true">
    <div class="crop-dialog" role="dialog" aria-modal="true" aria-labelledby="crop-title">
        <div class="crop-dialog-head">
            <div>
                <h3 id="crop-title">Adjust profile picture</h3>
                <p>Drag the photo and use the controls until it fits the circle perfectly.</p>
            </div>
            <button class="crop-close" type="button" id="crop-close" aria-label="Close crop editor"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="crop-stage">
            <img id="crop-image" alt="Image being cropped">
        </div>
        <div class="crop-tools" aria-label="Image adjustment controls">
            <button class="crop-tool" type="button" data-crop-action="zoom-in"><i class="fa-solid fa-magnifying-glass-plus"></i> Zoom in</button>
            <button class="crop-tool" type="button" data-crop-action="zoom-out"><i class="fa-solid fa-magnifying-glass-minus"></i> Zoom out</button>
            <button class="crop-tool" type="button" data-crop-action="rotate-left"><i class="fa-solid fa-rotate-left"></i> Rotate left</button>
            <button class="crop-tool" type="button" data-crop-action="rotate-right"><i class="fa-solid fa-rotate-right"></i> Rotate right</button>
            <button class="crop-tool" type="button" data-crop-action="reset"><i class="fa-solid fa-arrow-rotate-left"></i> Reset</button>
        </div>
        <div class="crop-dialog-actions">
            <button class="btn btn-secondary" type="button" id="crop-cancel">Cancel</button>
            <button class="btn btn-primary" type="button" id="crop-apply"><i class="fa-solid fa-crop-simple"></i> Apply crop</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script src="../../js/profile-image.js"></script>

<?php include "../includes/footer.php"; ?>
