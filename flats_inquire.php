<?php
require_once 'database.inc.php';
session_start();

// Ensure manager is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'manager') {
    header('Location: login.php');
    exit;
}

$pdo = db_connect();

// Fetch filters from GET request
$location = $_GET['location'] ?? '';
$available_from = $_GET['available_from'] ?? '';
$available_to = $_GET['available_to'] ?? '';
$available_on = $_GET['available_on'] ?? '';
$owner_id = $_GET['owner_id'] ?? '';
$customer_id = $_GET['customer_id'] ?? '';

$conditions = [];
$params = [];

if (!empty($location)) {
    $conditions[] = "f.location = :location";
    $params['location'] = $location;
}

if (!empty($available_from) && !empty($available_to)) {
    $conditions[] = "r.start_date >= :available_from AND r.end_date <= :available_to";
    $params['available_from'] = $available_from;
    $params['available_to'] = $available_to;
} elseif (!empty($available_on)) {
    $conditions[] = ":available_on BETWEEN r.start_date AND r.end_date";
    $params['available_on'] = $available_on;
}

if (!empty($owner_id)) {
    $conditions[] = "f.owner_id = :owner_id";
    $params['owner_id'] = $owner_id;
}

if (!empty($customer_id)) {
    $conditions[] = "c.customerID = :customer_id";
    $params['customer_id'] = $customer_id;
}

$where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

$sql = "SELECT r.*, 
               f.ref_number, f.location, f.owner_id, f.price AS monthly_cost,
               o.ownerID AS owner_id, o.name AS owner_name, o.city AS owner_city, o.telephone AS owner_phone, o.email AS owner_email,
               c.customerID AS customer_id, c.name AS customer_name, c.city AS customer_city, c.mobile AS customer_mobile, c.email AS customer_email
        FROM rentals r
        JOIN flats f ON r.flat_id = f.id
        JOIN owners o ON f.owner_id = o.ownerID
        JOIN customers c ON r.customer_id = c.customerID
        $where
        ORDER BY r.start_date DESC";



$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Flats Inquiry</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <?php include 'navigation.php'; ?>

        <main>
            <h2>Flats Inquiry</h2>
            <form method="get" class="filter-form">
                <label>Location: <input type="text" name="location" value="<?= htmlentities($location) ?>"></label>
                <label>Available From: <input type="date" name="available_from" value="<?= htmlentities($available_from) ?>"></label>
                <label>Available To: <input type="date" name="available_to" value="<?= htmlentities($available_to) ?>"></label>
                <label>Available On: <input type="date" name="available_on" value="<?= htmlentities($available_on) ?>"></label>
                <label>Owner ID: <input type="number" name="owner_id" value="<?= htmlentities($owner_id) ?>"></label>
                <label>Customer ID: <input type="number" name="customer_id" value="<?= htmlentities($customer_id) ?>"></label>
                <button type="submit">Search</button>
            </form>

            <?php if ($results): ?>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Flat Ref</th>
                            <th>Monthly Cost</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Location</th>
                            <th>Total Rent</th>
                            <th>Owner</th>
                            <th>Customer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><a class="ref-button" href="detailpage.php?ref=<?= urlencode($row['ref_number']) ?>" target="_blank">View <?= htmlentities($row['ref_number']) ?></a></td>
                                <td><?= number_format($row['monthly_cost'], 2) ?></td>


                                <td><?= htmlentities($row['start_date']) ?></td>
                                <td><?= htmlentities($row['end_date']) ?></td>
                                <td><?= htmlentities($row['location']) ?></td>
                                <td><?= number_format($row['total_rent'], 2) ?></td>
                                <td>
                                    <a href="usercard.php?id=<?= urlencode($row['owner_id']) ?>&role=owner"
                                        target="_blank"
                                        class="owner-link">
                                        <?= htmlentities($row['owner_name']) ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="usercard.php?id=<?= urlencode($row['customer_id']) ?>&role=customer"
                                        target="_blank"
                                        class="customer-link">
                                        <?= htmlentities($row['customer_name']) ?>
                                    </a>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No flats matched your filters.</p>
            <?php endif; ?>
        </main>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>