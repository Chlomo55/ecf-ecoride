<?php
session_start();

$error = '';
$info = '';

require 'vendor/autoload.php';
try {
    $mongoClient = new MongoDB\Client("mongodb://localhost:27017");
    $db = $mongoClient->selectDatabase('admin');

    // Demande de mot de passe temporaire
    if (isset($_POST['reset']) && !empty($_POST['email'])) {
        $email = $_POST['email'];
        $admin = $db->admins->findOne(['email' => $email]);
        if ($admin) {
            // Générer un mot de passe temporaire
            $temp_pass = bin2hex(random_bytes(4));
            $hash = password_hash($temp_pass, PASSWORD_DEFAULT);
            $db->admins->updateOne(['_id' => $admin['_id']], ['$set' => ['password' => $hash]]);

            // Envoyer le mail
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ecoride.ecf.studi@gmail.com';
            $mail->Password = 'jsdglhptfbkgmwzg';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('ton.email@gmail.com', 'Ecoride');
            $mail->addAddress($email);
            $mail->Subject = "Votre mot de passe temporaire";
            $mail->Body = "Bonjour,\n\nVotre nouveau mot de passe temporaire est : $temp_pass\nConnectez-vous puis changez-le dans les paramètres.";

            if ($mail->send()) {
                $info = "Un mot de passe temporaire a été envoyé à votre adresse email.";
            } else {
                $error = "Erreur lors de l'envoi de l'email : " . $mail->ErrorInfo;
            }
        } else {
            $error = "Aucun compte admin avec cet email.";
        }
    }

    // Connexion classique
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['reset'])) {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $admin = $db->admins->findOne(['email' => $email]);
        // DEBUG
        echo '<pre style="color:blue">';
        var_dump($admin);
        if ($admin) {
            $verif = password_verify($password, $admin['password']);
            echo 'password_verify: ' . ($verif ? 'true' : 'false') . "\n";
        }
        echo '</pre>';
        // FIN DEBUG
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = (string)$admin['_id'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Identifiants incorrects.';
        }
    }
} catch (Exception $e) {
    $error = "Erreur de connexion à la base de données.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style-admin.css">
    <title>Connexion Admin</title>
</head>
<body>
    <h1>Connexion Administrateur</h1>
    <?php if ($error): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if ($info): ?>
        <p style="color:green;"><?php echo htmlspecialchars($info); ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Email : <input type="email" name="email" required></label><br>
        <label>Mot de passe : <input type="password" name="password" required></label><br>
        <button type="submit">Se connecter</button>
    </form>
    <hr>
    <form method="post">
        <label>Vous avez oublié votre mot de passe ?<br>
            Entrez votre email pour recevoir un mot de passe temporaire :</label><br>
        <input type="email" name="email" required>
        <button type="submit" name="reset" value="1">Recevoir un mot de passe temporaire</button>
    </form>
</body>
</html>