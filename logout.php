<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logout - Lara Flat Rent</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<main>
    <div class="logout-message">
        <h2>You have been successfully logged out.</h2>
        <p><a href="search.php">Return to Home Page</a></p>
    </div>
</main>


<?php include 'footer.php'; ?>

</body>
</html>
