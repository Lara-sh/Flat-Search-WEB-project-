<?php
session_start();
include("database.inc.php");

$conn = db_connect();

$role = $_SESSION['role'] ?? null;
$step1 = $_SESSION['step1'] ?? null;
$step2 = $_SESSION['step2'] ?? null;

if (!$role || !$step1 || !$step2) {
    header("Location: register.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {

    function generateUniqueID($conn, $table, $id_column) {
        do {
            $id = str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
            $stmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE $id_column = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
        } while ($count > 0);
        return $id;
    }

    if ($role === 'customer') {
        $table = 'customers';
        $id_column = 'customerID';
    } else {
        $table = 'owners';
        $id_column = 'ownerID';
    }

    $uniqueID = generateUniqueID($conn, $table, $id_column);

    if ($role === 'customer') {
        $sql = "INSERT INTO customers (customerID, nid, name, flat_no, street, city, postal, dob, email, mobile, telephone, username, password)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $uniqueID,
            $step1['nid'],
            $step1['name'],
            $step1['flat_no'],
            $step1['street'],
            $step1['city'],
            $step1['postal'],
            $step1['dob'],
            $step1['email'],
            $step1['mobile'],
            $step1['telephone'],
            $step2['username'],
            $step2['password']

        ];
    } else {
        $sql = "INSERT INTO owners (ownerID, nid, name, flat_no, street, city, postal, dob, email, mobile, telephone, bank_name, bank_branch, account_no, username, password)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $uniqueID,
            $step1['nid'],
            $step1['name'],
            $step1['flat_no'],
            $step1['street'],
            $step1['city'],
            $step1['postal'],
            $step1['dob'],
            $step1['email'],
            $step1['mobile'],
            $step1['telephone'],
            $step1['bank_name'],
            $step1['bank_branch'],
            $step1['account_no'],
            $step2['username'],
            $step2['password']
        ];
    }

   try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    session_unset();
    session_destroy();
    echo "<div class='success-container'>";
echo "<h2>Registration Successful!</h2>";
echo "<p>Thank you, " . htmlspecialchars($step1['name']) . ".</p>";
echo "<p>Your unique ID is: <strong>$uniqueID</strong></p>";
echo "<p><a href='login.php'>Go to Login</a></p>";
echo "</div>";
    exit;
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "<p style='color:red;'>Error: This username/email is already registered.</p>";
    } else {
        echo "<p style='color:red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= ucfirst(htmlspecialchars($role)) ?> Registration - Step 3</title>
    <link rel="stylesheet" href="style.css">
</head>
<?php include 'header.php'; ?>

<body>
<h2 class="page-title"><?= ucfirst(htmlspecialchars($role)) ?> Registration - Step 3 (Review & Confirm)</h2>

<form method="post" action="register_step3.php" class="review-form">

    <h3 class="section-heading">Personal Details:</h3>
    <p class="review-paragraph"><span class="review-label">National ID:</span> <?= htmlspecialchars($step1['nid'] ?? '') ?></p>
    <p class="review-paragraph"><span class="review-label">Name:</span> <?= htmlspecialchars($step1['name'] ?? '') ?></p>
    <p class="review-paragraph"><span class="review-label">Address:</span>
        Flat/House No: <?= htmlspecialchars($step1['flat_no'] ?? '') ?>,
        Street: <?= htmlspecialchars($step1['street'] ?? '') ?>,
        City: <?= htmlspecialchars($step1['city'] ?? '') ?>,
        Postal Code: <?= htmlspecialchars($step1['postal'] ?? '') ?></p>
    <p class="review-paragraph"><span class="review-label">Date of Birth:</span> <?= htmlspecialchars($step1['dob'] ?? '') ?></p>
    <p class="review-paragraph"><span class="review-label">Email:</span> <?= htmlspecialchars($step1['email'] ?? '') ?></p>
    <p class="review-paragraph"><span class="review-label">Mobile:</span> <?= htmlspecialchars($step1['mobile'] ?? '') ?></p>
    <p class="review-paragraph"><span class="review-label">Telephone:</span> <?= htmlspecialchars($step1['telephone'] ?? '') ?></p>

    <?php if ($role === 'owner'): ?>
        <h3 class="section-heading">Bank Details:</h3>
        <p class="review-paragraph"><span class="review-label">Bank Name:</span> <?= htmlspecialchars($step1['bank_name'] ?? '') ?></p>
        <p class="review-paragraph"><span class="review-label">Bank Branch:</span> <?= htmlspecialchars($step1['bank_branch'] ?? '') ?></p>
        <p class="review-paragraph"><span class="review-label">Account Number:</span> <?= htmlspecialchars($step1['account_no'] ?? '') ?></p>
    <?php endif; ?>

    <h3 class="section-heading">E-account:</h3>
    <p class="review-paragraph"><span class="review-label">Username (Email):</span> <?= htmlspecialchars($step2['username'] ?? '') ?></p>

    <button type="submit" name="confirm" class="confirm-btn">Confirm Registration</button>
</form>

 <?php include 'footer.php'; ?>
</body>
</html>
