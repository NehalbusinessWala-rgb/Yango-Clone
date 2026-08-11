<?php
// api/wallet.php - Wallet Recharge & Ledger API
 
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
 
if ($action === 'topup') {
    $amount = (float)($data['amount'] ?? 0);
    $paymentSource = trim($data['payment_source'] ?? 'Credit Card (Simulated)');
 
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid top-up amount.']);
        exit;
    }
 
    // Add balance to user
    $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
    $stmt->execute([$amount, $userId]);
 
    // Record credit transaction
    $stmtTx = $pdo->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description) VALUES (?, 'credit', ?, ?)");
    $stmtTx->execute([$userId, $amount, "Wallet top-up via " . htmlspecialchars($paymentSource)]);
 
    // Fetch updated balance
    $stmtBal = $pdo->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $stmtBal->execute([$userId]);
    $newBalance = (float)$stmtBal->fetchColumn();
    $_SESSION['wallet_balance'] = $newBalance;
 
    echo json_encode([
        'success' => true,
        'message' => "Successfully added $" . number_format($amount, 2) . " to your wallet!",
        'new_balance' => $newBalance
    ]);
    exit;
}
 
if ($action === 'get_ledger') {
    $stmt = $pdo->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY id DESC LIMIT 20");
    $stmt->execute([$userId]);
    $transactions = $stmt->fetchAll();
 
    echo json_encode([
        'success' => true,
        'transactions' => $transactions
    ]);
    exit;
}
 
echo json_encode(['success' => false, 'message' => 'Invalid wallet action']);
 
