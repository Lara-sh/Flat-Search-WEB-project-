<?php
session_start();
require_once 'database.inc.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit;
}

$pdo = db_connect();

// Get all POST data
$flat_id = $_POST['flat_id'] ?? null;
$flat_ref = $_POST['flat_ref'] ?? null;
$owner_id = $_POST['owner_id'] ?? null;
$owner_name = $_POST['owner_name'] ?? null;
$owner_mobile = $_POST['owner_mobile'] ?? null;
$start_date = $_POST['start_date'] ?? null;
$end_date = $_POST['end_date'] ?? null;
$total_rent = $_POST['total_rent'] ?? null;

$card_number = $_POST['card_number'] ?? '';
$card_expiry = $_POST['card_expiry'] ?? '';
$card_name = $_POST['card_name'] ?? '';

// Validate required fields
if (!$flat_id || !$owner_id || !$start_date || !$end_date || !$total_rent) {
    die("Missing required rental data.");
}

// Validate credit card number: exactly 9 digits
if (!preg_match('/^\d{9}$/', $card_number)) {
    die("Invalid credit card number. It must be exactly 9 digits.");
}

// TODO: Additional validation on expiry and card_name could be added here

// Extract last 4 digits of card for record
$card_last4 = substr($card_number, -4);

try {
    $pdo->beginTransaction();

    // Insert into rentals table
    $stmt = $pdo->prepare("INSERT INTO rentals (customer_id, flat_id, start_date, end_date, total_rent, payment_card_last4) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user']['id'],
        $flat_id,
        $start_date,
        $end_date,
        $total_rent,
        $card_last4
    ]);

    // Mark flat as rented
    $stmt2 = $pdo->prepare("UPDATE flats SET rented = 1 WHERE id = ?");
    $stmt2->execute([$flat_id]);

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Failed to process rental: " . $e->getMessage());
}

// Fetch owner's email from owners table
$stmt_owner_email = $pdo->prepare("SELECT email FROM owners WHERE ownerID = ?");
$stmt_owner_email->execute([$owner_id]);
$owner_email = $stmt_owner_email->fetchColumn();

if (!$owner_email) {
    $owner_email = 'owner@example.com'; 
}

// Prepare email to owner
$to = $owner_email;
$subject = "New Rental Confirmation for Flat $flat_ref";
$customer_mobile = $_SESSION['user']['mobile'] ?? 'Not Provided';
$message = "Dear $owner_name,\n\nCustomer " . $_SESSION['user']['name'] . " (Mobile: $customer_mobile) has rented your flat $flat_ref.\n\nPlease contact them to arrange key handover.\n\nThanks.";
$headers = 'From: noreply@yourdomain.com' . "\r\n";

// Remove flat from rental_basket for this customer and flat_id
$deleteBasket = $pdo->prepare("DELETE FROM rental_basket WHERE customerID = ? AND flat_id = ?");
$deleteBasket->execute([$_SESSION['user']['id'], $flat_id]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Rental Confirmation</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <?php include 'navigation.php'; ?>
    <main>
        <h2>Rental Confirmed!</h2>
        <p>Thank you, <?= htmlspecialchars($_SESSION['user']['name']) ?>. Your rental for flat <?= htmlspecialchars($flat_ref) ?> is successful.</p>
        <p>You can collect the key from the owner:</p>
        <ul>
            <li>Name: <?= htmlspecialchars($owner_name) ?></li>
            <li>Mobile: <?= htmlspecialchars($owner_mobile) ?></li>
        </ul>
        <p>A confirmation message has been sent to the owner with your contact details.</p>
    </main>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
