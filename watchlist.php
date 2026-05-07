<?php
/**
 * watchlist.php - Shows movies saved to the session watchlist.
 * No database table needed — uses $_SESSION['watchlist'] array of movie IDs.
 */
session_start();
require_once __DIR__ . '/config.php';
$conn = getDbConnection();

$watchlist = isset($_SESSION['watchlist']) ? $_SESSION['watchlist'] : [];
$movies = [];

if (!empty($watchlist)) {
    // Build a comma-separated list of IDs for the IN() clause
    $ids = implode(',', array_map('intval', $watchlist));
    $result = mysqli_query($conn, "SELECT id, title, year, rated, runtime, plot, poster FROM movies WHERE id IN ($ids) ORDER BY title");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $movies[] = $row;
        }
    }
}

$pageTitle = 'My Watchlist';
$breadcrumb = ['Watchlist' => null];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <h1 class="h2 mb-4">My Watchlist</h1>

    <?php if (empty($movies)): ?>
    <div class="text-center py-5">
        <p class="text-muted">Your watchlist is empty.</p>
        <p class="text-muted">Browse movies and click "Add to Watchlist" on any movie page.</p>
        <a href="index.php" class="btn btn-danger">Browse Movies</a>
    </div>
    <?php else: ?>
    <p class="text-muted"><?php echo count($movies); ?> movie(s) in your watchlist.</p>
    <div class="row g-4">
        <?php foreach ($movies as $movie): ?>
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card bg-dark text-white h-100">
                <div class="card-header text-truncate" title="<?php echo htmlspecialchars($movie['title']); ?>">
                    <span class="badge bg-primary"><?php echo htmlspecialchars($movie['title']); ?></span>
                </div>
                <a href="movie.php?id=<?php echo (int)$movie['id']; ?>">
                    <img src="<?php echo htmlspecialchars(getPosterSrc($movie['poster'] ?? '')); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($movie['title']); ?>" loading="lazy" />
                </a>
                <div class="card-body">
                    <small class="text-muted"><?php echo htmlspecialchars(($movie['year'] ?? '') . ' | ' . ($movie['runtime'] ?? '') . ' | ' . ($movie['rated'] ?? '')); ?></small>
                    <p class="mt-2 small"><?php echo htmlspecialchars(strlen($movie['plot'] ?? '') > 100 ? substr($movie['plot'], 0, 100) . '...' : ($movie['plot'] ?? '')); ?></p>
                    <a href="movie.php?id=<?php echo (int)$movie['id']; ?>" class="btn btn-sm btn-primary me-1">Details</a>
                    <a href="movie.php?id=<?php echo (int)$movie['id']; ?>&watchlist=remove" class="btn btn-sm btn-outline-danger">Remove</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
