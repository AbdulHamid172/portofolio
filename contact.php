<?php
require_once 'config/db.php';

$message_sent = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }

    if (empty($subject)) {
        $errors[] = "Subject is required";
    }

    if (empty($message)) {
        $errors[] = "Message is required";
    }

    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);

            $message_sent = true;

        } catch (PDOException $e) {
            $errors[] = "Failed to send message. Please try again.";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<!-- Contact Hero -->
<section class="page-hero">
    <div class="container">
        <h1 class="page-title" data-aos="fade-up">Get In Touch</h1>
        <p class="page-subtitle" data-aos="fade-up" data-aos-delay="100">
            Let's discuss your next project or connect
        </p>
    </div>
</section>

<!-- Contact Section -->
<section class="section contact-section">
    <div class="container">
        <div class="contact-grid">
            <!-- Left Column - Social Connect & Contact Info -->
            <div class="social-connect-section" data-aos="fade-right">
                <h2>Connect With Me</h2>
                <p class="subtitle">Follow me on social media or reach out directly</p>

                <!-- Social Icons -->
                <div class="social-icons-row">
                    <a href="https://github.com/AbdulHamid172" target="_blank" class="social-icon-btn github" data-tooltip="GitHub" aria-label="GitHub">
                        <i class="fab fa-github"></i>
                    </a>

                    <a href="https://www.facebook.com/share/1GpioouqC7/" target="_blank" class="social-icon-btn facebook" data-tooltip="Facebook" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>

                    <a href="https://www.instagram.com/sharafydeen?igsh=Y3hrOGVsN2ZieWJy" target="_blank" class="social-icon-btn instagram" data-tooltip="Instagram" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>

                    <a href="https://wa.me/254115405261" target="_blank" class="social-icon-btn whatsapp" data-tooltip="WhatsApp" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>

                <!-- Availability Badge -->
                <div class="availability-badge">
                    <span class="dot"></span>
                    Available for collaboration
                </div>

                <!-- Contact Information -->
                <div class="contact-info-list">
                    <div class="contact-info-item">
                        <div class="icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="details">
                            <h4>Email</h4>
                            <p>abdulhamidismailahmed@gmail.com</p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="details">
                            <h4>Phone / WhatsApp</h4>
                            <p>+254 115 405 261</p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="details">
                            <h4>Location</h4>
                            <p>Mombasa, Kenya</p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="details">
                            <h4>Education</h4>
                            <p>Mount Kenya University, Coast Campus</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Contact Form -->
            <div class="contact-form-container" data-aos="fade-left">
                <h2>Send a Message</h2>
                <p class="subtitle">I'll get back to you as soon as possible</p>

                <?php if ($message_sent): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        <h3>Message Sent Successfully!</h3>
                        <p>Thank you for contacting me. I'll get back to you soon.</p>
                        <a href="/contact.php" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Another Message
                        </a>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="error-message">
                            <strong><i class="fas fa-exclamation-circle"></i> Please fix the following errors:</strong>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="contact-form" id="contactForm">
                        <div class="form-group">
                            <label for="name">Full Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" required
                                   placeholder="Your Name"
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required
                                   placeholder="your.email@example.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject <span class="required">*</span></label>
                            <input type="text" id="subject" name="subject" required
                                   placeholder="Project Inquiry"
                                   value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="message">Your Message <span class="required">*</span></label>
                            <textarea id="message" name="message" required rows="5"
                                      placeholder="Tell me about your project or inquiry..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn-submit" id="submitBtn">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="section map-section">
    <div class="container">
        <div class="map-container" data-aos="fade-up">
            <div class="map-placeholder">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Based in Mombasa, Kenya</h3>
                <p>Available for remote work and collaboration worldwide</p>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');

    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();

            if (!name || !email || !subject || !message) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;

            setTimeout(() => {
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Message';
                submitBtn.disabled = false;
            }, 10000);
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>