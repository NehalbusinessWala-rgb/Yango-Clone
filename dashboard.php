<?php
require_once __DIR__ . '/includes/auth_check.php';
requireAuth('user'); // Ensure user role
require_once __DIR__ . '/includes/header.php';
?>
 
<div class="dashboard-wrapper">
    <div class="dashboard-grid">
 
        <!-- Left Column: Booking Controls / Active Tracking -->
        <div>
 
            <!-- Booking Forms Panel (Shown when no active ride) -->
            <div id="booking-forms-panel" class="glass-panel">
                <div class="section-title">
                    <span>What service do you need?</span>
                    <span style="font-size: 13px; color: var(--accent-yellow); font-weight: 600;">⚡ Instant Dispatch</span>
                </div>
 
                <!-- Service Tabs -->
                <div class="service-toggle-tabs">
                    <button type="button" class="tab-btn active" data-service="ride">
                        🚗 Book a Ride
                    </button>
                    <button type="button" class="tab-btn" data-service="delivery">
                        📦 Send Package
                    </button>
                </div>
 
                <!-- Ride Booking Form -->
                <form id="form-ride">
                    <input type="hidden" id="selected_tier" value="Economy">
                    <input type="hidden" id="pickup_lat" value="40.712776">
                    <input type="hidden" id="pickup_lng" value="-74.005974">
                    <input type="hidden" id="dropoff_lat" value="40.730610">
                    <input type="hidden" id="dropoff_lng" value="-73.935242">
 
                    <div class="form-group">
                        <label class="form-label">Pickup Location</label>
                        <div class="input-with-icon">
                            <span class="input-icon" style="color: var(--accent-yellow);">📍</span>
                            <input type="text" id="pickup_address" class="form-control" value="Financial District, NYC" placeholder="Click map or type pickup" required>
                        </div>
                    </div>
 
                    <div class="form-group">
                        <label class="form-label">Drop-off Destination</label>
                        <div class="input-with-icon">
                            <span class="input-icon" style="color: #E74C3C;">🏁</span>
                            <input type="text" id="dropoff_address" class="form-control" value="Times Square, Midtown NYC" placeholder="Click map or type dropoff" required>
                        </div>
                    </div>
 
                    <!-- Vehicle Tiers -->
                    <label class="form-label" style="margin-top: 18px;">Select Service Class</label>
                    <div class="tier-grid">
                        <div class="tier-card selected" data-tier="Economy">
                            <span class="tier-icon">🚗</span>
                            <span class="tier-name">Economy</span>
                            <span class="tier-price">$1.20/km</span>
                        </div>
                        <div class="tier-card" data-tier="Comfort">
                            <span class="tier-icon">🚘</span>
                            <span class="tier-name">Comfort</span>
                            <span class="tier-price">$1.80/km</span>
                        </div>
                        <div class="tier-card" data-tier="Express">
                            <span class="tier-icon">⚡</span>
                            <span class="tier-name">Express</span>
                            <span class="tier-price">$1.50/km</span>
                        </div>
                    </div>
 
                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <select id="payment_method" class="form-control" style="padding-left: 14px;">
                            <option value="wallet">💳 Yango Wallet (Bal: $<?php echo number_format($_SESSION['wallet_balance'] ?? 0, 2); ?>)</option>
                            <option value="cash">💵 Cash to Driver</option>
                        </select>
                    </div>
 
                    <!-- Fare Estimation Footer -->
                    <div style="background: #121214; padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <div>
                            <small style="color: var(--text-secondary); display: block;">Calculated Fare</small>
                            <strong id="est-fare" style="font-size: 22px; color: var(--accent-yellow);">$9.24</strong>
                        </div>
                        <div style="text-align: right;">
                            <small style="color: var(--text-secondary); display: block;">Est. Distance</small>
                            <strong id="est-distance" style="font-size: 16px; color: #fff;">5.20 km</strong>
                        </div>
                    </div>
 
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 14px; font-size: 16px;">
                        Confirm Ride Request
                    </button>
                </form>
 
                <!-- Parcel Delivery Form (Hidden by default) -->
                <form id="form-delivery" style="display: none;">
                    <div class="form-group">
                        <label class="form-label">Parcel Pickup Address</label>
                        <div class="input-with-icon">
                            <span class="input-icon" style="color: var(--accent-yellow);">📦</span>
                            <input type="text" class="form-control" value="Financial District, NYC" placeholder="Pickup Address" required>
                        </div>
                    </div>
 
                    <div class="form-group">
                        <label class="form-label">Recipient Address</label>
                        <div class="input-with-icon">
                            <span class="input-icon" style="color: #E74C3C;">🏢</span>
                            <input type="text" class="form-control" value="Empire State Building, NYC" placeholder="Drop-off Address" required>
                        </div>
                    </div>
 
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-group">
                            <label class="form-label">Recipient Name</label>
                            <input type="text" id="recipient_name" class="form-control" placeholder="Alice Smith" style="padding-left: 14px;" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Recipient Phone</label>
                            <input type="tel" id="recipient_phone" class="form-control" placeholder="+1 555 1234" style="padding-left: 14px;" required>
                        </div>
                    </div>
 
                    <div class="form-group">
                        <label class="form-label">Package Description</label>
                        <input type="text" id="package_details" class="form-control" placeholder="e.g. Legal Documents / Electronic Item" style="padding-left: 14px;" required>
                    </div>
 
                    <div class="form-group">
                        <label class="form-label">Weight Category</label>
                        <select id="package_weight" class="form-control" style="padding-left: 14px;">
                            <option value="Small (< 2kg)">Small Document / Envelope (< 2kg)</option>
                            <option value="Medium (2 - 10kg)">Medium Package (2 - 10kg)</option>
                            <option value="Heavy (10 - 20kg)">Heavy Parcel (10 - 20kg)</option>
                        </select>
                    </div>
 
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 14px; font-size: 16px;">
                        Dispatch Express Courier
                    </button>
                </form>
            </div>
 
            <!-- Active Ride Live Tracker Panel (Shown when trip is active) -->
            <div id="active-tracker-panel" class="glass-panel" style="display: none; border: 1px solid var(--border-highlight);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 id="track-service-type" style="color: var(--accent-yellow); font-size: 18px;">🚗 Ride Booking #--</h3>
                    <span class="request-badge badge-ride" id="track-status-pill">ACTIVE</span>
                </div>
 
                <!-- Stepper Progress Bar -->
                <div class="stepper-container" style="margin-bottom: 24px;">
                    <div class="stepper-progress-bar">
                        <div class="stepper-progress-fill" id="stepper-fill"></div>
                    </div>
                    <div class="step-node active">
                        <div class="step-dot">1</div>
                        <div class="step-title">Requested</div>
                    </div>
                    <div class="step-node">
                        <div class="step-dot">2</div>
                        <div class="step-title">Assigned</div>
                    </div>
                    <div class="step-node">
                        <div class="step-dot">3</div>
                        <div class="step-title">Arriving</div>
                    </div>
                    <div class="step-node">
                        <div class="step-dot">4</div>
                        <div class="step-title">In Transit</div>
                    </div>
                    <div class="step-node">
                        <div class="step-dot">5</div>
                        <div class="step-title">Completed</div>
                    </div>
                </div>
 
                <!-- Assigned Driver Card -->
                <div id="driver-info-card" style="display: none; background: #121214; padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color); margin-bottom: 20px; align-items: center; gap: 16px;">
                    <div class="avatar-circle" style="width: 50px; height: 50px; font-size: 20px;">🚖</div>
                    <div style="flex: 1;">
                        <strong id="driver-name" style="font-size: 16px; display: block; color: #fff;">Alex Driver</strong>
                        <small id="driver-vehicle" style="color: var(--text-secondary); display: block;">Yango Comfort (YG-8821-NY)</small>
                    </div>
                    <div>
                        <a id="driver-phone" href="#" class="btn-secondary" style="padding: 8px 14px; font-size: 13px;">📞 Call Driver</a>
                    </div>
                </div>
 
                <div style="background: #121214; padding: 14px; border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 13px; margin-bottom: 20px;">
                    <div style="margin-bottom: 6px;"><strong>📍 Pickup:</strong> <span id="track-pickup">--</span></div>
                    <div style="margin-bottom: 6px;"><strong>🏁 Dropoff:</strong> <span id="track-dropoff">--</span></div>
                    <div><strong>Fare Amount:</strong> <strong id="track-fare" style="color: var(--accent-yellow);">$0.00</strong> (<span id="track-payment">WALLET</span>)</div>
                </div>
 
                <button type="button" id="btn-cancel-ride" class="btn-secondary" style="width: 100%; border-color: var(--danger); color: var(--danger);">
                    Cancel Request
                </button>
            </div>
 
        </div>
 
        <!-- Right Column: Interactive Leaflet Map -->
        <div>
            <div class="map-viewport-card">
                <div id="passenger-map"></div>
            </div>
        </div>
 
    </div>
</div>
 
<script src="assets/js/passenger.js"></script>
 
<?php require_once __DIR__ . '/includes/footer.php'; ?>
 
