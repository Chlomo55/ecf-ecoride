<?php
$host = 'localhost';
$dbname = 'ecoride'; // Remplace par le nom de ta base
$user = 'root';
$pass = '';

if (isset($_GET['id'])) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->prepare("DELETE FROM user WHERE id = ?");
        $stmt->execute([intval($_GET['id'])]);
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}
header('Location: users.php');
exit;