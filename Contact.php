<?php
session_start();
require_once 'database.inc.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"] ?? '';
    $email = $_POST["email"] ?? '';
    $location = $_POST["location"] ?? '';
    $subject = $_POST["subject"] ?? '';
    $message = $_POST["message"] ?? '';

    $log = "Name: $name\nEmail: $email\nLocation: $location\nSubject: $subject\nMessage: $message";
    file_put_contents("messages.txt", $log, FILE_APPEND);

    $confirmation = "Your message has been sent successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contact Us - Clothing Store</title>
</head>

<body>
    <?php include 'header.php'; ?>

    

    <main>
        <article>
            <fieldset>
                <legend>Contact Us</legend>

                <?php if (!empty($confirmation)): ?>
                    <p><strong><?php echo $confirmation; ?></strong></p>
                <?php endif; ?>

                <section>
                    <p>📍Address: &copy; 2025 Lara's Flat Rent | Ramallah, Palestine</p>
                    <p>📧Email: <a href="mailto:support@Laraflatrent.com">support@Laraflatrent.com</a></p>
                    <p>📞Phone: <a href="tel:+9720597940094">+972 (0)597940094</a></p>
                </section>

                <form method="post" action="http://yhassouneh.studentprojects.ritaj.ps/util/process.php">
                    <label for="name">Sender Name:</label><br>
                    <input type="text" id="name" name="name" required><br><br>

                    <label for="email">Sender E-mail:</label><br>
                    <input type="email" id="email" name="email" required><br><br>

                    <label for="location">Sender Location (City):</label><br>
                    <input type="text" id="location" name="location" required><br><br>

                    <label for="subject">Message Subject:</label><br>
                    <input type="text" id="subject" name="subject" required><br><br>

                    <label for="message">Message Body:</label><br>
                    <textarea id="message" name="message" rows="6" cols="40" required></textarea><br><br>

                    <button type="submit">Send</button>
                    <button type="reset">Reset</button>
                </form>
            </fieldset>
        </article>
    </main>

    <?php include 'footer.php'; ?>

</body>

</html>