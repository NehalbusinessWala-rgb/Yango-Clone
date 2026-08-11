<?php
// api/auth.php - Authentication Endpoint
 
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
 
$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
 
$pdo = getDBConnection();
 
if ($action === 'signup') {
    $fullName = trim($data['full_name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'] ?? '';
    $role = in_array($data['role'] ?? '', ['user', 'driver']) ? $data['role'] : 'user';
    $vehicleType = trim($data['vehicle_type'] ?? '');
    $vehicleNumber = trim($data['vehicle_number'] ?? '');
 
    if (empty($fullName) || empty($email) || empty($phone) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit;
    }
 
    // Check existing email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists.']);
        exit;
    }
 
    $passHash = password_hash($password, PASSWORD_BCRYPT);
    $initialWallet = ($role === 'user') ? 50.00 : 20.00; // Welcome signup bonus
 
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role, vehicle_type, vehicle_number, wallet_balance, status) VALUES (:name, :email, :phone, :pass, :role, :vtype, :vnum, :wallet, 'online')");
    $stmt->execute([
        'name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'pass' => $passHash,
        'role' => $role,
        'vtype' => $role === 'driver' ? ($vehicleType ?: 'Yango Express Comfort') : null,
        'vnum' => $role === 'driver' ? ($vehicleNumber ?: 'YG-1001-NY') : null,
        'wallet' => $initialWallet
    ]);
 
    $userId = $pdo->lastInsertId();
 
    // Log wallet initial bonus transaction
    $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description) VALUES (?, 'credit', ?, 'Welcome Signup Wallet Bonus')")
        ->execute([$userId, $initialWallet]);
 
    // Set session
    $_SESSION['user_id'] = $userId;
    $_SESSION['full_name'] = $fullName;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    $_SESSION['wallet_balance'] = $initialWallet;
 
    $redirect = ($role === 'driver') ? 'driver_dashboard.php' : 'dashboard.php';
 
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Welcome to Yango.',
        'redirect' => $redirect
    ]);
    exit;
}
 
if ($action === 'login') {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
 
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please provide email and password.']);
        exit;
    }
 
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
 
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address or password.']);
        exit;
    }
 
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['wallet_balance'] = (float)$user['wallet_balance'];
 
    $redirect = ($user['role'] === 'driver') ? 'driver_dashboard.php' : 'dashboard.php';
 
    echo json_encode([
        'success' => true,
        'message' => 'Welcome back, ' . htmlspecialchars($user['full_name']),
        'redirect' => $redirect
    ]);
    exit;
}
 
echo json_encode(['success' => false, 'message' => 'Invalid authentication request.']);
 
