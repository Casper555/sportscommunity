<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="header">
    <div class="header-logo-name">
        <a href="../src/index.php">
            <img class="header-logo" src="../assets/images/judo.png" alt="Logo">
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="header-welcome">
                <?= htmlspecialchars(ucwords($_SESSION['username'] ?? 'gebruiker')) ?>
            </span>
        <?php endif; ?>
    </div>
    <div>
        <nav>
            <ul class="header-nav">
                <li><a href="../src/create-event.php">Nieuw Evenement</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li>
                        <a href="../src/profile.php">
                            <img class="header-icon" src="../assets/images/profile.png" alt="Profile">
                        </a>
                    </li>
                    <li><a href="../src/logout.php">Uitloggen</a></li>
                <?php else: ?>
                    <li><a href="../src/login.php">Inloggen</a></li>
                <?php endif; ?>
                
            </ul>
        </nav>
    </div>
</header>