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

$userId = $_SESSION['user_id'] ?? null;

// 2. Handle Flash Messages from Session (PRG Pattern)
$successMessage = $_SESSION['success_message'] ?? '';
$errorMessage = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// 3. Handle registration / unregistration form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$userId) {
        header('Location: login.php');
        exit;
    }

    if (isset($_POST['register_event'])) {
        if (registerForEvent($pdo, $userId, $event['id'])) {
            $_SESSION['success_message'] = "Je bent succesvol ingeschreven!";
        } else {
            $_SESSION['error_message'] = "Inschrijven mislukt: het evenement is vol of je bent al ingeschreven.";
        }
    } elseif (isset($_POST['unregister_event'])) {
        if (unregisterFromEvent($pdo, $userId, $event['id'])) {
            $_SESSION['success_message'] = "Je bent succesvol uitgeschreven.";
        } else {
            $_SESSION['error_message'] = "Er is iets misgegaan bij het uitschrijven.";
        }
    }

    // Redirect to self (GET request) to clean browser history
    header('Location: event-detail.php?id=' . $event['id']);
    exit;
}

// 4. Fetch other upcoming events
$newevents = getUpcomingEvents($pdo, 3);

// 5. Check user status relative to this event
$isRegistered = false;
$isOwner = false;

if ($userId) {
    $isRegistered = isUserRegistered($pdo, $userId, $event['id']);
    if (isset($event['user_id'])) {
        $isOwner = ($userId == $event['user_id']);
    }
}
?>

<!DOCTYPE html>
<html lang="nl">

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
                
                <!-- Back Link -->
                <div class="back-navigation">
                    <a href="events.php" class="back-link">&larr; Terug naar overzicht</a>
                </div>

                <div class="events-intro">
                    <h1 class="title events-title"><?= htmlspecialchars($event['title']) ?></h1>
                    <p class="content events-content">Bekijk alle informatie over dit evenement, controleer de beschikbaarheid en meld je direct aan vanuit deze pagina.</p>
                </div>

                <?php if (!empty($successMessage)): ?>
                    <div style="color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 12px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                        <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div style="color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 12px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php endif; ?>

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
                                    <div><?= htmlspecialchars($event['target_group']) ?></div>
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
                            <h3 class="event-detail-button-text">Deelname</h3>

                            <?php if ($isOwner): ?>
                                <p class="event-detail-button-text">Dit evenement is door jou georganiseerd.</p>
                            <?php elseif ($isRegistered): ?>
                                <p class="event-detail-button-text">Je bent momenteel ingeschreven voor dit evenement.</p>
                                <form method="POST" action="event-detail.php?id=<?= $event['id'] ?>">
                                    <button type="submit" name="unregister_event" class="event-detail-button unregister-button">Uitschrijven</button>
                                </form>
                            <?php elseif ($event['max_participants'] !== null && $event['registration_count'] >= $event['max_participants']): ?>
                                <p class="event-detail-button-text" style="color: #c62828; font-weight: bold;">Dit evenement is vol.</p>
                                <button type="button" class="event-detail-button" disabled style="opacity: 0.6; cursor: not-allowed;">Vol</button>
                            <?php else: ?>
                                <p class="event-detail-button-text">Schrijf je hier meteen in, vol is vol. Je kunt altijd nog uitschrijven.</p>
                                <form method="POST" action="event-detail.php?id=<?= $event['id'] ?>">
                                    <button type="submit" name="register_event" class="event-detail-button">Inschrijven</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <div class="detail event-detail-organisation">
                            <h3 class="event-detail-organisation-title">Organisatie</h3>

                            <h4 class="event-detail-organisator">Organisator</h4>
                            <p class="event-detail-organisator-info"><?= htmlspecialchars($event['organizer_name'] ?? 'Onbekend') ?></p>

                            <h4 class="event-detail-contact">Contact</h4>
                            <p class="event-detail-contact-info"><?= htmlspecialchars($event['contact_email'] ?? 'Geen e-mail bekend') ?></p>

                            <h4 class="event-detail-group">Doelgroep</h4>
                            <p class="event-detail-group-info"><?= htmlspecialchars($event['target_group']) ?></p>

                            <h4 class="event-detail-costs">Kosten</h4>
                            <p class="event-detail-costs-info">€<?= number_format($event['price'] ?? 0, 2, ',', '.') ?></p>
                        </div>
                    </div>
                </div>

                <div class="event-detail-events-title">
                    <h2 class="title">Andere evenementen</h2>
                </div>
                <div class="event-detail-events">
                    <?php if (empty($newevents)): ?>
                        <p>Er zijn op dit moment geen komende evenementen.</p>
                    <?php else: ?>
                        <?php foreach ($newevents as $newEvent): ?>
                            <div class="event-card">
                                <img class="event-card-image" src="../assets/images/<?= htmlspecialchars($newEvent['image']) ?>" alt="Event image">
                                <div class="event-card-content">
                                    <?= htmlspecialchars($newEvent['title']) ?>
                                    <div class="event-card-placetime">
                                        <?= htmlspecialchars($newEvent['city']) ?>
                                        <?= date('d-m-Y', strtotime($newEvent['event_date'])) ?>
                                    </div>
                                    <div class="event-card-countgroup">
                                        <div><?= $newEvent['registration_count'] ?> / <?= $newEvent['max_participants'] ?> deelnemers</div>
                                        <div><?= htmlspecialchars($newEvent['target_group']) ?></div>
                                    </div>
                                    <a class="event-card-button" href="event-detail.php?id=<?= $newEvent['id'] ?>">Meer info & inschrijven</a>
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