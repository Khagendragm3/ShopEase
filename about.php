<?php
$pageTitle = "About Us";
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1 class="page-title">About Us</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo URL_ROOT; ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">About Us</li>
            </ol>
        </nav>
    </div>
</section>

<!-- About Us Content -->
<section class="about-section">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="about-image">
                    <img src="<?php echo getImageUrl(getSetting('about_image', ''), 'uploads/settings', 'assets/images/about-us.jpg'); ?>" alt="About <?php echo SITE_NAME; ?>" class="img-fluid rounded shadow">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-content">
                    <h2 class="section-title"><?php echo getSetting('about_story_title', 'Our Story'); ?></h2>
                    <p><?php echo getSetting('about_story', 'Founded in 2010, ' . SITE_NAME . ' has been at the forefront of e-commerce innovation, providing customers with high-quality products and exceptional service. What started as a small online store has grown into a trusted marketplace serving customers worldwide.'); ?></p>
                    <p><?php echo getSetting('about_story_continued', 'Our journey has been defined by our commitment to customer satisfaction and our passion for delivering value. We believe in building lasting relationships with our customers, suppliers, and partners.'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="row mb-5">
            <div class="col-md-6 mb-4">
                <div class="mission-vision-card">
                    <div class="card h-100 shadow">
                        <div class="card-body">
                            <h3 class="card-title"><i class="fas fa-bullseye text-primary me-2"></i> Our Mission</h3>
                            <p class="card-text"><?php echo getSetting('about_mission', 'Our mission is to provide customers with a seamless shopping experience, offering high-quality products at competitive prices. We strive to exceed customer expectations through exceptional service, innovative solutions, and a commitment to continuous improvement.'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="mission-vision-card">
                    <div class="card h-100 shadow">
                        <div class="card-body">
                            <h3 class="card-title"><i class="fas fa-eye text-primary me-2"></i> Our Vision</h3>
                            <p class="card-text"><?php echo getSetting('about_vision', 'Our vision is to become the leading e-commerce platform, recognized for our integrity, quality, and customer-centric approach. We aim to set new standards in online shopping, making it accessible, enjoyable, and rewarding for everyone.'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-5">
            <div class="col-12 text-center mb-4">
                <h2 class="section-title">Our Values</h2>
            </div>
            <div class="col-md-4 mb-4">
                <div class="value-card text-center">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Customer First</h3>
                    <p>We prioritize our customers' needs and strive to exceed their expectations in every interaction.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="value-card text-center">
                    <div class="value-icon">
                        <i class="fas fa-gem"></i>
                    </div>
                    <h3>Quality</h3>
                    <p>We are committed to offering only the highest quality products and services to our customers.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="value-card text-center">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Integrity</h3>
                    <p>We conduct our business with honesty, transparency, and ethical practices.</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h2 class="section-title">Our Team</h2>
                <p class="section-subtitle">Meet the dedicated professionals behind our success</p>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="team-member">
                    <div class="team-image">
                        <img src="<?php echo getImageUrl(getSetting('team_image_1', ''), 'uploads/team', 'assets/images/team-1.jpg'); ?>" alt="<?php echo getSetting('team_name_1', 'Team Member'); ?>" class="img-fluid rounded">
                    </div>
                    <div class="team-info">
                        <h4><?php echo getSetting('team_name_1', 'John Doe'); ?></h4>
                        <p class="designation"><?php echo getSetting('team_position_1', 'CEO & Founder'); ?></p>
                        <div class="social-links">
                            <?php if (!empty(getSetting('team_linkedin_1', ''))): ?>
                            <a href="<?php echo getSetting('team_linkedin_1'); ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                            <?php endif; ?>
                            <?php if (!empty(getSetting('team_twitter_1', ''))): ?>
                            <a href="<?php echo getSetting('team_twitter_1'); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                            <?php endif; ?>
                            <?php if (!empty(getSetting('team_facebook_1', ''))): ?>
                            <a href="<?php echo getSetting('team_facebook_1'); ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="team-member">
                    <div class="team-image">
                        <img src="<?php echo getImageUrl(getSetting('team_image_2', ''), 'uploads/team', 'assets/images/team-2.jpg'); ?>" alt="<?php echo getSetting('team_name_2', 'Team Member'); ?>" class="img-fluid rounded">
                    </div>
                    <div class="team-info">
                        <h4><?php echo getSetting('team_name_2', 'Jane Smith'); ?></h4>
                        <p class="designation"><?php echo getSetting('team_position_2', 'Operations Manager'); ?></p>
                        <div class="social-links">
                            <?php if (!empty(getSetting('team_linkedin_2', ''))): ?>
                            <a href="<?php echo getSetting('team_linkedin_2'); ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                            <?php endif; ?>
                            <?php if (!empty(getSetting('team_twitter_2', ''))): ?>
                            <a href="<?php echo getSetting('team_twitter_2'); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                            <?php endif; ?>
                            <?php if (!empty(getSetting('team_facebook_2', ''))): ?>
                            <a href="<?php echo getSetting('team_facebook_2'); ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="team-member">
                    <div class="team-image">
                        <img src="<?php echo getImageUrl(getSetting('team_image_3', ''), 'uploads/team', 'assets/images/team-3.jpg'); ?>" alt="<?php echo getSetting('team_name_3', 'Team Member'); ?>" class="img-fluid rounded">
                    </div>
                    <div class="team-info">
                        <h4><?php echo getSetting('team_name_3', 'Michael Johnson'); ?></h4>
                        <p class="designation"><?php echo getSetting('team_position_3', 'Marketing Director'); ?></p>
                        <div class="social-links">
                            <?php if (!empty(getSetting('team_linkedin_3', ''))): ?>
                            <a href="<?php echo getSetting('team_linkedin_3'); ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                            <?php endif; ?>
                            <?php if (!empty(getSetting('team_twitter_3', ''))): ?>
                            <a href="<?php echo getSetting('team_twitter_3'); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                            <?php endif; ?>
                            <?php if (!empty(getSetting('team_facebook_3', ''))): ?>
                            <a href="<?php echo getSetting('team_facebook_3'); ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="team-member">
                    <div class="team-image">
                        <img src="<?php echo getImageUrl(getSetting('team_image_4', ''), 'uploads/team', 'assets/images/team-4.jpg'); ?>" alt="<?php echo getSetting('team_name_4', 'Team Member'); ?>" class="img-fluid rounded">
                    </div>
                    <div class="team-info">
                        <h4><?php echo getSetting('team_name_4', 'Emily Brown'); ?></h4>
                        <p class="designation"><?php echo getSetting('team_position_4', 'Customer Support Lead'); ?></p>
                        <div class="social-links">
                            <?php if (!empty(getSetting('team_linkedin_4', ''))): ?>
                            <a href="<?php echo getSetting('team_linkedin_4'); ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                            <?php endif; ?>
                            <?php if (!empty(getSetting('team_twitter_4', ''))): ?>
                            <a href="<?php echo getSetting('team_twitter_4'); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                            <?php endif; ?>
                            <?php if (!empty(getSetting('team_facebook_4', ''))): ?>
                            <a href="<?php echo getSetting('team_facebook_4'); ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials bg-light py-5">
    <div class="container">
        <div class="section-title text-center">
            <h2>What Our Customers Say</h2>
        </div>
        <div class="row">
            <?php
            // Check if testimonials table exists
            $tableExists = false;
            $checkTable = $conn->query("SHOW TABLES LIKE 'testimonials'");
            if ($checkTable && $checkTable->num_rows > 0) {
                $tableExists = true;
            }
            
            $testimonials = [];
            
            // Only query if table exists
            if ($tableExists) {
                // Get active testimonials from database
                $stmt = $conn->prepare("SELECT * FROM testimonials WHERE status = 'active' ORDER BY id DESC LIMIT 3");
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $testimonials[] = $row;
                }
            }
            
            // If we have testimonials in the database, display them
            if (!empty($testimonials)):
                foreach ($testimonials as $testimonial):
            ?>
            <div class="col-md-4">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"<?php echo htmlspecialchars($testimonial['content']); ?>"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="<?php echo getImageUrl($testimonial['image'], 'uploads/testimonials', 'assets/images/testimonial-1.jpg'); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>">
                        </div>
                        <div class="author-info">
                            <h5><?php echo htmlspecialchars($testimonial['name']); ?></h5>
                            <p><?php echo htmlspecialchars($testimonial['position']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                endforeach;
            else:
                // If no testimonials in database, display default ones
            ?>
            <div class="col-md-4">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"<?php echo getSetting('testimonial_1_text', "I'm extremely satisfied with the quality of products and the excellent customer service. Will definitely shop here again!"); ?>"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="<?php echo getImageUrl(getSetting('testimonial_image_1', ''), 'uploads/testimonials', 'assets/images/testimonial-1.jpg'); ?>" alt="<?php echo getSetting('testimonial_1_name', 'John Doe'); ?>">
                        </div>
                        <div class="author-info">
                            <h5><?php echo getSetting('testimonial_1_name', 'John Doe'); ?></h5>
                            <p><?php echo getSetting('testimonial_1_position', 'Regular Customer'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"<?php echo getSetting('testimonial_2_text', "Fast shipping and great prices. The products are of high quality and exactly as described. Highly recommended!"); ?>"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="<?php echo getImageUrl(getSetting('testimonial_image_2', ''), 'uploads/testimonials', 'assets/images/testimonial-2.jpg'); ?>" alt="<?php echo getSetting('testimonial_2_name', 'Jane Smith'); ?>">
                        </div>
                        <div class="author-info">
                            <h5><?php echo getSetting('testimonial_2_name', 'Jane Smith'); ?></h5>
                            <p><?php echo getSetting('testimonial_2_position', 'Loyal Customer'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"<?php echo getSetting('testimonial_3_text', "The website is easy to navigate, and the checkout process is smooth. I received my order earlier than expected. Great experience!"); ?>"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-image">
                            <img src="<?php echo getImageUrl(getSetting('testimonial_image_3', ''), 'uploads/testimonials', 'assets/images/testimonial-3.jpg'); ?>" alt="<?php echo getSetting('testimonial_3_name', 'Mike Johnson'); ?>">
                        </div>
                        <div class="author-info">
                            <h5><?php echo getSetting('testimonial_3_name', 'Mike Johnson'); ?></h5>
                            <p><?php echo getSetting('testimonial_3_position', 'New Customer'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?> 