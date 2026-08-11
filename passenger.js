// passenger.js - Passenger Booking Flow & Live Map Tracking
 
let passengerMap = null;
let activeRidePollTimer = null;
let simulatedMoveTimer = null;
 
document.addEventListener('DOMContentLoaded', () => {
    // Initialize Passenger Map if map element exists
    if (document.getElementById('passenger-map')) {
        passengerMap = new YangoMap('passenger-map', 40.712776, -74.005974, 13);
 
        // Listen to click on map to automatically pick pickup or dropoff point
        let clickMode = 'pickup';
        passengerMap.map.on('click', (e) => {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);
 
            if (clickMode === 'pickup') {
                document.getElementById('pickup_lat').value = lat;
                document.getElementById('pickup_lng').value = lng;
                document.getElementById('pickup_address').value = `Map Point (${lat}, ${lng})`;
                passengerMap.setPickupMarker(lat, lng, "Selected Pickup");
                clickMode = 'dropoff';
            } else {
                document.getElementById('dropoff_lat').value = lat;
                document.getElementById('dropoff_lng').value = lng;
                document.getElementById('dropoff_address').value = `Map Point (${lat}, ${lng})`;
                passengerMap.setDropoffMarker(lat, lng, "Selected Dropoff");
                clickMode = 'pickup';
            }
            updateFareEstimate();
        });
    }
 
    // Auto update fare estimate on address input change
    const pAddress = document.getElementById('pickup_address');
    const dAddress = document.getElementById('dropoff_address');
    if (pAddress && dAddress) {
        pAddress.addEventListener('input', updateFareEstimate);
        dAddress.addEventListener('input', updateFareEstimate);
    }
 
    // Handle Ride Booking Form Submission
    const rideForm = document.getElementById('form-ride');
    if (rideForm) {
        rideForm.addEventListener('submit', (e) => {
            e.preventDefault();
            submitBooking('ride');
        });
    }
 
    // Handle Parcel Delivery Form Submission
    const deliveryForm = document.getElementById('form-delivery');
    if (deliveryForm) {
        deliveryForm.addEventListener('submit', (e) => {
            e.preventDefault();
            submitBooking('delivery');
        });
    }
 
    // Cancel Active Ride Button
    const btnCancel = document.getElementById('btn-cancel-ride');
    if (btnCancel) {
        btnCancel.addEventListener('click', cancelActiveRide);
    }
 
    // Start auto polling active ride
    pollActiveRide();
    setInterval(pollActiveRide, 3000);
});
 
function updateFareEstimate() {
    const pLat = parseFloat(document.getElementById('pickup_lat')?.value || 40.712776);
    const pLng = parseFloat(document.getElementById('pickup_lng')?.value || -74.005974);
    const dLat = parseFloat(document.getElementById('dropoff_lat')?.value || 40.730610);
    const dLng = parseFloat(document.getElementById('dropoff_lng')?.value || -73.935242);
 
    const dist = calculateDistance(pLat, pLng, dLat, dLng);
    const selectedTier = document.getElementById('selected_tier')?.value || 'Economy';
    const fare = estimateFare(dist, 'ride', selectedTier);
 
    const estDistEl = document.getElementById('est-distance');
    const estFareEl = document.getElementById('est-fare');
    if (estDistEl) estDistEl.textContent = dist + ' km';
    if (estFareEl) estFareEl.textContent = '$' + fare;
 
    if (passengerMap) {
        passengerMap.drawRoute(pLat, pLng, dLat, dLng);
    }
}
 
function submitBooking(serviceType) {
    const pAddress = document.getElementById('pickup_address')?.value;
    const dAddress = document.getElementById('dropoff_address')?.value;
    const pLat = document.getElementById('pickup_lat')?.value || 40.712776;
    const pLng = document.getElementById('pickup_lng')?.value || -74.005974;
    const dLat = document.getElementById('dropoff_lat')?.value || 40.730610;
    const dLng = document.getElementById('dropoff_lng')?.value || -73.935242;
    const tier = document.getElementById('selected_tier')?.value || 'Economy';
    const paymentMethod = document.getElementById('payment_method')?.value || 'wallet';
 
    const dist = calculateDistance(pLat, pLng, dLat, dLng);
 
    const payload = {
        service_type: serviceType,
        tier: tier,
        pickup_address: pAddress,
        dropoff_address: dAddress,
        pickup_lat: pLat,
        pickup_lng: pLng,
        dropoff_lat: dLat,
        dropoff_lng: dLng,
        distance_km: dist,
        payment_method: paymentMethod,
        package_details: document.getElementById('package_details')?.value || '',
        package_weight: document.getElementById('package_weight')?.value || '',
        recipient_name: document.getElementById('recipient_name')?.value || '',
        recipient_phone: document.getElementById('recipient_phone')?.value || ''
    };
 
    fetch('api/rides.php?action=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            pollActiveRide();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(err => showToast('Error creating booking request', 'error'));
}
 
function pollActiveRide() {
    fetch('api/rides.php?action=get_active')
    .then(res => res.json())
    .then(data => {
        if (data.success && data.active_ride) {
            renderActiveRidePanel(data.active_ride);
        } else {
            hideActiveRidePanel();
        }
    });
}
 
function renderActiveRidePanel(ride) {
    const bookingPanel = document.getElementById('booking-forms-panel');
    const trackerPanel = document.getElementById('active-tracker-panel');
 
    if (bookingPanel) bookingPanel.style.display = 'none';
    if (trackerPanel) trackerPanel.style.display = 'block';
 
    // Update text indicators
    document.getElementById('track-service-type').textContent = (ride.service_type === 'delivery' ? '📦 Parcel Delivery' : '🚗 Ride Booking') + ' #' + ride.id;
    document.getElementById('track-pickup').textContent = ride.pickup_address;
    document.getElementById('track-dropoff').textContent = ride.dropoff_address;
    document.getElementById('track-fare').textContent = '$' + parseFloat(ride.fare_amount).toFixed(2);
    document.getElementById('track-payment').textContent = ride.payment_method.toUpperCase();
 
    // Update Stepper nodes
    const steps = ['requested', 'accepted', 'arriving', 'in_progress', 'completed'];
    const currIndex = steps.indexOf(ride.status);
    const progressPercent = Math.max(0, (currIndex / (steps.length - 1)) * 100);
 
    const fillBar = document.getElementById('stepper-fill');
    if (fillBar) fillBar.style.width = progressPercent + '%';
 
    document.querySelectorAll('.step-node').forEach((node, i) => {
        if (i <= currIndex) {
            node.classList.add('active');
        } else {
            node.classList.remove('active');
        }
    });
 
    // Update Driver Information Card if driver assigned
    const driverCard = document.getElementById('driver-info-card');
    if (ride.driver_id && ride.driver_name) {
        if (driverCard) driverCard.style.display = 'flex';
        document.getElementById('driver-name').textContent = ride.driver_name;
        document.getElementById('driver-vehicle').textContent = `${ride.vehicle_type} (${ride.vehicle_number})`;
        document.getElementById('driver-phone').href = 'tel:' + ride.driver_phone;
 
        // Render Driver Position on Map
        if (passengerMap && ride.driver_lat && ride.driver_lng) {
            passengerMap.setPickupMarker(ride.pickup_lat, ride.pickup_lng);
            passengerMap.setDropoffMarker(ride.dropoff_lat, ride.dropoff_lng);
            passengerMap.drawRoute(ride.pickup_lat, ride.pickup_lng, ride.dropoff_lat, ride.dropoff_lng);
            passengerMap.updateDriverPosition(ride.driver_lat, ride.driver_lng, ride.vehicle_type);
        }
 
        // Trigger simulation ping to move driver position gradually
        fetch('api/location.php?action=simulate_step', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ride_id: ride.id })
        });
    } else {
        if (driverCard) driverCard.style.display = 'none';
        if (passengerMap) {
            passengerMap.setPickupMarker(ride.pickup_lat, ride.pickup_lng);
            passengerMap.setDropoffMarker(ride.dropoff_lat, ride.dropoff_lng);
            passengerMap.drawRoute(ride.pickup_lat, ride.pickup_lng, ride.dropoff_lat, ride.dropoff_lng);
        }
    }
}
 
function hideActiveRidePanel() {
    const bookingPanel = document.getElementById('booking-forms-panel');
    const trackerPanel = document.getElementById('active-tracker-panel');
 
    if (bookingPanel) bookingPanel.style.display = 'block';
    if (trackerPanel) trackerPanel.style.display = 'none';
}
 
function cancelActiveRide() {
    if (!confirm('Are you sure you want to cancel this booking?')) return;
 
    fetch('api/rides.php?action=get_active')
    .then(res => res.json())
    .then(data => {
        if (data.active_ride) {
            fetch('api/rides.php?action=cancel', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ride_id: data.active_ride.id })
            })
            .then(res => res.json())
            .then(res => {
                showToast(res.message, 'info');
                hideActiveRidePanel();
            });
        }
    });
}
 
