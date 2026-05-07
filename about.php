<?php
/**
 * about.php - Static about page.
 * Describes the site, its purpose, and the technology used.
 */
session_start();
$pageTitle = 'About';
$breadcrumb = ['About' => null];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="mb-4">About Us</h1>

            <div class="card bg-dark text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title text-danger">What is Fresh Potatos?</h5>
                    <p>Fresh Potatos is a movie browsing and review website where you can discover movies, read reviews, and share your own opinions. Browse by genre, search for your favorites, and build your personal watchlist.</p>
                </div>
            </div>

            <div class="card bg-dark text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title text-danger">Contact Us On</h5>
                    <ul>
                        <li>Twitter.com</li>
                        <li>instagram:@fresh_potatos</li>
                        <li>Email:FreshPotatos@Movies.net</li>
                    </ul>
                </div>
            </div>

            <div class="card bg-dark text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title text-danger">Technology Stack</h5>
                    <table class="table table-dark table-sm">
                        <tr><th style="width:150px">Backend</th><td>PHP</td></tr>
                        <tr><th>Database</th><td>MySQL (MariaDB)</td></tr>
                        <tr><th>Frontend</th><td>HTML, CSS, Bootstrap 5</td></tr>
                        <tr><th>Server</th><td>Apache (XAMPP)</td></tr>
                        <tr><th>Movie Data</th><td>TMDb API (The Movie Database)</td></tr>
                    </table>
                </div>
            </div>

            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h5 class="card-title text-danger">Special Thanks </h5>
                    <p class="mb-0">mom</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
