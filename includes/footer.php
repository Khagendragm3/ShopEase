        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row">
                <!-- About Us -->
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">About Us</h5>
                    <p>
                        <?php echo !empty(getSetting('site_description')) ? getSetting('site_description') : SITE_NAME . ' is your one-stop shop for all your shopping needs, offering a wide range of products with excellent customer service.'; ?>
                    </p>
                    <div class="social-icons mt-3">
                        <?php if(!empty(getSetting('facebook_url'))): ?>
                        <a href="<?php echo getSetting('facebook_url'); ?>" class="text-white me-2" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                        <?php if(!empty(getSetting('twitter_url'))): ?>
                        <a href="<?php echo getSetting('twitter_url'); ?>" class="text-white me-2" target="_blank"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if(!empty(getSetting('instagram_url'))): ?>
                        <a href="<?php echo getSetting('instagram_url'); ?>" class="text-white me-2" target="_blank"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php if(!empty(getSetting('youtube_url'))): ?>
                        <a href="<?php echo getSetting('youtube_url'); ?>" class="text-white" target="_blank"><i class="fab fa-youtube"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo URL_ROOT; ?>" class="text-white">Home</a></li>
                        <li class="mb-2"><a href="<?php echo URL_ROOT; ?>/shop.php" class="text-white">Shop</a></li>
                        <li class="mb-2"><a href="<?php echo URL_ROOT; ?>/about.php" class="text-white">About Us</a></li>
                        <li class="mb-2"><a href="<?php echo URL_ROOT; ?>/contact.php" class="text-white">Contact</a></li>
                        <li><a href="<?php echo URL_ROOT; ?>/privacy-policy.php" class="text-white">Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Customer Service</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo URL_ROOT; ?>/account.php" class="text-white">My Account</a></li>
                        <li class="mb-2"><a href="<?php echo URL_ROOT; ?>/orders.php" class="text-white">Order History</a></li>
                        <li class="mb-2"><a href="<?php echo URL_ROOT; ?>/wishlist.php" class="text-white">Wishlist</a></li>
                        <li class="mb-2"><a href="<?php echo URL_ROOT; ?>/faq.php" class="text-white">FAQ</a></li>
                        <li><a href="<?php echo URL_ROOT; ?>/returns-refunds.php" class="text-white">Returns & Refunds</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Contact Info</h5>
                    <ul class="list-unstyled contact-info">
                        <?php if(!empty(getSetting('site_address'))): ?>
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i> <?php echo getSetting('site_address'); ?>
                        </li>
                        <?php endif; ?>
                        <?php if(!empty(getSetting('site_phone'))): ?>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2"></i> <?php echo getSetting('site_phone'); ?>
                        </li>
                        <?php endif; ?>
                        <?php if(!empty(getSetting('site_email'))): ?>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i> <?php echo getSetting('site_email'); ?>
                        </li>
                        <?php endif; ?>
                        <li>
                            <i class="fas fa-clock me-2"></i> Mon - Fri: 9:00 AM - 5:00 PM
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="my-4">

            

            <!-- Payment Methods -->
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h5 class="mb-3">Payment Methods</h5>
                    <div class="payment-methods">
                        <i class="fab fa-cc-visa fa-2x me-2"></i>
                        <i class="fab fa-cc-mastercard fa-2x me-2"></i>
                        <i class="fab fa-cc-amex fa-2x me-2"></i>
                        <i class="fab fa-cc-paypal fa-2x me-2"></i>
                        <i class="fab fa-cc-discover fa-2x"></i>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo URL_ROOT; ?>/assets/js/main.js"></script>
    <?php if(isset($extraJS)) echo $extraJS; ?>
</body>
</html> 