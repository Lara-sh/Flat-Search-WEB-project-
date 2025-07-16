<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['role'])) {
        header("Location: register.php");
        exit;
    }
    $_SESSION['role'] = $_POST['role'];
} elseif (!isset($_SESSION['role'])) {
    header("Location: register.php");
    exit;
}

$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= ucfirst(htmlspecialchars($role)) ?> Registration - Step 1</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
<div class="register-step-container">
<h2><?= ucfirst(htmlspecialchars($role)) ?> Registration - Step 1</h2>

<form action="register_step2.php" method="post">

    <label for="nid">National ID Number:</label><br>
    <input type="text" name="nid" id="nid" required><br><br>

    <label for="name">Full Name:</label><br>
    <input type="text" name="name" id="name" pattern="[A-Za-z ]+" title="Letters only" required><br><br>

    <fieldset>
        <legend>Address</legend>
        <label>Flat/House No:</label><br>
        <input type="text" name="flat_no" required><br>

        <label>Street Name:</label><br>
        <input type="text" name="street" required><br>

        <label>City:</label><br>
        <input type="text" name="city" required><br>

        <label>Postal Code:</label><br>
        <input type="text" name="postal" required><br>
    </fieldset>
    <br>

    <label for="dob">Date of Birth:</label><br>
    <input type="date" name="dob" id="dob" required><br><br>

    <label for="email">Email Address:</label><br>
    <input type="email" name="email" id="email" required><br><br>

    <label for="mobile">Mobile Number:</label><br>
    <input type="text" name="mobile" id="mobile" required><br><br>

    <label for="telephone">Telephone Number:</label><br>
    <input type="text" name="telephone" id="telephone"><br><br>

    <?php if ($role == 'owner'): ?>
        <fieldset>
            <legend>Bank Details</legend>
            <label for="bank_name">Bank Name:</label><br>
            <input type="text" name="bank_name" id="bank_name" required><br>

            <label for="bank_branch">Bank Branch:</label><br>
            <input type="text" name="bank_branch" id="bank_branch" required><br>

            <label for="account_no">Account Number:</label><br>
            <input type="text" name="account_no" id="account_no" required><br>
        </fieldset>
        <br>
    <?php endif; ?>

    <button type="submit">Next</button>
</form>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
