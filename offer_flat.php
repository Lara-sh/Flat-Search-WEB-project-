<?php
session_start();
require_once 'database.inc.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header("Location: login.php");
    exit;
}

$pdo = db_connect();

$owner_id = $_SESSION['user']['id'];

// Gather and sanitize inputs
$location = $_POST['location'] ?? '';
$address = $_POST['address'] ?? '';
$price = $_POST['price'] ?? 0;
$available_from = $_POST['available_from'] ?? null;
$available_to = $_POST['available_to'] ?? null;
$bedrooms = $_POST['bedrooms'] ?? 0;
$bathrooms = $_POST['bathrooms'] ?? 0;
$size_sqm = $_POST['size_sqm'] ?? 0;
$conditions = $_POST['conditions'] ?? '';

// Optional features (checkboxes: 1 or 0)
$heating = isset($_POST['heating']) ? 1 : 0;
$air_conditioning = isset($_POST['air_conditioning']) ? 1 : 0;
$access_control = isset($_POST['access_control']) ? 1 : 0;
$parking = isset($_POST['parking']) ? 1 : 0;
$playground = isset($_POST['playground']) ? 1 : 0;
$storage = isset($_POST['storage']) ? 1 : 0;
$furnished = isset($_POST['furnished']) ? 1 : 0;

// Insert flat into database
$stmt = $pdo->prepare("INSERT INTO flats (
    owner_id, location, address, price, available_from, available_to,
    bedrooms, bathrooms, size_sqm, conditions,
    heating, air_conditioning, access_control, parking,
    playground, storage, furnished
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");


$stmt->execute([
    $owner_id, $location, $address, $price, $available_from, $available_to,
    $bedrooms, $bathrooms, $size_sqm, $conditions,
    $heating, $air_conditioning, $access_control, $parking, $playground, $storage, $furnished
]);

$flat_id = $pdo->lastInsertId();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Offer Flat for Rent</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <?php include 'navigation.php'; ?>

        <main>
            <h2>Offer Flat for Rent</h2>

            <form action="submit_flat_offer.php" method="post" enctype="multipart/form-data" class="offer-form">

                <!-- Step 1: Flat Details -->
                <fieldset>
                    <legend>Flat Details</legend>

                    <label>Location: <input type="text" name="location" required></label>
                    <label>Address: <input type="text" name="address" required></label>
                    <label>Monthly Rent (₪): <input type="number" name="price" min="0" required></label>
                    <label>Available From:
                        <input type="date" name="available_from" lang="en" style="direction: ltr;" required>
                    </label>

                    <label>Available To:
                        <input type="date" name="available_to" lang="en" style="direction: ltr;" required>
                    </label>

                    <label>Bedrooms: <input type="number" name="bedrooms" min="0" required></label>
                    <label>Bathrooms: <input type="number" name="bathrooms" min="0" required></label>
                    <label for="size_sqm">Size (sqm):</label>
                    <input type="number" name="size_sqm" id="size_sqm" required>

                    <label>Rent Conditions: <textarea name="conditions" required></textarea></label>

                    <!-- Features -->
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="heating" value="1"> Heating System</label>
                        <label><input type="checkbox" name="air_conditioning" value="1"> Air-conditioning</label>
                        <label><input type="checkbox" name="access_control" value="1"> Access Control</label>
                        <label><input type="checkbox" name="parking" value="1"> Car Parking</label>
                        <label><input type="checkbox" name="backyard" value="individual"> Backyard (Individual)</label>
                        <label><input type="checkbox" name="backyard" value="shared"> Backyard (Shared)</label>
                        <label><input type="checkbox" name="playground" value="1"> Playground</label>
                        <label><input type="checkbox" name="storage" value="1"> Storage</label>
                        <label><input type="checkbox" name="furnished" value="1"> Furnished</label>

                    </div>


                    <label>Photos (at least 3):
                        <input type="file" name="photos[]" accept="image/*" multiple required>
                    </label>

                </fieldset>

                <!-- Step 2: Marketing Info -->
                <fieldset>
                    <legend>Nearby Marketing Information (Optional)</legend>

                    <div class="marketing-entry">
                        <label>Title: <input type="text" name="marketing_title[]"></label>
                        <label>Description: <input type="text" name="marketing_description[]"></label>
                        <label>URL: <input type="url" name="marketing_url[]"></label>
                    </div>
                </fieldset>

                <!-- Step 3: Preview Timetable -->
                <fieldset>
                    <legend>Preview Timetable</legend>

                    <label>Day: <input type="text" name="preview_day[]" placeholder="e.g., Monday" required></label>
                    <label>Time: <input type="text" name="preview_time[]" placeholder="e.g., 2:00 PM - 4:00 PM" required></label>
                    <label>Contact Phone: <input type="text" name="preview_phone[]" required></label>
                </fieldset>

                <button type="submit" class="submit-button">Submit for Approval</button>
            </form>
        </main>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>