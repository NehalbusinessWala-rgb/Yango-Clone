<?php
require_once __DIR__ . '/includes/header.php';
 
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'driver') {
        header("Location: driver_dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}
?>
 
<div style="max-width: 460px; margin: 60px auto; padding: 0 20px;">
    <div class="glass-panel" style="border: 1px solid var(--border-highlight);">
        <div style="text-align: center; margin-bottom: 28px;">
            <div class="logo-badge" style="font-size: 20px; padding: 6px 16px;">YANGO LOG IN</div>
            <h2 style="font-size: 24px; margin-top: 10px;">Welcome Back</h2>
            <p style="color: var(--text-secondary); font-size: 14px;">Log in to manage bookings, track drivers, or start driving.</p>
        </div>
 
        <form id="auth-login-form">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-with-icon">
                    <span class="input-icon">✉️</span>
                    <input type="email" id="login-email" class="form-control" placeholder="user@yango.com" required>
                </div>
            </div>
 
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-with-icon">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="login-password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>
 
            <button type="submit" class="btn-primary" style="width: 100%; padding: 14px; margin-top: 10px; font-size: 16px;">
                Log In
            </button>
        </form>
 
        <div style="margin-top: 24px; text-align: center; font-size: 14px; color: var(--text-secondary);">
            Don't have an account yet? <a href="signup.php" style="color: var(--accent-yellow); font-weight: 600;">Sign up now</a>
        </div>
 
        <div style="margin-top: 20px; background: #121214; padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); font-size: 13px;">
            <strong style="color: var(--accent-yellow); display: block; margin-bottom: 4px;">💡 Demo Accounts:</strong>
            Passenger: <code>user@yango.com</code> | <code>123456</code><br>
            Driver: <code>driver@yango.com</code> | <code>123456</code>
        </div>
    </div>
</div>
 
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('auth-login-form');
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
 
        fetch('api/auth.php?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email, password: password })
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
 
