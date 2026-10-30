<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

$events = getUpcomingEvents($pdo, 3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Document</title>
</head>
<body>
    <div class="page-shell">
        <?php include '../includes/header.php'; ?>
        <main>
            <section class="section section-homepage">
                <div class="homepage-intro">
                    <h1 class="homepage-hero-title">Judocommunity</h1>
                    <h3 class="homepage-hero-subtitle">Jouw centrale plek voor alle judo-evenementen, toernooien en stages.</h3>
                    <p class="homepage-hero-content">Of je nu op zoek bent naar je volgende toernooi, wilt deelnemen aan een inspirerende judostage, of jezelf voorbereidt op je volgende dan-examen: op de Judo Sportscommunity vind je alle evenementen overzichtelijk op één plek. Bekijk het actuele aanbod, schrijf je direct in en blijf verbonden met de judocommunity.
                    <br><br>Organiseer je zelf een toernooi of clubactiviteit? Maak binnen een minuut een account aan en plaats jouw evenement direct op het platform.</p>
                </div>
                    <a class="homepage-hero-button" href="./events.php">Bekijk evenementen
                        <img class="homepage-hero-button-arrow" src="../assets/images/arrow.png">
                    </a>
            </section>

            <section class="section section-homepage-events">
                <h1 class="homepage-event-title">Komende evenementen</h1>
                <div class="homepage-event-cards-wrap">
                    <?php if (empty($events)): ?>
                        <p>Er zijn op dit moment geen komende evenementen.</p>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <div class="event-card">
                                <img class="event-card-image" src="../assets/images/<?= htmlspecialchars($event['image']) ?>" alt="Event image">
                                <div class="event-card-content">
                                    <?= htmlspecialchars($event['title']) ?>
                                    <div class="event-card-placetime">
                                        <div><?= htmlspecialchars($event['city']) ?></div>
                                        <div><?= date('d-m-Y', strtotime($event['event_date'])) ?></div>
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
            <section class="section section-homepage-catagories">
                <h1 class="homepage-catagories-title">Catagoriën</h1>
                <div class="homepage-catagories-cards-wrap">
                    <a href="./events.php?type=Toernooi" class="catagories-card">
                        <img class="event-card-image" src="../assets/images/judo.jpeg">
                        <h6 class="catagories-card-title">Tournooien</h6>
                    </a>
                     <a href="./events.php?type=Stage" class="catagories-card">
                        <img class="event-card-image" src="../assets/images/judo.jpeg">
                        <h6 class="catagories-card-title">Stages & Trainingen</h6>
                    </a>
                     <a href="./events.php?type=Examen" class="catagories-card">
                        <img class="event-card-image" src="../assets/images/judo.jpeg">
                        <h6 class="catagories-card-title">Examens</h6>
                    </a>
                </div>
            </section>

        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</body>
</html>