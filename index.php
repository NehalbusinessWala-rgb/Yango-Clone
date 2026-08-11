<?php
require_once __DIR__ . '/includes/header.php';
?>
 
<!-- Hero Banner Section -->
<section class="hero-section" style="padding: 60px 0; background: linear-gradient(180deg, rgba(255, 204, 0, 0.08) 0%, rgba(18, 18, 20, 0) 100%);">
    <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: center;">
        <div>
            <span class="logo-badge" style="margin-bottom: 14px; display: inline-block;">URBAN MOBILITY & LOGISTICS</span>
            <h1 style="font-size: 48px; margin-bottom: 16px; letter-spacing: -1px;">Ride Fast. Send Packages. Live Track Anywhere.</h1>
            <p style="font-size: 18px; color: var(--text-secondary); margin-bottom: 30px;">
                Experience the next level of urban mobility inspired by Yango. Book comfortable city rides or dispatch parcel deliveries with instant driver matching and real-time map tracking.
            </p>
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <a href="dashboard.php" class="btn-primary" style="padding: 14px 32px; font-size: 16px;">Book a Ride / Delivery</a>
                <a href="signup.php?role=driver" class="btn-secondary" style="padding: 14px 32px; font-size: 16px;">Drive & Earn with Yango</a>
            </div>
        </div>
 
        <div>
            <div class="map-viewport-card" style="height: 420px; border: 1px solid var(--border-highlight);">
                <div id="hero-preview-map" style="width: 100%; height: 100%;"></div>
            </div>
        </div>
    </div>
</section>
 
<!-- Service Highlights Section -->
<section id="services" style="padding: 80px 0; background: #18181E; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">
        <div style="text-align: center; margin-bottom: 50px;">
            <h2 style="font-size: 36px; margin-bottom: 10px;">Our Core Services</h2>
            <p style="color: var(--text-secondary);">Designed for fast, secure city travel and package delivery.</p>
        </div>
 
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">
            <div class="glass-panel" style="transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-6px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="font-size: 42px; margin-bottom: 16px;">🚗</div>
                <h3 style="font-size: 22px; margin-bottom: 10px; color: var(--accent-yellow);">Yango Rides</h3>
                <p style="color: var(--text-secondary); margin-bottom: 20px;">Comfortable, transparently priced rides across town with top-rated verified drivers.</p>
                <ul style="color: var(--text-muted); font-size: 14px; list-style: none; line-height: 1.8;">
                    <li>✓ Economy & Comfort vehicle tiers</li>
                    <li>✓ Upfront fare calculation before booking</li>
                    <li>✓ In-app live map driver tracking</li>
                </ul>
            </div>
 
            <div class="glass-panel" style="transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-6px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="font-size: 42px; margin-bottom: 16px;">📦</div>
                <h3 style="font-size: 22px; margin-bottom: 10px; color: var(--accent-yellow);">Parcel Delivery</h3>
                <p style="color: var(--text-secondary); margin-bottom: 20px;">Send documents, gifts, or heavy packages door-to-door with express courier dispatch.</p>
                <ul style="color: var(--text-muted); font-size: 14px; list-style: none; line-height: 1.8;">
                    <li>✓ Express bike & car parcel couriers</li>
                    <li>✓ SMS recipient updates & status step tracking</li>
                    <li>✓ Proof of delivery confirmation</li>
                </ul>
            </div>
 
            <div class="glass-panel" style="transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-6px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="font-size: 42px; margin-bottom: 16px;">💳</div>
                <h3 style="font-size: 22px; margin-bottom: 10px; color: var(--accent-yellow);">Wallet & Cash Payments</h3>
                <p style="color: var(--text-secondary); margin-bottom: 20px;">Seamless wallet integration allowing instant top-ups, automated fare deductions, and cash options.</p>
                <ul style="color: var(--text-muted); font-size: 14px; list-style: none; line-height: 1.8;">
                    <li>✓ In-app digital wallet top-up</li>
                    <li>✓ Instant payouts for drivers</li>
                    <li>✓ Complete transparent transaction log</li>
                </ul>
            </div>
        </div>
    </div>
</section>
 
<!-- Interactive Quick Estimator Section -->
<section id="estimate" style="padding: 80px 0;">
    <div style="max-width: 1000px; margin: 0 auto; padding: 0 24px;">
        <div class="glass-panel" style="border: 1px solid var(--border-highlight);">
            <div style="text-align: center; margin-bottom: 24px;">
                <h2 style="font-size: 28px; margin-bottom: 8px;">Instant Trip & Delivery Fare Calculator</h2>
                <p style="color: var(--text-secondary);">Estimate distance and cost before creating your booking.</p>
            </div>
 
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label class="form-label">Pickup Point</label>
                    <input type="text" id="calc-pickup" class="form-control" value="Times Square, NYC" placeholder="Enter pickup address">
                </div>
                <div>
                    <label class="form-label">Destination</label>
                    <input type="text" id="calc-dropoff" class="form-control" value="Central Park, NYC" placeholder="Enter destination address">
                </div>
            </div>
 
            <div style="display: flex; justify-content: space-between; align-items: center; background: #121214; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div>
                    <span style="color: var(--text-secondary); font-size: 14px;">Estimated Distance:</span>
                    <strong id="calc-dist" style="display: block; font-size: 20px; color: #fff;">5.20 km</strong>
                </div>
                <div>
                    <span style="color: var(--text-secondary); font-size: 14px;">Estimated Economy Fare:</span>
                    <strong id="calc-price" style="display: block; font-size: 26px; color: var(--accent-yellow);">$9.24</strong>
                </div>
                <div>
                    <a href="dashboard.php" class="btn-primary">Book This Ride</a>
                </div>
            </div>
        </div>
    </div>
</section>
 
<!-- Driver CTA Banner -->
<section id="driver-signup" style="padding: 60px 0; background: linear-gradient(135deg, #1C1C21 0%, #2B2B36 100%); text-align: center; border-top: 1px solid var(--border-color);">
    <div style="max-width: 800px; margin: 0 auto; padding: 0 24px;">
        <span class="logo-badge" style="margin-bottom: 16px; display: inline-block;">JOIN THE YANGO FLEET</span>
        <h2 style="font-size: 36px; margin-bottom: 16px;">Drive with Yango and Keep 85% of Every Fare</h2>
        <p style="color: var(--text-secondary); font-size: 16px; margin-bottom: 30px;">
            Set your own flexible schedule, accept ride & parcel delivery jobs in real time, and withdraw earnings directly to your wallet.
        </p>
        <a href="signup.php?role=driver" class="btn-primary" style="padding: 14px 36px; font-size: 16px;">Sign Up as a Driver Now</a>
    </div>
</section>
 
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Initialize hero preview map
    const heroMap = new YangoMap('hero-preview-map', 40.712776, -74.005974, 13);
    heroMap.setPickupMarker(40.712776, -74.005974, "Financial District");
    heroMap.setDropoffMarker(40.758896, -73.985130, "Times Square");
    heroMap.drawRoute(40.712776, -74.005974, 40.758896, -73.985130);
    heroMap.updateDriverPosition(40.730000, -73.995000, "Car");
});
</script>
 
<?php require_once __DIR__ . '/includes/footer.php'; ?>
 
