<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: events.php');
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $errorMessage = 'Vul A.U.B. alle velden in.';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Wachtwoorden komen niet overeen.';
    } else {
        if (registerUser($pdo, $username, $email, $password)) {
            header('Location: login.php?registered=1');
            exit;
        } else {
            $errorMessage = 'Dit e-mailadres of deze gebruikersnaam is al in gebruik.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Document</title>
</head>

<body>
    <div class="page-shell">
        <?php include '../includes/header.php'; ?>
        <main>
            <section class="section register-section">
                <div class="login-intro">
                    <h1 class="title login-title">Registreer</h1>
                    <p class="content login-subtitle">Maak een account aan om evenementen te plaatsen, je inschrijvingen te beheren en direct contact te houden met de judocommunity.</p>
                </div>
                <div class="register-login">
                    <div class="login">
                        <div class="login-card-intro">
                            <h2 class="login-title">Maak een account aan</h2>
                            <p class="login-subtitle">Vul je gegevens in om een account aan te maken. Je kunt direct starten met het plaatsen van evenementen, het beheren van je inschrijvingen en contact houden met de judocommunity.</p>
                        </div>

                        <?php if (!empty($errorMessage)): ?>
                            <div style="color: #ff3333; background: #ffe6e6; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                                <?= htmlspecialchars($errorMessage) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="register.php">
                            <p class="login-text">Gebruikersnaam</p>
                            <div class="input-wrapper">
                                <i class="fa fa-user input-icon"></i>
                                <input class="input input-login" placeholder="Gebruikersnaam" type="text" name="username" required>
                            </div>

                            <p class="login-text">E-mailadres</p>
                            <div class="input-wrapper">
                                <i class="fa fa-envelope input-icon"></i>
                                <input class="input input-login" placeholder="naam@mail.nl" type="email" name="email" required>
                            </div>

                            <p class="login-text">Wachtwoord</p>
                            <div class="input-wrapper">
                                <i class="fa fa-lock input-icon"></i>
                                <input class="input input-login" type="password" placeholder="••••••••" name="password" required>
                            </div>

                            <p class="login-text">Herhaal wachtwoord</p>
                            <div class="input-wrapper">
                                <i class="fa fa-lock input-icon"></i>
                                <input class="input input-login" type="password" placeholder="••••••••" name="confirm_password" required>
                            </div>

                            <button type="submit" name="register" class="button inlog-button"><i class="fa fa-user-plus"></i> Account aanmaken</button>
                        </form>
                    </div>
                    <div class="login register-card">
                        <div class="login-intro">
                            <h1 class="title login-title">Al een account?</h1>
                            <p class="content login-subtitle">Heb je al een account? Log dan direct in om je evenementen te beheren, je inschrijvingen te bekijken en contact te houden met de judocommunity.</p>
                        </div>
                        <div class="account-button-margin">
                            <a href="login.php" class="button account-register-button">Inloggen</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>