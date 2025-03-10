<?php
// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    flash('error', 'You do not have permission to access this page.');
    redirect(URL_ROOT . '/login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME . ' Admin' : SITE_NAME . ' Admin'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/admin.css">
    <!-- Favicon -->
    <?php if(!empty(getSetting('site_favicon'))): ?>
    <link rel="icon" href="<?php echo URL_ROOT; ?>/uploads/settings/<?php echo getSetting('site_favicon'); ?>" type="image/x-icon">
    <?php else: ?>
    <link rel="icon" href="<?php echo URL_ROOT; ?>/assets/images/favicon.ico" type="image/x-icon">
    <?php endif; ?>
    <?php if(isset($extraCSS)) echo $extraCSS; ?>
</head>
<body>
    <!-- Top Navbar -->
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="<?php echo URL_ROOT; ?>/admin/index.php">
            <?php if(!empty(getSetting('site_logo'))): ?>
            <img src="<?php echo URL_ROOT; ?>/uploads/settings/<?php echo getSetting('site_logo'); ?>" alt="<?php echo SITE_NAME; ?> Admin" class="img-fluid" style="max-height: 30px;">
            <?php else: ?>
            <?php echo SITE_NAME; ?> Admin
            <?php endif; ?>
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <input class="form-control form-control-dark w-100" type="text" placeholder="Search" aria-label="Search">
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="<?php echo URL_ROOT; ?>/logout.php">Sign out</a>
            </div>
        </div>
    </header>
</body>
</html> 