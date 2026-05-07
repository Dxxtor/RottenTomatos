<?php
/**
 * index.php - Home page. Lists movies with pagination (or filtered by search).
 * URL: index.php, index.php?page=2, index.php?search=avatar
 */
$pageTitle = 'Home';
$breadcrumb = ['Home' => null];
require_once __DIR__ . '/includes/header.php';

$conn = getDbConnection();

// Get carousel movies (6 random movies with real posters for the carousel)
$carouselSql = "SELECT id, title, year, rated, runtime, plot, poster FROM movies WHERE poster IS NOT NULL AND poster != '' AND poster NOT LIKE '%placeholder%' ORDER BY RAND() LIMIT 6";
$carouselResult = mysqli_query($conn, $carouselSql);
$carouselMovies = [];
if ($carouselResult) {
    while ($row = mysqli_fetch_assoc($carouselResult)) {
        $carouselMovies[] = $row;
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = max(1, (int) (isset($_GET['page']) ? $_GET['page'] : 1));
$perPage = defined('MOVIES_PER_PAGE') ? MOVIES_PER_PAGE : 24;
$offset = ($page - 1) * $perPage;

if ($search !== '') {
    $searchEsc = mysqli_real_escape_string($conn, $search);
    $where = "WHERE m.title LIKE '%{$searchEsc}%' OR m.plot LIKE '%{$searchEsc}%'";
    $orderLimit = "ORDER BY m.year DESC, m.title LIMIT {$perPage} OFFSET {$offset}";
    $countSql = "SELECT COUNT(*) AS total FROM movies m {$where}";
    $sql = "SELECT m.id, m.title, m.year, m.rated, m.runtime, m.plot, m.poster FROM movies m {$where} {$orderLimit}";
} else {
    $countSql = "SELECT COUNT(*) AS total FROM movies";
    $sql = "SELECT id, title, year, rated, runtime, plot, poster FROM movies ORDER BY year DESC, title LIMIT {$perPage} OFFSET {$offset}";
}

$totalResult = mysqli_query($conn, $countSql);
$total = 0;
if ($totalResult && $row = mysqli_fetch_assoc($totalResult)) {
    $total = (int) $row['total'];
}
$totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;

$result = mysqli_query($conn, $sql);
$movies = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $movies[] = $row;
    }
}

// Fetch latest blog posts for sidebar
$blogPosts = [];
$blogResult = mysqli_query($conn, "SELECT bp.id, bp.title, bp.content, bp.created_at, u.username FROM blog_posts bp INNER JOIN users u ON u.id = bp.user_id ORDER BY bp.created_at DESC LIMIT 5");
if ($blogResult) {
    while ($row = mysqli_fetch_assoc($blogResult)) {
        $blogPosts[] = $row;
    }
}
?>
<div class="container py-4">
    <?php if (!empty($carouselMovies)): ?>
    <!-- Movie Carousel -->
    <div id="movieCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach ($carouselMovies as $index => $movie): ?>
            <button type="button" data-bs-target="#movieCarousel" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($carouselMovies as $index => $movie): ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <img src="<?php echo htmlspecialchars(getPosterSrc($movie['poster'] ?? '')); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($movie['title']); ?>" style="max-height: 500px; object-fit: cover;">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-75 p-3">
                    <h5><?php echo htmlspecialchars($movie['title']); ?></h5>
                    <p><?php echo htmlspecialchars($movie['year'] . ' | ' . $movie['runtime'] . ' | ' . $movie['rated']); ?></p>
                    <p><?php echo htmlspecialchars(strlen($movie['plot'] ?? '') > 150 ? substr($movie['plot'], 0, 150) . '...' : ($movie['plot'] ?? '')); ?></p>
                    <a href="movie.php?id=<?php echo (int)$movie['id']; ?>" class="btn btn-sm btn-danger">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#movieCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#movieCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <?php endif; ?>
    
    <?php if ($search !== ''): ?>
    <p class="text-muted">Found <?php echo $total; ?> movie(s) for "<?php echo htmlspecialchars($search); ?>".</p>
    <?php endif; ?>

    <div class="row">
    <!-- Main content: movies -->
    <div class="col-lg-9">

    <?php if (empty($movies)): ?>
    <div class="text-center py-5">
        <p class="text-muted">No movies found<?php echo $search !== '' ? '' : '.'; ?>.</p>
        <?php if ($search !== ''): ?><a href="index.php" class="btn btn-danger">Show all</a><?php endif; ?>
    </div>
    <?php else: ?>
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
                    <p class="mt-2 small"><?php echo htmlspecialchars(strlen($movie['plot'] ?? '') > 100 ? substr($movie['plot'], 0, 100) . '...' : ($movie['plot'] ?? '')); ?></p>
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
    <nav aria-label="Movie pagination" class="mt-4 d-flex justify-content-center">
        <ul class="pagination pagination-lg">
            <?php
            $baseUrl = 'index.php?' . ($search !== '' ? 'search=' . urlencode($search) . '&' : '') . 'page=';
            if ($page > 1): ?>
            <li class="page-item"><a class="page-link bg-dark text-danger border-secondary" href="<?php echo $baseUrl . ($page - 1); ?>">Previous</a></li>
            <?php endif; ?>
            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><a class="page-link bg-dark text-danger border-secondary" href="<?php echo $baseUrl . $i; ?>"><?php echo $i; ?></a></li>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <li class="page-item"><a class="page-link bg-dark text-danger border-secondary" href="<?php echo $baseUrl . ($page + 1); ?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <p class="text-center text-muted small">Page <?php echo $page; ?> of <?php echo $totalPages; ?> (<?php echo $total; ?> movies)</p>
    <?php endif; ?>
    <?php endif; ?>

    </div><!-- end col-lg-9 -->

    <!-- Blog Sidebar -->
    <div class="col-lg-3">
        <div class="card bg-dark text-white mb-4">
            <div class="card-body">
                <h5 class="card-title text-danger">Blog</h5>
                <?php if (empty($blogPosts)): ?>
                <p class="text-muted small">No blog posts yet.</p>
                <?php else: ?>
                <?php foreach ($blogPosts as $post): ?>
                <div class="mb-3 pb-3 border-bottom border-secondary">
                    <h6 class="mb-1"><?php echo htmlspecialchars($post['title']); ?></h6>
                    <small class="text-muted"><?php echo date('M j, Y', strtotime($post['created_at'])); ?> &middot; <?php echo htmlspecialchars($post['username']); ?></small>
                    <p class="mt-1 mb-0 small"><?php echo htmlspecialchars(strlen($post['content']) > 120 ? substr($post['content'], 0, 120) . '...' : $post['content']); ?></p>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div><!-- end col-lg-3 -->

    </div><!-- end row -->
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
