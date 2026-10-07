<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// 1. Get and validate Event ID from URL
$eventId = $_GET['id'] ?? null;

if (!$eventId) {
    header('Location: events.php');
    exit;
}

$event = getEventById($pdo, (int)$eventId);

if (!$event) {
    header('Location: events.php');
    exit;
}

// 2. Fetch other upcoming events (excluding current event if needed)
$newevents = getUpcomingEvents($pdo, 3);

$successMessage = '';
$errorMessage = '';

// 3. Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_event'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $userId = $_SESSION['user_id'];

    if (registerForEvent($pdo, $userId, $event['id'])) {
        $successMessage = "Je bent succesvol ingeschreven!";
        // Re-fetch event to update participant count
        $event = getEventById($pdo, (int)$eventId);
    } else {
        $errorMessage = "Je bent al ingeschreven voor dit evenement.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Event detail</title>
</head>

<body>
    <div class="page-shell">
        <?php include '../includes/header.php'; ?>
        <main>
            <section class="section events-detail-section">
                <div class="events-intro">
                    <h1 class="title events-title"><?= htmlspecialchars($event['title']) ?></h1>
                    <p class="content events-content">Bekijk alle informatie over dit evenement, controleer de beschikbaarheid en meld je direct aan vanuit deze pagina.</p>
                </div>
                <div class="event-detail-layout">
                    <?php if ($event): ?>
                        <div class="event-card-detail">
                            <img class="event-card-image" src="../assets/images/<?= htmlspecialchars($event['image']) ?>" alt="Event image">
                            <div class="event-card-content-detail">
                                <?= htmlspecialchars($event['title']) ?>
                                <div class="event-card-detail-placetime">
                                    <span><?= htmlspecialchars($event['city']) ?></span>
                                    <span><?= date('d-m-Y', strtotime($event['event_date'])) ?></span>
                                </div>
                                <div class="event-card-countgroup">
                                    <div><?= $event['target_group'] ?> </div>
                                </div>
                                <div class="event-detail-card-count">
                                    <div class="event-detail-count">
                                        <p class="event-detail-count-text">Beschikbaarheid</p>
                                        <?= $event['registration_count'] ?> / <?= $event['max_participants'] ?> deelnemers

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="event-detail-info">
                        <div class="detail event-detail-description">
                            <h3 class="event-detail-description-title">Overzicht</h3>
                            <p class="event-detail-description-info"><?= htmlspecialchars($event['description'] ?? 'Geen beschrijving beschikbaar.') ?></p>
                        </div>

                        <div class="event-detail-locationdate">
                            <div class="detail event-detail-location">
                                <h3 class="event-detail-location-title">Locatie</h3>
                                <p class="event-detail-location-info"><?= htmlspecialchars($event['city']) ?></p>
                            </div>
                            <div class="detail event-detail-datetime">
                                <h3 class="event-detail-datetime-title">Datum en tijd</h3>
                                <p class="event-detail-date"><?= date('d-m-Y', strtotime($event['event_date'])) ?></p>
                                <p class="event-detail-time"><?= date('H:i', strtotime($event['event_date'])) ?> uur</p>
                            </div>
                        </div>

                        <div class="event-detail-signup">
                            <h3 class="event-detail-button-text">Dit iets voor jou?</h3>
                            <p class="event-detail-button-text">Schrijf je hier meteen in, vol is vol. Je kunt altijd nog uitschrijven.</p>

                            <form method="POST" action="event-detail.php?id=<?= $event['id'] ?>">
                                <button type="submit" name="register_event" class="event-detail-button">Inschrijven</button>
                            </form>
                        </div>

                        <div class="detail event-detail-organisation">
                            <h3 class="event-detail-organisation-title">Organisatie</h3>

                            <h4 class="event-detail-organisator">Organisator</h4>
                            <p class="event-detail-organisator-info"><?= htmlspecialchars($event['organizer_name'] ?? 'Onbekend') ?></p>

                            <h4 class="event-detail-contact">Contact</h4>
                            <p class="event-detail-contact-info"><?= htmlspecialchars($event['organizer_email'] ?? 'Geen e-mail bekend') ?></p>

                            <h4 class="event-detail-group">Doelgroep</h4>
                            <p class="event-detail-group-info"><?= htmlspecialchars($event['target_group']) ?></p>

                            <h4 class="event-detail-costs">Kosten</h4>
                            <p class="event-detail-costs-info">€<?= number_format($event['price'] ?? 0, 2, ',', '.') ?></p>
                        </div>
                    </div>
                </div>
                <div class="event-detail-events-title">
                    <h2 class="title ">Andere evenementen</h2>
                </div>
                <div class="event-detail-events">
                    <?php if (empty($newevents)): ?>
                        <p>Er zijn op dit moment geen komende evenementen.</p>
                    <?php else: ?>
                        <?php foreach ($newevents as $event): ?>
                            <div class="event-card">
                                <img class="event-card-image" src="../assets/images/<?= htmlspecialchars($event['image']) ?>" alt="Event image">
                                <div class="event-card-content">
                                    <?= htmlspecialchars($event['title']) ?>
                                    <div class="event-card-placetime">
                                        <?= htmlspecialchars($event['city']) ?>
                                        <?= date('d-m-Y', strtotime($event['event_date'])) ?>
                                    </div>
                                    <div class="event-card-countgroup">
                                        <div><?= $event['registration_count'] ?> / <?= $event['max_participants'] ?> deelnemers</div>
                                        <div><?= $event['target_group'] ?> </div>
                                    </div>
                                    <a class="event-card-button" href="event-detail.php?id=<?= $event['id'] ?>">Meer info & inschrijven</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </section>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>