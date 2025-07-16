<?php
require_once 'database.inc.php';
session_start();

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$role = isset($_GET['role']) ? $_GET['role'] : '';

if (!$user_id || !in_array($role, ['owner', 'customer'])) {
  echo "Invalid user data.";
  exit;
}

$pdo = db_connect();

if ($role === 'owner') {
  $sql = "SELECT name, city, telephone AS phone, email FROM owners WHERE ownerID = :id";
} else {
  $sql = "SELECT name, city, mobile AS phone, email FROM customers WHERE customerID = :id";
}

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  echo ucfirst($role) . " not found.";
  exit;
}

function format_phone_number($phone)
{
  return preg_replace('/(\d{3})(?=\d)/', '$1 ', $phone);
}

$isLoggedInCustomer = isset($_SESSION['user']) &&
  $_SESSION['user']['role'] === 'customer' &&
  $_SESSION['user']['id'] == $user_id;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?= ucfirst($role) ?> Card - <?= htmlentities($user['name']) ?></title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <?php include 'header.php'; ?>

  <div class="user-card-container <?= $role === 'customer' ? 'user-card-customer' : 'user-card-owner' ?>">

    <?php if ($role === 'customer'): ?>
      <?php if ($isLoggedInCustomer): ?>
        <a href="profile.php?id=<?= urlencode($_SESSION['user']['id']) ?>&role=<?= urlencode($_SESSION['user']['role']) ?>">
          <img src="images/boy.png" alt="User Photo" class="user-photo">
        </a>
      <?php else: ?>
        <img src="images/boy.png" alt="User Photo" class="user-photo">
      <?php endif; ?>
    <?php endif; ?>

    <h2><?= htmlentities($user['name']) ?></h2>

    <p class="city"><?= htmlentities($user['city']) ?></p>

    <div class="contact-info">
      <span>📞</span>
      <span class="phone-number"><?= htmlentities(format_phone_number($user['phone'])) ?></span>
    </div>

    <div class="contact-info">
      <span>📧</span>
      <a href="mailto:<?= htmlentities($user['email']) ?>" class="email-link"><?= htmlentities($user['email']) ?></a>
    </div>

  </div>

  <?php include 'footer.php'; ?>
</body>

</html>

