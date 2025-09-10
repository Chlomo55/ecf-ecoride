<?php 
require_once('header.php');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Si demande de mot de passe oublié
    if (isset($_POST['forgot']) && !empty($_POST['username'])) {
        session_start();
        $_SESSION['reset_step'] = 1;
        $username = $_POST['username'];
        $stmt = $pdo->prepare("SELECT * FROM user WHERE pseudo = ? OR mail = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user) {
            // Générer un code à 6 chiffres
            $code = str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);
            $expire = date('Y-m-d H:i:s', time() + 15 * 60); // 15 min
            $stmt = $pdo->prepare("UPDATE user SET reset_code = ?, reset_code_expire = ? WHERE id = ?");
            $stmt->execute([$code, $expire, $user['id']]);

            // Envoi du mail avec PHPMailer
            require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
            require 'vendor/phpmailer/phpmailer/src/SMTP.php';
            require 'vendor/phpmailer/phpmailer/src/Exception.php';
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'chlomo.freoua@gmail.com'; // à personnaliser
            $mail->Password = 'mjkauepkaitjimeo'; // à personnaliser
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom('chlomo.freoua@gmail.com', 'EcoRide');
            $mail->addAddress($user['mail']);
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe';
            $mail->Body = "<div style='font-family:sans-serif;color:#222;'>"
                . "<h2 style='color:#2e8b57;'>Réinitialisation de votre mot de passe EcoRide</h2>"
                . "<p>Bonjour <b>" . htmlspecialchars($user['pseudo']) . "</b>,</p>"
                . "<p>Voici votre code de réinitialisation&nbsp;: <span style='font-size:1.5em;font-weight:bold;color:#2e8b57;'>$code</span></p>"
                . "<p>Ce code est valable <b>15 minutes</b>.</p>"
                . "<hr><small>Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.</small>"
                . "</div>";
            if (!$mail->send()) {
                echo "<p style='color:red;'>Erreur lors de l'envoi du mail. Contactez l'administrateur.</p>";
                exit;
            }

            // Redirection vers la page de réinitialisation
            header('Location: reset_password.php?mail=' . urlencode($user['mail']));
            exit;
        } else {
            echo "<p style='color:red;'>Aucun utilisateur trouvé avec cet identifiant.</p>";
        }
    }
    // Connexion classique
    elseif (isset($_POST['username']) && isset($_POST['pass'])) {
        $username = $_POST['username'];
        $password = $_POST['pass'];
        
        $stmt = $pdo->prepare("SELECT * FROM user WHERE pseudo = ? OR mail = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['pass'])) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['pseudo']; 
            $_SESSION['mail'] = $user['mail'];
            $_SESSION['credit'] = $user['credit'];
            $_SESSION['category'] = $user['category'];
            header('Location: compte.php');
            exit;
        } else {
            echo "<p style='color: red;'>Identifiants incorrects.</p>";
        }
    }
}
?>
<div>
    <form method="post">
        <h1>Connexion</h1>
        <label for="username">Adresse mail ou pseudo:</label><br>
        <input type="text" id="username" name="username" required><br><br>
        <label for="pass">Mot de passe :</label><br>
        <input type="password" id="pass" name="pass" required><br><br>
        <input type="submit" value="Se connecter">
        <p>Pas encore inscrit ? <a href="inscription.php">Inscrivez-vous ici</a></p>
        <p><a href="#" id="showForgot">Mot de passe oublié ?</a></p>
    </form>
    <div id="forgotDiv" style="display:none; margin-top:20px;">
        <form method="post">
            <h2>Mot de passe oublié</h2>
            <label for="username_forgot">Adresse mail ou pseudo :</label><br>
            <input type="text" id="username_forgot" name="username" required><br><br>
            <button type="submit" name="forgot" value="1">Recevoir un mot de passe temporaire</button>
        </form>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    $('#showForgot').click(function(e){
        e.preventDefault();
        $('#forgotDiv').slideToggle();
    });
});
</script>