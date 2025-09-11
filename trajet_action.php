<?php
require_once('pdo.php');
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non connecté']);
    exit;
}
$user_id = $_SESSION['user_id'];
$trajet_id = isset($_POST['trajet_id']) ? intval($_POST['trajet_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
if ($trajet_id > 0 && in_array($action, ['commencer','finir'])) {
    if ($action === 'commencer') {
        $stmt = $pdo->prepare('UPDATE covoiturage SET etat = 2 WHERE id = ? AND id_chauffeur = ?');
        $stmt->execute([$trajet_id, $user_id]);
        echo json_encode(['success' => true, 'etat' => 2]);
    } else if ($action === 'finir') {
        $stmt = $pdo->prepare('UPDATE covoiturage SET etat = 3 WHERE id = ? AND id_chauffeur = ?');
        $stmt->execute([$trajet_id, $user_id]);
        // Récupérer les passagers
        $passagers = $pdo->prepare('SELECT u.mail, u.pseudo FROM passager p JOIN user u ON p.user_id = u.id WHERE p.covoiturage_id = ?');
        $passagers->execute([$trajet_id]);
        $mails = $passagers->fetchAll(PDO::FETCH_ASSOC);
        // Envoi du mail à chaque passager
        require_once 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once 'vendor/phpmailer/phpmailer/src/SMTP.php';
        require_once 'vendor/phpmailer/phpmailer/src/Exception.php';
        foreach ($mails as $p) {
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'chlomo.freoua@gmail.com';
            $mail->Password = 'mjkauepkaitjimeo';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom('chlomo.freoua@gmail.com', 'EcoRide');
            $mail->addAddress($p['mail']);
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->Subject = 'Donnez votre avis sur votre trajet';
            $mail->Body = "<div style='font-family:sans-serif;color:#222;'>"
                . "<h2 style='color:#2e8b57;'>Votre trajet EcoRide est terminé</h2>"
                . "<p>Bonjour <b>" . htmlspecialchars($p['pseudo']) . "</b>,</p>"
                . "<p>Merci d'avoir voyagé avec EcoRide. Donnez votre avis sur le trajet depuis votre espace personnel !</p>"
                . "<hr><small>Merci pour votre confiance.</small>"
                . "</div>";
            $mail->send();
        }
        echo json_encode(['success' => true, 'etat' => 3]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Paramètres invalides']);
}
