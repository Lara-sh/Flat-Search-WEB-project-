<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Offer Submitted</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <?php include 'navigation.php'; ?>
        <main>
            <h2>Flat Offer Submitted</h2>
            <p>Your flat offer has been submitted and is awaiting manager approval.</p>
            <a href="search.php">Back to Home</a>
        </main>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
