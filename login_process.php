<?php
session_start();
include("database.inc.php");

if (!isset($_SESSION['login_role'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['login_role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['username']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: login_form.php");
        exit;
    }

    $conn = db_connect();

    switch ($role) {
        case 'customer':
            $table = 'customers';
            $user_id_col = 'customerID';
            break;
        case 'owner':
            $table = 'owners';
            $user_id_col = 'ownerID';
            break;
        case 'manager':
            $table = 'manager';
            $user_id_col = 'manager_id';
            break;

        default:
            $_SESSION['error'] = "Invalid role selected.";
            header("Location: login.php");
            exit;
    }

    $stmt = $conn->prepare("SELECT * FROM $table WHERE email  = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
    if ($role === 'customer') {
        $_SESSION['user'] = [
            'id' => $user['customerID'],
            'nid' => $user['nid'],
            'name' => $user['name'],
            'flat_no' => $user['flat_no'],
            'street' => $user['street'],
            'city' => $user['city'],
            'postal' => $user['postal'],
            'dob' => $user['dob'],
            'email' => $user['email'],
            'mobile' => $user['mobile'],
            'telephone' => $user['telephone'],
            'username' => $user['username'],
            'role' => 'customer',
            'photo' => $user['photo'] ?? 'images/default.png'
        ];
        header("Location: profile.php");
        exit;
    } else {
        // For other roles, you can keep minimal info
        $_SESSION['user_id'] = $user[$user_id_col];
        $_SESSION['username'] = $user['email'];
        $_SESSION['role'] = $role;
        $_SESSION['success'] = "Login successfully";

        header("Location: search.php");
        exit;
    }
} else {
    // Login failed
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: login_form.php");
    exit;
}
}