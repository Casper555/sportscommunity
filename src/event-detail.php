<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

$events = getUpcomingEvents($pdo,1);
$event = !empty($events) ? $events[0] : null;
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
                    <h1 class="title events-title">Titel evenement</h1>
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
                            <p class="event-detail-description-info">Lorem</p>
                        </div>
                        <div class="event-detail-locationdate">
                            <div class="detail event-detail-location">
                                <h3 class="event-detail-location-title">Locatie</h3>
                                <p class="event-detail-location-info">Urmond</p>
                            </div>
                            <div class="detail event-detail-datetime">
                                <h3 class="event-detail-datetime-title">Datum en tijd</h3>
                                <p class="event-detail-date">25-10-2026</p>
                                <p class="event-detail-time">9:00</p>
                            </div>
                        </div>
                        <div class="event-detail-signup">
                            <button>Inschrijven</button>
                        </div>
                        <div class="detail event-detail-organisation">
                            <h3 class="event-detail-organisation-title">Organisatie</h3>

                            <h4 class="event-detail-organisator">Organisator</h4>
                            <p class="event-detail-organisator-info">Jududoclub urmond</p>

                            <h4 class="event-detail-contact">Contact</h4>
                            <p class="event-detail-contact-info">Jududoclub@gmail.com</p>

                            <h4 class="event-detail-group">Doelgroep</h4>
                            <p class="event-detail-group-info">15 jaar en jonger</p>

                            <h4 class="event-detail-costs">Kosten</h4>
                            <p class="event-detail-costs-info">€5</p>
                        </div>
                    </div>
                </div>


            </section>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</body>
</html>