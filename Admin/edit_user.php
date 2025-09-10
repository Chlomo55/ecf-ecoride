<?php
$host = 'localhost';
$dbname = 'ecoride'; // Remplace par le nom de ta base
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pseudo = $_POST['pseudo'];
            $mail = $_POST['mail'];
            $credit = $_POST['credit'];
            $stmt = $pdo->prepare("UPDATE user SET pseudo = ?, mail = ?, credit = ? WHERE id = ?");
            $stmt->execute([$pseudo, $mail, $credit, $id]);
            header('Location: users.php');
            exit;
        } else {
            $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                die("Utilisateur non trouvé.");
            }
        }
    } else {
        die("ID manquant.");
    }
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Utilisateur</title>
</head>
<body>
    <h1>Modifier Utilisateur</h1>
    <form method="post">
        <label>Nom : <input type="text" name="pseudo" value="<?php echo htmlspecialchars($user['pseudo']); ?>" required></label><br>
        <label>Email : <input type="email" name="mail" value="<?php echo htmlspecialchars($user['mail']); ?>" required></label><br>
        <label>Crédits : <input type="number" name="credit" value="<?php echo (int)($user['credit']); ?>" required></label><br>
        <input type="hidden" name="id" value="<?php echo (int)($user['id']); ?>">
        <button type="submit">Enregistrer</button>
        <a href="users.php">Annuler</a>
    </form>
</body>
</html>