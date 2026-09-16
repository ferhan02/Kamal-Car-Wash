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
$states = [
    'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang',
    'Penang', 'Perak', 'Perlis', 'Sabah', 'Sarawak', 'Selangor',
    'Terengganu', 'Kuala Lumpur', 'Putrajaya', 'Labuan'
];
$fieldErrors = [];
$error = '';
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['staff_name'] ?? '');
    $email = strtolower(trim($_POST['staff_email'] ?? ''));
    $phone = trim($_POST['staff_phonenum'] ?? '');
    $dob = trim($_POST['staff_dob'] ?? '');
    $state = trim($_POST['staff_state'] ?? '');
    $gender = $_POST['staff_gender'] ?? '';
    $croppedImage = trim($_POST['cropped_image'] ?? '');

    if ($name === '') $fieldErrors['staff_name'] = 'Enter your full name.';
    if ($email === '') {
        $fieldErrors['staff_email'] = 'Enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['staff_email'] = 'Enter a valid email address.';
    }
    if ($phone === '') $fieldErrors['staff_phonenum'] = 'Enter your phone number.';
    if ($dob === '') $fieldErrors['staff_dob'] = 'Choose your date of birth.';
    if (!in_array($state, $states, true)) $fieldErrors['staff_state'] = 'Choose a state from the list.';
    if (!in_array((string) $gender, ['0', '1'], true)) $fieldErrors['staff_gender'] = 'Choose one gender option.';

    if (!$fieldErrors) {
        $stmt = $conn->prepare('SELECT staff_id FROM staff WHERE staff_email = ? AND staff_id <> ? LIMIT 1');
        $stmt->execute([$email, $staff_id]);
        if ($stmt->fetch()) $fieldErrors['staff_email'] = 'That email is already used by another staff account.';
    }

    $image = $staff['staff_image'] ?: 'uploads/default.png';
    $newAbsolute = null;
    $oldImage = $image;

    if (!$fieldErrors && $croppedImage !== '') {
        if (strlen($croppedImage) > 4 * 1024 * 1024) {
            $fieldErrors['staff_image'] = 'The cropped profile image is too large.';
        } elseif (!preg_match('#^data:image/(jpeg|jpg|png|webp);base64,#i', $croppedImage)) {
            $fieldErrors['staff_image'] = 'The selected profile image could not be processed.';
        } else {
            $binary = base64_decode(substr($croppedImage, strpos($croppedImage, ',') + 1), true);
            $info = $binary !== false ? @getimagesizefromstring($binary) : false;
            if (!$info || !in_array($info['mime'] ?? '', ['image/jpeg', 'image/png', 'image/webp'], true)) {
                $fieldErrors['staff_image'] = 'Use a JPG, PNG or WEBP image.';
            } else {
                $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime']];
                $folder = __DIR__ . '/../../images/uploads/staff/';
                if (!is_dir($folder)) mkdir($folder, 0755, true);
                $filename = 'staff_' . $staff_id . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                $newAbsolute = $folder . $filename;
                if (file_put_contents($newAbsolute, $binary) === false) {
                    $newAbsolute = null;
                    $fieldErrors['staff_image'] = 'The profile image could not be saved.';
                } else {
                    $image = 'uploads/staff/' . $filename;
                }
            }
        }
    }

    if (!$fieldErrors) {
        try {
            $stmt = $conn->prepare(
                'UPDATE staff SET
                    staff_name = ?, staff_email = ?, staff_phonenum = ?, staff_dob = ?,
                    staff_state = ?, staff_gender = ?, staff_image = ?
                 WHERE staff_id = ?'
            );
            $stmt->execute([$name, $email, $phone, $dob, $state, (int) $gender, $image, $staff_id]);
            $_SESSION['staff_name'] = $name;
            $_SESSION['success'] = 'Profile updated successfully.';

            if ($newAbsolute && $oldImage && str_starts_with($oldImage, 'uploads/staff/')) {
                $oldAbsolute = __DIR__ . '/../../images/' . $oldImage;
                if (is_file($oldAbsolute) && realpath($oldAbsolute) !== realpath($newAbsolute)) @unlink($oldAbsolute);
            }

            header('Location: update_profile.php');
            exit();
        } catch (Throwable $e) {
            if ($newAbsolute && is_file($newAbsolute)) @unlink($newAbsolute);
            $error = 'Your profile could not be updated. Please try again.';
        }
    }
}

$formStaff = $staff;
if (isset($_POST['update_profile']) && ($fieldErrors || $error)) {
    foreach (['staff_name', 'staff_email', 'staff_phonenum', 'staff_dob', 'staff_state', 'staff_gender'] as $key) {
        if (array_key_exists($key, $_POST)) $formStaff[$key] = $_POST[$key];
    }
}

$root_prefix = '../../';
$page_title = 'Profile';
include __DIR__ . '/../includes/head.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
.profile-layout {
    display: grid;
    grid-template-columns: 330px 1fr;
    gap: 14px
}
.identity-card {
    position: sticky;
    top: 112px;
    align-self: start;
    padding: 24px;
    text-align: center
}
.avatar-wrap {
    position: relative;
    width: 132px;
    height: 132px;
    margin: 2px auto 14px
}
.avatar-wrap img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid var(--staff-surface-solid);
    box-shadow: 0 0 0 1px var(--staff-border),var(--staff-shadow-soft)
}
.avatar-badge {
    position: absolute;
    right: 3px;
    bottom: 4px;
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    border: 3px solid var(--staff-surface-solid);
    border-radius: 50%;
    background: var(--staff-blue);
    color: #fff;
    cursor: pointer;
    box-shadow: var(--staff-shadow-soft);
    transition: .18s ease
}
.avatar-badge:hover {
    transform: translateY(-1px) scale(1.04);
    background: var(--staff-blue-strong)
}
.photo-helper {
    margin: 0 auto 17px;
    max-width: 230px;
    color: var(--staff-muted);
    font-size: .7rem;
    line-height: 1.45
}
.identity-card h2 {
    margin: 0;
    font-size: 1.25rem;
    letter-spacing: -.03em
}
.identity-card .role {
    display: inline-flex;
    margin-top: 7px;
    padding: 5px 9px;
    border-radius: 999px;
    background: var(--staff-blue-soft);
    color: var(--staff-blue);
    font-size: .7rem;
    font-weight: 850
}
.identity-meta {
    display: grid;
    gap: 8px;
    margin-top: 20px;
    text-align: left
}
.identity-meta div {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 10px 11px;
    border-radius: 14px;
    background: var(--staff-surface-2);
    color: var(--staff-muted);
    font-size: .72rem
}
.profile-form {
    padding: 22px
}
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px
}
.span-2 {
    grid-column: 1/-1
}
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 20px;
    padding-top: 18px;
    border-top: 1px solid var(--staff-border)
}
.crop-modal {
    position: fixed;
    inset: 0;
    z-index: 100010;
    display: none;
    place-items: center;
    padding: 22px;
    background: rgba(4,8,14,.42);
    backdrop-filter: blur(13px)
}
.crop-modal.open {
    display: grid
}
.crop-dialog {
    width: min(760px, 100%);
    overflow: hidden;
    border: 1px solid var(--staff-border);
    border-radius: 28px;
    background: color-mix(in srgb,var(--staff-surface-solid) 86%,transparent);
    box-shadow: var(--staff-shadow);
    backdrop-filter: blur(30px)
}
.crop-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 20px 22px;
    border-bottom: 1px solid var(--staff-border)
}
.crop-head h3 {
    margin: 0;
    font-size: 1.05rem
}
.crop-head p {
    margin: 3px 0 0;
    color: var(--staff-muted);
    font-size: .74rem
}
.crop-close {
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    border: 1px solid var(--staff-border);
    border-radius: 12px;
    background: var(--staff-surface-2);
    color: var(--staff-text);
    cursor: pointer
}
.crop-stage {
    height: min(56vh, 470px);
    min-height: 320px;
    padding: 18px;
    background: #090e15
}
.crop-stage img {
    display: block;
    max-width: 100%
}
.cropper-view-box, .cropper-face {
    border-radius: 50%
}
.crop-tools {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 15px 20px;
    border-bottom: 1px solid var(--staff-border)
}
.crop-actions {
    display: flex;
    justify-content: flex-end;
    gap: 9px;
    padding: 17px 20px
}
@media (max-width: 850px) {
    .profile-layout {
        grid-template-columns: 1fr
    }
    .identity-card {
        position: static
    }
    .form-grid {
        grid-template-columns: 1fr
    }
    .span-2 {
        grid-column: auto
    }
}
@media (max-width: 560px) {
    .crop-modal {
        padding: 10px
    }
    .crop-stage {
        height: 48vh;
        min-height: 280px
    }
    .crop-actions {
        flex-direction: column
    }
    .crop-actions .ios-btn {
        width: 100%
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
                <span class="staff-eyebrow"><i class="fa-solid fa-user"></i> Account</span>
                <h1>Your profile</h1>
                <p>Keep your contact details and staff photo accurate.</p>
            </div>
            <a class="ios-btn ios-btn-secondary" href="change_password.php">
                <i class="fa-solid fa-lock"></i> Change password</a>
        </div>
        <?php if ($error): ?>
            <div class="ios-toast error"><strong>Could not save changes</strong><span><?= kcw_h($error) ?></span></div>
        <?php endif; ?>
        <section class="profile-layout">
            <aside class="ios-card identity-card">
                <div class="avatar-wrap">
                    <img
                    class="profile-photo-preview"
                    src="<?= kcw_h(kcw_staff_image_url($staff, $root_prefix)) ?>"
                    alt="Profile photo"
                    onerror="this.src='../../images/uploads/default.png'"
                    >
                    <button
                    type="button"
                    class="avatar-badge"
                    id="staffPhotoButton"
                    aria-label="Change profile photo"
                    title="Change profile photo"
                    >
                    <i class="fa-solid fa-camera"></i>
                </button>
            </div>
            <p class="photo-helper">Use the camera button to choose, crop and reposition your profile photo.</p>
            <?php if (isset($fieldErrors['staff_image'])): ?>
                <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_image']) ?></span>
            <?php endif; ?>
            <h2><?= kcw_h($staff['staff_name']) ?></h2>
            <span class="role"><?= kcw_h($staff['staff_job']) ?></span>
            <div class="identity-meta">
                <div>
                    <i class="fa-solid fa-envelope"></i>
                    <?= kcw_h($staff['staff_email']?:'No email') ?>
                </div>
                <div>
                    <i class="fa-solid fa-calendar-check"></i>Joined <?= date('d M Y',strtotime($staff['staff_hiredate'])) ?>
                </div>
                <div>
                    <i class="fa-solid fa-location-dot"></i>
                    <?= kcw_h($staff['staff_state']) ?>
                </div>
            </div>
        </aside>
        <article class="ios-card profile-form">
            <form method="POST">
                <input type="file" id="staffPhotoInput" accept="image/jpeg,image/png,image/webp" hidden>
                <input type="hidden" name="cropped_image" id="croppedImage">
                <div class="form-grid">
                    <div class="ios-field<?= isset($fieldErrors['staff_name']) ? ' has-error' : '' ?>">
                        <label for="staff_name">Full name</label>
                        <input class="ios-input" id="staff_name" name="staff_name" value="<?= kcw_h($formStaff['staff_name']) ?>" required>
                        <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_name'] ?? '') ?></span>
                    </div>
                    <div class="ios-field<?= isset($fieldErrors['staff_email']) ? ' has-error' : '' ?>">
                        <label for="staff_email">Email</label>
                        <input class="ios-input" id="staff_email" type="email" name="staff_email" value="<?= kcw_h($formStaff['staff_email']) ?>" required>
                        <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_email'] ?? '') ?></span>
                    </div>
                    <div class="ios-field<?= isset($fieldErrors['staff_phonenum']) ? ' has-error' : '' ?>">
                        <label for="staff_phonenum">Phone number</label>
                        <input class="ios-input" id="staff_phonenum" name="staff_phonenum" inputmode="numeric" value="<?= kcw_h($formStaff['staff_phonenum']) ?>" required>
                        <span class="ios-helper">The current database stores phone numbers as an integer.</span>
                        <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_phonenum'] ?? '') ?></span>
                    </div>
                    <div class="ios-field<?= isset($fieldErrors['staff_dob']) ? ' has-error' : '' ?>">
                        <label for="staff_dob">Date of birth</label>
                        <input class="ios-input" id="staff_dob" type="date" name="staff_dob" value="<?= kcw_h($formStaff['staff_dob']) ?>" required>
                        <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_dob'] ?? '') ?></span>
                    </div>
                    <div class="ios-field<?= isset($fieldErrors['staff_state']) ? ' has-error' : '' ?>">
                        <label for="staff_state">State</label>
                        <input
                            class="ios-input"
                            id="staff_state"
                            name="staff_state"
                            list="profile-state-options"
                            autocomplete="address-level1"
                            value="<?= kcw_h($formStaff['staff_state']) ?>"
                            placeholder="Type or choose a state"
                            required
                        >
                        <datalist id="profile-state-options">
                            <?php foreach ($states as $s): ?><option value="<?= kcw_h($s) ?>"></option><?php endforeach; ?>
                        </datalist>
                        <span class="ios-helper">Type to search or open the list and scroll.</span>
                        <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_state'] ?? '') ?></span>
                    </div>
                    <div class="ios-field<?= isset($fieldErrors['staff_gender']) ? ' has-error' : '' ?>">
                        <label>Gender</label>
                        <div class="kcw-choice-grid">
                            <label class="kcw-choice">
                                <input type="radio" name="staff_gender" value="0" <?= (string) $formStaff['staff_gender'] === '0' ? 'checked' : '' ?> required>
                                <span><i class="fa-solid fa-person"></i> Male</span>
                            </label>
                            <label class="kcw-choice">
                                <input type="radio" name="staff_gender" value="1" <?= (string) $formStaff['staff_gender'] === '1' ? 'checked' : '' ?>>
                                <span><i class="fa-solid fa-person-dress"></i> Female</span>
                            </label>
                        </div>
                        <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_gender'] ?? '') ?></span>
                    </div>
                </div>
                <div class="form-actions">
                    <a class="ios-btn ios-btn-secondary" href="../staff_home.php">Cancel</a>
                    <button class="ios-btn ios-btn-primary" name="update_profile">
                        <i class="fa-solid fa-check"></i> Save changes</button>
                </div>
            </form>
        </article>
    </section>
</main>
<div class="crop-modal" id="cropModal" aria-hidden="true">
    <div class="crop-dialog">
        <div class="crop-head">
            <div>
                <h3>Adjust profile photo</h3>
                <p>Drag to reposition and zoom until it fits the circle.</p>
            </div>
            <button type="button" class="crop-close" id="cropClose">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="crop-stage">
            <img id="cropImage" alt="Image to crop">
        </div>
        <div class="crop-tools">
            <button type="button" class="ios-btn ios-btn-secondary ios-btn-sm" data-crop-action="zoom-in">
                <i class="fa-solid fa-magnifying-glass-plus"></i> Zoom in</button>
            <button type="button" class="ios-btn ios-btn-secondary ios-btn-sm" data-crop-action="zoom-out">
                <i class="fa-solid fa-magnifying-glass-minus"></i> Zoom out</button>
            <button type="button" class="ios-btn ios-btn-secondary ios-btn-sm" data-crop-action="left">
                <i class="fa-solid fa-rotate-left"></i> Rotate</button>
            <button type="button" class="ios-btn ios-btn-secondary ios-btn-sm" data-crop-action="reset">
                <i class="fa-solid fa-arrow-rotate-left"></i> Reset</button>
        </div>
        <div class="crop-actions">
            <button type="button" class="ios-btn ios-btn-secondary" id="cropCancel">Cancel</button>
            <button type="button" class="ios-btn ios-btn-primary" id="cropApply">
                <i class="fa-solid fa-check"></i> Use photo</button>
        </div>
    </div>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js">
    </script>
    <script>
                                    (() => {
                                        const button = document.getElementById('staffPhotoButton');
                                        const input = document.getElementById('staffPhotoInput');
                                        const modal = document.getElementById('cropModal');
                                        const image = document.getElementById('cropImage');
                                        const hidden = document.getElementById('croppedImage');
                                        let cropper = null;

                                        const close = () => {
                                            modal.classList.remove('open');
                                            modal.setAttribute('aria-hidden', 'true');
                                            cropper?.destroy();
                                            cropper = null;
                                            input.value = '';
                                        };

                                        button.addEventListener('click', () => input.click());

                                        input.addEventListener('change', () => {
                                            const file = input.files?.[0];
                                            if (!file) return;

                                            if (file.size > 2e6) {
                                                window.KamalUI?.toast(
                                                    'Please choose an image smaller than 2 MB.',
                                                    {
                                                        title: 'Photo is too large',
                                                        tone: 'warning'
                                                    }
                                                );
                                                input.value = '';
                                                return;
                                            }

                                            const reader = new FileReader();
                                            reader.onload = (event) => {
                                                image.src = event.target.result;
                                                modal.classList.add('open');
                                                modal.setAttribute('aria-hidden', 'false');

                                                setTimeout(() => {
                                                    cropper = new Cropper(image, {
                                                        aspectRatio: 1,
                                                        viewMode: 1,
                                                        dragMode: 'move',
                                                        autoCropArea: .88,
                                                        responsive: true,
                                                        background: false,
                                                        guides: false,
                                                        center: false
                                                    });
                                                }, 50);
                                            };
                                            reader.readAsDataURL(file);
                                        });

                                        document.querySelectorAll('[data-crop-action]').forEach((control) => {
                                            control.addEventListener('click', () => {
                                                if (!cropper) return;

                                                const action = control.dataset.cropAction;
                                                if (action === 'zoom-in') cropper.zoom(.1);
                                                if (action === 'zoom-out') cropper.zoom(-.1);
                                                if (action === 'left') cropper.rotate(-90);
                                                if (action === 'reset') cropper.reset();
                                            });
                                        });

                                        document.getElementById('cropApply').addEventListener('click', () => {
                                            if (!cropper) return;

                                            const canvas = cropper.getCroppedCanvas({
                                                width: 512,
                                                height: 512,
                                                imageSmoothingEnabled: true,
                                                imageSmoothingQuality: 'high'
                                            });
                                            const data = canvas.toDataURL('image/jpeg', .9);

                                            hidden.value = data;
                                            document.querySelectorAll('.profile-photo-preview').forEach((element) => {
                                                element.src = data;
                                            });

                                            modal.classList.remove('open');
                                            cropper.destroy();
                                            cropper = null;
                                            input.value = '';

                                            window.KamalUI?.toast(
                                                'Your cropped photo is ready. Save changes to apply it.',
                                                {
                                                    title: 'Photo ready',
                                                    tone: 'success'
                                                }
                                            );
                                        });

                                        document.getElementById('cropClose').addEventListener('click', close);
                                        document.getElementById('cropCancel').addEventListener('click', close);
                                        modal.addEventListener('click', (event) => {
                                            if (event.target === modal) close();
                                        });
                                    })();
    </script>
</body>
</html>
