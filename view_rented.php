<?php
session_start();
require_once 'database.inc.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$pdo = db_connect();

// Fetch rented flats for current logged-in customer
$customer_id = $_SESSION['user']['id'];

// This query joins rentals with flats and owners, fetching relevant data
$sql = "
SELECT r.*, 
       f.ref_number, f.price AS monthly_cost, f.location, 
       o.ownerID AS owner_id, o.name AS owner_name, o.city AS owner_city, 
       o.email AS owner_email, o.mobile AS owner_mobile
FROM rentals r
JOIN flats f ON r.flat_id = f.id
JOIN owners o ON f.owner_id = o.ownerID
WHERE r.customer_id = ?
ORDER BY r.start_date DESC
";


$stmt = $pdo->prepare($sql);
$stmt->execute([$customer_id]);
$rented_flats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>View Rented Flats</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <?php include 'navigation.php'; ?>

        <?php if (count($rented_flats) === 0): ?>
            <p>You have no rented flats.</p>
        <?php else: ?>
            <table border="1" cellpadding="5" cellspacing="0">
                <thead>
                    <tr>
                        <th>Flat Ref</th>
                        <th>Monthly Cost</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Location</th>
                        <th>Owner</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $today = date('Y-m-d');
                    foreach ($rented_flats as $row):
                        $class = ($row['end_date'] >= $today) ? 'rental-current' : 'rental-past';
                    ?>
                        <tr class="<?= $class ?>">
                            <td>
                                <a class="ref-button" href="detailpage.php?ref=<?= urlencode($row['ref_number']) ?>" target="_blank">
                                    View <?= htmlentities($row['ref_number']) ?>
                                </a>
                            </td>
                            <td>$<?= number_format($row['monthly_cost'], 2) ?></td>
                            <td><?= htmlentities($row['start_date']) ?></td>
                            <td><?= htmlentities($row['end_date']) ?></td>
                            <td><?= htmlentities($row['location']) ?></td>
                            <td>
                                <a href="usercard.php?id=<?= urlencode($row['owner_id']) ?>&role=owner"
                                    target="_blank"
                                    class="owner-link">
                                    <?= htmlentities($row['owner_name']) ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>