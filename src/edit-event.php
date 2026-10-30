<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: login.php');
    exit;
}

// Get and validate Event ID
$eventId = $_GET['id'] ?? null;
if (!$eventId) {
    header('Location: profile.php');
    exit;
}

$event = getEventById($pdo, (int)$eventId);

// Verify event exists and belongs to logged-in user
if (!$event || $event['user_id'] != $userId) {
    header('Location: profile.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $title                = trim($_POST['title'] ?? '');
    $type                 = trim($_POST['type'] ?? '');
    $description          = trim($_POST['description'] ?? '');
    $eventDate            = $_POST['event_date'] ?? '';
    $startTime            = $_POST['start_time'] ?? '';
    $endTime              = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
    $locationName         = trim($_POST['location_name'] ?? '');
    $street               = trim($_POST['street'] ?? '');
    $postalCode           = trim($_POST['postal_code'] ?? '');
    $city                 = trim($_POST['city'] ?? '');
    $organizerName        = trim($_POST['organizer_name'] ?? '');
    $contactPerson        = trim($_POST['contact_person'] ?? '');
    $contactEmail         = trim($_POST['contact_email'] ?? '');
    $targetGroup          = trim($_POST['target_group'] ?? '');
    $maxParticipants      = !empty($_POST['max_participants']) ? (int)$_POST['max_participants'] : null;
    $price                = isset($_POST['price']) && $_POST['price'] !== '' ? (float)$_POST['price'] : 0.00;
    $registrationDeadline = $_POST['registration_deadline'] ?? '';

    // Keep old image by default
    $imageName = $event['image'];

    // Handle new image upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['image']['tmp_name'];
        $fileName      = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts   = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExtension, $allowedExts)) {
            $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
            $uploadDir   = '../assets/images/';

            if (move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                $imageName = $newFileName;
            } else {
                $errorMessage = "Fout bij het opslaan van de nieuwe afbeelding.";
            }
        } else {
            $errorMessage = "Alleen JPG-, PNG- of WEBP-afbeeldingen zijn toegestaan.";
        }
    }

    if (empty($errorMessage)) {
        $eventData = [
            'title'                => $title,
            'type'                 => $type,
            'description'          => $description,
            'image'                => $imageName,
            'event_date'           => $eventDate,
            'start_time'           => $startTime,
            'end_time'             => $endTime,
            'location_name'        => $locationName,
            'street'               => $street,
            'postal_code'          => $postalCode,
            'city'                 => $city,
            'organizer_name'       => $organizerName,
            'contact_person'       => $contactPerson,
            'contact_email'        => $contactEmail,
            'target_group'         => $targetGroup,
            'max_participants'     => $maxParticipants,
            'price'                => $price,
            'registration_deadline' => $registrationDeadline
        ];

        if (updateEvent($pdo, (int)$eventId, (int)$userId, $eventData)) {
            $_SESSION['success_message'] = "Evenement succesvol bijgewerkt!";
            header('Location: profile.php');
            exit;
        } else {
            $errorMessage = "Er is iets misgegaan bij het bijwerken van het evenement.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Evenement bewerken</title>
</head>

<body>
    <div class="page-shell">
        <?php include '../includes/header.php'; ?>
        <main>
            <section class="section login-section">
                <div class="back-navigation">
                    <a href="profile.php" class="back-link">&larr; Terug naar profiel</a>
                </div>
                <h1 class="title login-title">Evenement bewerken</h1>
                <p class="content login-subtitle">Pas de gegevens van je evenement hieronder aan.</p>

                <?php if (!empty($errorMessage)): ?>
                    <div style="color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 12px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="edit-event.php?id=<?= $event['id'] ?>" enctype="multipart/form-data">

                    <!-- Algemene informatie -->
                    <div class="make-event">
                        <div class="event-card-intro">
                            <h2 class="title margin-20">Algemene informatie</h2>
                            <p class="subtitle margin-20">Vertel wat je organiseert en kies de categorie die het beste past.</p>
                        </div>

                        <p class="login-text">Evenementnaam *</p>
                        <input class="input input-login" placeholder="Bijv. Toernooi Judoclub Urmond" type="text" name="title" required value="<?= htmlspecialchars($event['title']) ?>">

                        <div class="general-category-description">
                            <div class="general-category">
                                <label class="event-label" for="category">Categorie *</label>
                                <select class="input event-category-select" id="category" name="type" required>
                                    <option value="" disabled>Kies een categorie</option>
                                    <option value="Toernooi" <?= ($event['type'] === 'Toernooi') ? 'selected' : '' ?>>Toernooi</option>
                                    <option value="Stage" <?= ($event['type'] === 'Stage') ? 'selected' : '' ?>>Stage / Training</option>
                                    <option value="Examen" <?= ($event['type'] === 'Examen') ? 'selected' : '' ?>>Examen</option>
                                </select>

                                <div class="event-image">
                                    <p class="event-label">Evenementafbeelding <span>(optioneel)</span></p>
                                    <div class="event-image-control">
                                        <input class="event-image-input" id="event-image-file" type="file" name="image" accept="image/jpeg,image/png,image/webp" aria-describedby="event-image-hint">
                                        <label class="event-image-button" for="event-image-file">
                                            <i class="fa fa-folder-open" aria-hidden="true"></i>
                                            Nieuw bestand kiezen
                                        </label>
                                        <span class="event-image-hint" id="event-image-hint">Huidige: <?= htmlspecialchars($event['image']) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="general-category">
                                <p class="event-label">Beschrijving *</p>
                                <textarea class="input input-description input-login" placeholder="Beschrijf het evenement..." name="description" required><?= htmlspecialchars($event['description']) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Datum en locatie -->
                    <div class="make-event">
                        <div class="event-card-intro">
                            <h2 class="title margin-20">Datum en locatie</h2>
                        </div>
                        <div class="make-event-date">
                            <p class="event-label">Datum *</p>
                            <input class="input input-login" type="date" name="event_date" required value="<?= htmlspecialchars($event['event_date']) ?>">
                        </div>
                        <div class="make-event-time">
                            <div class="make-event-starttime">
                                <p class="event-label">Begintijd *</p>
                                <input class="input input-login" type="time" name="start_time" required value="<?= htmlspecialchars($event['start_time']) ?>">
                            </div>
                            <div class="make-event-endtime">
                                <p class="event-label">Eindtijd</p>
                                <input class="input input-login" type="time" name="end_time" value="<?= htmlspecialchars($event['end_time'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="make-event-location">
                            <div class="make-event-location-info">
                                <p class="event-label">Locatie / Sporthal *</p>
                                <input class="input input-login" type="text" name="location_name" required value="<?= htmlspecialchars($event['location_name']) ?>">
                            </div>
                            <div class="make-event-location-info">
                                <p class="event-label">Straat en huisnummer *</p>
                                <input class="input input-login" type="text" name="street" required value="<?= htmlspecialchars($event['street']) ?>">
                            </div>
                            <div class="make-event-location-info">
                                <p class="event-label">Postcode *</p>
                                <input class="input input-login" type="text" name="postal_code" required value="<?= htmlspecialchars($event['postal_code']) ?>">
                            </div>
                            <div class="make-event-location-info">
                                <p class="event-label">Plaats *</p>
                                <input class="input input-login" type="text" name="city" required value="<?= htmlspecialchars($event['city']) ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Organisatie en contact -->
                    <div class="make-event">
                        <div class="event-card-intro">
                            <h2 class="title margin-20">Organisatie en contact</h2>
                        </div>
                        <div class="make-event-date">
                            <p class="event-label">Organisator / Club *</p>
                            <input class="input input-login" type="text" name="organizer_name" required value="<?= htmlspecialchars($event['organizer_name']) ?>">
                        </div>
                        <div class="make-event-time">
                            <div class="make-event-contact">
                                <p class="event-label">Contactpersoon *</p>
                                <input class="input input-login" type="text" name="contact_person" required value="<?= htmlspecialchars($event['contact_person']) ?>">
                            </div>
                            <div class="make-event-contact">
                                <p class="event-label">E-mailadres *</p>
                                <input class="input input-login" type="email" name="contact_email" required value="<?= htmlspecialchars($event['contact_email']) ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Deelname en inschrijving -->
                    <div class="make-event">
                        <div class="event-card-intro">
                            <h2 class="title margin-20">Deelname en inschrijving</h2>
                        </div>
                        <div class="make-event-date">
                            <p class="event-label">Doelgroep / leeftijd *</p>
                            <input class="input input-login" type="text" name="target_group" required value="<?= htmlspecialchars($event['target_group']) ?>">
                        </div>
                        <div class="make-event-time">
                            <div class="make-event-contact">
                                <p class="event-label">Maximaal aantal deelnemers</p>
                                <input class="input input-login" type="number" min="1" name="max_participants" value="<?= htmlspecialchars($event['max_participants'] ?? '') ?>">
                            </div>
                            <div class="make-event-contact">
                                <p class="event-label">Deelnamekosten (€) *</p>
                                <input class="input input-login" type="number" step="0.01" min="0" name="price" required value="<?= htmlspecialchars($event['price']) ?>">
                            </div>
                        </div>
                        <div class="make-event-starttime">
                            <p class="event-label">Deadline inschrijven *</p>
                            <input class="input input-login" type="date" name="registration_deadline" required value="<?= htmlspecialchars($event['registration_deadline']) ?>">
                        </div>
                    </div>

                    <button type="submit" name="update_event" class="button button-create"><i class="fa fa-arrow-right"></i> Wijzigingen opslaan</button>

                </form>
            </section>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
    <script src="../assets/js/main.js"></script>
</body>

</html>