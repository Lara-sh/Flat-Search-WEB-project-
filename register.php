<?php
session_start();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Choose Registration Type</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <div class="register-container">
        <h2>Register as:</h2>
        <form method="post" action="register_step1.php">
            <input type="radio" id="customer" name="role" value="customer" required>
            <label for="customer">Customer</label><br>

            <input type="radio" id="owner" name="role" value="owner" required>
            <label for="owner">Owner</label><br><br>

            <button type="submit">Next</button>
        </form>
    </div>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>

</html>
