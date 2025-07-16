<?php
session_start();
require_once 'database.inc.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit;
}

$pdo = db_connect();

// Get POST data from previous form
$flat_id = $_POST['flat_id'] ?? null;
$flat_ref = $_POST['ref_number'] ?? null;
$owner_id = $_POST['owner_id'] ?? null;
$owner_name = $_POST['owner_name'] ?? null;
$owner_mobile = $_POST['owner_mobile'] ?? null;
$start_date = $_POST['start_date'] ?? null;
$end_date = $_POST['end_date'] ?? null;

if (!$flat_id || !$start_date || !$end_date) {
    die("Missing required data.");
}

// Validate dates
if (strtotime($end_date) < strtotime($start_date)) {
    die("End date must be after start date.");
}

// Fetch flat price per day 
$stmt = $pdo->prepare("SELECT price FROM flats WHERE id = ?");
$stmt->execute([$flat_id]);
$flat = $stmt->fetch();

if (!$flat) {
    die("Flat not found.");
}

// Calculate number of days renting
$start_ts = strtotime($start_date);
$end_ts = strtotime($end_date);
$days = ceil(($end_ts - $start_ts) / (60 * 60 * 24)) + 1;

// Calculate total rent
$total_rent = $days * $flat['price'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Confirm Rent and Payment</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <?php include 'navigation.php'; ?>
    <main>
        <h2>Confirm Rent & Payment</h2>
        <form action="process_rent.php" method="post" class="rent-confirm-form">
            <fieldset>
                <legend>Rent Summary</legend>
                <p><strong>Flat Ref:</strong> <?= htmlspecialchars($flat_ref) ?></p>
                <p><strong>Rental Period:</strong> <?= htmlspecialchars($start_date) ?> to <?= htmlspecialchars($end_date) ?> (<?= $days ?> days)</p>
                <p><strong>Total Rent:</strong> $<?= number_format($total_rent, 2) ?></p>
            </fieldset>

            <fieldset>
                <legend>Customer Details</legend>
                <p>Name: <?= htmlspecialchars($_SESSION['user']['name']) ?></p>
                <p>Mobile: <?= htmlspecialchars($_SESSION['user']['mobile'] ?? '') ?></p>
            </fieldset>

            <fieldset>
                <legend>Payment Details</legend>
                <label>Credit Card Number (9 digits): <input type="text" name="card_number" pattern="\d{9}" maxlength="9" required></label>
                <label>Expiry Date: <input type="month" name="card_expiry" required></label>
                <label>Name on Card: <input type="text" name="card_name" required></label>
            </fieldset>

            <input type="hidden" name="flat_id" value="<?= htmlspecialchars($flat_id) ?>">
            <input type="hidden" name="flat_ref" value="<?= htmlspecialchars($flat_ref) ?>">
            <input type="hidden" name="owner_id" value="<?= htmlspecialchars($owner_id) ?>">
            <input type="hidden" name="owner_name" value="<?= htmlspecialchars($owner_name) ?>">
            <input type="hidden" name="owner_mobile" value="<?= htmlspecialchars($owner_mobile) ?>">
            <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
            <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
            <input type="hidden" name="total_rent" value="<?= htmlspecialchars($total_rent) ?>">

            <button type="submit">Confirm Rent</button>
        </form>
    </main>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
