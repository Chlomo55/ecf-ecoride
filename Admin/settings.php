<?php
session_start();
$host = 'localhost';
$dbname = 'ecoride'; // Mets ici le nom de ta base
$user = 'root';
$pass = '';

// Exemple : on suppose que l'admin a l'id 1
$admin_id = 1;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les infos actuelles
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        die("Admin introuvable.");
    }

    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom = $_POST['pseudo'] ?? $admin['pseudo'];
        $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';
        $confirmer_mdp = $_POST['confirmer_mdp'] ?? '';

        // Mise à jour du mot de passe si renseigné et confirmé
        if (!empty($nouveau_mdp)) {
            if ($nouveau_mdp === $confirmer_mdp) {
                $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE user SET pseudo = ?, pass = ? WHERE id = ?");
                $stmt->execute([$nom, $hash, $admin_id]);
                $message = "Paramètres et mot de passe mis à jour.";
            } else {
                $message = "Les mots de passe ne correspondent pas.";
            }
        } else {
            $stmt = $pdo->prepare("UPDATE user SET pseudo = ? WHERE id = ?");
            $stmt->execute([$pseudo, $admin_id]);
            $message = "Paramètres mis à jour.";
        }
        // Rafraîchir les infos
        $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres administrateur</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 500px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; }
        label { display: block; margin-top: 15px; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 20px; padding: 10px 20px; }
        .message { margin-top: 15px; color: green; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Paramètres administrateur</h1>
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="post">
            <label>Nom :
                <input type="text" name="pseudo" value="<?php echo htmlspecialchars($admin['pseudo']); ?>" required>
            </label>
            
            <hr>
            <label>Nouveau mot de passe :
                <input type="password" name="nouveau_mdp" autocomplete="new-password">
            </label>
            <label>Confirmer le mot de passe :
                <input type="password" name="confirmer_mdp" autocomplete="new-password">
            </label>
            <button type="submit">Enregistrer</button>
        </form>
    </div>
</body>
</html>