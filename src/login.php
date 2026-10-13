<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: events.php');
    exit;
}

$errorMessage = '';
$successMessage = '';

// Display success notification when redirected from register.php
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $successMessage = 'Account succesvol aangemaakt! Je kunt nu inloggen.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errorMessage = 'Vul A.U.B. alle velden in.';
    } else {
        $user = loginUser($pdo, $email, $password);

        if ($user) {
            // Save user details in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Redirect to dashboard/events page
            header('Location: events.php');
            exit;
        } else {
            $errorMessage = 'Ongeldig e-mailadres of wachtwoord.';
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
    <title>Login</title>
</head>

<body>
    <div class="page-shell">
        <?php include '../includes/header.php'; ?>
        <main>
            <section class="section login-section">
                <div class="login-intro">
                    <h1 class="title login-title">Inloggen</h1>
                    <p class="content login-subtitle">Log in op je account om je evenementen te bekijken, je inschrijvingen te beheren en direct contact te houden met de judocommunity.</p>
                </div>
                <div class="login">
                    <div class="login-card-intro">
                        <h2 class="login-title">Welkom terug</h2>
                        <p class="login-subtitle">Vul je e-mailadres en wachtwoord in om toegang te krijgen tot je account, je evenementen en je persoonlijke instellingen.</p>
                    </div>

                    <?php if (!empty($successMessage)): ?>
                        <div style="color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                            <?= htmlspecialchars($successMessage) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errorMessage)): ?>
                        <div style="color: #ff3333; background: #ffe6e6; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                            <?= htmlspecialchars($errorMessage) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
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
                        <button type="submit" name="login" class="button inlog-button"><i class="fa fa-sign-in"></i> Inloggen</button>
                        <div class="account-button-margin">
                            <a href="register.php" class="button account-button">Nog geen account?</a>
                        </div>
                    </form>
                </div>
            </section>
        </main>
        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>