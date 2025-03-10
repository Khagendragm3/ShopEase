<?php
$pageTitle = "Contact Us";
include 'includes/header.php';

// Initialize variables
$name = '';
$email = '';
$subject = '';
$message = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required.';
    }
    
    // If no errors, save to database and/or send email
    if (empty($errors)) {
        // Check if contact_messages table exists
        $tableExists = false;
        $checkTable = $conn->query("SHOW TABLES LIKE 'contact_messages'");
        if ($checkTable && $checkTable->num_rows > 0) {
            $tableExists = true;
        }
        
        // Create table if it doesn't exist
        if (!$tableExists) {
            $sql = "CREATE TABLE `contact_messages` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `email` varchar(255) NOT NULL,
                `subject` varchar(255) NOT NULL,
                `message` text NOT NULL,
                `status` enum('new','read','replied') NOT NULL DEFAULT 'new',
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            $conn->query($sql);
        }
        
        // Save message to database
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            // Send email notification to admin (optional)
            $adminEmail = getSetting('site_email', 'admin@example.com');
            $emailSubject = "New Contact Form Submission: $subject";
            $emailBody = "Name: $name\nEmail: $email\nSubject: $subject\nMessage: $message";
            $headers = "From: $email";
            
            // Uncomment to enable email sending
            // mail($adminEmail, $emailSubject, $emailBody, $headers);
            
            // Set success flag
            $success = true;
            
            // Clear form data
            $name = '';
            $email = '';
            $subject = '';
            $message = '';
        } else {
            $errors[] = 'Failed to send message. Please try again later.';
        }
    }
}
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-title">Contact Us</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo URL_ROOT; ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section py-5">
    <div class="container">
        <?php if ($success): ?>
            <div class="alert alert-success mb-4">
                <h4 class="alert-heading">Thank you for your message!</h4>
                <p>We have received your inquiry and will get back to you as soon as possible.</p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mb-4">
                <h4 class="alert-heading">Please correct the following errors:</h4>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8 mb-5 mb-lg-0">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="card-title mb-4"><?php echo getSetting('contact_page_title', 'Get In Touch'); ?></h2>
                        <p class="mb-4"><?php echo getSetting('contact_page_subtitle', 'We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.'); ?></p>
                        <form method="POST" action="contact.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Your Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="5" required><?php echo htmlspecialchars($message); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-body p-4">
                        <h3 class="card-title mb-4">Contact Information</h3>
                        <div class="contact-info">
                            <div class="contact-item mb-3">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-text">
                                    <h5>Address</h5>
                                    <p><?php echo getSetting('site_address', '123 Main Street, New York, NY 10001, USA'); ?></p>
                                </div>
                            </div>
                            <div class="contact-item mb-3">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="contact-text">
                                    <h5>Phone</h5>
                                    <p><?php echo getSetting('site_phone', '+1 (555) 123-4567'); ?></p>
                                </div>
                            </div>
                            <div class="contact-item mb-3">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-text">
                                    <h5>Email</h5>
                                    <p><?php echo getSetting('site_email', 'info@example.com'); ?></p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="contact-text">
                                    <h5>Business Hours</h5>
                                    <p><?php echo nl2br(getSetting('business_hours', "Monday - Friday: 9:00 AM - 5:00 PM\nSaturday: 10:00 AM - 3:00 PM\nSunday: Closed")); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="card-title mb-4">Follow Us</h3>
                        <div class="social-links">
                            <?php if(!empty(getSetting('facebook_url'))): ?>
                            <a href="<?php echo getSetting('facebook_url'); ?>" class="social-link" target="_blank"><i class="fab fa-facebook-f"></i></a>
                            <?php endif; ?>
                            <?php if(!empty(getSetting('twitter_url'))): ?>
                            <a href="<?php echo getSetting('twitter_url'); ?>" class="social-link" target="_blank"><i class="fab fa-twitter"></i></a>
                            <?php endif; ?>
                            <?php if(!empty(getSetting('instagram_url'))): ?>
                            <a href="<?php echo getSetting('instagram_url'); ?>" class="social-link" target="_blank"><i class="fab fa-instagram"></i></a>
                            <?php endif; ?>
                            <?php if(!empty(getSetting('youtube_url'))): ?>
                            <a href="<?php echo getSetting('youtube_url'); ?>" class="social-link" target="_blank"><i class="fab fa-youtube"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section">
    <div class="container-fluid p-0">
        <div class="map-container">
            <?php echo getSetting('google_map_embed', '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.215256349542!2d-73.98784532342249!3d40.75798833440646!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25855c6480299%3A0x55194ec5a1ae072e!2sTimes%20Square!5m2!1s0x89c25855c6480299%3A0x55194ec5a1ae072e!2sTimes%20Square" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>'); ?>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section py-5 bg-light">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2>Frequently Asked Questions</h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="contactFaq">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                <?php echo getSetting('faq_question_1', 'How can I track my order?'); ?>
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#contactFaq">
                            <div class="accordion-body">
                                <?php echo getSetting('faq_answer_1', 'You can track your order by logging into your account and visiting the "My Orders" section. There, you\'ll find a list of all your orders and their current status. Alternatively, you can use the tracking number provided in your order confirmation email.'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                <?php echo getSetting('faq_question_2', 'What is your return policy?'); ?>
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#contactFaq">
                            <div class="accordion-body">
                                <?php echo getSetting('faq_answer_2', 'We offer a 30-day return policy for most items. Products must be returned in their original condition and packaging. Please note that certain items, such as personalized products or perishable goods, may not be eligible for return. For more details, please visit our Returns & Refunds page.'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                <?php echo getSetting('faq_question_3', 'How long does shipping take?'); ?>
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#contactFaq">
                            <div class="accordion-body">
                                <?php echo getSetting('faq_answer_3', 'Shipping times vary depending on your location and the shipping method selected. Standard shipping typically takes 3-5 business days within the continental US. Express shipping options are available for faster delivery. International shipping may take 7-14 business days, depending on the destination country.'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                <?php echo getSetting('faq_question_4', 'Do you offer international shipping?'); ?>
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#contactFaq">
                            <div class="accordion-body">
                                <?php echo getSetting('faq_answer_4', 'Yes, we ship to most countries worldwide. International shipping rates and delivery times vary by destination. Please note that customers are responsible for any customs duties, taxes, or import fees that may apply. These charges are not included in the purchase price or shipping cost.'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                <?php echo getSetting('faq_question_5', 'How can I change or cancel my order?'); ?>
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#contactFaq">
                            <div class="accordion-body">
                                <?php echo getSetting('faq_answer_5', 'If you need to change or cancel your order, please contact our customer service team as soon as possible. We process orders quickly, so changes or cancellations are only possible if the order has not yet been shipped. Once an order has been shipped, it cannot be canceled, but you can return it according to our return policy.'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 