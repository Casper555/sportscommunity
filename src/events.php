<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

$type = $_GET['type'] ?? null;
$search = $_GET['q'] ?? null;

$events = getAllEvents($pdo, $type, $search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>evenementen</title>
</head>
<body>
    <div class="page-shell">
        <?php include '../includes/header.php'; ?>
        <main>
            <section class="section events-section">                
                <div class="events-intro">
                    <h1 class="title events-title">Zoek evenementen</h1>
                    <p class="content events-content">Vind toernooien, stages en examens in één overzicht. Gebruik de zoekbalk om snel te zoeken en filter op categorie om direct de juiste activiteit te vinden.</p>
                </div>
                <form method="GET" action="events.php">
                    <!-- Keep existing type parameter active if filtering -->
                    <?php if ($type): ?>
                        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                    <?php endif; ?>
                    
                    <input 
                        type="text" 
                        name="q" 
                        class="input events-input" 
                        placeholder="🔍︎ Zoek op evenement, locatie of organisator"
                        value="<?= htmlspecialchars($search ?? '') ?>"
                    >
                </form>
                <div class="events-filter">
                    <a href="events.php" class="button button-event <?= empty($type) ? 'active' : '' ?>">
                    Alle categorieën
                    </a>
                    <a href="events.php?type=Toernooi" class="button button-event <?= $type === 'Toernooi' ? 'active' : '' ?>">
                    Toernooien
                    </a>
                    <a href="events.php?type=Stage" class="button button-event <?= $type === 'Stage' ? 'active' : '' ?>">
                    Stages en Trainingen
                    </a>
                    <a href="events.php?type=Examen" class="button button-event <?= $type === 'Examen' ? 'active' : '' ?>">
                    Examens
                    </a>
                </div>
                <div class="events-title-result">
                    <h1 class="homepage-event-title">Evenementen</h1>
                    <p><?= count($events) ?> <?= count($events) === 1 ? 'resultaat' : 'resultaten' ?></p>
                </div>
                <?php if (!empty($events)): ?>
                <div class="events-cards-wrap">
                    <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <img class="event-card-image" src="../assets/images/<?= htmlspecialchars($event['image']) ?>" alt="Event image">
                        <div class="event-card-content">
                            <?= htmlspecialchars($event['title']) ?>
                            <div class="event-card-placetime">
                                <?= htmlspecialchars($event['city']) ?>
                                <?= date('d-m-Y', strtotime($event['event_date'])) ?>
                            </div>
                            <?= $event['registration_count'] ?> / <?= $event['max_participants'] ?> deelnemers
                            <a class="event-card-button" href="event-detail.php?id=<?= $event['id'] ?>">Meer info & inschrijven</a>                        
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p>Geen evenementen gevonden voor deze zoekopdracht.</p>
                <?php endif; ?>
            </section>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div> 
</body>
</html>