<?php
/**
 * login.php - User login form.
 * POST: validates username + password against the users table.
 * Uses password_verify() to check the hashed password.
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '', 'string');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Please fill in all fields.';
        } elseif (!validateUsername($username)) {
            $error = 'Invalid username format.';
        } elseif (!checkLoginAttempts($username)) {
            $error = 'Too many login attempts. Please try again later.';
        } else {
            $usernameEsc = mysqli_real_escape_string($conn, $username);
            $result = mysqli_query($conn, "SELECT id, username, password, role FROM users WHERE username = '$usernameEsc' LIMIT 1");
            if ($result && $row = mysqli_fetch_assoc($result)) {
                if (password_verify($password, $row['password'])) {
                    // Login successful — reset attempts and store user info
                    resetLoginAttempts($username);
                    $_SESSION['user_id'] = (int)$row['id'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['role'] = $row['role'];
                    $_SESSION['login_time'] = time();
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Wrong password.';
                }
            } else {
                $error = 'User not found.';
            }
        }
    }
}

$pageTitle = 'Login';
$breadcrumb = ['Login' => null];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h2 class="card-title mb-4 text-center">Login</h2>

                    <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="post" action="login.php">
                        <?php echo getCSRFInput(); ?>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control bg-dark text-white border-secondary" required maxlength="50" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control bg-dark text-white border-secondary" required />
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Login</button>
                    </form>
                    <p class="text-center mt-3 mb-0">
                        Don't have an account? <a href="register.php" class="text-danger">Register</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
