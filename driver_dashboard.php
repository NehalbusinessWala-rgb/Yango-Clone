<?php
require_once __DIR__ . '/includes/auth_check.php';
requireAuth('driver'); // Require driver role
require_once __DIR__ . '/includes/header.php';
 
$pdo = getDBConnection();
$driverId = $_SESSION['user_id'];
 
$stmtDriver = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtDriver->execute([$driverId]);
$driver = $stmtDriver->fetch();
 
$isOnline = ($driver['status'] ?? 'offline') !== 'offline';
?>
 
<div class="dashboard-wrapper">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 28px;">Driver Dispatch Radar</h1>
            <p style="color: var(--text-secondary);">Vehicle: <strong><?php echo htmlspecialchars($driver['vehicle_type'] ?? 'Yango Comfort'); ?></strong> (<?php echo htmlspecialchars($driver['vehicle_number'] ?? 'N/A'); ?>)</p>
        </div>
 
        <!-- Online / Offline Availability Switch -->
        <div class="glass-panel" style="padding: 12px 24px; display: flex; align-items: center; gap: 16px; border-radius: 30px;">
            <span style="font-size: 14px; font-weight: 600;">Duty Status:</span>
            <span id="status-label-text" style="font-weight: 800; color: <?php echo $isOnline ? '#2ECC71' : '#E74C3C'; ?>;">
                <?php echo strtoupper($driver['status'] ?? 'OFFLINE'); ?>
            </span>
            <label style="position: relative; display: inline-block; width: 50px; height: 26px; cursor: pointer;">
                <input type="checkbox" id="driver-status-toggle" <?php echo $isOnline ? 'checked' : ''; ?> style="opacity: 0; width: 0; height: 0;">
                <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #2B2B36; transition: .4s; border-radius: 34px;"></span>
                <span style="position: absolute; content: ''; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%;"></span>
            </label>
        </div>
    </div>
 
    <div class="dashboard-grid">
 
        <!-- Left Column: Pending Radar Requests / Active Trip Execution -->
        <div>
 
            <!-- Incoming Requests Radar Container -->
            <div id="requests-radar-container" class="glass-panel">
                <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                    <div style="font-size: 40px; margin-bottom: 10px;">📡</div>
                    <h4>Scanning for nearby requests...</h4>
                </div>
            </div>
 
            <!-- Active Trip Control Panel -->
            <div id="driver-active-trip-panel" class="glass-panel" style="display: none; border: 1px solid var(--border-highlight);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 id="driver-trip-title" style="color: var(--accent-yellow); font-size: 18px;">🚗 Ride #--</h3>
                    <span class="request-badge badge-ride">IN PROGRESS</span>
                </div>
 
                <div style="background: #121214; padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 20px;">
                    <div style="font-size: 14px; margin-bottom: 8px;">
                        <strong>Passenger:</strong> <span id="driver-passenger-name">--</span>
                        <a id="driver-passenger-phone" href="#" style="margin-left: 10px; color: var(--accent-yellow); font-weight: 600;">📞 Call</a>
                    </div>
                    <div style="font-size: 14px; margin-bottom: 6px;">
                        <strong style="color: #2ECC71;">📍 Pickup:</strong> <span id="driver-pickup-text">--</span>
                    </div>
                    <div style="font-size: 14px; margin-bottom: 6px;">
                        <strong style="color: #E74C3C;">🏁 Destination:</strong> <span id="driver-dropoff-text">--</span>
                    </div>
                    <div style="font-size: 14px; margin-top: 10px; border-top: 1px dashed var(--border-color); padding-top: 8px;">
                        <strong>Payout Earnings (85%):</strong> <strong id="driver-fare-text" style="color: var(--accent-yellow); font-size: 18px;">$0.00</strong>
                    </div>
                </div>
 
                <button type="button" id="btn-driver-next-step" class="btn-primary" style="width: 100%; padding: 14px; font-size: 16px;">
                    Next Action Step
                </button>
            </div>
 
        </div>
 
        <!-- Right Column: Navigation Map -->
        <div>
            <div class="map-viewport-card">
                <div id="driver-map"></div>
            </div>
        </div>
 
    </div>
</div>
 
<style>
/* CSS toggle slider styling */
input:checked + span {
    background-color: #2ECC71 !important;
}
input:checked + span:before {
    transform: translateX(24px);
}
</style>
 
<script src="assets/js/driver.js"></script>
 
<?php require_once __DIR__ . '/includes/footer.php'; ?>
 
