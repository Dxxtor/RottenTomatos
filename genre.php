<?php
/**
 * genre.php - Lists movies for one genre with pagination.
 * URL: genre.php?genre=action&page=2
 */
$genreSlug = isset($_GET['genre']) ? trim($_GET['genre']) : '';
if ($genreSlug === '') {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config.php';
$conn = getDbConnection();

$slugEsc = mysqli_real_escape_string($conn, $genreSlug);
$genreResult = mysqli_query($conn, "SELECT id, name, slug FROM genres WHERE slug = '{$slugEsc}' LIMIT 1");
if (!$genreResult || mysqli_num_rows($genreResult) === 0) {
    $pageTitle = 'Genre Not Found';
    $breadcrumb = ['Home' => 'index.php', 'Genre' => null];
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container py-5 text-center"><p class="text-muted">Genre not found.</p><a href="index.php" class="btn btn-danger">Back to Home</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
$genre = mysqli_fetch_assoc($genreResult);
$genreId = (int) $genre['id'];

$page = max(1, (int) (isset($_GET['page']) ? $_GET['page'] : 1));
$perPage = defined('MOVIES_PER_PAGE') ? MOVIES_PER_PAGE : 24;
$offset = ($page - 1) * $perPage;

$countSql = "SELECT COUNT(*) AS total FROM movies m INNER JOIN movie_genres mg ON mg.movie_id = m.id AND mg.genre_id = {$genreId}";
$totalResult = mysqli_query($conn, $countSql);
$total = 0;
if ($totalResult && $row = mysqli_fetch_assoc($totalResult)) {
    $total = (int) $row['total'];
}
$totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;

$sql = "SELECT m.id, m.title, m.year, m.rated, m.runtime, m.plot, m.poster
        FROM movies m
        INNER JOIN movie_genres mg ON mg.movie_id = m.id AND mg.genre_id = {$genreId}
        ORDER BY m.year DESC, m.title
        LIMIT {$perPage} OFFSET {$offset}";
$result = mysqli_query($conn, $sql);
$movies = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $movies[] = $row;
    }
}

$pageTitle = $genre['name'];
$breadcrumb = ['Home' => 'index.php', $genre['name'] => null];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <h1 class="h2 mb-4"><?php echo htmlspecialchars($genre['name']); ?> Movies</h1>
    <?php if (empty($movies)): ?>
        <p class="text-muted">No movies in this genre yet.</p>
        <a href="index.php" class="btn btn-danger">Back to Home</a>
    <?php else: ?>
        <p class="text-muted"><?php echo $total; ?> movie(s) in this genre.</p>
        <div class="row g-4">
            <?php foreach ($movies as $movie): ?>
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card bg-dark text-white h-100">
                    <div class="card-header text-truncate" title="<?php echo htmlspecialchars($movie['title']); ?>">
                        <span class="badge bg-primary"><?php echo htmlspecialchars($movie['title']); ?></span>
                    </div>
                    <img src="<?php echo htmlspecialchars(getPosterSrc($movie['poster'] ?? '')); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($movie['title']); ?>" loading="lazy" />
                    <div class="card-body">
                        <small class="text-muted"><?php echo htmlspecialchars(($movie['year'] ?? '') . ' | ' . ($movie['runtime'] ?? '') . ' | ' . ($movie['rated'] ?? '')); ?></small>
                        <p class="mt-2 small"><?php echo htmlspecialchars(strlen($movie['plot'] ?? '') > 120 ? substr($movie['plot'], 0, 120) . '...' : ($movie['plot'] ?? '')); ?></p>
                        <div class="d-flex gap-1">
                            <a href="movie.php?id=<?php echo (int)$movie['id']; ?>" class="btn btn-sm btn-primary">Details</a>
                            <?php if (!empty($movie['trailer_url'])): ?>
                            <a href="movie.php?id=<?php echo (int)$movie['id']; ?>#trailer" class="btn btn-sm btn-danger">Trailer</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-dark border-top-0 text-muted">
                        <small>ONLY ON <span class="text-danger">Fresh Potatos</span></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav aria-label="Genre pagination" class="mt-4 d-flex justify-content-center">
            <ul class="pagination pagination-lg">
                <?php $baseUrl = 'genre.php?genre=' . urlencode($genreSlug) . '&page='; ?>
                <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link bg-dark text-danger border-secondary" href="<?php echo $baseUrl . ($page - 1); ?>">Previous</a></li>
                <?php endif; ?>
                <?php $start = max(1, $page - 2); $end = min($totalPages, $page + 2); for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><a class="page-link bg-dark text-danger border-secondary" href="<?php echo $baseUrl . $i; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link bg-dark text-danger border-secondary" href="<?php echo $baseUrl . ($page + 1); ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <p class="text-center text-muted small">Page <?php echo $page; ?> of <?php echo $totalPages; ?></p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
