<?php
/**
 * Κοινό footer για όλες τις σελίδες.
 * Δεν απαιτούνται μεταβλητές. Προαιρετικά κλείσιμο ανοιχτών συνδέσεων με τη βάση (η PHP το κάνει αυτόματα στο τέλος).
 */
?>
<footer class="text-center text-secondary py-3 mt-4"> <!-- Footer με κεντρικό στοίχιση και γκρι χρώμα -->
    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'Fresh Potatos'); ?> &mdash; <?php echo htmlspecialchars(defined('SITE_TAGLINE') ? SITE_TAGLINE : 'Totally Real Reviews'); ?> <!-- Πνευματικά δικαιώματα με τρέχον έτος, όνομα site και tagline -->
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> <!-- Φόρτωση Bootstrap JavaScript για λειτουργικότητα dropdown, carousel κλπ. -->
</body> <!-- Κλείσιμο σώματος HTML -->
</html> <!-- Κλείσιμο HTML document -->
