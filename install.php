<?php
// install.php - Automatic Database Setup Script for Yango Clone
 
require_once __DIR__ . '/config/db.php';
 
$message = '';
$status = 'info';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['auto'])) {
    try {
        $pdo = getDBConnection();
 
        // Read SQL schema file
        $sql = file_get_contents(__DIR__ . '/db.sql');
 
        // Execute multi-query DB creation & setup
        $pdo->exec($sql);
 
        // Update initial test user password hashes to ensure valid login with '123456'
        $passHash = password_hash('123456', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = :pass WHERE email IN ('user@yango.com', 'driver@yango.com', 'delivery@yango.com')");
        $stmt->execute(['pass' => $passHash]);
 
        $message = "Database `rsoa_rsoa_rsoa324_25` and tables configured successfully!";
        $status = "success";
    } catch (Exception $e) {
        $message = "Installation Error: " . $e->getMessage();
        $status = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yango Clone - Auto Database Installer</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .install-card {
            max-width: 600px;
            margin: 60px auto;
            background: #1C1C21;
            border: 1px solid rgba(255, 204, 0, 0.2);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            text-align: center;
        }
        .logo-badge {
            display: inline-block;
            background: #FFCC00;
            color: #000;
            font-weight: 800;
            font-size: 24px;
            padding: 8px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }
        .btn-install {
            background: #FFCC00;
            color: #000;
            font-size: 16px;
            font-weight: 700;
            padding: 14px 32px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-install:hover {
            background: #FFE066;
            transform: translateY(-2px);
        }
        .demo-credentials {
            background: #121214;
            border-radius: 10px;
            padding: 16px;
            margin-top: 24px;
            text-align: left;
            border: 1px solid #2B2B36;
        }
    </style>
</head>
<body style="background: #121214; color: #fff; font-family: 'Outfit', sans-serif;">
 
    <div class="install-card">
        <div class="logo-badge">YANGO CLONE</div>
        <h2>Database Setup & Installer</h2>
        <p style="color: #9E9EA7; margin-bottom: 24px;">Configure database <code>rsoa_rsoa_rsoa324_25</code> and insert sample demo accounts.</p>
 
        <?php if (!empty($message)): ?>
            <div style="padding: 14px; border-radius: 10px; margin-bottom: 24px; font-weight: 600; background: <?php echo $status === 'success' ? 'rgba(46, 204, 113, 0.2)' : 'rgba(231, 76, 60, 0.2)'; ?>; color: <?php echo $status === 'success' ? '#2ecc71' : '#e74c3c'; ?>; border: 1px solid <?php echo $status === 'success' ? '#2ecc71' : '#e74c3c'; ?>;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
 
        <?php if ($status !== 'success'): ?>
            <form method="POST">
                <button type="submit" class="btn-install">Run Automated Installation</button>
            </form>
        <?php else: ?>
            <div style="display: flex; gap: 12px; justify-content: center; margin-bottom: 20px;">
                <a href="index.php" style="background: #FFCC00; color: #000; padding: 12px 24px; border-radius: 8px; font-weight: 700; text-decoration: none;">Go to Homepage</a>
                <a href="login.php" style="background: #2B2B36; color: #fff; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none;">Login Now</a>
            </div>
        <?php endif; ?>
 
        <div class="demo-credentials">
            <h4 style="color: #FFCC00; margin-top:0; margin-bottom: 10px;">🔑 Pre-configured Demo Accounts:</h4>
            <div style="margin-bottom: 8px;">
                <strong>Passenger Account:</strong><br>
                Email: <code>user@yango.com</code> | Password: <code>123456</code>
            </div>
            <div>
                <strong>Driver Account:</strong><br>
                Email: <code>driver@yango.com</code> | Password: <code>123456</code>
            </div>
        </div>
    </div>
 
</body>
</html>
 
