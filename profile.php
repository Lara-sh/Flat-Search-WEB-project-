<?php
session_start();
include("database.inc.php"); 

// Only allow logged-in customers or owners
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['customer', 'owner'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$role = $user['role'];

// Connect to DB
$conn = db_connect();

if ($role === 'customer') {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE customerID = ?");
    $stmt->execute([$user['id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        // User not found, force logout or redirect
        session_destroy();
        header("Location: login.php");
        exit;
    }
} elseif ($role === 'owner') {
    $stmt = $conn->prepare("SELECT * FROM owners WHERE ownerID = ?");
    $stmt->execute([$user['id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        session_destroy();
        header("Location: login.php");
        exit;
    }
} else {
    // Optional: handle other roles or error
    $userData = [];
}
?>


<!DOCTYPE html>
<html>
<head>
    <title><?= ucfirst(htmlspecialchars($role)) ?> Profile Update</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="register-step-container">
<h2><?= ucfirst(htmlspecialchars($role)) ?> Profile Update</h2>

<form action="update_profile.php" method="post">
    <label for="nid">National ID Number:</label><br>
  <input type="text" name="customerID" id="customerID" value="<?= htmlspecialchars($userData['customerID'] ?? '') ?>" readonly><br><br>



    <label for="name">Full Name:</label><br>
    <input type="text" name="name" id="name" pattern="[A-Za-z ]+" value="<?= htmlspecialchars($userData['name']) ?>" required><br><br>

    <fieldset>
        <legend>Address</legend>
        <label>Flat/House No:</label><br>
        <input type="text" name="flat_no" value="<?= htmlspecialchars($userData['flat_no'] ?? '') ?>" required><br>

        <label>Street Name:</label><br>
        <input type="text" name="street" value="<?= htmlspecialchars($userData['street'] ?? '') ?>" required><br>

        <label>City:</label><br>
        <input type="text" name="city" value="<?= htmlspecialchars($userData['city'] ?? '') ?>" required><br>

        <label>Postal Code:</label><br>
        <input type="text" name="postal" value="<?= htmlspecialchars($userData['postal'] ?? '') ?>" required><br>
    </fieldset>
    <br>

    <label for="dob">Date of Birth:</label><br>
    <input type="date" name="dob" id="dob" value="<?= htmlspecialchars($userData['dob'] ?? '') ?>" required><br><br>

    <label for="email">Email Address:</label><br>
    <input type="email" name="email" id="email" value="<?= htmlspecialchars($userData['email']) ?>" required><br><br>

    <label for="mobile">Mobile Number:</label><br>
    <input type="text" name="mobile" id="mobile" value="<?= htmlspecialchars($userData['mobile']) ?>" required><br><br>

    <label for="telephone">Telephone Number:</label><br>
    <input type="text" name="telephone" id="telephone" value="<?= htmlspecialchars($userData['telephone'] ?? '') ?>"><br><br>

    <?php if ($role == 'owner'): ?>
        <fieldset>
            <legend>Bank Details</legend>
            <label for="bank_name">Bank Name:</label><br>
            <input type="text" name="bank_name" id="bank_name" value="<?= htmlspecialchars($userData['bank_name'] ?? '') ?>" required><br>

            <label for="bank_branch">Bank Branch:</label><br>
            <input type="text" name="bank_branch" id="bank_branch" value="<?= htmlspecialchars($userData['bank_branch'] ?? '') ?>" required><br>

            <label for="account_no">Account Number:</label><br>
            <input type="text" name="account_no" id="account_no" value="<?= htmlspecialchars($userData['account_no'] ?? '') ?>" required><br>
        </fieldset>
        <br>
    <?php endif; ?>

    <button type="submit">Update</button>
</form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
