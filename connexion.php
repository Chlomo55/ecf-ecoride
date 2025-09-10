<?php 
require_once('header.php');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Si demande de mot de passe oublié
    if (isset($_POST['forgot']) && !empty($_POST['username'])) {
        $username = $_POST['username'];
        $stmt = $pdo->prepare("SELECT * FROM user WHERE pseudo = ? OR mail = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user) {
            $temp_pass = bin2hex(random_bytes(4));
            $hash = password_hash($temp_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE user SET pass = ? WHERE id = ?");
            $stmt->execute([$hash, $user['id']]);

            // Pour le développement local, on écrit le mot de passe dans un fichier
            file_put_contents('mail_debug.txt', "Mail à : {$user['mail']}\nMot de passe temporaire : $temp_pass\n", FILE_APPEND);
            echo "<p style='color:green;'>Un mot de passe temporaire a été généré. Consultez votre mail ou demandez à l'administrateur.</p>";
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
    </form>
    <form method="post" style="margin-top:20px;">
        <h2>Mot de passe oublié ?</h2>
        <label for="username_forgot">Adresse mail ou pseudo :</label><br>
        <input type="text" id="username_forgot" name="username" required><br><br>
        <button type="submit" name="forgot" value="1">Recevoir un mot de passe temporaire</button>
    </form>
</div>