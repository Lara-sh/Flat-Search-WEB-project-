<?php
session_start();
include("database.inc.php");

$conn = db_connect();

$role = $_SESSION['role'] ?? null;
if (!$role) {
    header("Location: register.php");
    exit;
}

// Only save step1 data if POST contains step1 fields 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nid'])) {
    $_SESSION['step1'] = $_POST;
}

if (!isset($_SESSION['step1'])) {
    header("Location: register_step1.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'], $_POST['confirm_password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $error = "Username must be a valid email address.";
    } else {
        $table = ($role === 'customer') ? 'customers' : 'owners';

        $stmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE email = ?");
        $stmt->execute([$username]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error = "Email is already registered.";
        }
    }

    if (!$error) {
        if (strlen($password) < 6 || strlen($password) > 15) {
            $error = "Password must be 6-15 characters.";
        } elseif (!preg_match('/^\d.*[a-z]$/', $password)) {
            $error = "Password must start with a digit and end with a lowercase letter.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        }
    }

    if (!$error) {
        $_SESSION['step2'] = [
            'username' => $username,
            'password' => $password 
        ];
        header("Location: register_step3.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title><?= ucfirst(htmlspecialchars($role)) ?> Registration - Step 2</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
 <?php include 'header.php'; ?>
<h2><?= ucfirst(htmlspecialchars($role)) ?> Registration - Step 2 (Create e-account)</h2>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" action="register_step2.php">

    <label for="username">Username (Email):</label><br>
    <input type="email" id="username" name="username" required
           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"><br><br>

    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password" required><br><br>

    <label for="confirm_password">Confirm Password:</label><br>
    <input type="password" id="confirm_password" name="confirm_password" required><br><br>

    <button type="submit">Next</button>
</form>
 <?php include 'footer.php'; ?>
</body>
</html>
