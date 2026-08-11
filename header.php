<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? 'user';
$userName = $_SESSION['full_name'] ?? 'User';
$walletBalance = number_format($_SESSION['wallet_balance'] ?? 0, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yango - Rides, Parcel Delivery & Express Services</title>
    <meta name="description" content="Book instant rides, track drivers live, and send parcel deliveries with Yango - fast, comfortable, and reliable.">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/leaflet-custom.css">
</head>
<body>
 
    <!-- Yango Header Navigation Bar -->
    <header class="yango-navbar">
        <div class="nav-container">
            <a href="index.php" class="brand-logo">
                <span class="logo-yango">YANGO</span>
                <span class="logo-badge">EXPRESS</span>
            </a>
 
            <nav class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <?php if ($isLoggedIn): ?>
                    <?php if ($userRole === 'driver'): ?>
                        <a href="driver_dashboard.php" class="nav-link highlight-link">Driver Console</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="nav-link highlight-link">Book Ride / Delivery</a>
                    <?php endif; ?>
                    <a href="wallet.php" class="nav-link">Wallet</a>
                    <a href="profile.php" class="nav-link">Profile</a>
                <?php else: ?>
                    <a href="index.php#services" class="nav-link">Services</a>
                    <a href="index.php#estimate" class="nav-link">Price Estimator</a>
                    <a href="index.php#driver-signup" class="nav-link">Become a Driver</a>
                <?php endif; ?>
            </nav>
 
            <div class="nav-actions">
                <?php if ($isLoggedIn): ?>
                    <a href="wallet.php" class="wallet-pill">
                        <span class="wallet-icon">💳</span>
                        <span class="wallet-amount">$<?php echo $walletBalance; ?></span>
                    </a>
                    <div class="user-profile-menu">
                        <span class="avatar-circle"><?php echo strtoupper(substr($userName, 0, 1)); ?></span>
                        <div class="user-dropdown">
                            <div class="user-dropdown-header">
                                <strong><?php echo htmlspecialchars($userName); ?></strong>
                                <small><?php echo ucfirst($userRole); ?></small>
                            </div>
                            <hr>
                            <a href="<?php echo $userRole === 'driver' ? 'driver_dashboard.php' : 'dashboard.php'; ?>">Dashboard</a>
                            <a href="wallet.php">My Wallet</a>
                            <a href="profile.php">Account Settings</a>
                            <hr>
                            <a href="logout.php" class="logout-link">Sign Out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn-secondary">Log In</a>
                    <a href="signup.php" class="btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
 
