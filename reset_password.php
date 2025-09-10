<?php
require_once('header.php');
require_once('pdo.php');

if (!isset($_GET['mail'])) {
    echo "<p style='color:red;'>Lien invalide.</p>";
    exit;
}
$mail = $_GET['mail'];

$stmt = $pdo->prepare("SELECT * FROM user WHERE mail = ?");
$stmt->execute([$mail]);
$user = $stmt->fetch();
if (!$user) {
    echo "<p style='color:red;'>Utilisateur introuvable.</p>";
    exit;
}

if (!isset($_SESSION['reset_step'])) {
    $_SESSION['reset_step'] = 1;
}

// Étape 1 : Vérification du code à 6 chiffres
if ($_SESSION['reset_step'] == 1 && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_code'])) {
    $reset_code = $_POST['reset_code'];
    $stmt = $pdo->prepare("SELECT reset_code, reset_code_expire FROM user WHERE mail = ?");
    $stmt->execute([$mail]);
    $row = $stmt->fetch();
    if ($row && $reset_code === $row['reset_code']) {
        if (strtotime($row['reset_code_expire']) >= time()) {
            $_SESSION['reset_step'] = 2;
        } else {
            echo "<p style='color:red;'>Le code a expiré. Veuillez refaire une demande.</p>";
        }
    } else {
        echo "<p style='color:red;'>Code incorrect.</p>";
    }
}

// Étape 2 : Saisie du nouveau mot de passe
if ($_SESSION['reset_step'] == 2 && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_pass'], $_POST['confirm_pass'])) {
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];
    if ($new_pass !== $confirm_pass) {
        echo "<p style='color:red;'>Les mots de passe ne correspondent pas.</p>";
    } elseif (strlen($new_pass) < 6) {
        echo "<p style='color:red;'>Le mot de passe doit contenir au moins 6 caractères.</p>";
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE user SET pass = ?, reset_code = NULL, reset_code_expire = NULL WHERE mail = ?");
        $stmt->execute([$hash, $mail]);
        // Envoi du mail de confirmation
        require_once 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once 'vendor/phpmailer/phpmailer/src/SMTP.php';
        require_once 'vendor/phpmailer/phpmailer/src/Exception.php';
        $mailConfirm = new PHPMailer\PHPMailer\PHPMailer();
        $mailConfirm->isSMTP();
        $mailConfirm->Host = 'smtp.gmail.com';
        $mailConfirm->SMTPAuth = true;
        $mailConfirm->Username = 'chlomo.freoua@gmail.com'; // à personnaliser
        $mailConfirm->Password = 'mjkauepkaitjimeo'; // à personnaliser
        $mailConfirm->SMTPSecure = 'tls';
        $mailConfirm->Port = 587;
        $mailConfirm->setFrom('chlomo.freoua@gmail.com', 'EcoRide');
        $mailConfirm->addAddress($user['mail']);
        $mailConfirm->CharSet = 'UTF-8';
        $mailConfirm->isHTML(true);
        $mailConfirm->Subject = 'Confirmation de modification de mot de passe';
        $mailConfirm->Body = "<div style='font-family:sans-serif;color:#222;'>"
            . "<h2 style='color:#2e8b57;'>Votre mot de passe EcoRide a été modifié</h2>"
            . "<p>Bonjour <b>" . htmlspecialchars($user['pseudo']) . "</b>,</p>"
            . "<p>Votre mot de passe vient d'être modifié avec succès.</p>"
            . "<hr><small>Si vous n'êtes pas à l'origine de cette modification, contactez-nous immédiatement.</small>"
            . "</div>";
        $mailConfirm->send();
        // Connexion automatique
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['pseudo'];
        $_SESSION['mail'] = $user['mail'];
        $_SESSION['credit'] = $user['credit'];
        $_SESSION['category'] = $user['category'];
        echo "<p style='color:green;'>Mot de passe modifié avec succès. Redirection...</p>";
        $_SESSION['reset_step'] = 1;
        header('Refresh:2; url=compte.php');
        exit;
    }
}
?>
<div style="max-width:400px;margin:auto;">
    <h2>Réinitialiser votre mot de passe</h2>
    <?php if ($_SESSION['reset_step'] == 1): ?>
        <form method="post">
            <label for="reset_code">Code reçu par mail :</label><br>
            <input type="text" id="reset_code" name="reset_code" maxlength="6" pattern="\d{6}" required><br><br>
            <button type="submit">Vérifier</button>
        </form>
    <?php elseif ($_SESSION['reset_step'] == 2): ?>
        <form method="post">
            <label for="new_pass">Nouveau mot de passe :</label><br>
            <input type="password" id="new_pass" name="new_pass" required><br><br>
            <label for="confirm_pass">Confirmer le mot de passe :</label><br>
            <input type="password" id="confirm_pass" name="confirm_pass" required><br><br>
            <button type="submit">Modifier le mot de passe</button>
        </form>
    <?php endif; ?>
</div>
