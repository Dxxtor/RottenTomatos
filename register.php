<?php
/**
 * register.php - User registration form.
 * POST: creates a new user with password_hash() and redirects to login.
 */
session_start();
require_once __DIR__ . '/config.php';

// If already logged in, redirect to home
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getDbConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '', 'string');
        $email    = sanitizeInput($_POST['email'] ?? '', 'email');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm'] ?? '';

        if ($username === '' || $email === '' || $password === '') {
            $error = 'Please fill in all fields.';
        } elseif (!validateUsername($username)) {
            $error = 'Invalid username format (3-50 chars, alphanumeric and underscore only).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } elseif (!validatePassword($password)) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
        // Check if username already exists
        $usernameEsc = mysqli_real_escape_string($conn, $username);
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$usernameEsc' LIMIT 1");
        if ($check && mysqli_num_rows($check) > 0) {
            $error = 'Username already taken.';
        } else {
            // Hash the password and insert
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $emailEsc = mysqli_real_escape_string($conn, $email);
            $hashEsc = mysqli_real_escape_string($conn, $hash);
            $sql = "INSERT INTO users (username, password, email, role) VALUES ('$usernameEsc', '$hashEsc', '$emailEsc', 'user')";
            if (mysqli_query($conn, $sql)) {
                $success = 'Account created! You can now login.';
            } else {
                $error = 'Could not create account. Please try again.';
            }
        }
    }
}

$pageTitle = 'Register';
$breadcrumb = ['Register' => null];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h2 class="card-title mb-4 text-center">Register</h2>

                    <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($success !== ''): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <p class="text-center"><a href="login.php" class="btn btn-danger">Go to Login</a></p>
                    <?php else: ?>

                    <form method="post" action="register.php">
                        <?php echo getCSRFInput(); ?>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control bg-dark text-white border-secondary" required minlength="3" maxlength="50" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control bg-dark text-white border-secondary" required maxlength="255" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control bg-dark text-white border-secondary" required minlength="6" />
                        </div>
                        <div class="mb-3">
                            <label for="confirm" class="form-label">Confirm Password</label>
                            <input type="password" name="confirm" id="confirm" class="form-control bg-dark text-white border-secondary" required minlength="6" />
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Register</button>
                    </form>
                    <p class="text-center mt-3 mb-0">
                        Already have an account? <a href="login.php" class="text-danger">Login</a>
                    </p>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
