<?php
// api/rides.php - Ride & Parcel Delivery Booking Engine
 
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
 
$pdo = getDBConnection();
$userId = $_SESSION['user_id'] ?? null;
 
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}
 
$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
 
if ($action === 'create') {
    $serviceType = in_array($data['service_type'] ?? '', ['ride', 'delivery']) ? $data['service_type'] : 'ride';
    $tier = trim($data['tier'] ?? 'Economy');
    $pickupAddress = trim($data['pickup_address'] ?? '');
    $dropoffAddress = trim($data['dropoff_address'] ?? '');
    $pickupLat = (float)($data['pickup_lat'] ?? 40.712776);
    $pickupLng = (float)($data['pickup_lng'] ?? -74.005974);
    $dropoffLat = (float)($data['dropoff_lat'] ?? 40.730610);
    $dropoffLng = (float)($data['dropoff_lng'] ?? -73.935242);
    $distanceKm = (float)($data['distance_km'] ?? 5.2);
    $durationMins = (int)($data['duration_mins'] ?? 15);
    $paymentMethod = in_array($data['payment_method'] ?? '', ['wallet', 'cash', 'card']) ? $data['payment_method'] : 'wallet';
 
    // Package specific details if delivery
    $packageDetails = trim($data['package_details'] ?? '');
    $packageWeight = trim($data['package_weight'] ?? '');
    $recipientName = trim($data['recipient_name'] ?? '');
    $recipientPhone = trim($data['recipient_phone'] ?? '');
 
    // Calculate Fare
    $baseRate = ($serviceType === 'delivery') ? 2.50 : 3.00;
    $perKm = ($tier === 'Comfort') ? 1.80 : 1.20;
    $fareAmount = max(5.00, $baseRate + ($distanceKm * $perKm));
 
    // Check user wallet balance if paying via wallet
    if ($paymentMethod === 'wallet') {
        $stmtUser = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $currWallet = (float)$stmtUser->fetchColumn();
 
        if ($currWallet < $fareAmount) {
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient wallet balance ($' . number_format($currWallet, 2) . '). Required: $' . number_format($fareAmount, 2) . '. Please top up your wallet or choose cash payment.'
            ]);
            exit;
        }
    }
 
    // Insert Ride request into DB
    $stmt = $pdo->prepare("INSERT INTO rides 
        (user_id, service_type, tier, pickup_address, dropoff_address, pickup_lat, pickup_lng, dropoff_lat, dropoff_lng, distance_km, duration_mins, fare_amount, payment_method, status, package_details, package_weight, recipient_name, recipient_phone) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'requested', ?, ?, ?, ?)");
 
    $stmt->execute([
        $userId, $serviceType, $tier, $pickupAddress, $dropoffAddress,
        $pickupLat, $pickupLng, $dropoffLat, $dropoffLng,
        $distanceKm, $durationMins, $fareAmount, $paymentMethod,
        $packageDetails, $packageWeight, $recipientName, $recipientPhone
    ]);
 
    $rideId = $pdo->lastInsertId();
 
    echo json_encode([
        'success' => true,
        'message' => 'Searching for available Yango drivers nearby...',
        'ride_id' => $rideId,
        'fare_amount' => $fareAmount
    ]);
    exit;
}
 
// Get active ride status for passenger
if ($action === 'get_active') {
    $stmt = $pdo->prepare("
        SELECT r.*, 
               d.full_name as driver_name, d.phone as driver_phone, 
               d.vehicle_type, d.vehicle_number, d.rating as driver_rating,
               d.current_lat as driver_lat, d.current_lng as driver_lng
        FROM rides r 
        LEFT JOIN users d ON r.driver_id = d.id 
        WHERE r.user_id = :uid AND r.status IN ('requested', 'accepted', 'arriving', 'in_progress') 
        ORDER BY r.id DESC LIMIT 1
    ");
    $stmt->execute(['uid' => $userId]);
    $activeRide = $stmt->fetch();
 
    echo json_encode([
        'success' => true,
        'active_ride' => $activeRide ?: null
    ]);
    exit;
}
 
// Cancel Ride
if ($action === 'cancel') {
    $rideId = (int)($data['ride_id'] ?? 0);
    $stmt = $pdo->prepare("UPDATE rides SET status = 'cancelled' WHERE id = :id AND user_id = :uid AND status IN ('requested', 'accepted')");
    $stmt->execute(['id' => $rideId, 'uid' => $userId]);
 
    echo json_encode([
        'success' => true,
        'message' => 'Your booking request has been cancelled.'
    ]);
    exit;
}
 
echo json_encode(['success' => false, 'message' => 'Invalid action']);
 
