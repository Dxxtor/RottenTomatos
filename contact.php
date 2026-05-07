<?php
/**
 * contact.php - Contact/support form.
 * POST: saves the message to the contact_messages table.
 */
session_start();
require_once __DIR__ . '/config.php';
$conn = getDbConnection();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name    = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email   = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $body    = isset($_POST['message']) ? trim($_POST['message']) : '';

    if ($name === '' || $email === '' || $subject === '' || $body === '') {
        $message = 'Please fill in all fields.';
        $messageType = 'danger';
    } else {
        $nameEsc    = mysqli_real_escape_string($conn, $name);
        $emailEsc   = mysqli_real_escape_string($conn, $email);
        $subjectEsc = mysqli_real_escape_string($conn, $subject);
        $bodyEsc    = mysqli_real_escape_string($conn, $body);
        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES ('$nameEsc', '$emailEsc', '$subjectEsc', '$bodyEsc')";
        if (mysqli_query($conn, $sql)) {
            $message = 'Your message has been sent! We will get back to you soon.';
            $messageType = 'success';
        } else {
            $message = 'Could not send message. Please try again.';
            $messageType = 'danger';
        }
    }
}

$pageTitle = 'Contact Us';
$breadcrumb = ['Contact' => null];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="mb-4">Contact Us</h1>
            <p class="text-muted mb-4">Have a question, suggestion, or found a bug? Send us a message!</p>

            <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card bg-dark text-white">
                <div class="card-body">
                    <form method="post" action="contact.php">
                        <input type="hidden" name="send_message" value="1" />
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" name="name" id="name" class="form-control bg-dark text-white border-secondary" required maxlength="100" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" />
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Your Email</label>
                            <input type="email" name="email" id="email" class="form-control bg-dark text-white border-secondary" required maxlength="255" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" name="subject" id="subject" class="form-control bg-dark text-white border-secondary" required maxlength="255" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" />
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea name="message" id="message" class="form-control bg-dark text-white border-secondary" rows="5" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
