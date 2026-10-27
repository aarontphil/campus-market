<?php
/**
 * Register — Campus Marketplace
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /campus-marketplace/dashboard.php');
    exit;
}

$errors = [];
$old = ['full_name' => '', 'college_email' => '', 'student_id' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $full_name     = trim($_POST['full_name'] ?? '');
    $college_email = trim($_POST['college_email'] ?? '');
    $student_id    = trim($_POST['student_id'] ?? '');
    $password      = $_POST['password'] ?? '';
    $confirm       = $_POST['confirm_password'] ?? '';

    $old = compact('full_name', 'college_email', 'student_id');

    // Validate
    if ($full_name === '')     $errors[] = 'Full name is required.';
    if (!isValidEmail($college_email)) $errors[] = 'A valid college email is required.';
    if ($student_id === '')    $errors[] = 'Student ID is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $db = getDB();

        // Check uniqueness
        $stmt = $db->prepare('SELECT id FROM users WHERE college_email = ? OR student_id = ?');
        $stmt->execute([$college_email, $student_id]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email or student ID already exists.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare('INSERT INTO users (full_name, college_email, student_id, password_hash) VALUES (?, ?, ?, ?)');
        $stmt->execute([$full_name, $college_email, $student_id, $hash]);

        setFlash('success', 'Registration successful! Please log in.');
        header('Location: /campus-marketplace/login.php');
        exit;
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-3">
            <i class="fas fa-user-plus fa-2x text-accent"></i>
        </div>
        <h2 class="text-center">Create Account</h2>
        <p class="auth-subtitle text-center">Join the campus marketplace to buy &amp; sell.</p>

        <?php if ($errors): ?>
            <div class="alert alert-danger py-2">
                <ul class="mb-0 small">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <?= csrfField() ?>

            <div class="mb-3">
                <label class="form-label" for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Aarav Sharma" value="<?= e($old['full_name']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="college_email">College Email</label>
                <input type="email" id="college_email" name="college_email" class="form-control" placeholder="you@campus.edu" value="<?= e($old['college_email']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="student_id">Student ID</label>
                <input type="text" id="student_id" name="student_id" class="form-control" placeholder="STU2024001" value="<?= e($old['student_id']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 8 characters" required>
            </div>

            <div class="mb-4">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary-custom">
                <i class="fas fa-user-plus me-2"></i>Register
            </button>
        </form>

        <p class="text-center text-muted mt-3 small">
            Already have an account? <a href="/campus-marketplace/login.php">Log in here</a>.
        </p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
