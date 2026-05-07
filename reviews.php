<?php
/**
 * reviews.php - List reviews (optionally filtered by movie) and add a new review.
 * GET ?movie_id=X – show only reviews for that movie and pre-select that movie in the form
 * POST with add_review – insert a new row into the reviews table
 */
require_once __DIR__ . '/config.php';
$conn = getDbConnection();

// ----- Handle form submission: add new review -----
$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review'])) {
    // Read form fields. (int) and trim() help prevent bad data
    $movieId = isset($_POST['movie_id']) ? (int) $_POST['movie_id'] : 0;
    $authorName = isset($_POST['author_name']) ? trim($_POST['author_name']) : '';
    $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    $valid = true;
    if ($movieId < 1) {
        $message = 'Please select a movie.';
        $messageType = 'danger';
        $valid = false;
    }
    if ($valid && $authorName === '') {
        $message = 'Please enter your name.';
        $messageType = 'danger';
        $valid = false;
    }
    if ($valid && ($rating < 1 || $rating > 10)) {
        $message = 'Rating must be between 1 and 10.';
        $messageType = 'danger';
        $valid = false;
    }
    if ($valid && $comment === '') {
        $message = 'Please enter a comment.';
        $messageType = 'danger';
        $valid = false;
    }

    if ($valid) {
        $stmt = $conn->prepare("INSERT INTO reviews (movie_id, author_name, rating, comment) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$movieId, $authorName, $rating, $comment])) {
            $message = 'Review added successfully.';
            $messageType = 'success';
        } else {
            $message = 'Could not save review. Please try again.';
            $messageType = 'danger';
        }
    }
}

// Optional filter from URL: reviews.php?movie_id=3 shows only reviews for movie 3
$filterMovieId = isset($_GET['movie_id']) ? (int) $_GET['movie_id'] : 0;

// Build SQL: list all reviews, or only for one movie. JOIN movies so we can show movie title
if ($filterMovieId > 0) {
    $sql = "SELECT r.id, r.movie_id, r.author_name, r.rating, r.comment, r.created_at, m.title AS movie_title
            FROM reviews r
            INNER JOIN movies m ON m.id = r.movie_id
            WHERE r.movie_id = {$filterMovieId}
            ORDER BY r.created_at DESC";
} else {
    $sql = "SELECT r.id, r.movie_id, r.author_name, r.rating, r.comment, r.created_at, m.title AS movie_title
            FROM reviews r
            INNER JOIN movies m ON m.id = r.movie_id
            ORDER BY r.created_at DESC";
}
$reviews = [];
try {
    $stmt = $conn->query($sql);
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $reviews = [];
}

// Load all movies for the "Add review" form dropdown
$movies = [];
try {
    $stmt = $conn->query("SELECT id, title FROM movies ORDER BY title");
    $movies = $stmt->fetchAll();
} catch (PDOException $e) {
    $movies = [];
}

$pageTitle = 'Reviews';
$breadcrumb = ['Home' => 'index.php', 'Reviews' => null];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <h1 class="h2 mb-4">Reviews</h1>

    <?php if ($message !== ''): ?>
    <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Form: Add new review -->
    <section class="mb-5">
        <h2 class="h5 mb-3">Add a review</h2>
        <form method="post" action="reviews.php<?php echo $filterMovieId > 0 ? '?movie_id=' . $filterMovieId : ''; ?>">
            <input type="hidden" name="add_review" value="1" />
            <div class="mb-3">
                <label for="movie_id" class="form-label">Movie</label>
                <select name="movie_id" id="movie_id" class="form-select bg-dark text-white">
                    <option value="">-- Select a movie --</option>
                    <?php foreach ($movies as $m): ?>
                    <option value="<?php echo (int)$m['id']; ?>"<?php echo ($filterMovieId === (int)$m['id']) ? ' selected' : ''; ?>><?php echo htmlspecialchars($m['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="author_name" class="form-label">Your name</label>
                <input type="text" name="author_name" id="author_name" class="form-control bg-dark text-white" required maxlength="100" />
            </div>
            <div class="mb-3">
                <label for="rating" class="form-label">Rating (1-10)</label>
                <select name="rating" id="rating" class="form-select bg-dark text-white" required>
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="comment" class="form-label">Your review</label>
                <textarea name="comment" id="comment" class="form-control bg-dark text-white" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-danger">Submit review</button>
        </form>
    </section>

    <!-- List of reviews -->
    <section>
        <h2 class="h5 mb-3">All reviews<?php echo $filterMovieId > 0 ? ' for this movie' : ''; ?></h2>
        <?php if (empty($reviews)): ?>
            <p class="text-muted">No reviews yet. Be the first to add one!</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($reviews as $r): ?>
                <div class="list-group-item bg-dark text-white border-secondary">
                    <div class="d-flex justify-content-between align-items-start">
                        <strong><?php echo htmlspecialchars($r['author_name']); ?></strong>
                        <span class="badge bg-primary"><?php echo (int)$r['rating']; ?>/10</span>
                    </div>
                    <small class="text-muted"><?php echo htmlspecialchars($r['movie_title']); ?> &middot; <?php echo date('M j, Y', strtotime($r['created_at'])); ?></small>
                    <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($r['comment'])); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
