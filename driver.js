// driver.js - Driver Command Center & Radar Requests Feed
 
let driverMap = null;
 
document.addEventListener('DOMContentLoaded', () => {
    // Initialize Driver Map
    if (document.getElementById('driver-map')) {
        driverMap = new YangoMap('driver-map', 40.715000, -74.008000, 13);
    }
 
    // Toggle Online / Offline Switch
    const onlineToggle = document.getElementById('driver-status-toggle');
    if (onlineToggle) {
        onlineToggle.addEventListener('change', (e) => {
            const isOnline = e.target.checked;
            toggleDriverOnlineStatus(isOnline ? 'online' : 'offline');
        });
    }
 
    // Initial load and periodic polling
    pollDriverRadar();
    setInterval(pollDriverRadar, 3000);
});
 
function toggleDriverOnlineStatus(status) {
    fetch('api/driver.php?action=toggle_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: status })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, status === 'online' ? 'success' : 'info');
            const statusLabel = document.getElementById('status-label-text');
            if (statusLabel) {
                statusLabel.textContent = status.toUpperCase();
                statusLabel.style.color = status === 'online' ? '#2ECC71' : '#E74C3C';
            }
        }
    });
}
 
function pollDriverRadar() {
    fetch('api/driver.php?action=get_available_requests')
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (data.active_ride) {
                renderDriverActiveTrip(data.active_ride);
            } else {
                renderRequestsFeed(data.requests);
            }
        }
    });
}
 
function renderRequestsFeed(requests) {
    const radarContainer = document.getElementById('requests-radar-container');
    const activeTripPanel = document.getElementById('driver-active-trip-panel');
 
    if (activeTripPanel) activeTripPanel.style.display = 'none';
    if (radarContainer) radarContainer.style.display = 'block';
 
    if (!requests || requests.length === 0) {
        radarContainer.innerHTML = `
            <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                <div style="font-size: 40px; margin-bottom: 10px;">📡</div>
                <h4>Scanning for nearby requests...</h4>
                <p style="font-size: 13px;">Make sure your status is set to ONLINE to receive instant ride & delivery jobs.</p>
            </div>
        `;
        return;
    }
 
    let html = '<h4 style="margin-bottom: 16px; color: var(--accent-yellow);">⚡ Incoming Dispatch Radar (' + requests.length + ')</h4>';
 
    requests.forEach(req => {
        const isDelivery = req.service_type === 'delivery';
        html += `
            <div class="driver-request-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span class="request-badge ${isDelivery ? 'badge-delivery' : 'badge-ride'}">
                        ${isDelivery ? '📦 Parcel Delivery' : '🚗 Ride Booking'} (${req.tier})
                    </span>
                    <strong style="color: var(--accent-yellow); font-size: 18px;">$${parseFloat(req.fare_amount).toFixed(2)}</strong>
                </div>
 
                <div style="font-size: 14px; margin-bottom: 6px;">
                    <strong style="color: #2ECC71;">📍 Pickup:</strong> ${req.pickup_address}
                </div>
                <div style="font-size: 14px; margin-bottom: 10px;">
                    <strong style="color: #E74C3C;">🏁 Dropoff:</strong> ${req.dropoff_address}
                </div>
 
                ${isDelivery ? `
                    <div style="background: #1C1C21; padding: 8px 12px; border-radius: 8px; font-size: 12px; margin-bottom: 12px; border: 1px solid var(--border-color);">
                        <strong>Package:</strong> ${req.package_details || 'Standard Parcel'} (${req.package_weight || 'Light'})<br>
                        <strong>Recipient:</strong> ${req.recipient_name || 'N/A'} (${req.recipient_phone || 'N/A'})
                    </div>
                ` : ''}
 
                <div style="display: flex; gap: 10px; align-items: center;">
                    <button class="btn-primary" style="flex: 1; padding: 10px;" onclick="acceptRide(${req.id})">Accept Request</button>
                    <small style="color: var(--text-muted);">${req.distance_km} km</small>
                </div>
            </div>
        `;
    });
 
    radarContainer.innerHTML = html;
}
 
function acceptRide(rideId) {
    fetch('api/driver.php?action=accept_ride', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ride_id: rideId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            pollDriverRadar();
        } else {
            showToast(data.message, 'error');
        }
    });
}
 
function renderDriverActiveTrip(ride) {
    const radarContainer = document.getElementById('requests-radar-container');
    const activeTripPanel = document.getElementById('driver-active-trip-panel');
 
    if (radarContainer) radarContainer.style.display = 'none';
    if (activeTripPanel) activeTripPanel.style.display = 'block';
 
    document.getElementById('driver-trip-title').textContent = (ride.service_type === 'delivery' ? '📦 Parcel Delivery' : '🚗 Ride') + ' #' + ride.id;
    document.getElementById('driver-passenger-name').textContent = ride.passenger_name;
    document.getElementById('driver-passenger-phone').href = 'tel:' + ride.passenger_phone;
    document.getElementById('driver-pickup-text').textContent = ride.pickup_address;
    document.getElementById('driver-dropoff-text').textContent = ride.dropoff_address;
    document.getElementById('driver-fare-text').textContent = '$' + parseFloat(ride.fare_amount).toFixed(2);
 
    // Setup action buttons based on step
    const btnAction = document.getElementById('btn-driver-next-step');
    if (btnAction) {
        if (ride.status === 'accepted') {
            btnAction.textContent = '📍 Mark Arrived at Pickup';
            btnAction.onclick = () => updateTripStep(ride.id, 'arriving');
        } else if (ride.status === 'arriving') {
            btnAction.textContent = '🚀 Start Trip / Picked Up';
            btnAction.onclick = () => updateTripStep(ride.id, 'in_progress');
        } else if (ride.status === 'in_progress') {
            btnAction.textContent = '🏁 Complete Trip & Collect Payout';
            btnAction.onclick = () => updateTripStep(ride.id, 'completed');
        }
    }
 
    // Render on Map
    if (driverMap) {
        driverMap.setPickupMarker(ride.pickup_lat, ride.pickup_lng, "Pickup Customer");
        driverMap.setDropoffMarker(ride.dropoff_lat, ride.dropoff_lng, "Destination");
        driverMap.drawRoute(ride.pickup_lat, ride.pickup_lng, ride.dropoff_lat, ride.dropoff_lng);
    }
}
 
function updateTripStep(rideId, nextStep) {
    fetch('api/driver.php?action=update_trip_step', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ride_id: rideId, step: nextStep })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            pollDriverRadar();
            if (nextStep === 'completed') {
                setTimeout(() => window.location.reload(), 1500);
            }
        } else {
            showToast(data.message, 'error');
        }
    });
}
 
