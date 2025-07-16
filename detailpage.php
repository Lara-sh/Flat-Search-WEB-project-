<?php
session_start();
require_once 'database.inc.php';
$pdo = db_connect();

$ref = $_GET['ref'] ?? null;
if (!$ref) {
    echo "<p>Flat reference not specified.</p>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM flats WHERE ref_number = :ref");
$stmt->execute([':ref' => $ref]);
$flat = $stmt->fetch();

if (!$flat) {
    echo "<p>Flat not found.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Flat Detail</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="flat-detail-page">
        <main>
            <div class="flatcard">
                <div class="photos">
                    <?php
                    $photoFolder = "uploads/flats/" . $flat['id'] . "/";
                    $photos = [];

                    if (is_dir($photoFolder)) {
                        $files = scandir($photoFolder);
                        foreach ($files as $file) {
                            if (preg_match('/^photo_.*\.(jpg|jpeg|png|gif)$/i', $file)) {
                                $photos[] = $photoFolder . $file;
                            }
                        }
                    }

                    if (empty($photos)) {
                       
                        $photos[] = "uploads/default_photo.jpg";
                    }

                    // Output all photos found 
                    foreach (array_slice($photos, 0, 5) as $photo) {
                        echo '<img src="' . htmlspecialchars($photo) . '" alt="Flat photo" style="max-width:200px; height:auto; margin-right:10px;">';
                    }
                    ?>
                </div>

                <div class="description">
                    <h2><?= htmlspecialchars($flat['description']) ?></h2>
                    <p><em>Address:</em> <?= htmlspecialchars($flat['location']) ?></p>
                    <p><em>Price:</em> $<?= htmlspecialchars($flat['price']) ?>/month</p>
                   <p><em>Available from:</em> <?= htmlspecialchars($flat['available_from']) ?> - <em>Available to:</em> <?= htmlspecialchars($flat['available_to']) ?></p>
                    <p><em>Rental Status:</em> <?= $flat['rented'] ? 'Rented' : 'Available' ?></p>
                    <p><em>Furnished:</em> <?= $flat['furnished'] ? 'Yes' : 'No' ?></p>
                    <p><em>Bedrooms:</em> <?= htmlspecialchars($flat['bedrooms']) ?></p>
                    <p><em>Bathrooms:</em> <?= htmlspecialchars($flat['bathrooms']) ?></p>
                    <p><em>Size:</em> <?= htmlspecialchars($flat['size_sqm']) ?> m<sup>2</sup></p>
                    <p><em>Heating:</em> <?= htmlspecialchars($flat['heating']) ?></p>
                    <p><em>Air Conditioning:</em> <?= htmlspecialchars($flat['air_conditioning']) ?></p>
                    <p><em>Access Control:</em> <?= htmlspecialchars($flat['access_control']) ?></p>
                    <p><em>Parking:</em> <?= $flat['parking'] ? 'Available' : 'Not available' ?></p>
                    <p><em>Backyard:</em> <?= $flat['backyard'] ? 'Yes' : 'No' ?></p>
                    <p><em>Playground:</em> <?= $flat['playground'] ? 'Yes' : 'No' ?></p>
                    <p><em>Storage:</em> <?= $flat['storage'] ? 'Yes' : 'No' ?></p>

                </div>
            </div>

            <aside>
                <h3>Nearby Landmarks</h3>
                <?php
                $landmarkStmt = $pdo->prepare("SELECT description FROM marketing_info WHERE flat_id = ? AND title = 'NearBy'");
                $landmarkStmt->execute([$flat['id']]);
                $landmarks = $landmarkStmt->fetchAll(PDO::FETCH_COLUMN);

                if ($landmarks && count($landmarks) > 0): ?>
                    <ul>
                        <?php foreach ($landmarks as $entry): ?>
                            <?php
                            $items = explode('<br>', $entry);
                            foreach ($items as $item):
                                $item = trim($item);
                                if ($item !== ''):
                            ?>
                                    <li><?= htmlspecialchars($item) ?></li>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No nearby landmarks listed.</p>
                <?php endif; ?>


                <div class="side-nav">
                    <a href="Appointment.php?ref=<?= urlencode($flat['ref_number']) ?>">📅 Request Flat Preview</a>

                    <a href="RentFlat.php?ref_number=<?= urlencode($flat['ref_number']) ?>">📝 Rent the Flat</a>


                </div>
            </aside>

         </main>
</div>

    <?php include 'footer.php'; ?>

</body>

</html>