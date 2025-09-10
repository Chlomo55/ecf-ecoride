<?php
require_once('pdo.php');
session_start(); // Démarre la session
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRide</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>EcoRide</h1>
        <nav>
            <div class="nav-container">
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="vue.php">Covoiturage</a></li>
                    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                        <li><a href="compte.php">Compte</a></li>
                    <?php else: ?>
                        <li><a href="connexion.php">Connexion</a></li>
                    <?php endif; ?>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
                <div class="burger">
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
        </nav>
    </header>
    <script src="script.js"></script>
</body>
</html>