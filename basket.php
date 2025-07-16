<?php
session_start();
require_once 'database.inc.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

$pdo = db_connect();
$customer_id = $_SESSION['user']['id'];

// Get basket items for this customer
$stmt = $pdo->prepare("
    SELECT b.*, f.ref_number, f.location, f.address, f.price 
    FROM rental_basket b
    JOIN flats f ON b.flat_id = f.id
    WHERE b.customerID = ?
    ORDER BY b.created_at DESC
");

$stmt->execute([$customer_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Basket</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <?php include 'navigation.php'; ?>
    <main>
        <h2>Your Rental Basket</h2>

        <?php if (count($items) === 0): ?>
            <p>You have no ongoing rentals.</p>
        <?php else: ?>
            <table class="basket-table">
                <thead>
                    <tr>
                        <th>Ref No</th>
                        <th>Location</th>
                        <th>Address</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Estimated Rent</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): 
                    $days = (strtotime($item['end_date']) - strtotime($item['start_date'])) / (60 * 60 * 24) + 1;
                    $estimated_rent = $days * $item['price'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($item['ref_number']) ?></td>
                        <td><?= htmlspecialchars($item['location']) ?></td>
                        <td><?= htmlspecialchars($item['address']) ?></td>
                        <td><?= htmlspecialchars($item['start_date']) ?></td>
                        <td><?= htmlspecialchars($item['end_date']) ?></td>
                        <td>$<?= number_format($estimated_rent, 2) ?></td>
                        <td>
                            <a href="RentFlat.php?ref_number=<?= urlencode($item['ref_number']) ?>" class="button">Resume</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
