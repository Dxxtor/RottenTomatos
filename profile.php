<?php
/**
 * profile.php - Shows the logged-in user's profile info.
 * If not logged in, redirects to login page.
 */
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$conn = getDbConnection();
$userId = (int)$_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT username, email, role, created_at FROM users WHERE id = $userId LIMIT 1");
$user = $result ? mysqli_fetch_assoc($result) : null;

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Count how many reviews this user has written (by matching author_name to username)
$usernameEsc = mysqli_real_escape_string($conn, $user['username']);
$reviewCount = 0;
$rcResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM reviews WHERE author_name = '$usernameEsc'");
if ($rcResult && $row = mysqli_fetch_assoc($rcResult)) {
    $reviewCount = (int)$row['cnt'];
}

$pageTitle = 'My Profile';
$breadcrumb = ['Profile' => null];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h2 class="card-title mb-4 text-center">My Profile</h2>
                    <table class="table table-dark table-borderless">
                        <tr>
                            <th>Username</th>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td><span class="badge <?php echo $user['role'] === 'admin' ? 'bg-danger' : 'bg-secondary'; ?>"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></span></td>
                        </tr>
                        <tr>
                            <th>Member since</th>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th>Reviews written</th>
                            <td><?php echo $reviewCount; ?></td>
                        </tr>
                    </table>
                    <div class="text-center mt-3">
                        <?php if ($user['role'] === 'admin'): ?>
                        <a href="admin/index.php" class="btn btn-outline-danger me-2">Admin Panel</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn btn-outline-light">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
