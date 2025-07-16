<?php
session_start();
include("database.inc.php");
$conn = db_connect();

$error = '';
$role = $_SESSION['login_role'] ?? null;

if (!$role) {
    header("Location: login.php");
    exit;
}

// Login logic only if user submitted username & password
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['username'], $_POST['password'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Determine SQL query based on role
    if ($role === 'customer') {
        $sql = "SELECT customerID AS id, name, password FROM customers WHERE email = ?";
    } elseif ($role === 'owner') {
        $sql = "SELECT ownerID AS id, name, password FROM owners WHERE email = ?";
    } elseif ($role === 'manager') {
        $sql = "SELECT manager_id AS id, email AS name, password FROM manager WHERE email = ?";
    } else {
        $error = "Invalid role.";
    }

    // Only run DB logic if there's no error and SQL was set
    if (!$error && isset($sql)) {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['password'] === $password) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $role
            ];
            unset($_SESSION['login_role']); // Clean up
            header("Location: search.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login - <?= htmlspecialchars(ucfirst($role)) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include("header.php"); ?>

<main>
    <section style="width: 50%; margin: auto;">
        <h2><?= ucfirst(htmlspecialchars($role)) ?> Login</h2>
        <?php if ($error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" action="login_form.php">
            <label for="username">Email:</label><br>
            <input type="email" name="username" id="username" required><br><br>

            <label for="password">Password:</label><br>
            <input type="password" name="password" id="password" required><br><br>

            <button type="submit">Login</button>
        </form>
    </section>
</main>

<?php include("footer.php"); ?>
</body>
</html>
