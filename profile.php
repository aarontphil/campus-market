<?php
/**
 * User Profile — Campus Marketplace
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$db  = getDB();
$uid = currentUserId();

$user = currentUser();

$errors = [];
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $action = $_POST['action'] ?? 'update_profile';

    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');

        if ($fullName === '' || strlen($fullName) < 3) {
            $errors[] = 'Full name must be at least 3 characters.';
        }

        if (empty($errors)) {
            $stmt = $db->prepare('UPDATE users SET full_name = ? WHERE id = ?');
            $stmt->execute([$fullName, $uid]);
            $_SESSION['user_name'] = $fullName;
            setFlash('success', 'Profile updated successfully.');
            header('Location: /campus-marketplace/profile.php');
            exit;
        }
    } elseif ($action === 'change_password') {
        $currPassword = $_POST['current_password'] ?? '';
        $newPassword  = $_POST['new_password'] ?? '';
        $confPassword = $_POST['confirm_password'] ?? '';

        if (!password_verify($currPassword, $user['password_hash'])) {
            $errors[] = 'Current password is incorrect.';
        }

        if (strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        }

        if ($newPassword !== $confPassword) {
            $errors[] = 'New passwords do not match.';
        }

        if (empty($errors)) {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([$hash, $uid]);
            setFlash('success', 'Password changed successfully.');
            header('Location: /campus-marketplace/profile.php');
            exit;
        }
    }
}

// Stats
$stmt = $db->prepare('SELECT COUNT(*) FROM products WHERE seller_id = ?');
$stmt->execute([$uid]);
$totalListings = (int) $stmt->fetchColumn();

$stmt = $db->prepare('SELECT COUNT(*) FROM products WHERE seller_id = ? AND status = "Sold"');
$stmt->execute([$uid]);
$totalSold = (int) $stmt->fetchColumn();

$stmt = $db->prepare('SELECT COUNT(*) FROM offers WHERE buyer_id = ? AND status = "Accepted"');
$stmt->execute([$uid]);
$totalBought = (int) $stmt->fetchColumn();

$pageTitle = 'My Profile';
require_once __DIR__ . '/includes/header.php';
?>

<section class="container py-5">
    <div class="row g-4">
        <!-- Sidebar / User Card -->
        <div class="col-lg-4">
            <div class="card bg-navy-mid border-card text-center p-4">
                <div class="avatar-circle mx-auto mb-3" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent-light)); display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--navy); font-weight: 800;">
                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                </div>
                <h4 class="text-light mb-1"><?= e($user['full_name']) ?></h4>
                <p class="text-muted small mb-2"><i class="fas fa-id-badge text-accent me-1"></i><?= e($user['student_id'] ?? 'Student') ?></p>
                <div class="badge bg-navy border-card text-accent mb-3 px-3 py-2">
                    <i class="fas fa-university me-1"></i>Verified Campus Member
                </div>

                <hr class="border-card my-3">

                <div class="row text-center g-2">
                    <div class="col-4">
                        <div class="p-2 rounded bg-navy">
                            <div class="fw-bold text-light"><?= $totalListings ?></div>
                            <div class="text-muted" style="font-size: 0.7rem;">Listings</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-navy">
                            <div class="fw-bold text-accent"><?= $totalSold ?></div>
                            <div class="text-muted" style="font-size: 0.7rem;">Sold</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-navy">
                            <div class="fw-bold text-info"><?= $totalBought ?></div>
                            <div class="text-muted" style="font-size: 0.7rem;">Bought</div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-start small text-muted">
                    <div class="mb-2"><i class="fas fa-envelope text-accent me-2"></i><?= e($user['college_email']) ?></div>
                    <div><i class="fas fa-calendar-alt text-accent me-2"></i>Joined <?= date('M Y', strtotime($user['created_at'])) ?></div>
                </div>
            </div>
        </div>

        <!-- Settings Forms -->
        <div class="col-lg-8">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Edit Details -->
            <div class="card bg-navy-mid border-card p-4 mb-4">
                <h4 class="text-light mb-3"><i class="fas fa-user-edit text-accent me-2"></i>Account Details</h4>
                <form action="/campus-marketplace/profile.php" method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_profile">

                    <div class="mb-3">
                        <label class="form-label text-light small">Full Name</label>
                        <input type="text" name="full_name" class="form-control bg-navy border-card text-light" value="<?= e($user['full_name']) ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-light small">College Email (Institutional)</label>
                            <input type="email" class="form-control bg-navy border-card text-muted" value="<?= e($user['college_email']) ?>" disabled>
                            <small class="text-muted">Email cannot be changed after registration.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light small">Student ID / Roll Number</label>
                            <input type="text" class="form-control bg-navy border-card text-muted" value="<?= e($user['student_id'] ?? 'N/A') ?>" disabled>
                            <small class="text-muted">Tied to college verification.</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-accent px-4">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card bg-navy-mid border-card p-4">
                <h4 class="text-light mb-3"><i class="fas fa-lock text-accent me-2"></i>Security & Password</h4>
                <form action="/campus-marketplace/profile.php" method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label class="form-label text-light small">Current Password</label>
                        <input type="password" name="current_password" class="form-control bg-navy border-card text-light" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-light small">New Password</label>
                            <input type="password" name="new_password" class="form-control bg-navy border-card text-light" minlength="8" required>
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light small">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control bg-navy border-card text-light" minlength="8" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-outline-accent px-4">
                        <i class="fas fa-key me-1"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
