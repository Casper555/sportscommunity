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

// Fetch user data
$user = getUserById($pdo, (int)$userId);
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$successMessage = $_SESSION['success_message'] ?? '';
$errorMessage = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Handle unregister from profile page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unregister_event'])) {
    $eventId = (int)$_POST['event_id'];
    if (unregisterFromEvent($pdo, $userId, $eventId)) {
        $_SESSION['success_message'] = "Je bent succesvol uitgeschreven.";
    } else {
        $_SESSION['error_message'] = "Er is iets misgegaan bij het uitschrijven.";
    }
    header('Location: profile.php');
    exit;
}

// Fetch data for lists
$registeredEvents = getUserRegisteredEvents($pdo, (int)$userId);
$createdEvents = getUserCreatedEvents($pdo, (int)$userId);

// Generate initials for avatar badge
$username = $user['username'] ?? 'Gebruiker';
$initials = strtoupper(substr($username, 0, 2));
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Mijn profiel</title>
</head>

<body>
    <div class="page-shell">
        <?php include '../includes/header.php'; ?>
        <main>
            <section class="section profile-section">
                <h1 class="title login-title">Mijn profiel</h1> 
                <p class="content login-subtitle">Welkom, <?= htmlspecialchars($username) ?>. Bekijk je gegevens en houd je judo-evenementen bij op één plek.</p>

                <?php if (!empty($successMessage)): ?>
                    <div class="alert-success">
                        <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert-error">
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php endif; ?>

                <div class="profile-container">
                    <div class="profile-card">
                        <div class="profile-user-header">
                            <div class="profile-avatar-badge">
                                <?= htmlspecialchars($initials) ?>
                            </div>
                            <div>
                                <h3 class="profile-user-title"><?= htmlspecialchars($username) ?></h3>
                                <span class="profile-user-subtitle">Lid van de community</span>
                            </div>
                        </div>

                        <h4 class="content profile-section-heading">Persoonlijke gegevens</h4>
                        
                        <p class="profile-info-label">Naam</p>
                        <p class="profile-info-value"><?= htmlspecialchars($username) ?></p>

                        <p class="profile-info-label">E-mailadres</p>
                        <p class="profile-info-value"><?= htmlspecialchars($user['email']) ?></p>
                    </div>

                    <div class="profile-events-area">
                        
                        <div class="profile-events-header">
                            <h2 class="title" style="font-size: 1.4rem; margin: 0;">Ingeschreven evenementen</h2>
                            <span style="color: #606B78; font-size: 0.9rem;"><?= count($registeredEvents) ?> inschrijvingen</span>
                        </div>

                        <?php if (empty($registeredEvents)): ?>
                            <p style="color: #606B78; margin-bottom: 30px;">Je bent momenteel voor geen enkele evenement ingeschreven.</p>
                        <?php else: ?>
                            <div class="profile-events-grid">
                                <?php foreach ($registeredEvents as $event): ?>
                                    <div class="profile-event-card">
                                        <img class="profile-event-img" src="../assets/images/<?= htmlspecialchars($event['image']) ?>" alt="Event image">
                                        <div class="profile-event-content">
                                            <span class="profile-event-badge"><?= htmlspecialchars($event['type']) ?></span>
                                            <h3 class="profile-event-title"><?= htmlspecialchars($event['title']) ?></h3>
                                            
                                            <div class="profile-event-meta">
                                                <div><i class="fa fa-calendar"></i> <?= date('d-m-Y', strtotime($event['event_date'])) ?></div>
                                                <div><i class="fa fa-map-marker"></i> <?= htmlspecialchars($event['city']) ?></div>
                                            </div>

                                            <div class="profile-event-actions">
                                                <a class="profile-event-link-btn" href="event-detail.php?id=<?= $event['id'] ?>">Evenement bekijken</a>
                                                <form method="POST" action="profile.php">
                                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                                    <button type="submit" name="unregister_event" class="profile-unregister-btn">Uitschrijven</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="profile-events-header">
                            <h2 class="title" style="font-size: 1.4rem; margin: 0;">Eigen evenementen</h2>
                            <span style="color: #606B78; font-size: 0.9rem;"><?= count($createdEvents) ?> evenementen</span>
                        </div>

                        <?php if (empty($createdEvents)): ?>
                            <p style="color: #606B78;">Je hebt nog geen eigen evenementen aangemaakt.</p>
                        <?php else: ?>
                            <div class="profile-created-list">
                                <?php foreach ($createdEvents as $event): ?>
                                    <div class="profile-created-item">
                                        <img class="profile-created-img" src="../assets/images/<?= htmlspecialchars($event['image']) ?>" alt="">
                                        <div class="profile-created-info">
                                            <span class="profile-created-badge"><?= htmlspecialchars($event['type']) ?></span>
                                            <h3 class="profile-created-title"><?= htmlspecialchars($event['title']) ?></h3>
                                            <p class="profile-created-meta"><i class="fa fa-calendar"></i> <?= date('d-m-Y', strtotime($event['event_date'])) ?> &nbsp;|&nbsp; <i class="fa fa-map-marker"></i> <?= htmlspecialchars($event['city']) ?></p>
                                        </div>
                                        <div>
                                            <a href="edit-event.php?id=<?= $event['id'] ?>" class="profile-created-action-link">Evenement bewerken</a> 
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </section>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>