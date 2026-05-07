<?php
/**
 * movie.php - Movie detail page.
 * URL: movie.php?id=X
 * Shows full movie info, genres, average user rating, and watchlist button.
 */
session_start();
require_once __DIR__ . '/config.php';
$conn = getDbConnection();

$movieId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($movieId < 1) {
    header('Location: index.php');
    exit;
}

// Handle watchlist toggle
if (isset($_GET['watchlist'])) {
    if (!isset($_SESSION['watchlist'])) {
        $_SESSION['watchlist'] = [];
    }
    if ($_GET['watchlist'] === 'add') {
        if (!in_array($movieId, $_SESSION['watchlist'])) {
            $_SESSION['watchlist'][] = $movieId;
        }
    } elseif ($_GET['watchlist'] === 'remove') {
        $_SESSION['watchlist'] = array_values(array_diff($_SESSION['watchlist'], [$movieId]));
    }
    header('Location: movie.php?id=' . $movieId);
    exit;
}

// Fetch movie
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ? LIMIT 1");
$stmt->execute([$movieId]);
$movie = $stmt->fetch();
if (!$movie) {
    $pageTitle = 'Movie Not Found';
    $breadcrumb = ['Movies' => 'index.php', 'Not Found' => null];
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container py-5 text-center"><h2>Movie not found</h2><p class="text-muted">The movie you are looking for does not exist.</p><a href="index.php" class="btn btn-danger">Back to Home</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch genres for this movie
$movieGenres = [];
try {
    $stmt = $conn->query("SELECT g.name, g.slug FROM genres g INNER JOIN movie_genres mg ON mg.genre_id = g.id WHERE mg.movie_id = $movieId ORDER BY g.name");
    $movieGenres = $stmt->fetchAll();
} catch (PDOException $e) {
    $movieGenres = [];
}

// Average user rating from reviews
$avgRating = 0;
$reviewCount = 0;
try {
    $stmt = $conn->query("SELECT AVG(rating) AS avg_rating, COUNT(*) AS review_count FROM reviews WHERE movie_id = $movieId");
    $row = $stmt->fetch();
    if ($row) {
        $avgRating = $row['avg_rating'] ? round((float)$row['avg_rating'], 1) : 0;
        $reviewCount = (int)$row['review_count'];
    }
} catch (PDOException $e) {
    $avgRating = 0;
    $reviewCount = 0;
}

// Check if movie is in watchlist
$inWatchlist = isset($_SESSION['watchlist']) && in_array($movieId, $_SESSION['watchlist']);

$pageTitle = $movie['title'];
$breadcrumb = ['Movies' => 'index.php', $movie['title'] => null];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <!-- Poster -->
        <div class="col-md-4 mb-4">
            <img src="<?php echo htmlspecialchars(getPosterSrc($movie['poster'] ?? '')); ?>" class="img-fluid rounded shadow" alt="<?php echo htmlspecialchars($movie['title']); ?>" />
        </div>

        <!-- Movie Info -->
        <div class="col-md-8">
            <h1 class="mb-1"><?php echo htmlspecialchars($movie['title']); ?></h1>
            <p class="text-muted mb-3">
                <?php echo htmlspecialchars(($movie['year'] ?? '') . ' | ' . ($movie['runtime'] ?? '') . ' | ' . ($movie['rated'] ?? '')); ?>
            </p>

            <!-- Genres -->
            <?php if (!empty($movieGenres)): ?>
            <div class="mb-3">
                <?php foreach ($movieGenres as $g): ?>
                <a href="genre.php?genre=<?php echo urlencode($g['slug']); ?>" class="badge bg-danger text-decoration-none me-1"><?php echo htmlspecialchars($g['name']); ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Plot -->
            <?php if (!empty($movie['plot'])): ?>
            <h5>Plot</h5>
            <p><?php echo htmlspecialchars($movie['plot']); ?></p>
            <?php endif; ?>

            <!-- Details table -->
            <table class="table table-dark table-sm mt-3">
                <?php if (!empty($movie['director'])): ?>
                <tr><th style="width:120px">Director</th><td><?php echo htmlspecialchars($movie['director']); ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($movie['actors'])): ?>
                <tr><th>Actors</th><td><?php echo htmlspecialchars($movie['actors']); ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($movie['language'])): ?>
                <tr><th>Language</th><td><?php echo htmlspecialchars($movie['language']); ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($movie['country'])): ?>
                <tr><th>Country</th><td><?php echo htmlspecialchars($movie['country']); ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($movie['awards'])): ?>
                <tr><th>Awards</th><td><?php echo htmlspecialchars($movie['awards']); ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($movie['imdb_rating'])): ?>
                <tr><th>IMDb Rating</th><td><?php echo htmlspecialchars($movie['imdb_rating']); ?>/10</td></tr>
                <?php endif; ?>
            </table>

            <!-- User rating -->
            <div class="mb-3">
                <strong>User Rating:</strong>
                <?php if ($reviewCount > 0): ?>
                <span class="badge bg-primary fs-6"><?php echo $avgRating; ?>/10</span>
                <small class="text-muted">(<?php echo $reviewCount; ?> review<?php echo $reviewCount !== 1 ? 's' : ''; ?>)</small>
                <?php else: ?>
                <span class="text-muted">No reviews yet</span>
                <?php endif; ?>
            </div>

            <!-- Action buttons -->
            <div class="d-flex gap-2 flex-wrap">
                <a href="reviews.php?movie_id=<?php echo $movieId; ?>" class="btn btn-danger">Reviews</a>
                <?php if ($inWatchlist): ?>
                <a href="movie.php?id=<?php echo $movieId; ?>&watchlist=remove" class="btn btn-outline-light">Remove from Watchlist</a>
                <?php else: ?>
                <a href="movie.php?id=<?php echo $movieId; ?>&watchlist=add" class="btn btn-outline-light">Add to Watchlist</a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
            </div>
        </div>
    </div>

    <!-- Trailer Section -->
    <?php if (!empty($movie['trailer_url'])): ?>
    <div class="row mt-4" id="trailer">
        <div class="col-12">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h4 class="card-title text-danger mb-3">Trailer</h4>
                    <div class="ratio ratio-16x9">
                        <?php 
                        // Convert YouTube watch URL to embed URL
                        $embedUrl = str_replace('watch?v=', 'embed/', $movie['trailer_url']);
                        $embedUrl .= '?rel=0&modestbranding=1';
                        ?>
                        <iframe src="<?php echo htmlspecialchars($embedUrl); ?>" 
                                title="Movie Trailer" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
