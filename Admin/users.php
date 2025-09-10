<?php
// Connexion à MySQL
$host = 'localhost';
$dbname = 'ecoride'; // Remplace par le nom de ta base
$user = 'root'; // Ou ton utilisateur MySQL
$pass = ''; // Mot de passe MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT id, pseudo, mail, credit FROM user");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <link rel="stylesheet" href="style-admin.css">
</head>
<body>
    <header>
        <h1>Gestion des Utilisateurs</h1>
        <nav>
            <a href="dashboard.php">Tableau de Bord</a>
            <a href="add_user.php">Ajouter un Utilisateur</a>
            <a href="settings.php">Paramètres</a>
        </nav>
    </header>

    <main>
        <h2>Liste des Utilisateurs</h2>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Credits</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        
                        <td><?php echo htmlspecialchars($user['pseudo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($user['mail'] ?? ''); ?></td>
                        <td><?php echo (int)($user['credit'] ); ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo htmlspecialchars($user['id']); ?>">Modifier</a>
                            <a href="delete_user.php?id=<?php echo htmlspecialchars($user['id']); ?>">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>