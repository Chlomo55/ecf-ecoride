<?php
session_start();
$host = 'localhost';
$dbname = 'ecoride';
$user = 'root';
$pass = '';

$error = '';
$info = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Demande de mot de passe temporaire
    if (isset($_POST['reset']) && !empty($_POST['email'])) {
        $email = $_POST['email'];
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            // Générer un mot de passe temporaire
            $temp_pass = bin2hex(random_bytes(4));
            $hash = password_hash($temp_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $admin['id']]);
            
            // Envoyer le mail
            require 'vendor/autoload.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ecoride.ecf.studi@gmail.com'; // Ton adresse Gmail
            $mail->Password = 'jsdglhptfbkgmwzg'; // Mot de passe d'application Gmail
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
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Identifiants incorrects.';
        }
    }
} catch (PDOException $e) {
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