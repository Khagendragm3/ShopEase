<?php
$pageTitle = "General Settings";
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect('../login.php');
}

// Get current settings
$settings = [];
$stmt = $conn->prepare("SELECT * FROM settings");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the active tab from the form submission
    $activeTab = isset($_POST['save_settings']) ? $_POST['save_settings'] : 'general';
    
    // Site Information
    $site_name = sanitize($_POST['site_name']);
    $site_description = sanitize($_POST['site_description']);
    $site_email = sanitize($_POST['site_email']);
    $site_phone = sanitize($_POST['site_phone']);
    $site_address = sanitize($_POST['site_address']);
    
    // Social Media
    $facebook_url = sanitize($_POST['facebook_url']);
    $twitter_url = sanitize($_POST['twitter_url']);
    $instagram_url = sanitize($_POST['instagram_url']);
    $youtube_url = sanitize($_POST['youtube_url']);
    
    // SEO Settings
    $meta_title = sanitize($_POST['meta_title']);
    $meta_description = sanitize($_POST['meta_description']);
    $meta_keywords = sanitize($_POST['meta_keywords']);
    
    // Payment Settings
    $currency_symbol = sanitize($_POST['currency_symbol']);
    $currency_code = sanitize($_POST['currency_code']);
    $tax_rate = (float) sanitize($_POST['tax_rate']);
    
    // Shipping Settings
    $free_shipping_min = (float) sanitize($_POST['free_shipping_min']);
    $default_shipping_fee = (float) sanitize($_POST['default_shipping_fee']);
    
    // Homepage Content
    $hero_title = sanitize($_POST['hero_title']);
    $hero_description = sanitize($_POST['hero_description']);
    $hero_button_text = sanitize($_POST['hero_button_text']);
    $hero_button_url = sanitize($_POST['hero_button_url']);
    
    // Hero Slider Settings
    $use_hero_slider = isset($_POST['use_hero_slider']) ? '1' : '0';
    $hero_slide_title_1 = sanitize($_POST['hero_slide_title_1']);
    $hero_slide_description_1 = sanitize($_POST['hero_slide_description_1']);
    $hero_slide_button_text_1 = sanitize($_POST['hero_slide_button_text_1']);
    $hero_slide_button_url_1 = sanitize($_POST['hero_slide_button_url_1']);
    
    $hero_slide_title_2 = sanitize($_POST['hero_slide_title_2']);
    $hero_slide_description_2 = sanitize($_POST['hero_slide_description_2']);
    $hero_slide_button_text_2 = sanitize($_POST['hero_slide_button_text_2']);
    $hero_slide_button_url_2 = sanitize($_POST['hero_slide_button_url_2']);
    
    $hero_slide_title_3 = sanitize($_POST['hero_slide_title_3']);
    $hero_slide_description_3 = sanitize($_POST['hero_slide_description_3']);
    $hero_slide_button_text_3 = sanitize($_POST['hero_slide_button_text_3']);
    $hero_slide_button_url_3 = sanitize($_POST['hero_slide_button_url_3']);
    
    // About Us Content
    $about_story_title = sanitize($_POST['about_story_title']);
    $about_story = sanitize($_POST['about_story']);
    $about_story_continued = sanitize($_POST['about_story_continued']);
    $about_mission = sanitize($_POST['about_mission']);
    $about_vision = sanitize($_POST['about_vision']);
    
    // Team Members
    $team_name_1 = sanitize($_POST['team_name_1']);
    $team_position_1 = sanitize($_POST['team_position_1']);
    $team_linkedin_1 = sanitize($_POST['team_linkedin_1']);
    $team_twitter_1 = sanitize($_POST['team_twitter_1']);
    $team_facebook_1 = sanitize($_POST['team_facebook_1']);
    
    $team_name_2 = sanitize($_POST['team_name_2']);
    $team_position_2 = sanitize($_POST['team_position_2']);
    $team_linkedin_2 = sanitize($_POST['team_linkedin_2']);
    $team_twitter_2 = sanitize($_POST['team_twitter_2']);
    $team_facebook_2 = sanitize($_POST['team_facebook_2']);
    
    $team_name_3 = sanitize($_POST['team_name_3']);
    $team_position_3 = sanitize($_POST['team_position_3']);
    $team_linkedin_3 = sanitize($_POST['team_linkedin_3']);
    $team_twitter_3 = sanitize($_POST['team_twitter_3']);
    $team_facebook_3 = sanitize($_POST['team_facebook_3']);
    
    $team_name_4 = sanitize($_POST['team_name_4']);
    $team_position_4 = sanitize($_POST['team_position_4']);
    $team_linkedin_4 = sanitize($_POST['team_linkedin_4']);
    $team_twitter_4 = sanitize($_POST['team_twitter_4']);
    $team_facebook_4 = sanitize($_POST['team_facebook_4']);
    
    // Contact Us Content
    $contact_page_title = sanitize($_POST['contact_page_title']);
    $contact_page_subtitle = sanitize($_POST['contact_page_subtitle']);
    $business_hours = sanitize($_POST['business_hours']);
    $google_map_embed = $_POST['google_map_embed']; // Don't sanitize to allow HTML
    
    // FAQ Content
    $faq_question_1 = sanitize($_POST['faq_question_1']);
    $faq_answer_1 = sanitize($_POST['faq_answer_1']);
    $faq_question_2 = sanitize($_POST['faq_question_2']);
    $faq_answer_2 = sanitize($_POST['faq_answer_2']);
    $faq_question_3 = sanitize($_POST['faq_question_3']);
    $faq_answer_3 = sanitize($_POST['faq_answer_3']);
    $faq_question_4 = sanitize($_POST['faq_question_4']);
    $faq_answer_4 = sanitize($_POST['faq_answer_4']);
    $faq_question_5 = sanitize($_POST['faq_question_5']);
    $faq_answer_5 = sanitize($_POST['faq_answer_5']);
    
    // Pages Content
    $privacy_policy_content = $_POST['privacy_policy_content']; // Don't sanitize to allow HTML
    $privacy_policy_updated = sanitize($_POST['privacy_policy_updated']);
    $returns_refunds_content = $_POST['returns_refunds_content']; // Don't sanitize to allow HTML
    $returns_refunds_updated = sanitize($_POST['returns_refunds_updated']);
    $faq_intro = sanitize($_POST['faq_intro']);
    $faq_content = $_POST['faq_content']; // Don't sanitize to allow HTML
    
    // Testimonial 1
    $testimonial_1_text = sanitize($_POST['testimonial_1_text']);
    $testimonial_1_name = sanitize($_POST['testimonial_1_name']);
    $testimonial_1_position = sanitize($_POST['testimonial_1_position']);
    
    // Testimonial 2
    $testimonial_2_text = sanitize($_POST['testimonial_2_text']);
    $testimonial_2_name = sanitize($_POST['testimonial_2_name']);
    $testimonial_2_position = sanitize($_POST['testimonial_2_position']);
    
    // Testimonial 3
    $testimonial_3_text = sanitize($_POST['testimonial_3_text']);
    $testimonial_3_name = sanitize($_POST['testimonial_3_name']);
    $testimonial_3_position = sanitize($_POST['testimonial_3_position']);
    
    // Blog 1
    $blog_1_title = sanitize($_POST['blog_1_title']);
    $blog_1_excerpt = sanitize($_POST['blog_1_excerpt']);
    $blog_1_date = sanitize($_POST['blog_1_date']);
    $blog_1_url = sanitize($_POST['blog_1_url']);
    
    // Blog 2
    $blog_2_title = sanitize($_POST['blog_2_title']);
    $blog_2_excerpt = sanitize($_POST['blog_2_excerpt']);
    $blog_2_date = sanitize($_POST['blog_2_date']);
    $blog_2_url = sanitize($_POST['blog_2_url']);
    
    // Blog 3
    $blog_3_title = sanitize($_POST['blog_3_title']);
    $blog_3_excerpt = sanitize($_POST['blog_3_excerpt']);
    $blog_3_date = sanitize($_POST['blog_3_date']);
    $blog_3_url = sanitize($_POST['blog_3_url']);
    
    // Validation
    $errors = [];
    
    if (empty($site_name)) {
        $errors[] = "Site name is required.";
    }
    
    if (empty($site_email) || !filter_var($site_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid site email is required.";
    }
    
    if ($tax_rate < 0 || $tax_rate > 100) {
        $errors[] = "Tax rate must be between 0 and 100.";
    }
    
    if ($free_shipping_min < 0) {
        $errors[] = "Free shipping minimum must be a positive number.";
    }
    
    if ($default_shipping_fee < 0) {
        $errors[] = "Default shipping fee must be a positive number.";
    }
    
    // Process logo upload
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleImageUpload(
            $_FILES['site_logo'],
            'uploads/settings',
            $settings['site_logo'] ?? '',
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
            'logo_'
        );
        
        if ($uploadResult['success']) {
            $site_logo = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['error'];
        }
    } else {
        $site_logo = $settings['site_logo'] ?? '';
        
        // Check if remove logo checkbox is checked
        if (isset($_POST['remove_logo']) && $_POST['remove_logo'] == '1') {
            // Delete the logo file if it exists
            if (!empty($site_logo) && file_exists('../uploads/settings/' . $site_logo)) {
                unlink('../uploads/settings/' . $site_logo);
            }
            $site_logo = ''; // Set to empty to remove from database
        }
    }
    
    // Process favicon upload
    if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleImageUpload(
            $_FILES['site_favicon'],
            'uploads/settings',
            $settings['site_favicon'] ?? '',
            ['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml'],
            'favicon_'
        );
        
        if ($uploadResult['success']) {
            $site_favicon = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['error'];
        }
            } else {
        $site_favicon = $settings['site_favicon'] ?? '';
        
        // Check if remove favicon checkbox is checked
        if (isset($_POST['remove_favicon']) && $_POST['remove_favicon'] == '1') {
            // Delete the favicon file if it exists
            if (!empty($site_favicon) && file_exists('../uploads/settings/' . $site_favicon)) {
                unlink('../uploads/settings/' . $site_favicon);
            }
            $site_favicon = ''; // Set to empty to remove from database
        }
    }
    
    // Process hero image upload
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleImageUpload(
            $_FILES['hero_image'],
            'uploads/settings',
            $settings['hero_image'] ?? '',
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'hero_'
        );
        
        if ($uploadResult['success']) {
            $hero_image = $uploadResult['filename'];
    } else {
            $errors[] = $uploadResult['error'];
        }
    } else {
        $hero_image = $settings['hero_image'] ?? '';
        
        // Check if remove hero image checkbox is checked
        if (isset($_POST['remove_hero_image']) && $_POST['remove_hero_image'] == '1') {
            // Delete the hero image file if it exists
            if (!empty($hero_image) && file_exists('../uploads/settings/' . $hero_image)) {
                unlink('../uploads/settings/' . $hero_image);
            }
            $hero_image = ''; // Set to empty to remove from database
        }
    }
    
    // Process hero slider images
    $hero_slide_images = [];
    for ($i = 1; $i <= 3; $i++) {
        $field_name = 'hero_slide_image_' . $i;
        if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
            $uploadResult = handleImageUpload(
                $_FILES[$field_name],
                'uploads/hero',
                $settings[$field_name] ?? '',
                ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                'hero_slide_' . $i . '_'
            );
            
            if ($uploadResult['success']) {
                $hero_slide_images[$field_name] = $uploadResult['filename'];
        } else {
                $errors[] = $uploadResult['error'];
            }
        } else {
            $hero_slide_images[$field_name] = $settings[$field_name] ?? '';
            
            // Check if remove hero slide image checkbox is checked
            $remove_field = 'remove_hero_slide_image_' . $i;
            if (isset($_POST[$remove_field]) && $_POST[$remove_field] == '1') {
                // Delete the hero slide image file if it exists
                if (!empty($hero_slide_images[$field_name]) && file_exists('../uploads/hero/' . $hero_slide_images[$field_name])) {
                    unlink('../uploads/hero/' . $hero_slide_images[$field_name]);
                }
                $hero_slide_images[$field_name] = ''; // Set to empty to remove from database
            }
        }
    }
    
    // Process about image upload
    if (isset($_FILES['about_image']) && $_FILES['about_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleImageUpload(
            $_FILES['about_image'],
            'uploads/settings',
            $settings['about_image'] ?? '',
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'about_'
        );
        
        if ($uploadResult['success']) {
            $about_image = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['error'];
        }
            } else {
        $about_image = $settings['about_image'] ?? '';
    }
    
    // Process team member images
    $team_images = [];
    for ($i = 1; $i <= 4; $i++) {
        $field_name = 'team_image_' . $i;
        if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
            $uploadResult = handleImageUpload(
                $_FILES[$field_name],
                'uploads/team',
                $settings[$field_name] ?? '',
                ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                'team_' . $i . '_'
            );
            
            if ($uploadResult['success']) {
                $team_images[$field_name] = $uploadResult['filename'];
            } else {
                $errors[] = $uploadResult['error'];
        }
    } else {
            $team_images[$field_name] = $settings[$field_name] ?? '';
        }
    }
    
    // Process testimonial images
    $testimonial_images = [];
    for ($i = 1; $i <= 3; $i++) {
        $field_name = 'testimonial_image_' . $i;
        if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
            $uploadResult = handleImageUpload(
                $_FILES[$field_name],
                'uploads/testimonials',
                $settings[$field_name] ?? '',
                ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                'testimonial_' . $i . '_'
            );
            
            if ($uploadResult['success']) {
                $testimonial_images[$field_name] = $uploadResult['filename'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        } else {
            $testimonial_images[$field_name] = $settings[$field_name] ?? '';
        }
    }
    
    // Process blog images
    $blog_images = [];
    for ($i = 1; $i <= 3; $i++) {
        $field_name = 'blog_image_' . $i;
        if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
            $uploadResult = handleImageUpload(
                $_FILES[$field_name],
                'uploads/blog',
                $settings[$field_name] ?? '',
                ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                'blog_' . $i . '_'
            );
            
            if ($uploadResult['success']) {
                $blog_images[$field_name] = $uploadResult['filename'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        } else {
            $blog_images[$field_name] = $settings[$field_name] ?? '';
        }
    }
    
    // If no errors, update settings
    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Prepare settings array
            $settingsToUpdate = [
                'site_name' => $site_name,
                'site_description' => $site_description,
                'site_email' => $site_email,
                'site_phone' => $site_phone,
                'site_address' => $site_address,
                'site_logo' => $site_logo,
                'site_favicon' => $site_favicon,
                'hero_image' => $hero_image,
                'hero_title' => $hero_title,
                'hero_description' => $hero_description,
                'hero_button_text' => $hero_button_text,
                'hero_button_url' => $hero_button_url,
                'use_hero_slider' => $use_hero_slider,
                'hero_slide_image_1' => $hero_slide_images['hero_slide_image_1'],
                'hero_slide_title_1' => $hero_slide_title_1,
                'hero_slide_description_1' => $hero_slide_description_1,
                'hero_slide_button_text_1' => $hero_slide_button_text_1,
                'hero_slide_button_url_1' => $hero_slide_button_url_1,
                'hero_slide_image_2' => $hero_slide_images['hero_slide_image_2'],
                'hero_slide_title_2' => $hero_slide_title_2,
                'hero_slide_description_2' => $hero_slide_description_2,
                'hero_slide_button_text_2' => $hero_slide_button_text_2,
                'hero_slide_button_url_2' => $hero_slide_button_url_2,
                'hero_slide_image_3' => $hero_slide_images['hero_slide_image_3'],
                'hero_slide_title_3' => $hero_slide_title_3,
                'hero_slide_description_3' => $hero_slide_description_3,
                'hero_slide_button_text_3' => $hero_slide_button_text_3,
                'hero_slide_button_url_3' => $hero_slide_button_url_3,
                'testimonial_image_1' => $testimonial_images['testimonial_image_1'],
                'testimonial_1_text' => $testimonial_1_text,
                'testimonial_1_name' => $testimonial_1_name,
                'testimonial_1_position' => $testimonial_1_position,
                'testimonial_image_2' => $testimonial_images['testimonial_image_2'],
                'testimonial_2_text' => $testimonial_2_text,
                'testimonial_2_name' => $testimonial_2_name,
                'testimonial_2_position' => $testimonial_2_position,
                'testimonial_image_3' => $testimonial_images['testimonial_image_3'],
                'testimonial_3_text' => $testimonial_3_text,
                'testimonial_3_name' => $testimonial_3_name,
                'testimonial_3_position' => $testimonial_3_position,
                'blog_image_1' => $blog_images['blog_image_1'],
                'blog_1_title' => $blog_1_title,
                'blog_1_excerpt' => $blog_1_excerpt,
                'blog_1_date' => $blog_1_date,
                'blog_1_url' => $blog_1_url,
                'blog_image_2' => $blog_images['blog_image_2'],
                'blog_2_title' => $blog_2_title,
                'blog_2_excerpt' => $blog_2_excerpt,
                'blog_2_date' => $blog_2_date,
                'blog_2_url' => $blog_2_url,
                'blog_image_3' => $blog_images['blog_image_3'],
                'blog_3_title' => $blog_3_title,
                'blog_3_excerpt' => $blog_3_excerpt,
                'blog_3_date' => $blog_3_date,
                'blog_3_url' => $blog_3_url,
                'facebook_url' => $facebook_url,
                'twitter_url' => $twitter_url,
                'instagram_url' => $instagram_url,
                'youtube_url' => $youtube_url,
                'meta_title' => $meta_title,
                'meta_description' => $meta_description,
                'meta_keywords' => $meta_keywords,
                'currency_symbol' => $currency_symbol,
                'currency_code' => $currency_code,
                'tax_rate' => $tax_rate,
                'free_shipping_min' => $free_shipping_min,
                'default_shipping_fee' => $default_shipping_fee,
                'about_story_title' => $about_story_title,
                'about_story' => $about_story,
                'about_story_continued' => $about_story_continued,
                'about_mission' => $about_mission,
                'about_vision' => $about_vision,
                'team_name_1' => $team_name_1,
                'team_position_1' => $team_position_1,
                'team_linkedin_1' => $team_linkedin_1,
                'team_twitter_1' => $team_twitter_1,
                'team_facebook_1' => $team_facebook_1,
                'team_name_2' => $team_name_2,
                'team_position_2' => $team_position_2,
                'team_linkedin_2' => $team_linkedin_2,
                'team_twitter_2' => $team_twitter_2,
                'team_facebook_2' => $team_facebook_2,
                'team_name_3' => $team_name_3,
                'team_position_3' => $team_position_3,
                'team_linkedin_3' => $team_linkedin_3,
                'team_twitter_3' => $team_twitter_3,
                'team_facebook_3' => $team_facebook_3,
                'team_name_4' => $team_name_4,
                'team_position_4' => $team_position_4,
                'team_linkedin_4' => $team_linkedin_4,
                'team_twitter_4' => $team_twitter_4,
                'team_facebook_4' => $team_facebook_4,
                'contact_page_title' => $contact_page_title,
                'contact_page_subtitle' => $contact_page_subtitle,
                'business_hours' => $business_hours,
                'google_map_embed' => $google_map_embed,
                'faq_question_1' => $faq_question_1,
                'faq_answer_1' => $faq_answer_1,
                'faq_question_2' => $faq_question_2,
                'faq_answer_2' => $faq_answer_2,
                'faq_question_3' => $faq_question_3,
                'faq_answer_3' => $faq_answer_3,
                'faq_question_4' => $faq_question_4,
                'faq_answer_4' => $faq_answer_4,
                'faq_question_5' => $faq_question_5,
                'faq_answer_5' => $faq_answer_5,
                'about_image' => $about_image,
                'team_image_1' => $team_images['team_image_1'],
                'team_image_2' => $team_images['team_image_2'],
                'team_image_3' => $team_images['team_image_3'],
                'team_image_4' => $team_images['team_image_4'],
                'privacy_policy_content' => $privacy_policy_content,
                'privacy_policy_updated' => $privacy_policy_updated,
                'returns_refunds_content' => $returns_refunds_content,
                'returns_refunds_updated' => $returns_refunds_updated,
                'faq_intro' => $faq_intro,
                'faq_content' => $faq_content
            ];
            
            // Update or insert settings
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            
            foreach ($settingsToUpdate as $key => $value) {
                $stmt->bind_param("ss", $key, $value);
                $stmt->execute();
            }
            
            // Commit transaction
            $conn->commit();
            
            // Update settings array
            $settings = $settingsToUpdate;
            
            // Set success message based on active tab
            $tabNames = [
                'general' => 'General',
                'social' => 'Social Media',
                'seo' => 'SEO',
                'payment' => 'Payment',
                'shipping' => 'Shipping',
                'homepage' => 'Homepage',
                'about' => 'About Us',
                'contact' => 'Contact Us',
                'pages' => 'Pages'
            ];
            
            $tabName = isset($tabNames[$activeTab]) ? $tabNames[$activeTab] : 'Settings';
            flash('success', $tabName . ' settings updated successfully.');
            
            // Redirect to the active tab
            header("Location: settings.php#" . $activeTab);
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php include '../includes/admin-sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">General Settings</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="initialize-settings.php" class="btn btn-sm btn-outline-secondary" onclick="return confirm('This will reset all settings to default values. Are you sure?');">
                        <i class="fas fa-sync-alt"></i> Initialize Settings
                    </a>
                </div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php flash(); ?>
            
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="POST" action="settings.php" enctype="multipart/form-data" id="settingsForm">
                        <div class="nav-tabs-responsive">
                            <ul class="nav nav-tabs flex-nowrap" id="settingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">General</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab" aria-controls="social" aria-selected="false">Social Media</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button" role="tab" aria-controls="seo" aria-selected="false">SEO</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab" aria-controls="payment" aria-selected="false">Payment</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab" aria-controls="shipping" aria-selected="false">Shipping</button>
                            </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="homepage-tab" data-bs-toggle="tab" data-bs-target="#homepage" type="button" role="tab" aria-controls="homepage" aria-selected="false">Homepage</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab" aria-controls="about" aria-selected="false">About Us</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Contact Us</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pages-tab" data-bs-toggle="tab" data-bs-target="#pages" type="button" role="tab" aria-controls="pages" aria-selected="false">Pages</button>
                                </li>
                        </ul>
                        </div>
                        
                        <div class="tab-content p-4" id="settingsTabsContent">
                            <!-- General Settings -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                <h4 class="mb-3">Site Information</h4>
                                
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Site Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_description" class="form-label">Site Description</label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="site_email" class="form-label">Site Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="site_email" name="site_email" value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="site_phone" class="form-label">Site Phone</label>
                                        <input type="text" class="form-control" id="site_phone" name="site_phone" value="<?php echo htmlspecialchars($settings['site_phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="site_address" class="form-label">Site Address</label>
                                    <textarea class="form-control" id="site_address" name="site_address" rows="3"><?php echo htmlspecialchars($settings['site_address'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="site_logo" class="form-label">Site Logo</label>
                                        <?php if (!empty($settings['site_logo'])): ?>
                                        <div class="mb-2 image-preview-container">
                                            <img src="<?php echo URL_ROOT . '/uploads/settings/' . $settings['site_logo']; ?>" alt="Site Logo" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="remove_logo" name="remove_logo" value="1">
                                            <label class="form-check-label" for="remove_logo">
                                                Remove current logo
                                            </label>
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="site_logo" name="site_logo" accept="image/*">
                                        <small class="form-text text-muted">Recommended size: 200x50 pixels</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="site_favicon" class="form-label">Site Favicon</label>
                                        <?php if (!empty($settings['site_favicon'])): ?>
                                        <div class="mb-2 image-preview-container">
                                            <img src="<?php echo URL_ROOT . '/uploads/settings/' . $settings['site_favicon']; ?>" alt="Site Favicon" class="img-thumbnail" style="max-height: 32px;">
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="remove_favicon" name="remove_favicon" value="1">
                                            <label class="form-check-label" for="remove_favicon">
                                                Remove current favicon
                                            </label>
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="site_favicon" name="site_favicon" accept=".ico,.png,.svg">
                                        <small class="form-text text-muted">Recommended size: 32x32 pixels</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Social Media Settings -->
                            <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab">
                                <h4 class="mb-3">Social Media Links</h4>
                                
                                <div class="mb-3">
                                    <label for="facebook_url" class="form-label">Facebook URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fab fa-facebook-f"></i></span>
                                        <input type="url" class="form-control" id="facebook_url" name="facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="twitter_url" class="form-label">Twitter URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fab fa-twitter"></i></span>
                                        <input type="url" class="form-control" id="twitter_url" name="twitter_url" value="<?php echo htmlspecialchars($settings['twitter_url'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="instagram_url" class="form-label">Instagram URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                        <input type="url" class="form-control" id="instagram_url" name="instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="youtube_url" class="form-label">YouTube URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fab fa-youtube"></i></span>
                                        <input type="url" class="form-control" id="youtube_url" name="youtube_url" value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- SEO Settings -->
                            <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                                <h4 class="mb-3">SEO Settings</h4>
                                
                                <div class="mb-3">
                                    <label for="meta_title" class="form-label">Default Meta Title</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title" value="<?php echo htmlspecialchars($settings['meta_title'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="meta_description" class="form-label">Default Meta Description</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description" rows="3"><?php echo htmlspecialchars($settings['meta_description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="meta_keywords" class="form-label">Default Meta Keywords</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="<?php echo htmlspecialchars($settings['meta_keywords'] ?? ''); ?>">
                                    <small class="form-text text-muted">Separate keywords with commas</small>
                                </div>
                            </div>
                            
                            <!-- Payment Settings -->
                            <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
                                <h4 class="mb-3">Payment Settings</h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="currency_symbol" class="form-label">Currency Symbol</label>
                                        <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" value="<?php echo htmlspecialchars($settings['currency_symbol'] ?? '$'); ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="currency_code" class="form-label">Currency Code</label>
                                        <input type="text" class="form-control" id="currency_code" name="currency_code" value="<?php echo htmlspecialchars($settings['currency_code'] ?? 'USD'); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                                    <input type="number" class="form-control" id="tax_rate" name="tax_rate" step="0.01" min="0" max="100" value="<?php echo htmlspecialchars($settings['tax_rate'] ?? '0'); ?>">
                                </div>
                            </div>
                            
                            <!-- Shipping Settings -->
                            <div class="tab-pane fade" id="shipping" role="tabpanel" aria-labelledby="shipping-tab">
                                <h4 class="mb-3">Shipping Settings</h4>
                                
                                <div class="mb-3">
                                    <label for="free_shipping_min" class="form-label">Free Shipping Minimum Order Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?php echo htmlspecialchars($settings['currency_symbol'] ?? '$'); ?></span>
                                        <input type="number" class="form-control" id="free_shipping_min" name="free_shipping_min" step="0.01" min="0" value="<?php echo htmlspecialchars($settings['free_shipping_min'] ?? '0'); ?>">
                                    </div>
                                    <small class="form-text text-muted">Set to 0 to disable free shipping</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="default_shipping_fee" class="form-label">Default Shipping Fee</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?php echo htmlspecialchars($settings['currency_symbol'] ?? '$'); ?></span>
                                        <input type="number" class="form-control" id="default_shipping_fee" name="default_shipping_fee" step="0.01" min="0" value="<?php echo htmlspecialchars($settings['default_shipping_fee'] ?? '0'); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Homepage Settings -->
                            <div class="tab-pane fade" id="homepage" role="tabpanel" aria-labelledby="homepage-tab">
                                <h4 class="mb-3">Hero Section</h4>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="use_hero_slider" name="use_hero_slider" value="1" <?php echo (isset($settings['use_hero_slider']) && $settings['use_hero_slider'] == '1') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="use_hero_slider">
                                        Use Hero Slider (multiple images)
                                    </label>
                                </div>
                                
                                <div id="single-hero-section" class="<?php echo (isset($settings['use_hero_slider']) && $settings['use_hero_slider'] == '1') ? 'd-none' : ''; ?>">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="hero_image" class="form-label">Hero Image</label>
                                            <?php if (!empty($settings['hero_image'])): ?>
                                            <div class="mb-2 image-preview-container">
                                                <img src="<?php echo getImageUrl($settings['hero_image'], 'uploads/settings'); ?>" alt="Hero Image" class="img-thumbnail" style="max-height: 100px;">
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" id="remove_hero_image" name="remove_hero_image" value="1">
                                                <label class="form-check-label" for="remove_hero_image">
                                                    Remove current hero image
                                                </label>
                                            </div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" id="hero_image" name="hero_image" accept="image/*">
                                            <small class="form-text text-muted">Recommended size: 1200x600 pixels</small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="hero_title" class="form-label">Hero Title</label>
                                            <input type="text" class="form-control" id="hero_title" name="hero_title" value="<?php echo htmlspecialchars($settings['hero_title'] ?? 'Welcome to our Store'); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="hero_description" class="form-label">Hero Description</label>
                                        <textarea class="form-control" id="hero_description" name="hero_description" rows="3"><?php echo htmlspecialchars($settings['hero_description'] ?? 'Discover amazing products with great deals. Shop now and enjoy exclusive offers on our wide range of products.'); ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="hero_button_text" class="form-label">Button Text</label>
                                            <input type="text" class="form-control" id="hero_button_text" name="hero_button_text" value="<?php echo htmlspecialchars($settings['hero_button_text'] ?? 'Shop Now'); ?>">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="hero_button_url" class="form-label">Button URL</label>
                                            <input type="text" class="form-control" id="hero_button_url" name="hero_button_url" value="<?php echo htmlspecialchars($settings['hero_button_url'] ?? '/shop.php'); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="hero-slider-section" class="<?php echo (isset($settings['use_hero_slider']) && $settings['use_hero_slider'] == '1') ? '' : 'd-none'; ?>">
                                    <!-- Hero Slider 1 -->
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0">Hero Slide 1</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="hero_slide_image_1" class="form-label">Slide Image</label>
                                                    <?php if (!empty($settings['hero_slide_image_1'])): ?>
                                                    <div class="mb-2 image-preview-container">
                                                        <img src="<?php echo getImageUrl($settings['hero_slide_image_1'], 'uploads/hero'); ?>" alt="Hero Slide 1" class="img-thumbnail" style="max-height: 100px;">
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="remove_hero_slide_image_1" name="remove_hero_slide_image_1" value="1">
                                                        <label class="form-check-label" for="remove_hero_slide_image_1">
                                                            Remove current slide image
                                                        </label>
                                                    </div>
                                                    <?php endif; ?>
                                                    <input type="file" class="form-control" id="hero_slide_image_1" name="hero_slide_image_1" accept="image/*">
                                                    <small class="form-text text-muted">Recommended size: 1920x600 pixels</small>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label for="hero_slide_title_1" class="form-label">Slide Title</label>
                                                    <input type="text" class="form-control" id="hero_slide_title_1" name="hero_slide_title_1" value="<?php echo htmlspecialchars($settings['hero_slide_title_1'] ?? 'Welcome to our Store'); ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="hero_slide_description_1" class="form-label">Slide Description</label>
                                                <textarea class="form-control" id="hero_slide_description_1" name="hero_slide_description_1" rows="2"><?php echo htmlspecialchars($settings['hero_slide_description_1'] ?? 'Discover amazing products with great deals. Shop now and enjoy exclusive offers.'); ?></textarea>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="hero_slide_button_text_1" class="form-label">Button Text</label>
                                                    <input type="text" class="form-control" id="hero_slide_button_text_1" name="hero_slide_button_text_1" value="<?php echo htmlspecialchars($settings['hero_slide_button_text_1'] ?? 'Shop Now'); ?>">
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label for="hero_slide_button_url_1" class="form-label">Button URL</label>
                                                    <input type="text" class="form-control" id="hero_slide_button_url_1" name="hero_slide_button_url_1" value="<?php echo htmlspecialchars($settings['hero_slide_button_url_1'] ?? '/shop.php'); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Hero Slider 2 -->
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0">Hero Slide 2</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="hero_slide_image_2" class="form-label">Slide Image</label>
                                                    <?php if (!empty($settings['hero_slide_image_2'])): ?>
                                                    <div class="mb-2 image-preview-container">
                                                        <img src="<?php echo getImageUrl($settings['hero_slide_image_2'], 'uploads/hero'); ?>" alt="Hero Slide 2" class="img-thumbnail" style="max-height: 100px;">
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="remove_hero_slide_image_2" name="remove_hero_slide_image_2" value="1">
                                                        <label class="form-check-label" for="remove_hero_slide_image_2">
                                                            Remove current slide image
                                                        </label>
                                                    </div>
                                                    <?php endif; ?>
                                                    <input type="file" class="form-control" id="hero_slide_image_2" name="hero_slide_image_2" accept="image/*">
                                                    <small class="form-text text-muted">Recommended size: 1920x600 pixels</small>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label for="hero_slide_title_2" class="form-label">Slide Title</label>
                                                    <input type="text" class="form-control" id="hero_slide_title_2" name="hero_slide_title_2" value="<?php echo htmlspecialchars($settings['hero_slide_title_2'] ?? 'New Arrivals'); ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="hero_slide_description_2" class="form-label">Slide Description</label>
                                                <textarea class="form-control" id="hero_slide_description_2" name="hero_slide_description_2" rows="2"><?php echo htmlspecialchars($settings['hero_slide_description_2'] ?? 'Check out our latest products and collections. Find the perfect items for you.'); ?></textarea>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="hero_slide_button_text_2" class="form-label">Button Text</label>
                                                    <input type="text" class="form-control" id="hero_slide_button_text_2" name="hero_slide_button_text_2" value="<?php echo htmlspecialchars($settings['hero_slide_button_text_2'] ?? 'View Collection'); ?>">
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label for="hero_slide_button_url_2" class="form-label">Button URL</label>
                                                    <input type="text" class="form-control" id="hero_slide_button_url_2" name="hero_slide_button_url_2" value="<?php echo htmlspecialchars($settings['hero_slide_button_url_2'] ?? '/shop.php?new=1'); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Hero Slider 3 -->
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0">Hero Slide 3</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="hero_slide_image_3" class="form-label">Slide Image</label>
                                                    <?php if (!empty($settings['hero_slide_image_3'])): ?>
                                                    <div class="mb-2 image-preview-container">
                                                        <img src="<?php echo getImageUrl($settings['hero_slide_image_3'], 'uploads/hero'); ?>" alt="Hero Slide 3" class="img-thumbnail" style="max-height: 100px;">
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="remove_hero_slide_image_3" name="remove_hero_slide_image_3" value="1">
                                                        <label class="form-check-label" for="remove_hero_slide_image_3">
                                                            Remove current slide image
                                                        </label>
                                                    </div>
                                                    <?php endif; ?>
                                                    <input type="file" class="form-control" id="hero_slide_image_3" name="hero_slide_image_3" accept="image/*">
                                                    <small class="form-text text-muted">Recommended size: 1920x600 pixels</small>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label for="hero_slide_title_3" class="form-label">Slide Title</label>
                                                    <input type="text" class="form-control" id="hero_slide_title_3" name="hero_slide_title_3" value="<?php echo htmlspecialchars($settings['hero_slide_title_3'] ?? 'Special Offers'); ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="hero_slide_description_3" class="form-label">Slide Description</label>
                                                <textarea class="form-control" id="hero_slide_description_3" name="hero_slide_description_3" rows="2"><?php echo htmlspecialchars($settings['hero_slide_description_3'] ?? 'Get up to 50% off on selected items. Limited time offer, don\'t miss out!'); ?></textarea>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="hero_slide_button_text_3" class="form-label">Button Text</label>
                                                    <input type="text" class="form-control" id="hero_slide_button_text_3" name="hero_slide_button_text_3" value="<?php echo htmlspecialchars($settings['hero_slide_button_text_3'] ?? 'Shop Sale'); ?>">
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label for="hero_slide_button_url_3" class="form-label">Button URL</label>
                                                    <input type="text" class="form-control" id="hero_slide_button_url_3" name="hero_slide_button_url_3" value="<?php echo htmlspecialchars($settings['hero_slide_button_url_3'] ?? '/shop.php?sale=1'); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <h4 class="mt-4 mb-3">Testimonials Section</h4>
                                
                                <!-- Testimonial 1 -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Testimonial 1</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="testimonial_image_1" class="form-label">Image</label>
                                                <?php if (!empty($settings['testimonial_image_1'])): ?>
                                                <div class="mb-2 image-preview-container">
                                                    <img src="<?php echo getImageUrl($settings['testimonial_image_1'], 'uploads/testimonials'); ?>" alt="Testimonial 1" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" id="testimonial_image_1" name="testimonial_image_1" accept="image/*">
                                                <small class="form-text text-muted">Recommended size: 100x100 pixels</small>
                                            </div>
                                            
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label for="testimonial_1_text" class="form-label">Testimonial Text</label>
                                                    <textarea class="form-control" id="testimonial_1_text" name="testimonial_1_text" rows="3"><?php echo htmlspecialchars($settings['testimonial_1_text'] ?? "I'm extremely satisfied with the quality of products and the excellent customer service. Will definitely shop here again!"); ?></textarea>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="testimonial_1_name" class="form-label">Name</label>
                                                        <input type="text" class="form-control" id="testimonial_1_name" name="testimonial_1_name" value="<?php echo htmlspecialchars($settings['testimonial_1_name'] ?? 'John Doe'); ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-6 mb-3">
                                                        <label for="testimonial_1_position" class="form-label">Position</label>
                                                        <input type="text" class="form-control" id="testimonial_1_position" name="testimonial_1_position" value="<?php echo htmlspecialchars($settings['testimonial_1_position'] ?? 'Regular Customer'); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Testimonial 2 -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Testimonial 2</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="testimonial_image_2" class="form-label">Image</label>
                                                <?php if (!empty($settings['testimonial_image_2'])): ?>
                                                <div class="mb-2 image-preview-container">
                                                    <img src="<?php echo getImageUrl($settings['testimonial_image_2'], 'uploads/testimonials'); ?>" alt="Testimonial 2" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" id="testimonial_image_2" name="testimonial_image_2" accept="image/*">
                                                <small class="form-text text-muted">Recommended size: 100x100 pixels</small>
                                            </div>
                                            
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label for="testimonial_2_text" class="form-label">Testimonial Text</label>
                                                    <textarea class="form-control" id="testimonial_2_text" name="testimonial_2_text" rows="3"><?php echo htmlspecialchars($settings['testimonial_2_text'] ?? "Fast shipping and great prices. The products are of high quality and exactly as described. Highly recommended!"); ?></textarea>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="testimonial_2_name" class="form-label">Name</label>
                                                        <input type="text" class="form-control" id="testimonial_2_name" name="testimonial_2_name" value="<?php echo htmlspecialchars($settings['testimonial_2_name'] ?? 'Jane Smith'); ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-6 mb-3">
                                                        <label for="testimonial_2_position" class="form-label">Position</label>
                                                        <input type="text" class="form-control" id="testimonial_2_position" name="testimonial_2_position" value="<?php echo htmlspecialchars($settings['testimonial_2_position'] ?? 'Loyal Customer'); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Testimonial 3 -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Testimonial 3</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="testimonial_image_3" class="form-label">Image</label>
                                                <?php if (!empty($settings['testimonial_image_3'])): ?>
                                                <div class="mb-2 image-preview-container">
                                                    <img src="<?php echo getImageUrl($settings['testimonial_image_3'], 'uploads/testimonials'); ?>" alt="Testimonial 3" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" id="testimonial_image_3" name="testimonial_image_3" accept="image/*">
                                                <small class="form-text text-muted">Recommended size: 100x100 pixels</small>
                                            </div>
                                            
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label for="testimonial_3_text" class="form-label">Testimonial Text</label>
                                                    <textarea class="form-control" id="testimonial_3_text" name="testimonial_3_text" rows="3"><?php echo htmlspecialchars($settings['testimonial_3_text'] ?? "The website is easy to navigate, and the checkout process is smooth. I received my order earlier than expected. Great experience!"); ?></textarea>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="testimonial_3_name" class="form-label">Name</label>
                                                        <input type="text" class="form-control" id="testimonial_3_name" name="testimonial_3_name" value="<?php echo htmlspecialchars($settings['testimonial_3_name'] ?? 'Mike Johnson'); ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-6 mb-3">
                                                        <label for="testimonial_3_position" class="form-label">Position</label>
                                                        <input type="text" class="form-control" id="testimonial_3_position" name="testimonial_3_position" value="<?php echo htmlspecialchars($settings['testimonial_3_position'] ?? 'New Customer'); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <h4 class="mt-4 mb-3">Blog Section</h4>
                                
                                <!-- Blog Post 1 -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Blog Post 1</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="blog_image_1" class="form-label">Image</label>
                                                <?php if (!empty($settings['blog_image_1'])): ?>
                                                <div class="mb-2 image-preview-container">
                                                    <img src="<?php echo getImageUrl($settings['blog_image_1'], 'uploads/blog'); ?>" alt="Blog 1" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" id="blog_image_1" name="blog_image_1" accept="image/*">
                                                <small class="form-text text-muted">Recommended size: 400x300 pixels</small>
                                            </div>
                                            
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label for="blog_1_title" class="form-label">Title</label>
                                                    <input type="text" class="form-control" id="blog_1_title" name="blog_1_title" value="<?php echo htmlspecialchars($settings['blog_1_title'] ?? 'Summer Fashion Trends'); ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="blog_1_excerpt" class="form-label">Excerpt</label>
                                                    <textarea class="form-control" id="blog_1_excerpt" name="blog_1_excerpt" rows="2"><?php echo htmlspecialchars($settings['blog_1_excerpt'] ?? 'Discover the hottest fashion trends for this summer season...'); ?></textarea>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="blog_1_date" class="form-label">Date (e.g., "15 Jun")</label>
                                                        <input type="text" class="form-control" id="blog_1_date" name="blog_1_date" value="<?php echo htmlspecialchars($settings['blog_1_date'] ?? '15 Jun'); ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-6 mb-3">
                                                        <label for="blog_1_url" class="form-label">URL</label>
                                                        <input type="text" class="form-control" id="blog_1_url" name="blog_1_url" value="<?php echo htmlspecialchars($settings['blog_1_url'] ?? '#'); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Blog Post 2 -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Blog Post 2</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="blog_image_2" class="form-label">Image</label>
                                                <?php if (!empty($settings['blog_image_2'])): ?>
                                                <div class="mb-2 image-preview-container">
                                                    <img src="<?php echo getImageUrl($settings['blog_image_2'], 'uploads/blog'); ?>" alt="Blog 2" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" id="blog_image_2" name="blog_image_2" accept="image/*">
                                                <small class="form-text text-muted">Recommended size: 400x300 pixels</small>
                                            </div>
                                            
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label for="blog_2_title" class="form-label">Title</label>
                                                    <input type="text" class="form-control" id="blog_2_title" name="blog_2_title" value="<?php echo htmlspecialchars($settings['blog_2_title'] ?? 'Top 10 Gadgets of 2023'); ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="blog_2_excerpt" class="form-label">Excerpt</label>
                                                    <textarea class="form-control" id="blog_2_excerpt" name="blog_2_excerpt" rows="2"><?php echo htmlspecialchars($settings['blog_2_excerpt'] ?? 'Check out our list of the best tech gadgets released this year...'); ?></textarea>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="blog_2_date" class="form-label">Date (e.g., "10 Jun")</label>
                                                        <input type="text" class="form-control" id="blog_2_date" name="blog_2_date" value="<?php echo htmlspecialchars($settings['blog_2_date'] ?? '10 Jun'); ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-6 mb-3">
                                                        <label for="blog_2_url" class="form-label">URL</label>
                                                        <input type="text" class="form-control" id="blog_2_url" name="blog_2_url" value="<?php echo htmlspecialchars($settings['blog_2_url'] ?? '#'); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Blog Post 3 -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Blog Post 3</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="blog_image_3" class="form-label">Image</label>
                                                <?php if (!empty($settings['blog_image_3'])): ?>
                                                <div class="mb-2 image-preview-container">
                                                    <img src="<?php echo getImageUrl($settings['blog_image_3'], 'uploads/blog'); ?>" alt="Blog 3" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" id="blog_image_3" name="blog_image_3" accept="image/*">
                                                <small class="form-text text-muted">Recommended size: 400x300 pixels</small>
                                            </div>
                                            
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label for="blog_3_title" class="form-label">Title</label>
                                                    <input type="text" class="form-control" id="blog_3_title" name="blog_3_title" value="<?php echo htmlspecialchars($settings['blog_3_title'] ?? 'Home Decor Ideas'); ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="blog_3_excerpt" class="form-label">Excerpt</label>
                                                    <textarea class="form-control" id="blog_3_excerpt" name="blog_3_excerpt" rows="2"><?php echo htmlspecialchars($settings['blog_3_excerpt'] ?? 'Transform your living space with these creative home decor ideas...'); ?></textarea>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="blog_3_date" class="form-label">Date (e.g., "05 Jun")</label>
                                                        <input type="text" class="form-control" id="blog_3_date" name="blog_3_date" value="<?php echo htmlspecialchars($settings['blog_3_date'] ?? '05 Jun'); ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-6 mb-3">
                                                        <label for="blog_3_url" class="form-label">URL</label>
                                                        <input type="text" class="form-control" id="blog_3_url" name="blog_3_url" value="<?php echo htmlspecialchars($settings['blog_3_url'] ?? '#'); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- About Us Settings -->
                            <div class="tab-pane fade" id="about" role="tabpanel" aria-labelledby="about-tab">
                                <h4 class="mb-3">About Us Page Settings</h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="about_image" class="form-label">About Us Image</label>
                                        <?php if (!empty($settings['about_image'])): ?>
                                        <div class="mb-2 image-preview-container">
                                            <img src="<?php echo getImageUrl($settings['about_image'], 'uploads/settings'); ?>" alt="About Us Image" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="about_image" name="about_image" accept="image/*">
                                        <small class="form-text text-muted">Recommended size: 600x400 pixels</small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="about_story_title" class="form-label">Our Story Title</label>
                                    <input type="text" class="form-control" id="about_story_title" name="about_story_title" value="<?php echo htmlspecialchars($settings['about_story_title'] ?? 'Our Story'); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="about_story" class="form-label">Our Story Content (First Paragraph)</label>
                                    <textarea class="form-control" id="about_story" name="about_story" rows="3"><?php echo htmlspecialchars($settings['about_story'] ?? 'Founded in 2010, ' . SITE_NAME . ' has been at the forefront of e-commerce innovation, providing customers with high-quality products and exceptional service. What started as a small online store has grown into a trusted marketplace serving customers worldwide.'); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="about_story_continued" class="form-label">Our Story Content (Second Paragraph)</label>
                                    <textarea class="form-control" id="about_story_continued" name="about_story_continued" rows="3"><?php echo htmlspecialchars($settings['about_story_continued'] ?? 'Our journey has been defined by our commitment to customer satisfaction and our passion for delivering value. We believe in building lasting relationships with our customers, suppliers, and partners.'); ?></textarea>
                                </div>
                                
                                <h5 class="mt-4 mb-3">Mission & Vision</h5>
                                
                                <div class="mb-3">
                                    <label for="about_mission" class="form-label">Our Mission</label>
                                    <textarea class="form-control" id="about_mission" name="about_mission" rows="3"><?php echo htmlspecialchars($settings['about_mission'] ?? 'Our mission is to provide customers with a seamless shopping experience, offering high-quality products at competitive prices. We strive to exceed customer expectations through exceptional service, innovative solutions, and a commitment to continuous improvement.'); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="about_vision" class="form-label">Our Vision</label>
                                    <textarea class="form-control" id="about_vision" name="about_vision" rows="3"><?php echo htmlspecialchars($settings['about_vision'] ?? 'Our vision is to become the leading e-commerce platform, recognized for our integrity, quality, and customer-centric approach. We aim to set new standards in online shopping, making it accessible, enjoyable, and rewarding for everyone.'); ?></textarea>
                                </div>
                                
                                <h5 class="mt-4 mb-3">Team Members</h5>
                                
                                <!-- Team Member 1 -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">Team Member 1</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="team_image_1" class="form-label">Image</label>
                                                <?php if (!empty($settings['team_image_1'])): ?>
                                                <div class="mb-2 image-preview-container">
                                                    <img src="<?php echo getImageUrl($settings['team_image_1'], 'uploads/team'); ?>" alt="Team Member 1" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" id="team_image_1" name="team_image_1" accept="image/*">
                                                <small class="form-text text-muted">Recommended size: 300x300 pixels</small>
                                            </div>
                                            
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label for="team_name_1" class="form-label">Name</label>
                                                    <input type="text" class="form-control" id="team_name_1" name="team_name_1" value="<?php echo htmlspecialchars($settings['team_name_1'] ?? 'John Doe'); ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="team_position_1" class="form-label">Position</label>
                                                    <input type="text" class="form-control" id="team_position_1" name="team_position_1" value="<?php echo htmlspecialchars($settings['team_position_1'] ?? 'CEO & Founder'); ?>">
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label for="team_linkedin_1" class="form-label">LinkedIn URL</label>
                                                        <input type="url" class="form-control" id="team_linkedin_1" name="team_linkedin_1" value="<?php echo htmlspecialchars($settings['team_linkedin_1'] ?? ''); ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-4 mb-3">
                                                        <label for="team_twitter_1" class="form-label">Twitter URL</label>
                                                        <input type="url" class="form-control" id="team_twitter_1" name="team_twitter_1" value="<?php echo htmlspecialchars($settings['team_twitter_1'] ?? ''); ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-4 mb-3">
                                                        <label for="team_facebook_1" class="form-label">Facebook URL</label>
                                                        <input type="url" class="form-control" id="team_facebook_1" name="team_facebook_1" value="<?php echo htmlspecialchars($settings['team_facebook_1'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Team Member 2 -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">Team Member 2</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="team_image_2" class="form-label">Image</label>
                                                <?php if (!empty($settings['team_image_2'])): ?>
                                                <div class="mb-2 image-preview-container">
                                                    <img src="<?php echo getImageUrl($settings['team_image_2'], 'uploads/team'); ?>" alt="Team Member 2" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" id="team_image_2" name="team_image_2" accept="image/*">
                                                <small class="form-text text-muted">Recommended size: 300x300 pixels</small>
                                            </div>
                                            
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label for="team_name_2" class="form-label">Name</label>
                                                    <input type="text" class="form-control" id="team_name_2" name="team_name_2" value="<?php echo htmlspecialchars($settings['team_name_2'] ?? 'Jane Smith'); ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="team_position_2" class="form-label">Position</label>
                                                    <input type="text" class="form-control" id="team_position_2" name="team_position_2" value="<?php echo htmlspecialchars($settings['team_position_2'] ?? 'Operations Manager'); ?>">
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label for="team_linkedin_2" class="form-label">LinkedIn URL</label>
                                                        <input type="url" class="form-control" id="team_linkedin_2" name="team_linkedin_2" value="<?php echo htmlspecialchars($settings['team_linkedin_2'] ?? ''); ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-4 mb-3">
                                                        <label for="team_twitter_2" class="form-label">Twitter URL</label>
                                                        <input type="url" class="form-control" id="team_twitter_2" name="team_twitter_2" value="<?php echo htmlspecialchars($settings['team_twitter_2'] ?? ''); ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-4 mb-3">
                                                        <label for="team_facebook_2" class="form-label">Facebook URL</label>
                                                        <input type="url" class="form-control" id="team_facebook_2" name="team_facebook_2" value="<?php echo htmlspecialchars($settings['team_facebook_2'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Team Member 3 -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">Team Member 3</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="team_image_3" class="form-label">Image</label>
                                                <?php if (!empty($settings['team_image_3'])): ?>
                                                <div class="mb-2 image-preview-container">
                                                    <img src="<?php echo getImageUrl($settings['team_image_3'], 'uploads/team'); ?>" alt="Team Member 3" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" id="team_image_3" name="team_image_3" accept="image/*">
                                                <small class="form-text text-muted">Recommended size: 300x300 pixels</small>
                                            </div>
                                            
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label for="team_name_3" class="form-label">Name</label>
                                                    <input type="text" class="form-control" id="team_name_3" name="team_name_3" value="<?php echo htmlspecialchars($settings['team_name_3'] ?? 'Michael Johnson'); ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="team_position_3" class="form-label">Position</label>
                                                    <input type="text" class="form-control" id="team_position_3" name="team_position_3" value="<?php echo htmlspecialchars($settings['team_position_3'] ?? 'Marketing Director'); ?>">
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label for="team_linkedin_3" class="form-label">LinkedIn URL</label>
                                                        <input type="url" class="form-control" id="team_linkedin_3" name="team_linkedin_3" value="<?php echo htmlspecialchars($settings['team_linkedin_3'] ?? ''); ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-4 mb-3">
                                                        <label for="team_twitter_3" class="form-label">Twitter URL</label>
                                                        <input type="url" class="form-control" id="team_twitter_3" name="team_twitter_3" value="<?php echo htmlspecialchars($settings['team_twitter_3'] ?? ''); ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-4 mb-3">
                                                        <label for="team_facebook_3" class="form-label">Facebook URL</label>
                                                        <input type="url" class="form-control" id="team_facebook_3" name="team_facebook_3" value="<?php echo htmlspecialchars($settings['team_facebook_3'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Team Member 4 -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">Team Member 4</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="team_image_4" class="form-label">Image</label>
                                                <?php if (!empty($settings['team_image_4'])): ?>
                                                <div class="mb-2 image-preview-container">
                                                    <img src="<?php echo getImageUrl($settings['team_image_4'], 'uploads/team'); ?>" alt="Team Member 4" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                                <?php endif; ?>
                                                <input type="file" class="form-control" id="team_image_4" name="team_image_4" accept="image/*">
                                                <small class="form-text text-muted">Recommended size: 300x300 pixels</small>
                                            </div>
                                            
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label for="team_name_4" class="form-label">Name</label>
                                                    <input type="text" class="form-control" id="team_name_4" name="team_name_4" value="<?php echo htmlspecialchars($settings['team_name_4'] ?? 'Emily Brown'); ?>">
                                                </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="team_position_4" class="form-label">Position</label>
                                                        <input type="text" class="form-control" id="team_position_4" name="team_position_4" value="<?php echo htmlspecialchars($settings['team_position_4'] ?? 'Customer Support Lead'); ?>">
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label for="team_linkedin_4" class="form-label">LinkedIn URL</label>
                                                        <input type="url" class="form-control" id="team_linkedin_4" name="team_linkedin_4" value="<?php echo htmlspecialchars($settings['team_linkedin_4'] ?? ''); ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-4 mb-3">
                                                        <label for="team_twitter_4" class="form-label">Twitter URL</label>
                                                        <input type="url" class="form-control" id="team_twitter_4" name="team_twitter_4" value="<?php echo htmlspecialchars($settings['team_twitter_4'] ?? ''); ?>">
                                                    </div>
                                                    
                                                    <div class="col-md-4 mb-3">
                                                        <label for="team_facebook_4" class="form-label">Facebook URL</label>
                                                        <input type="url" class="form-control" id="team_facebook_4" name="team_facebook_4" value="<?php echo htmlspecialchars($settings['team_facebook_4'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Contact Us Settings -->
                            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                                <h4 class="mb-3">Contact Us Page Settings</h4>
                                
                                <h5 class="mt-4 mb-3">Business Information</h5>
                                
                                <div class="mb-3">
                                    <label for="contact_page_title" class="form-label">Page Title</label>
                                    <input type="text" class="form-control" id="contact_page_title" name="contact_page_title" value="<?php echo htmlspecialchars($settings['contact_page_title'] ?? 'Get In Touch'); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="contact_page_subtitle" class="form-label">Page Subtitle</label>
                                    <input type="text" class="form-control" id="contact_page_subtitle" name="contact_page_subtitle" value="<?php echo htmlspecialchars($settings['contact_page_subtitle'] ?? 'We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.'); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="business_hours" class="form-label">Business Hours</label>
                                    <textarea class="form-control" id="business_hours" name="business_hours" rows="3"><?php echo htmlspecialchars($settings['business_hours'] ?? "Monday - Friday: 9:00 AM - 5:00 PM\nSaturday: 10:00 AM - 3:00 PM\nSunday: Closed"); ?></textarea>
                                    <small class="form-text text-muted">Enter each line as a separate day</small>
                                </div>
                                
                                <h5 class="mt-4 mb-3">Google Map</h5>
                                
                                <div class="mb-3">
                                    <label for="google_map_embed" class="form-label">Google Map Embed Code</label>
                                    <textarea class="form-control" id="google_map_embed" name="google_map_embed" rows="3"><?php echo htmlspecialchars($settings['google_map_embed'] ?? '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.215256349542!2d-73.98784532342249!3d40.75798833440646!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25855c6480299%3A0x55194ec5a1ae072e!2sTimes%20Square!5m2!1s0x89c25855c6480299%3A0x55194ec5a1ae072e!2sTimes%20Square" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>'); ?></textarea>
                                    <small class="form-text text-muted">Paste the embed code from Google Maps</small>
                                </div>
                                
                                <h5 class="mt-4 mb-3">FAQ Section</h5>
                                
                                <!-- FAQ 1 -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">FAQ 1</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="faq_question_1" class="form-label">Question</label>
                                            <input type="text" class="form-control" id="faq_question_1" name="faq_question_1" value="<?php echo htmlspecialchars($settings['faq_question_1'] ?? 'How can I track my order?'); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="faq_answer_1" class="form-label">Answer</label>
                                            <textarea class="form-control" id="faq_answer_1" name="faq_answer_1" rows="2"><?php echo htmlspecialchars($settings['faq_answer_1'] ?? 'You can track your order by logging into your account and visiting the "My Orders" section. There, you\'ll find a list of all your orders and their current status. Alternatively, you can use the tracking number provided in your order confirmation email.'); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- FAQ 2 -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">FAQ 2</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="faq_question_2" class="form-label">Question</label>
                                            <input type="text" class="form-control" id="faq_question_2" name="faq_question_2" value="<?php echo htmlspecialchars($settings['faq_question_2'] ?? 'What is your return policy?'); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="faq_answer_2" class="form-label">Answer</label>
                                            <textarea class="form-control" id="faq_answer_2" name="faq_answer_2" rows="2"><?php echo htmlspecialchars($settings['faq_answer_2'] ?? 'We offer a 30-day return policy for most items. Products must be returned in their original condition and packaging. Please note that certain items, such as personalized products or perishable goods, may not be eligible for return. For more details, please visit our Returns & Refunds page.'); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- FAQ 3 -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">FAQ 3</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="faq_question_3" class="form-label">Question</label>
                                            <input type="text" class="form-control" id="faq_question_3" name="faq_question_3" value="<?php echo htmlspecialchars($settings['faq_question_3'] ?? 'How long does shipping take?'); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="faq_answer_3" class="form-label">Answer</label>
                                            <textarea class="form-control" id="faq_answer_3" name="faq_answer_3" rows="2"><?php echo htmlspecialchars($settings['faq_answer_3'] ?? 'Shipping times vary depending on your location and the shipping method selected. Standard shipping typically takes 3-5 business days within the continental US. Express shipping options are available for faster delivery. International shipping may take 7-14 business days, depending on the destination country.'); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- FAQ 4 -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">FAQ 4</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="faq_question_4" class="form-label">Question</label>
                                            <input type="text" class="form-control" id="faq_question_4" name="faq_question_4" value="<?php echo htmlspecialchars($settings['faq_question_4'] ?? 'Do you offer international shipping?'); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="faq_answer_4" class="form-label">Answer</label>
                                            <textarea class="form-control" id="faq_answer_4" name="faq_answer_4" rows="2"><?php echo htmlspecialchars($settings['faq_answer_4'] ?? 'Yes, we ship to most countries worldwide. International shipping rates and delivery times vary by destination. Please note that customers are responsible for any customs duties, taxes, or import fees that may apply. These charges are not included in the purchase price or shipping cost.'); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- FAQ 5 -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">FAQ 5</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="faq_question_5" class="form-label">Question</label>
                                            <input type="text" class="form-control" id="faq_question_5" name="faq_question_5" value="<?php echo htmlspecialchars($settings['faq_question_5'] ?? 'How can I change or cancel my order?'); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="faq_answer_5" class="form-label">Answer</label>
                                            <textarea class="form-control" id="faq_answer_5" name="faq_answer_5" rows="2"><?php echo htmlspecialchars($settings['faq_answer_5'] ?? 'If you need to change or cancel your order, please contact our customer service team as soon as possible. We process orders quickly, so changes or cancellations are only possible if the order has not yet been shipped. Once an order has been shipped, it cannot be canceled, but you can return it according to our return policy.'); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pages Settings -->
                            <div class="tab-pane fade" id="pages" role="tabpanel" aria-labelledby="pages-tab">
                                <h4 class="mb-3">Privacy Policy</h4>
                                
                                <div class="mb-3">
                                    <label for="privacy_policy_updated" class="form-label">Last Updated Date</label>
                                    <input type="text" class="form-control" id="privacy_policy_updated" name="privacy_policy_updated" value="<?php echo htmlspecialchars($settings['privacy_policy_updated'] ?? date('F d, Y')); ?>">
                                    <small class="form-text text-muted">Format: Month Day, Year (e.g., January 1, 2023)</small>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="privacy_policy_content" class="form-label">Privacy Policy Content</label>
                                    <textarea class="form-control tinymce-editor" id="privacy_policy_content" name="privacy_policy_content" rows="15"><?php echo $settings['privacy_policy_content'] ?? ''; ?></textarea>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h4 class="mb-3">Returns & Refunds Policy</h4>
                                
                                <div class="mb-3">
                                    <label for="returns_refunds_updated" class="form-label">Last Updated Date</label>
                                    <input type="text" class="form-control" id="returns_refunds_updated" name="returns_refunds_updated" value="<?php echo htmlspecialchars($settings['returns_refunds_updated'] ?? date('F d, Y')); ?>">
                                    <small class="form-text text-muted">Format: Month Day, Year (e.g., January 1, 2023)</small>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="returns_refunds_content" class="form-label">Returns & Refunds Content</label>
                                    <textarea class="form-control tinymce-editor" id="returns_refunds_content" name="returns_refunds_content" rows="15"><?php echo $settings['returns_refunds_content'] ?? ''; ?></textarea>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h4 class="mb-3">FAQ Page</h4>
                                
                                <div class="mb-3">
                                    <label for="faq_intro" class="form-label">FAQ Introduction</label>
                                    <textarea class="form-control" id="faq_intro" name="faq_intro" rows="3"><?php echo htmlspecialchars($settings['faq_intro'] ?? 'Find answers to commonly asked questions about our products and services.'); ?></textarea>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="faq_content" class="form-label">Custom FAQ Content (Optional)</label>
                                    <textarea class="form-control tinymce-editor" id="faq_content" name="faq_content" rows="15"><?php echo $settings['faq_content'] ?? ''; ?></textarea>
                                    <small class="form-text text-muted">If provided, this content will override the individual FAQ items. Leave empty to use the individual FAQ items from the Contact tab.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="button" class="btn btn-secondary btn-lg me-md-2 close-tab-btn">Close</button>
                            <button type="submit" class="btn btn-primary btn-lg" name="save_settings" value="general">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Sticky Save Button for Mobile -->
            <div class="d-md-none sticky-save-button">
                <div class="d-flex">
                    <button type="button" form="settingsForm" class="btn btn-secondary btn-lg flex-grow-1 me-2 close-tab-btn">Close</button>
                    <button type="submit" form="settingsForm" class="btn btn-primary btn-lg flex-grow-1" name="save_settings" id="mobile-save-btn" value="general">Save</button>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.tiny.cloud/1/braohodt2kbyl43wj4uh47219bypwuk2q1tzvdmetj3q8050/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<style>
/* Responsive tabs */
.nav-tabs-responsive {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    white-space: nowrap;
    margin-bottom: -1px;
}

.nav-tabs-responsive .nav-tabs {
    display: flex;
    flex-wrap: nowrap;
}

.nav-tabs-responsive .nav-item {
    float: none;
    display: inline-block;
}

.nav-tabs-responsive::-webkit-scrollbar {
    height: 5px;
}

.nav-tabs-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.nav-tabs-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 5px;
}

.nav-tabs-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Responsive form elements */
@media (max-width: 767.98px) {
    .tab-content {
        padding: 1rem !important;
    }
    
    .row {
        margin-left: -5px;
        margin-right: -5px;
    }
    
    .col-md-6 {
        padding-left: 5px;
        padding-right: 5px;
    }
    
    .form-control, .input-group {
        margin-bottom: 0.5rem;
    }
    
    .img-thumbnail {
        max-width: 100%;
        height: auto;
    }
    
    .card-header {
        padding: 0.75rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    h4 {
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }
    
    .btn {
        padding: 0.375rem 0.75rem;
    }
    
    /* Improve form layout on mobile */
    .form-label {
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }
    
    .form-text {
        font-size: 0.75rem;
    }
    
    .form-check {
        margin-bottom: 0.5rem;
    }
    
    /* Adjust spacing for better mobile experience */
    .mb-3 {
        margin-bottom: 0.75rem !important;
    }
    
    .mb-4 {
        margin-bottom: 1rem !important;
    }
    
    /* Make input groups stack on mobile */
    .input-group {
        flex-wrap: wrap;
    }
    
    .input-group > .form-control {
        flex: 1 1 auto;
        width: 100%;
    }
    
    .input-group-text {
        width: 100%;
        border-radius: 0.25rem 0.25rem 0 0 !important;
    }
    
    .input-group > .form-control:not(:first-child) {
        border-radius: 0 0 0.25rem 0.25rem !important;
        margin-top: -1px;
    }
}

/* Responsive image containers */
.image-preview-container {
    position: relative;
    max-width: 100%;
    overflow: hidden;
}

.image-preview-container img {
    max-width: 100%;
    height: auto;
}

/* Responsive hero slider cards */
@media (max-width: 767.98px) {
    #hero-slider-section .card,
    #single-hero-section .card {
        margin-bottom: 1rem;
    }
    
    #hero-slider-section .card-header,
    #single-hero-section .card-header {
        padding: 0.5rem;
    }
    
    #hero-slider-section .card-body,
    #single-hero-section .card-body {
        padding: 0.75rem;
    }
}

/* Responsive TinyMCE editor */
@media (max-width: 767.98px) {
    .tox-tinymce {
        height: 300px !important;
    }
}

/* Sticky save button for mobile */
.sticky-save-button {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: #fff;
    padding: 10px;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
}

/* Style for close button */
.close-tab-btn {
    background-color: #f8f9fa;
    border-color: #ddd;
    color: #333;
}

.close-tab-btn:hover {
    background-color: #e9ecef;
    border-color: #ccc;
    color: #212529;
}

@media (max-width: 767.98px) {
    .card {
        margin-bottom: 70px; /* Add space for the sticky button */
    }
    
    main {
        padding-bottom: 60px; /* Add space for the sticky button */
    }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview image before upload
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.className = 'img-thumbnail mt-2';
                    preview.style.maxHeight = input.id === 'site_favicon' ? '32px' : '100px';
                    
                    const previewContainer = input.parentElement;
                    const existingPreview = previewContainer.querySelector('img.preview');
                    if (existingPreview) {
                        previewContainer.removeChild(existingPreview);
                    }
                    
                    // Add preview class to distinguish from existing image
                    preview.classList.add('preview');
                    
                    // Insert after the input
                    input.insertAdjacentElement('afterend', preview);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // Keep active tab after form submission
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`[data-bs-target="${hash}"]`);
        if (tab) {
            const tabInstance = new bootstrap.Tab(tab);
            tabInstance.show();
        }
    }

    // Toggle hero sections based on checkbox
    const useHeroSliderCheckbox = document.getElementById('use_hero_slider');
    const singleHeroSection = document.getElementById('single-hero-section');
    const heroSliderSection = document.getElementById('hero-slider-section');
    
    if (useHeroSliderCheckbox && singleHeroSection && heroSliderSection) {
        useHeroSliderCheckbox.addEventListener('change', function() {
            if (this.checked) {
                singleHeroSection.classList.add('d-none');
                heroSliderSection.classList.remove('d-none');
            } else {
                singleHeroSection.classList.remove('d-none');
                heroSliderSection.classList.add('d-none');
            }
        });
    }
    
    // Initialize TinyMCE for rich text editors
    tinymce.init({
        selector: '.tinymce-editor',
        height: 400,
        menubar: true,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount',
            'emoticons template textcolor colorpicker textpattern imagetools'
        ],
        toolbar: 'undo redo | formatselect | bold italic backcolor | \
                alignleft aligncenter alignright alignjustify | \
                bullist numlist outdent indent | link image media | removeformat | help',
        image_advtab: true,
        image_caption: true,
        automatic_uploads: true,
        file_picker_types: 'image',
        file_picker_callback: function (cb, value, meta) {
            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');

            input.onchange = function () {
                var file = this.files[0];

                var reader = new FileReader();
                reader.onload = function () {
                    var id = 'blobid' + (new Date()).getTime();
                    var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                    var base64 = reader.result.split(',')[1];
                    var blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);

                    cb(blobInfo.blobUri(), { title: file.name });
                };
                reader.readAsDataURL(file);
            };

            input.click();
        }
    });
    
    // Improve tab navigation on mobile
    const tabButtons = document.querySelectorAll('.nav-tabs .nav-link');
    const tabsContainer = document.querySelector('.nav-tabs-responsive');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Scroll the clicked tab into view
            setTimeout(() => {
                this.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }, 10);
            
            // Scroll to the top of the tab content on mobile
            if (window.innerWidth < 768) {
                setTimeout(() => {
                    const tabContent = document.querySelector(this.dataset.bsTarget);
                    if (tabContent) {
                        tabContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 300);
            }
            
            // Update save button value based on active tab
            const tabId = this.getAttribute('id').replace('-tab', '');
            saveButton.value = tabId;
            if (mobileSaveButton) {
                mobileSaveButton.value = tabId;
            }
        });
    });
    
    // Handle window resize for responsive layout
    window.addEventListener('resize', function() {
        // Adjust TinyMCE height on mobile
        if (window.innerWidth < 768) {
            tinymce.activeEditor?.theme.resizeTo('100%', 300);
        } else {
            tinymce.activeEditor?.theme.resizeTo('100%', 400);
        }
    });
    
    // Handle tab-specific save and close functionality
    const saveButton = document.querySelector('button[name="save_settings"]');
    const mobileSaveButton = document.getElementById('mobile-save-btn');
    const closeButtons = document.querySelectorAll('.close-tab-btn');
    
    // Handle close button functionality
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Get the active tab
            const activeTab = document.querySelector('.nav-link.active');
            const tabId = activeTab.getAttribute('id');
            
            // Determine action based on tab content
            switch (tabId) {
                case 'general-tab':
                case 'social-tab':
                case 'seo-tab':
                case 'payment-tab':
                case 'shipping-tab':
                    // For basic settings tabs, just redirect to dashboard
                    window.location.href = 'index.php';
                    break;
                case 'homepage-tab':
                    // For homepage tab, offer to view the homepage
                    if (confirm('Do you want to view the homepage?')) {
                        window.open('../index.php', '_blank');
                    } else {
                        window.location.href = 'index.php';
                    }
                    break;
                case 'about-tab':
                    // For about tab, offer to view the about page
                    if (confirm('Do you want to view the About Us page?')) {
                        window.open('../about.php', '_blank');
                    } else {
                        window.location.href = 'index.php';
                    }
                    break;
                case 'contact-tab':
                    // For contact tab, offer to view the contact page
                    if (confirm('Do you want to view the Contact Us page?')) {
                        window.open('../contact.php', '_blank');
                    } else {
                        window.location.href = 'index.php';
                    }
                    break;
                case 'pages-tab':
                    // For pages tab, offer options to view different pages
                    const pageOptions = ['Privacy Policy', 'Returns & Refunds', 'FAQ'];
                    const pageUrls = ['../privacy-policy.php', '../returns-refunds.php', '../faq.php'];
                    
                    const pageChoice = prompt('Which page would you like to view?\n1. Privacy Policy\n2. Returns & Refunds\n3. FAQ\n\nEnter 1, 2, or 3, or click Cancel to return to dashboard:');
                    
                    if (pageChoice && ['1', '2', '3'].includes(pageChoice)) {
                        window.open(pageUrls[parseInt(pageChoice) - 1], '_blank');
                    } else {
                        window.location.href = 'index.php';
                    }
                    break;
                default:
                    window.location.href = 'index.php';
            }
        });
    });
});
</script>

<?php include '../includes/admin-footer.php'; ?> 