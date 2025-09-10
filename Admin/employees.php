<?php
require_once 'mongo_connect.php';

// Récupération des employés
$collection = $db->employees; // Assurez-vous que la collection s'appelle 'employees'
$employees = $collection->find();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Employés</title>
    <link rel="stylesheet" href="style-admin.css">
</head>
<body>
    <header>
        <h1>Gestion des Employés</h1>
        <nav>
            <a href="dashboard.php">Tableau de Bord</a>
            <a href="add_employee.php">Ajouter un Employé</a>
            <a href="users.php">Utilisateurs</a>
            <a href="settings.php">Paramètres</a>
        </nav>
    </header>
    <main>
        <h2>Liste des Employés</h2>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($employee['name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($employee['email'] ?? ''); ?></td>
                        <td>
                            <?php
                                if (!empty($employee['deleted'])) {
                                    echo '<span style="color:gray;">Supprimé</span>';
                                } elseif (!empty($employee['suspended'])) {
                                    echo '<span style="color:red;">Suspendu</span>';
                                } else {
                                    echo 'Actif';
                                }
                            ?>
                        </td>
                        <td>
                            <?php if (!empty($employee['deleted'])): ?>
                                <!-- Aucun bouton -->
                            <?php elseif (!empty($employee['suspended'])): ?>
                                <a href="reactivate_employee.php?id=<?php echo htmlspecialchars((string)$employee['_id']); ?>">Réactiver</a>
                                <a href="delete_employee.php?id=<?php echo htmlspecialchars((string)$employee['_id']); ?>">Supprimer</a>
                            <?php else: ?>
                                <a href="suspend_employee.php?id=<?php echo htmlspecialchars((string)$employee['_id']); ?>">Suspendre</a>
                                <a href="delete_employee.php?id=<?php echo htmlspecialchars((string)$employee['_id']); ?>">Supprimer</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>