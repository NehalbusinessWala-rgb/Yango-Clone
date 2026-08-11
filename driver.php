<?php
// api/driver.php - Driver Dispatch Radar & Status API
 
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';
 
$pdo = getDBConnection();
$driverId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? 'user';
 
if (!$driverId || $userRole !== 'driver') {
    echo json_encode(['success' => false, 'message' => 'Driver authorization required']);
    exit;
}
 
$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
 
// Toggle Online / Offline driver status
if ($action === 'toggle_status') {
    $status = in_array($data['status'] ?? '', ['online', 'offline']) ? $data['status'] : 'online';
    $stmt = $pdo->prepare("UPDATE users SET status = :st WHERE id = :id");
    $stmt->execute(['st' => $status, 'id' => $driverId]);
 
    echo json_encode(['success' => true, 'status' => $status, 'message' => "You are now {$status}."]);
    exit;
}
 
// Fetch nearby pending ride and parcel delivery requests
if ($action === 'get_available_requests') {
    $stmt = $pdo->prepare("
        SELECT r.*, u.full_name as passenger_name, u.phone as passenger_phone 
        FROM rides r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.status = 'requested' AND r.driver_id IS NULL 
        ORDER BY r.id DESC LIMIT 10
    ");
    $stmt->execute();
    $requests = $stmt->fetchAll();
 
    // Check if driver currently has an active ride
    $stmtActive = $pdo->prepare("
        SELECT r.*, u.full_name as passenger_name, u.phone as passenger_phone 
        FROM rides r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.driver_id = :did AND r.status IN ('accepted', 'arriving', 'in_progress') 
        LIMIT 1
    ");
    $stmtActive->execute(['did' => $driverId]);
    $currentRide = $stmtActive->fetch();
 
    echo json_encode([
        'success' => true,
        'requests' => $requests,
        'active_ride' => $currentRide ?: null
    ]);
    exit;
}
 
// Accept a Ride / Delivery Request
if ($action === 'accept_ride') {
    $rideId = (int)($data['ride_id'] ?? 0);
 
    // Verify ride is still available
    $stmtCheck = $pdo->prepare("SELECT id, status FROM rides WHERE id = ? FOR UPDATE");
    $stmtCheck->execute([$rideId]);
    $ride = $stmtCheck->fetch();
 
    if (!$ride || $ride['status'] !== 'requested') {
        echo json_encode(['success' => false, 'message' => 'Ride was taken by another driver or cancelled.']);
        exit;
    }
 
    $stmtUpdate = $pdo->prepare("UPDATE rides SET driver_id = :did, status = 'accepted' WHERE id = :rid");
    $stmtUpdate->execute(['did' => $driverId, 'rid' => $rideId]);
 
    // Update driver status to busy
    $pdo->prepare("UPDATE users SET status = 'busy' WHERE id = ?")->execute([$driverId]);
 
    echo json_encode(['success' => true, 'message' => 'Ride accepted successfully! Head to pickup location.']);
    exit;
}
 
// Update Trip Status (arriving, in_progress, completed)
if ($action === 'update_trip_step') {
    $rideId = (int)($data['ride_id'] ?? 0);
    $nextStep = in_array($data['step'] ?? '', ['arriving', 'in_progress', 'completed', 'cancelled']) ? $data['step'] : '';
 
    if (!$nextStep) {
        echo json_encode(['success' => false, 'message' => 'Invalid step transition']);
        exit;
    }
 
    // Fetch ride details
    $stmtRide = $pdo->prepare("SELECT * FROM rides WHERE id = :rid AND driver_id = :did");
    $stmtRide->execute(['rid' => $rideId, 'did' => $driverId]);
    $ride = $stmtRide->fetch();
 
    if (!$ride) {
        echo json_encode(['success' => false, 'message' => 'Ride not found']);
        exit;
    }
 
    // Handle completed trip payment & earnings transfer
    if ($nextStep === 'completed') {
        $fareAmount = (float)$ride['fare_amount'];
        $passengerId = (int)$ride['user_id'];
        $paymentMethod = $ride['payment_method'];
 
        // If paying by wallet, deduct from passenger & credit driver
        if ($paymentMethod === 'wallet') {
            // Deduct passenger balance
            $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?")->execute([$fareAmount, $passengerId]);
            $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description) VALUES (?, 'debit', ?, ?)")
                ->execute([$passengerId, $fareAmount, "Fare payment for Ride #{$rideId}"]);
        }
 
        // Add 85% earnings to driver wallet (15% Yango platform commission)
        $driverEarnings = $fareAmount * 0.85;
        $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?")->execute([$driverEarnings, $driverId]);
        $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description) VALUES (?, 'credit', ?, ?)")
            ->execute([$driverId, $driverEarnings, "Earnings for Ride #{$rideId} (after 15% fee)"]);
 
        // Mark ride paid and completed
        $pdo->prepare("UPDATE rides SET status = 'completed', payment_status = 'paid' WHERE id = ?")->execute([$rideId]);
        // Set driver status back to online
        $pdo->prepare("UPDATE users SET status = 'online' WHERE id = ?")->execute([$driverId]);
 
        // Refresh session wallet balance
        $stmtW = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
        $stmtW->execute([$driverId]);
        $_SESSION['wallet_balance'] = (float)$stmtW->fetchColumn();
 
        echo json_encode([
            'success' => true,
            'message' => "Trip completed! Earned $" . number_format($driverEarnings, 2),
            'status' => 'completed'
        ]);
        exit;
    }
 
    // Normal step update (arriving, in_progress)
    $stmtUp = $pdo->prepare("UPDATE rides SET status = :st WHERE id = :rid");
    $stmtUp->execute(['st' => $nextStep, 'rid' => $rideId]);
 
    echo json_encode([
        'success' => true,
        'message' => "Trip status updated to " . ucfirst(str_replace('_', ' ', $nextStep)),
        'status' => $nextStep
    ]);
    exit;
}
 
echo json_encode(['success' => false, 'message' => 'Invalid driver request']);
 
