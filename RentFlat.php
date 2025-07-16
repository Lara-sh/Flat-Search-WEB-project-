<?php
session_start();
require_once 'database.inc.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit;
}

$pdo = db_connect();

if (!isset($_GET['ref_number'])) {
    die("No flat reference number provided.");
}
$flatRef = $_GET['ref_number'];

// Get flat info
$stmt = $pdo->prepare("SELECT f.*, o.name AS owner_name, o.nid AS owner_nid, o.street AS owner_street, o.city AS owner_city, o.postal AS owner_postal, o.mobile AS owner_mobile, o.ownerID
                       FROM flats f 
                       JOIN owners o ON f.owner_id = o.ownerID 
                       WHERE f.ref_number = ?");
$stmt->execute([$flatRef]);
$flat = $stmt->fetch();
if (!$flat) {
    die("Flat not found.");
}

$customerID = $_SESSION['user']['id'];

// Add to basket if not already there and if rental dates not submitted yet (form not posted)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Check if flat already in basket for this customer
    $check = $pdo->prepare("SELECT COUNT(*) FROM rental_basket WHERE customerID = ? AND flat_id = ?");
    $check->execute([$customerID, $flat['id']]);
    $exists = $check->fetchColumn();

    if (!$exists) {
        // Insert with NULL or default dates 
      $insert = $pdo->prepare("INSERT INTO rental_basket (customerID, flat_id, start_date, end_date) VALUES (?, ?, '1970-01-01', '1970-01-01')");
$insert->execute([$customerID, $flat['id']]);

    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rent Flat</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <?php include 'navigation.php'; ?>
    <main>
        <h2>Rent Flat</h2>
        <form action="confirm_rent.php" method="post" class="rent-form">
            <fieldset>
                <legend>Flat and Owner Details</legend>
                <input type="hidden" name="ref_number" value="<?= htmlspecialchars($flat['ref_number']) ?>">


                <input type="hidden" name="flat_id" value="<?= htmlspecialchars($flat['id']) ?>">
                <input type="hidden" name="owner_id" value="<?= htmlspecialchars($flat['ownerID']) ?>">
                <input type="hidden" name="owner_name" value="<?= htmlspecialchars($flat['owner_name']) ?>">
                <input type="hidden" name="owner_mobile" value="<?= htmlspecialchars($flat['owner_mobile']) ?>">

               <label>Flat Ref: <input type="text" value="<?= htmlspecialchars($flat['ref_number']) ?>" readonly></label>
                <label>Flat Number: <input type="text" value="<?= htmlspecialchars($flat['id']) ?>" readonly></label>
                <label>Location: <input type="text" value="<?= htmlspecialchars($flat['location']) ?>" readonly></label>
                <label>Address: <input type="text" value="<?= htmlspecialchars($flat['address']) ?>" readonly></label>
                <label>Details: <textarea readonly><?= htmlspecialchars($flat['description']) ?></textarea></label>

                <label>Owner Name: <input type="text" value="<?= htmlspecialchars($flat['owner_name']) ?>" readonly></label>
                <label>Owner NID: <input type="text" value="<?= htmlspecialchars($flat['owner_nid']) ?>" readonly></label>
                <label>Owner Address: <input type="text" value="<?= htmlspecialchars($flat['owner_street'] . ', ' . $flat['owner_city'] . ', ' . $flat['owner_postal']) ?>" readonly></label>
            </fieldset>

            <fieldset>
                <legend>Rental Period</legend>
                <label>Start Date: <input type="date" name="start_date" required></label>
                <label>End Date: <input type="date" name="end_date" required></label>
            </fieldset>

            <button type="submit" class="submit-button">Continue to Payment</button>
        </form>
    </main>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
