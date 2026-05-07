<?php
/**
 * includes/header.php - Κοινή κεφαλίδα για όλες τις σελίδες.
 * Περιλαμβάνει: HTML head, navbar με δυναμικούς συνδέσμους ειδών και Reviews, προαιρετικό breadcrumb και αναζήτηση.
 * Πριν την inclusion αυτού του αρχείου, ορίστε (προαιρετικά):
 *   $pageTitle  - π.χ. "Home" ή "Reviews"
 *   $breadcrumb - array π.χ. ['Home' => 'index.php', 'Reviews' => null] (null = τρέχουσα σελίδα, χωρίς σύνδεσμο)
 */
// Φόρτωση του config ώστε να έχουμε τα διαπιστευτήρια της βάσης δεδομένων και το SITE_NAME
require_once __DIR__ . '/../config.php';
// Άνοιγμα σύνδεσης με τη MySQL ώστε να φορτώσουμε τη λίστα των ειδών για το μενού
$conn = getDbConnection();
// Εκτέλεση ερωτήματος: λήψη id, name, και slug για κάθε είδος, ταξινομημένα κατά όνομα
$genresQuery = mysqli_query($conn, "SELECT id, name, slug FROM genres ORDER BY name");
$genres = [];
if ($genresQuery) {
    // Η mysqli_fetch_assoc() επιστρέφει μία γραμμή ως associative array (όνομα στήλης => τιμή)
    while ($row = mysqli_fetch_assoc($genresQuery)) {
        $genres[] = $row;
    }
}
// Αν η σελίδα δεν όρισε $pageTitle, χρησιμοποιούμε το όνομα του site ώστε το <title> να μην είναι ποτέ κενό
if (empty($pageTitle)) {
    $pageTitle = SITE_NAME;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" /> <!-- Ορισμός κωδικοποίησης UTF-8 για σωστή εμφάνιση χαρακτήρων -->
    <title><?php echo htmlspecialchars($pageTitle); ?> - <?php echo htmlspecialchars(SITE_NAME); ?></title> <!-- Τίτλος σελίδας με ασφαλή έξοδο -->
    <meta name="viewport" content="width=device-width, initial-scale=1" /> <!-- Responsive design για κινητά -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" /> <!-- Φόρτωση Bootstrap CSS framework -->
    <style>
        body { margin: 0; background-color: #111; color: #fff; font-family: Arial, Helvetica, sans-serif; } /* Στυλ σώματος: μαύρο φόντο, λευκά γράμματα */
        header { background-color: #b31b1b; padding: 20px; text-align: center; font-weight: bold; letter-spacing: 1px; } /* Κόκκινη κεφαλίδα με κεντρικό στοίχιση */
        .navbar-custom { background-color: #b31b1b !important; } /* Προσαρμοσμένο κόκκινο navbar */
        .card-img-top { width: 100%; height: auto; object-fit: contain; } /* Εικόνες καρτών να προσαρμόζονται σωστά */
        .breadcrumb { background: transparent; } /* Διάφανο breadcrumb */
        .breadcrumb-item a { color: #ff6b6b; } /* Σύνδεσμοι breadcrumb με κόκκινο χρώμα */
        .breadcrumb-item.active { color: #ccc; } /* Ενεργό breadcrumb με γκρι χρώμα */
    </style>
</head>
<body> <!-- Έναρξη σώματος HTML -->

<nav class="navbar navbar-dark navbar-expand-lg navbar-custom"> <!-- Κύρια πλοήγηση με Bootstrap -->
    <div class="container-fluid"> <!-- Πλήρους πλάτους container για responsive design -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle navigation"> <!-- Κουμπί για κινητά -->
            <span class="navbar-toggler-icon"></span> <!-- Εικονίδιο hamburger μενού -->
        </button>
        <a class="navbar-brand mx-auto mx-lg-0 me-lg-3 fw-bold fs-4" href="index.php"><?php echo htmlspecialchars(SITE_NAME); ?></a> <!-- Λογότυπο/όνομα site με σύνδεσμο στην αρχική -->
        <div class="collapse navbar-collapse" id="mobileMenu"> <!-- Κρυφό μενού που εμφανίζεται σε κινητά -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0"> <!-- Λίστα πλοήγησης με αυτόματο περιθώριο -->
                <li class="nav-item"> <!-- Στοιχείο πλοήγησης για Αρχική -->
                    <a class="nav-link active" href="index.php">Home</a>
                </li>
                <li class="nav-item dropdown"> <!-- Dropdown μενού για Είδη -->
                    <a class="nav-link dropdown-toggle" href="#" id="genreDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Genres</a> <!-- Σύνδεσμος που ανοίγει dropdown -->
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="genreDropdown"> <!-- Dropdown λίστα με σκούρο θέμα -->
                        <?php foreach ($genres as $g): ?> <!-- PHP loop για κάθε είδος από τη βάση -->
                        <!-- Link to genre page: genre.php?genre=action etc. urlencode() makes the URL safe -->
                        <li><a class="dropdown-item" href="genre.php?genre=<?php echo urlencode($g['slug']); ?>"><?php echo htmlspecialchars($g['name']); ?></a></li> <!-- Σύνδεσμος για κάθε είδος -->
                        <?php endforeach; ?> <!-- Τέλος loop -->
                    </ul>
                </li>
                <li class="nav-item"> <!-- Στοιχείο πλοήγησης για Κριτικές -->
                    <a class="nav-link" href="reviews.php">Reviews</a>
                </li>
                <li class="nav-item"> <!-- Στοιχείο πλοήγησης για Σύνδεση -->
                    <a class="nav-link" href="login.php">Login</a>
                </li>
                <li class="nav-item"> <!-- Στοιχείο πλοήγησης για Εγγραφή -->
                    <a class="nav-link" href="register.php">Register</a>
                </li>
            </ul>
            <!-- Search form: submits to index.php with ?search=... -->
            <!-- Search: GET request to index.php with ?search=... so user can bookmark or share the search -->
            <form class="d-flex" role="search" action="index.php" method="get"> <!-- Φόρμα αναζήτησης με GET method -->
                <input class="form-control me-2 bg-dark text-white border-secondary" type="search" name="search" placeholder="Search movies..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" aria-label="Search" /> <!-- Πεδίο εισαγωγής με τρέχουσα τιμή -->
                <button class="btn btn-outline-light" type="submit">Search</button> <!-- Κουμπί υποβολής αναζήτησης -->
            </form>
        </div>
    </div>
</nav>

<?php if (!empty($breadcrumb) && is_array($breadcrumb)): ?> <!-- Έλεγχος αν υπάρχει breadcrumb -->
<!-- Breadcrumb: Home > Genre > current page. $url = null means current page (no link) -->
<nav aria-label="breadcrumb" class="container-fluid py-2"> <!-- Πλοήγηση breadcrumb -->
    <ol class="breadcrumb mb-0"> <!-- Ταξινομημένη λίστα breadcrumb -->
        <li class="breadcrumb-item"><a href="index.php">Home</a></li> <!-- Πάντα σύνδεσμος στην αρχική -->
        <?php foreach ($breadcrumb as $label => $url): ?> <!-- PHP loop για κάθε στοιχείο breadcrumb -->
            <?php if ($url): ?> <!-- Αν υπάρχει URL, φτιάξε σύνδεσμο -->
        <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars($url); ?>"><?php echo htmlspecialchars($label); ?></a></li>
            <?php else: ?> <!-- Αλλιώς, ενεργό στοιχείο χωρίς σύνδεσμο -->
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($label); ?></li>
            <?php endif; ?>
        <?php endforeach; ?> <!-- Τέλος loop -->
    </ol>
</nav>
<?php endif; ?> <!-- Τέλος ελέγχου breadcrumb -->
