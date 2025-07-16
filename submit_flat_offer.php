<?php
session_start();
require_once 'database.inc.php';

class FlatOffer
{
    private $pdo;
    private $ownerId;
    private $errors = [];

    public function __construct($pdo, $ownerId)
    {
        $this->pdo = $pdo;
        $this->ownerId = $ownerId;
    }

    public function validate(array $data, array $files)
    {
        $requiredFields = ['location', 'price', 'available_from', 'available_to', 'bedrooms', 'bathrooms', 'size_sqm', 'conditions'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->errors[] = "Field '$field' is required.";
            }
        }

        if (!empty($data['available_from']) && !empty($data['available_to'])) {
            if (strtotime($data['available_from']) > strtotime($data['available_to'])) {
                $this->errors[] = "Available From date cannot be after Available To date.";
            }
        }

        if (!isset($files['photos']) || !is_array($files['photos']['name']) || count(array_filter($files['photos']['name'])) < 3) {
            $this->errors[] = "Please upload at least 3 photos.";
        }

        return empty($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function generateRefNumber()
    {
        return 'FLAT' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    public function save(array $data, array $files)
    {
        try {
            $this->pdo->beginTransaction();

            $refNumber = $this->generateRefNumber();

            // Handle photo filenames
            $mainPhoto = null;
            $photo2 = null;
            $photo3 = null;
            $uploadedPhotos = [];

            if (isset($files['photos']) && is_array($files['photos']['name'])) {
                for ($i = 0; $i < count($files['photos']['name']); $i++) {
                    if ($files['photos']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmpName = $files['photos']['tmp_name'][$i];
                        $originalName = basename($files['photos']['name'][$i]);
                        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                        $safeName = uniqid('photo_', true) . '.' . $ext;

                        $uploadedPhotos[] = [
                            'tmp' => $tmpName,
                            'filename' => $safeName
                        ];

                        // Assign to main photo fields
                        if (!$mainPhoto) $mainPhoto = $safeName;
                        elseif (!$photo2) $photo2 = $safeName;
                        elseif (!$photo3) $photo3 = $safeName;
                    }
                }
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO flats 
                (owner_id, ref_number, location, address, price, available_from, available_to, bedrooms, bathrooms, size_sqm, conditions, 
                 furnished, rented, photo, photo2, photo3, description, heating, air_conditioning, access_control, parking, backyard, 
                 playground, storage, nearby, is_approved)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
            ");

            $stmt->execute([
                $this->ownerId,
                $refNumber,
                $data['location'],
                $data['address'],
                $data['price'],
                $data['available_from'],
                $data['available_to'],
                $data['bedrooms'],
                $data['bathrooms'],
                $data['size_sqm'],
                $data['conditions'],
                isset($data['furnished']) ? 1 : 0,
                $mainPhoto,
                $photo2,
                $photo3,
                $data['description'] ?? null,
                isset($data['heating']) ? 1 : 0,
                isset($data['air_conditioning']) ? 1 : 0,
                isset($data['access_control']) ? 1 : 0,
                isset($data['parking']) ? 1 : 0,
                $data['backyard'] ?? null,
                isset($data['playground']) ? 1 : 0,
                isset($data['storage']) ? 1 : 0,
                $data['nearby'] ?? null
            ]);

            $flatId = $this->pdo->lastInsertId();

            // Upload photos after insert
            $this->uploadPhotos($flatId, $uploadedPhotos);

            // Optional: Insert marketing info
            if (!empty($data['marketing_title']) && is_array($data['marketing_title'])) {
                $marketingStmt = $this->pdo->prepare("INSERT INTO marketing_info (flat_id, title, description, url) VALUES (?, ?, ?, ?)");
                for ($i = 0; $i < count($data['marketing_title']); $i++) {
                    $title = trim($data['marketing_title'][$i]);
                    $desc = trim($data['marketing_description'][$i] ?? '');
                    $url = trim($data['marketing_url'][$i] ?? '');

                    if ($title !== '' || $desc !== '' || $url !== '') {
                        $marketingStmt->execute([$flatId, $title, $desc, $url]);
                    }
                }
            }

            // Optional: Insert preview timetable
            if (!empty($data['preview_day']) && is_array($data['preview_day'])) {
                $previewStmt = $this->pdo->prepare("INSERT INTO preview_timetable (flat_id, day, time, contact_phone) VALUES (?, ?, ?, ?)");
                for ($i = 0; $i < count($data['preview_day']); $i++) {
                    $day = $data['preview_day'][$i] ?? '';
                    $time = $data['preview_time'][$i] ?? '';
                    $phone = $data['preview_phone'][$i] ?? '';
                    if ($day && $time && $phone) {
                        $previewStmt->execute([$flatId, $day, $time, $phone]);
                    }
                }
            }

            // Send message to manager
            $msgStmt = $this->pdo->prepare("
                INSERT INTO messages 
                (receiver_id, receiver_role, title, body, sender, is_read, created_at) 
                VALUES (?, 'manager', ?, ?, ?, 0, NOW())
            ");
            $msgStmt->execute([
                1,
                "New Flat Approval Needed",
                "A new flat (Ref: $refNumber) has been submitted by owner #{$this->ownerId} and needs approval.",
                $this->ownerId
            ]);

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->errors[] = "Failed to save flat offer: " . $e->getMessage();
            return false;
        }
    }

    private function uploadPhotos($flatId, array $photos)
    {
        $uploadDir = __DIR__ . '/uploads/flats/' . $flatId . '/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $photoStmt = $this->pdo->prepare("INSERT INTO flat_photos (flat_id, filename) VALUES (?, ?)");

        foreach ($photos as $photo) {
            $dest = $uploadDir . $photo['filename'];
            if (move_uploaded_file($photo['tmp'], $dest)) {
                $photoStmt->execute([$flatId, $photo['filename']]);
            }
        }
    }
}

// === Usage ===
$pdo = db_connect();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header("Location: login.php");
    exit;
}

$ownerId = $_SESSION['user']['id'];
$flatOffer = new FlatOffer($pdo, $ownerId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($flatOffer->validate($_POST, $_FILES)) {
        if ($flatOffer->save($_POST, $_FILES)) {
            header("Location: offer_success.php");
            exit;
        } else {
            $errors = $flatOffer->getErrors();
        }
    } else {
        $errors = $flatOffer->getErrors();
    }
} else {
    header("Location: offer_flat.php");
    exit;
}

// Show errors if any
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo '<p style="color:red;">' . htmlspecialchars($error) . '</p>';
    }
}
