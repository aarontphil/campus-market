<?php
/**
 * Login — Campus Marketplace
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: /campus-marketplace/dashboard.php');
    exit;
}

$errors = [];
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $email    = trim($_POST['college_email'] ?? '');
    $password = $_POST['password'] ?? '';
    $old_email = $email;

    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    }

    if (empty($errors)) {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE college_email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            loginUser($user);
            setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
            header('Location: /campus-marketplace/dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-3">
            <i class="fas fa-sign-in-alt fa-2x text-accent"></i>
        </div>
        <h2 class="text-center">Welcome Back</h2>
        <p class="auth-subtitle text-center">Sign in to your campus marketplace account.</p>

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
                <label class="form-label" for="college_email">College Email</label>
                <input type="email" id="college_email" name="college_email" class="form-control" placeholder="you@campus.edu" value="<?= e($old_email) ?>" required autofocus>
            </div>

            <div class="mb-4">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary-custom">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
        </form>

        <p class="text-center text-muted mt-3 small">
            Don't have an account? <a href="/campus-marketplace/register.php">Register here</a>.
        </p>

        <div class="mt-4 p-3 rounded" style="background:var(--navy-mid);border:1px solid var(--card-border);">
            <p class="small text-muted mb-2"><i class="fas fa-magic me-1 text-accent"></i> <strong>Quick Demo Login</strong> (1-Click Fill)</p>
            <div class="d-flex flex-column gap-2">
                <button type="button" class="btn btn-sm btn-outline-light text-start d-flex justify-content-between align-items-center" onclick="quickFill('arjun.nair@campus.edu', 'Password123')">
                    <span><strong>Arjun Nair</strong> <small class="text-muted">(Seller: Books/Tech)</small></span>
                    <i class="fas fa-chevron-right text-accent small"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-light text-start d-flex justify-content-between align-items-center" onclick="quickFill('joel.mathew@campus.edu', 'Password123')">
                    <span><strong>Joel Mathew</strong> <small class="text-muted">(Seller: Audio/Table)</small></span>
                    <i class="fas fa-chevron-right text-accent small"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-light text-start d-flex justify-content-between align-items-center" onclick="quickFill('meera.krishnan@campus.edu', 'Password123')">
                    <span><strong>Meera Krishnan</strong> <small class="text-muted">(Active Buyer/Seller)</small></span>
                    <i class="fas fa-chevron-right text-accent small"></i>
                </button>
            </div>
            <script>
            function quickFill(email, pass) {
                document.getElementById('college_email').value = email;
                document.getElementById('password').value = pass;
            }
            </script>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
