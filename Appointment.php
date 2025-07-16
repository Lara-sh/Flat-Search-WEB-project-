<?php
session_start();
require_once 'database.inc.php';

// Check if the user is logged in and is a customer
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

$pdo = db_connect();

// Get preview timetable with flat reference number
$stmt = $pdo->query("SELECT pt.*, f.ref_number FROM preview_timetable pt
                     JOIN flats f ON pt.flat_id = f.id
                     ORDER BY pt.day, pt.time");

$timetable = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Request Flat Preview Appointment</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <?php include 'navigation.php'; ?>
    <main>
        <h2>Request Preview Appointments</h2>

        <?php if (empty($timetable)): ?>
            <p>No preview timetable entries found.</p>
        <?php else: ?>
            <table class="preview-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($timetable as $entry): ?>
                        <tr class="<?= $entry['is_booked'] ? 'taken-slot' : 'available-slot' ?>">
                            <td><?= htmlspecialchars($entry['day']) ?></td>
                            <td><?= htmlspecialchars($entry['time']) ?></td>
                            <td><?= $entry['is_booked'] ? 'Taken' : 'Available' ?></td>
                            <td>
                                <?php if (!$entry['is_booked']): ?>
                                    <form method="post" action="submitAppointment.php">
                                        <input type="hidden" name="slot_id" value="<?= $entry['id'] ?>" />
                                        <input type="hidden" name="flat_ref" value="<?= htmlspecialchars($entry['ref_number']) ?>" />
                                        <button type="submit">Book</button>
                                    </form>
                                <?php else: ?>
                                    <button disabled>Booked</button>
                                <?php endif; ?>
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
