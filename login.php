<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
    $_SESSION['login_role'] = $_POST['role'];
    header("Location: login_form.php");
    exit;
}


include("header.php");
?>

<main>
    <section style="width: 50%; margin: auto;">
        <h2>Login</h2>
        <p>Please select your role to login:</p>
        <form method="post" action="login.php">
            <select name="role" required>
                <option value="">-- Select Role --</option>
                <option value="customer">Customer</option>
                <option value="owner">Owner</option>
                <option value="manager">Manager</option>
            </select>
            <br><br>
            <button type="submit">Next</button>
        </form>
    </section>
</main>

<?php include("footer.php"); ?>
