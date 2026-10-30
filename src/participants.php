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

// Get and validate Event ID from URL
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

// Security check: Only the event owner can view the participant list
if ($event['user_id'] != $userId) {
    header('Location: event-detail.php?id=' . $event['id']);
    exit;
}

$participants = getEventParticipants($pdo, (int)$eventId);
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Inschrijvingen - <?= htmlspecialchars($event['title']) ?></title>
</head>

<body>
    <div class="page-shell">
        <?php include '../includes/header.php'; ?>
        <main>
            <section class="section profile-section">

                <!-- Back Link -->
                <div class="back-navigation participants-back-nav">
                    <a href="event-detail.php?id=<?= $event['id'] ?>" class="back-link">&larr; Terug naar evenement</a>
                </div>

                <div class="events-intro participants-intro-header">
                    <div>
                        <h1 class="title events-title">Inschrijvingen</h1>
                        <p class="content events-content">Bekijk hier alle deelnemers die zich hebben ingeschreven voor dit evenement, inclusief naam en e-mailadres.</p>
                    </div>
                    <span class="participants-count-text"><?= count($participants) ?> inschrijvingen</span>
                </div>

                <div class="participants-card">
                    <div>
                        <h3 class="subtitle participants-card-title">Deelnemers</h3>
                        <p class="participants-card-desc">Overzicht van alle geregistreerde deelnemers voor <?= htmlspecialchars($event['title']) ?>.</p>
                    </div>
                    <?php if (empty($participants)): ?>
                        <p class="participants-empty">Er hebben zich nog geen deelnemers ingeschreven voor dit evenement.</p>
                    <?php else: ?>
                        <div class="participants-table-wrapper">
                            <table class="participants-table">
                                <thead>
                                    <tr>
                                        <th>Naam</th>
                                        <th>E-mailadres</th>
                                        <th>Ingeschreven op</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($participants as $participant): ?>
                                        <tr>
                                            <td class="participant-name"><?= htmlspecialchars($participant['username']) ?></td>
                                            <td class="participant-email"><?= htmlspecialchars($participant['email']) ?></td>
                                            <td class="participant-date"><?= date('d-m-Y H:i', strtotime($participant['registration_date'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>