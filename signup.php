<?php
require_once __DIR__ . '/includes/header.php';
 
$defaultRole = ($_GET['role'] ?? '') === 'driver' ? 'driver' : 'user';
?>
 
<div style="max-width: 520px; margin: 50px auto; padding: 0 20px;">
    <div class="glass-panel" style="border: 1px solid var(--border-highlight);">
        <div style="text-align: center; margin-bottom: 24px;">
            <div class="logo-badge" style="font-size: 20px; padding: 6px 16px;">YANGO SIGN UP</div>
            <h2 style="font-size: 24px; margin-top: 10px;">Create an Account</h2>
            <p style="color: var(--text-secondary); font-size: 14px;">Select account type to get started.</p>
        </div>
 
        <!-- Role Toggle Tabs -->
        <div class="service-toggle-tabs" style="margin-bottom: 24px;">
            <button type="button" class="tab-btn <?php echo $defaultRole === 'user' ? 'active' : ''; ?>" id="tab-role-user">
                👤 Passenger Account
            </button>
            <button type="button" class="tab-btn <?php echo $defaultRole === 'driver' ? 'active' : ''; ?>" id="tab-role-driver">
                🚗 Driver Account
            </button>
        </div>
 
        <form id="auth-signup-form">
            <input type="hidden" id="signup-role" value="<?php echo $defaultRole; ?>">
 
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <div class="input-with-icon">
                    <span class="input-icon">👤</span>
                    <input type="text" id="signup-name" class="form-control" placeholder="John Doe" required>
                </div>
            </div>
 
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-with-icon">
                    <span class="input-icon">✉️</span>
                    <input type="email" id="signup-email" class="form-control" placeholder="john@example.com" required>
                </div>
            </div>
 
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <div class="input-with-icon">
                    <span class="input-icon">📞</span>
                    <input type="tel" id="signup-phone" class="form-control" placeholder="+1 234 567 8900" required>
                </div>
            </div>
 
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-with-icon">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="signup-password" class="form-control" placeholder="Choose a strong password" required>
                </div>
            </div>
 
            <!-- Driver Specific Fields -->
            <div id="driver-fields" style="display: <?php echo $defaultRole === 'driver' ? 'block' : 'none'; ?>; background: #121214; padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 20px;">
                <h4 style="color: var(--accent-yellow); margin-bottom: 12px;">🚗 Vehicle Information</h4>
 
                <div class="form-group">
                    <label class="form-label">Vehicle Make / Category</label>
                    <select id="signup-vtype" class="form-control" style="padding-left: 14px;">
                        <option value="Yango Comfort (Toyota Camry)">Yango Comfort (Car)</option>
                        <option value="Yango Economy (Honda Civic)">Yango Economy (Car)</option>
                        <option value="Yango Express Courier Bike">Yango Express Bike</option>
                        <option value="Yango Cargo Delivery Van">Yango Cargo Delivery Van</option>
                    </select>
                </div>
 
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">License Plate / Registration No.</label>
                    <input type="text" id="signup-vnum" class="form-control" placeholder="e.g., YG-8821-NY" style="padding-left: 14px;">
                </div>
            </div>
 
            <button type="submit" class="btn-primary" style="width: 100%; padding: 14px; font-size: 16px;">
                Complete Registration
            </button>
        </form>
 
        <div style="margin-top: 24px; text-align: center; font-size: 14px; color: var(--text-secondary);">
            Already registered? <a href="login.php" style="color: var(--accent-yellow); font-weight: 600;">Log in here</a>
        </div>
    </div>
</div>
 
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabUser = document.getElementById('tab-role-user');
    const tabDriver = document.getElementById('tab-role-driver');
    const roleInput = document.getElementById('signup-role');
    const driverFields = document.getElementById('driver-fields');
 
    tabUser.addEventListener('click', () => {
        tabUser.classList.add('active');
        tabDriver.classList.remove('active');
        roleInput.value = 'user';
        driverFields.style.display = 'none';
    });
 
    tabDriver.addEventListener('click', () => {
        tabDriver.classList.add('active');
        tabUser.classList.remove('active');
        roleInput.value = 'driver';
        driverFields.style.display = 'block';
    });
 
    const form = document.getElementById('auth-signup-form');
    form.addEventListener('submit', (e) => {
        e.preventDefault();
 
        const payload = {
            role: roleInput.value,
            full_name: document.getElementById('signup-name').value,
            email: document.getElementById('signup-email').value,
            phone: document.getElementById('signup-phone').value,
            password: document.getElementById('signup-password').value,
            vehicle_type: document.getElementById('signup-vtype').value,
            vehicle_number: document.getElementById('signup-vnum').value
        };
 
        fetch('api/auth.php?action=signup', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => window.location.href = data.redirect, 1000);
            } else {
                showToast(data.message, 'error');
            }
        });
    });
});
</script>
 
<?php require_once __DIR__ . '/includes/footer.php'; ?>
