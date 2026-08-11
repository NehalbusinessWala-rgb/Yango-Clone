<?php
// api/location.php - Driver GPS Location & Simulation Endpoint
 
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
 
$pdo = getDBConnection();
$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
 
if ($action === 'update_location') {
    $driverId = $_SESSION['user_id'] ?? null;
    $lat = (float)($data['lat'] ?? 40.712776);
    $lng = (float)($data['lng'] ?? -74.005974);
 
    if ($driverId) {
        $stmt = $pdo->prepare("UPDATE users SET current_lat = :lat, current_lng = :lng WHERE id = :id");
        $stmt->execute(['lat' => $lat, 'lng' => $lng, 'id' => $driverId]);
    }
 
    echo json_encode(['success' => true]);
    exit;
}
 
if ($action === 'simulate_step') {
    $rideId = (int)($data['ride_id'] ?? 0);
    $stmt = $pdo->prepare("
        SELECT r.*, d.current_lat as driver_lat, d.current_lng as driver_lng, d.id as driver_id 
        FROM rides r 
        JOIN users d ON r.driver_id = d.id 
        WHERE r.id = ?
    ");
    $stmt->execute([$rideId]);
    $ride = $stmt->fetch();
 
    if ($ride && $ride['driver_id']) {
        $status = $ride['status'];
        $dLat = (float)$ride['driver_lat'];
        $dLng = (float)$ride['driver_lng'];
 
        // Target coordinates depending on step (heading to pickup or heading to dropoff)
        $targetLat = ($status === 'accepted' || $status === 'arriving') ? (float)$ride['pickup_lat'] : (float)$ride['dropoff_lat'];
        $targetLng = ($status === 'accepted' || $status === 'arriving') ? (float)$ride['pickup_lng'] : (float)$ride['dropoff_lng'];
 
        // Interpolate 10% step towards target
        $newLat = $dLat + ($targetLat - $dLat) * 0.15;
        $newLng = $dLng + ($targetLng - $dLng) * 0.15;
 
        // Update driver position
        $pdo->prepare("UPDATE users SET current_lat = ?, current_lng = ? WHERE id = ?")->execute([$newLat, $newLng, $ride['driver_id']]);
 
        echo json_encode([
            'success' => true,
            'driver_lat' => $newLat,
            'driver_lng' => $newLng
        ]);
        exit;
    }
}
 
echo json_encode(['success' => false, 'message' => 'Invalid location request']);
 
