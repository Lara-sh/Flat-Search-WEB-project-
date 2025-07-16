<?php
session_start();
require_once 'database.inc.php';

// Ensure only logged-in customers can update profile
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$pdo = db_connect();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $user['id']; // from session

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');

    // Separate address parts
    $flat_no = trim($_POST['flat_no'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal = trim($_POST['postal'] ?? '');

    // Validation
    $errors = [];

    if (!preg_match("/^[a-zA-Z\s]+$/", $name)) $errors[] = "Name must contain letters only.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (!preg_match("/^\d{10,15}$/", $mobile)) $errors[] = "Mobile number must be digits only (10-15).";

    // Check address fields individually
    if (empty($flat_no) || empty($street) || empty($city) || empty($postal)) {
        $errors[] = "All address fields are required.";
    }

    if (empty($errors)) {
        try {
            // Update SQL with separate address fields
            $stmt = $pdo->prepare("UPDATE customers SET name = ?, email = ?, mobile = ?, flat_no = ?, street = ?, city = ?, postal = ? WHERE customerID = ?");
            $stmt->execute([$name, $email, $mobile, $flat_no, $street, $city, $postal, $id]);

            // Update session data
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['mobile'] = $mobile;
            $_SESSION['user']['flat_no'] = $flat_no;
            $_SESSION['user']['street'] = $street;
            $_SESSION['user']['city'] = $city;
            $_SESSION['user']['postal'] = $postal;

            header("Location: profile.php?updated=1");
            exit;

        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="profile-container">
    <h2>Update Failed</h2>

    <?php if (!empty($errors)): ?>
        <ul style="color: red;">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
        <p><a href="profile.php">← Back to Profile</a></p>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
