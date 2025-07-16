<?php
session_start();
require_once 'database.inc.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$pdo = db_connect();

$userId = $_SESSION['user']['id'];
$userRole = $_SESSION['user']['role'];

$stmt = $pdo->prepare("SELECT * FROM messages WHERE receiver_id = :uid AND receiver_role = :role ORDER BY created_at DESC");
$stmt->execute([':uid' => $userId, ':role' => $userRole]);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inbox</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <?php include 'navigation.php'; ?>
    <main>
        <h2>Inbox</h2>

        <?php if (empty($messages)): ?>
            <p>No messages found.</p>
        <?php else: ?>
            <table class="messages-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Sender</th>
                        <th>Message</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr class="<?= $msg['is_read'] ? 'read' : 'unread' ?>">
                        <td class="status-icon">
                            <?php if (!$msg['is_read']): ?>
                                <span class="new-icon">🆕</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($msg['title']) ?></td>
                        <td><?= htmlspecialchars($msg['created_at']) ?></td>
                        <td><?= htmlspecialchars($msg['sender'] ?? 'System') ?></td>
                        <td><?= nl2br(htmlspecialchars($msg['body'])) ?></td>
                        <td>
                            <?php
                            // Owner handling a Flat Preview Request
                            if (
                                $userRole === 'owner' &&
                                strpos($msg['title'], 'Flat Preview Request') !== false &&
                                preg_match('/Ref\s*(\w+)\s+on\s+(\w+)\s+at\s+([\d:apm\s]+)/i', $msg['body'], $matches)
                            ) {
                                $flat_ref = $matches[1];
                                $day = $matches[2];
                                $time = trim($matches[3]);
                                ?>
                                <form method="post" action="handleAppointment.php" style="display:inline;">
                                    <input type="hidden" name="flat_ref" value="<?= htmlspecialchars($flat_ref) ?>">
                                    <input type="hidden" name="day" value="<?= htmlspecialchars($day) ?>">
                                    <input type="hidden" name="time" value="<?= htmlspecialchars($time) ?>">
                                    <input type="hidden" name="customer_id" value="<?= htmlspecialchars($msg['sender_id']) ?>">
                                    <button name="action" value="accept" type="submit">Accept</button>
                                    <button name="action" value="reject" type="submit">Reject</button>
                                </form>
                            <?php
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
