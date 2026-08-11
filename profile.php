<?php
require_once __DIR__ . '/includes/auth_check.php';
requireAuth();
require_once __DIR__ . '/includes/header.php';
 
$pdo = getDBConnection();
$userId = $_SESSION['user_id'];
$message = '';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $vType = trim($_POST['vehicle_type'] ?? '');
    $vNum = trim($_POST['vehicle_number'] ?? '');
 
    $stmt = $pdo->prepare("UPDATE users SET full_name = :name, phone = :phone, vehicle_type = :vtype, vehicle_number = :vnum WHERE id = :id");
    $stmt->execute(['name' => $name, 'phone' => $phone, 'vtype' => $vType ?: null, 'vnum' => $vNum ?: null, 'id' => $userId]);
 
    $_SESSION['full_name'] = $name;
    $message = "Profile details updated successfully!";
}
 
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();
?>
 
<div class="dashboard-wrapper" style="max-width: 600px;">
    <div class="glass-panel" style="border: 1px solid var(--border-highlight);">
        <div style="text-align: center; margin-bottom: 24px;">
            <div class="avatar-circle" style="width: 70px; height: 70px; font-size: 28px; margin: 0 auto 12px;">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <span class="request-badge badge-ride" style="margin-top: 4px;"><?php echo strtoupper($user['role']); ?> ACCOUNT</span>
        </div>
 
        <?php if ($message): ?>
            <div style="padding: 12px; background: rgba(46, 204, 113, 0.15); border: 1px solid #2ECC71; color: #2ECC71; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; text-align: center;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
 
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" style="padding-left: 14px;" required>
            </div>
 
            <div class="form-group">
                <label class="form-label">Email Address (Read-only)</label>
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" style="padding-left: 14px; opacity: 0.6;" readonly>
            </div>
 
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" style="padding-left: 14px;" required>
            </div>
 
            <?php if ($user['role'] === 'driver'): ?>
                <div style="background: #121214; padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 20px;">
                    <h4 style="color: var(--accent-yellow); margin-bottom: 12px;">🚗 Vehicle Information</h4>
                    <div class="form-group">
                        <label class="form-label">Vehicle Make / Model</label>
                        <input type="text" name="vehicle_type" class="form-control" value="<?php echo htmlspecialchars($user['vehicle_type'] ?? ''); ?>" style="padding-left: 14px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">License Plate Number</label>
                        <input type="text" name="vehicle_number" class="form-control" value="<?php echo htmlspecialchars($user['vehicle_number'] ?? ''); ?>" style="padding-left: 14px;">
                    </div>
                </div>
            <?php endif; ?>
 
            <button type="submit" class="btn-primary" style="width: 100%; padding: 14px;">
                Save Profile Changes
            </button>
        </form>
    </div>
</div>
 
<?php require_once __DIR__ . '/includes/footer.php'; ?>
 
