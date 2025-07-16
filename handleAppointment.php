<?php
session_start();
require_once 'database.inc.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header("Location: login.php");
    exit;
}

$pdo = db_connect();

$action = $_POST['action'] ?? '';
$flat_ref = $_POST['flat_ref'] ?? '';
$day = $_POST['day'] ?? '';
$time = $_POST['time'] ?? '';
$customer_id = $_POST['customer_id'] ?? '';
$owner_id = $_SESSION['user']['id'];

if (!$action || !$flat_ref || !$day || !$time || !$customer_id) {
    die("Invalid request.");
}

$title = "Flat Preview Response";
if ($action === 'accept') {
    $body = "Your flat preview request for Ref $flat_ref on $day at $time has been accepted.";
} else {
    $body = "Your flat preview request for Ref $flat_ref on $day at $time has been rejected.";
}

$stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, sender_role, receiver_role, title, body, created_at)
                       VALUES (?, ?, 'owner', 'customer', ?, ?, NOW())");
$stmt->execute([$owner_id, $customer_id, $title, $body]);

header("Location: view_messages.php");
exit;
?>
