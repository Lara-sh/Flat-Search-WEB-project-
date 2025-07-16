<?php
session_start();
require_once 'database.inc.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

$pdo = db_connect();

$slot_id = $_POST['slot_id'] ?? null;
$flat_ref = $_POST['flat_ref'] ?? null;
$customer_id = $_SESSION['user']['id'];

if (!$slot_id || !$flat_ref) {
    die("Invalid request.");
}

// Mark slot as booked if available
$stmt = $pdo->prepare("UPDATE preview_timetable SET is_booked = 1 WHERE id = ? AND is_booked = 0");
$stmt->execute([$slot_id]);

if ($stmt->rowCount() === 0) {
    die("This slot is already booked or does not exist.");
}

// Fetch slot details and owner info 
$stmt = $pdo->prepare("
    SELECT pt.day, pt.time, f.owner_id 
    FROM preview_timetable pt
    JOIN flats f ON pt.flat_id = f.id
    WHERE pt.id = ?");
$stmt->execute([$slot_id]);
$slot = $stmt->fetch();

if (!$slot) {
    die("Slot not found.");
}

// Compose and insert message to owner
$message = "Customer has requested a flat preview for Ref $flat_ref on {$slot['day']} at {$slot['time']}.";

$stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, sender_role, receiver_role, title, body, created_at) 
                       VALUES (?, ?, 'customer', 'owner', 'Flat Preview Request', ?, NOW())");
$stmt->execute([
    $customer_id,
    $slot['owner_id'],
    $message
]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Request Sent</title>
<link rel="stylesheet" href="style.css" />
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <main>
        <h2>Appointment Request Sent</h2>
        <p>Your preview request for flat <strong><?= htmlspecialchars($flat_ref) ?></strong> on <?= htmlspecialchars($slot['day']) ?> at <?= htmlspecialchars($slot['time']) ?> has been sent to the owner.</p>
        <p>Please wait for the owner's confirmation message.</p>
    </main>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
