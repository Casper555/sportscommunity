<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Require login to post an event
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $userId               = $_SESSION['user_id'];
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

    // Handle Image Upload
    $imageName = 'default.jpg';
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
                $errorMessage = "Fout bij het opslaan van de afbeelding.";
            }
        } else {
            $errorMessage = "Alleen JPG-, PNG- of WEBP-afbeeldingen zijn toegestaan.";
        }
    }

    // Process Database Insertion if no errors
    if (empty($errorMessage)) {
        $eventData = [
            'user_id'               => $userId,
            'title'                 => $title,
            'type'                  => $type,
            'description'           => $description,
            'image'                 => $imageName,
            'event_date'            => $eventDate,
            'start_time'            => $startTime,
            'end_time'              => $endTime,
            'location_name'         => $locationName,
            'street'                => $street,
            'postal_code'           => $postalCode,
            'city'                  => $city,
            'organizer_name'        => $organizerName,
            'contact_person'        => $contactPerson,
            'contact_email'         => $contactEmail,
            'target_group'          => $targetGroup,
            'max_participants'      => $maxParticipants,
            'price'                 => $price,
            'registration_deadline' => $registrationDeadline
        ];

        if (createEvent($pdo, $eventData)) {
            $_SESSION['success_message'] = "Evenement succesvol aangemaakt!";
            header('Location: events.php');
            exit;
        } else {
            $errorMessage = "Er is iets misgegaan bij het opslaan van het evenement in de database.";
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
    <title>Evenement aanmaken</title>
</head>

<body>
    <div class="page-shell">
        <?php include '../includes/header.php'; ?>
        <main>
            <section class="section login-section">
                <h1 class="title login-title">Evenement aanmaken</h1>
                <p class="content login-subtitle">Deel jouw toernooi, stage of examen met de judocommunity. Vul de gegevens in en publiceer je evenement wanneer alles klaar is.</p>

                <?php if (!empty($errorMessage)): ?>
                    <div style="color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 12px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="create-event.php" enctype="multipart/form-data">

                    <!-- Algemene informatie -->
                    <div class="make-event">
                        <div class="event-card-intro">
                            <h2 class="title margin-20">Algemene informatie</h2>
                            <p class="subtitle margin-20">Vertel wat je organiseert en kies de categorie die het beste past.</p>
                        </div>

                        <p class="login-text">Evenementnaam *</p>
                        <input class="input input-login" placeholder="Bijv. Toernooi Judoclub Urmond" type="text" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">

                        <div class="general-category-description">
                            <div class="general-category">
                                <label class="event-label" for="category">Categorie *</label>
                                <select class="input event-category-select" id="category" name="type" required>
                                    <option value="" disabled selected>Kies een categorie</option>
                                    <option value="Toernooi" <?= (($_POST['type'] ?? '') === 'Toernooi') ? 'selected' : '' ?>>Toernooi</option>
                                    <option value="Stage" <?= (($_POST['type'] ?? '') === 'Stage') ? 'selected' : '' ?>>Stage / Training</option>
                                    <option value="Examen" <?= (($_POST['type'] ?? '') === 'Examen') ? 'selected' : '' ?>>Examen</option>
                                </select>

                                <div class="event-image">
                                    <p class="event-label">Evenementafbeelding <span>(optioneel)</span></p>
                                    <div class="event-image-control">
                                        <input class="event-image-input" id="event-image-file" type="file" name="image" accept="image/jpeg,image/png,image/webp" aria-describedby="event-image-hint">
                                        <label class="event-image-button" for="event-image-file">
                                            <i class="fa fa-folder-open" aria-hidden="true"></i>
                                            Bestand kiezen
                                        </label>
                                        <span class="event-image-hint" id="event-image-hint">JPG of PNG · maximaal 5 MB</span>
                                    </div>
                                </div>
                            </div>

                            <div class="general-category">
                                <p class="event-label">Beschrijving *</p>
                                <textarea class="input input-description input-login" placeholder="Beschrijf het evenement, het programma en wat deelnemers kunnen verwachten." name="description" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Datum en locatie -->
                    <div class="make-event">
                        <div class="event-card-intro">
                            <h2 class="title margin-20">Datum en locatie</h2>
                            <p class="subtitle margin-20">Geef aan wanneer en waar het evenement plaatsvindt.</p>
                        </div>
                        <div class="make-event-date">
                            <p class="event-label">Datum *</p>
                            <input class="input input-login" type="date" name="event_date" required value="<?= htmlspecialchars($_POST['event_date'] ?? '') ?>">
                        </div>
                        <div class="make-event-time">
                            <div class="make-event-starttime">
                                <p class="event-label">Begintijd *</p>
                                <input class="input input-login" type="time" name="start_time" required value="<?= htmlspecialchars($_POST['start_time'] ?? '') ?>">
                            </div>
                            <div class="make-event-endtime">
                                <p class="event-label">Eindtijd</p>
                                <input class="input input-login" type="time" name="end_time" value="<?= htmlspecialchars($_POST['end_time'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="make-event-location">
                            <div class="make-event-location-info">
                                <p class="event-label">Locatie / Sporthal *</p>
                                <input class="input input-login" type="text" placeholder="Bijv. Sporthal De Bramert" name="location_name" required value="<?= htmlspecialchars($_POST['location_name'] ?? '') ?>">
                            </div>
                            <div class="make-event-location-info">
                                <p class="event-label">Straat en huisnummer *</p>
                                <input class="input input-login" type="text" placeholder="Bijv. Sportlaan 12" name="street" required value="<?= htmlspecialchars($_POST['street'] ?? '') ?>">
                            </div>
                            <div class="make-event-location-info">
                                <p class="event-label">Postcode *</p>
                                <input class="input input-login" type="text" placeholder="Bijv. 6129 AB" name="postal_code" required value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>">
                            </div>
                            <div class="make-event-location-info">
                                <p class="event-label">Plaats *</p>
                                <input class="input input-login" type="text" placeholder="Bijv. Urmond" name="city" required value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Organisatie en contact -->
                    <div class="make-event">
                        <div class="event-card-intro">
                            <h2 class="title margin-20">Organisatie en contact</h2>
                            <p class="subtitle margin-20">Laat deelnemers weten wie het evenement organiseert en waar ze terechtkunnen met vragen.</p>
                        </div>
                        <div class="make-event-date">
                            <p class="event-label">Organisator / Club *</p>
                            <input class="input input-login" type="text" placeholder="Bijv. Judoclub Urmond" name="organizer_name" required value="<?= htmlspecialchars($_POST['organizer_name'] ?? '') ?>">
                        </div>
                        <div class="make-event-time">
                            <div class="make-event-contact">
                                <p class="event-label">Contactpersoon *</p>
                                <input class="input input-login" type="text" placeholder="Voor- en achternaam" name="contact_person" required value="<?= htmlspecialchars($_POST['contact_person'] ?? '') ?>">
                            </div>
                            <div class="make-event-contact">
                                <p class="event-label">E-mailadres *</p>
                                <input class="input input-login" type="email" placeholder="Bijv. evenementen@judoclub.nl" name="contact_email" required value="<?= htmlspecialchars($_POST['contact_email'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Deelname en inschrijving -->
                    <div class="make-event">
                        <div class="event-card-intro">
                            <h2 class="title margin-20">Deelname en inschrijving</h2>
                            <p class="subtitle margin-20">Maak duidelijk voor wie het evenement is en hoe deelnemers zich kunnen aanmelden.</p>
                        </div>
                        <div class="make-event-date">
                            <p class="event-label">Doelgroep / leeftijd *</p>
                            <input class="input input-login" type="text" placeholder="Bijv. jeugd van 8 t/m 15 jaar" name="target_group" required value="<?= htmlspecialchars($_POST['target_group'] ?? '') ?>">
                        </div>
                        <div class="make-event-time">
                            <div class="make-event-contact">
                                <p class="event-label">Maximaal aantal deelnemers <span>(optioneel)</span></p>
                                <input class="input input-login" type="number" placeholder="Bijv. 75" min="1" name="max_participants" value="<?= htmlspecialchars($_POST['max_participants'] ?? '') ?>">
                            </div>
                            <div class="make-event-contact">
                                <p class="event-label">Deelnamekosten (€) *</p>
                                <input class="input input-login" type="number" step="0.01" min="0" placeholder="Bijv. 12.50" name="price" required value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="make-event-starttime">
                            <p class="event-label">Deadline inschrijven *</p>
                            <input class="input input-login" type="date" name="registration_deadline" required value="<?= htmlspecialchars($_POST['registration_deadline'] ?? '') ?>">
                        </div>
                    </div>

                    <h2 class="title margin-20">Klaar om je evenement te delen?</h2>
                    <p class="subtitle margin-20">Controleer de gegevens voor publicatie. Een gepubliceerd evenement verschijnt direct in het evenementenoverzicht.</p>
                    <button type="submit" name="create_event" class="button button-create"><i class="fa fa-arrow-right"></i> Evenement publiceren</button>

                </form>
            </section>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
    <script src="../assets/js/main.js"></script>
</body>

</html>