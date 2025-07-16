<?php
session_start();
require_once 'database.inc.php';

// Check if the user is logged in and is a manager
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'manager') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = db_connect();

    // Validate inputs
    $flat_id = isset($_POST['flat_id']) ? (int)$_POST['flat_id'] : 0;
    $action = $_POST['action'] ?? '';

    if ($flat_id <= 0 || !in_array($action, ['approve', 'reject'])) {
        header("Location: messages.php");
        exit;
    }

    // Get the flat's owner
    $stmt = $pdo->prepare("SELECT owner_id FROM flats WHERE id = ?");
    $stmt->execute([$flat_id]);
    $flat = $stmt->fetch();

    if (!$flat) {
        header("Location: messages.php");
        exit;
    }

    $ownerId = $flat['owner_id'];

    // Determine approval status and message
    if ($action === 'approve') {
        $is_approved = 1;
        $approval_message = 'Your flat offer has been approved.';
        $title = 'Flat Approved';
        $flatRef = 'FLAT' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    } else {
        $is_approved = 0;
        $approval_message = 'Your flat offer has been rejected.';
        $title = 'Flat Rejected';
        $flatRef = null; 
    }

    // Update flats table
    $stmt = $pdo->prepare("UPDATE flats SET is_approved = ?, approval_message = ?, ref_number = ? WHERE id = ?");
    $stmt->execute([$is_approved, $approval_message, $flatRef, $flat_id]);

    // Get manager's username from session
    $sender = $_SESSION['user']['username'] ?? 'Manager';

    // Insert notification message to flat owner
    $body = $approval_message;

    $stmt = $pdo->prepare("INSERT INTO messages (sender, receiver_id, receiver_role, title, body, is_read, created_at, flat_id) 
                           VALUES (?, ?, ?, ?, ?, 0, NOW(), ?)");
    $stmt->execute([
        $sender,
        $ownerId,
        'owner',
        $title,
        $body,
        $flat_id
    ]);

    // Redirect to messages or search page
    header("Location: search.php");
    exit;
}
?>
