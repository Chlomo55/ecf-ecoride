<?php
require_once 'mongo_connect.php';

if (!isset($_GET['id'])) {
    header('Location: employees.php');
    exit;
}

$id = $_GET['id'];
$collection = $db->employees;

// Récupérer l'employé pour afficher son nom
$employee = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
if (!$employee) {
    die("Employé non trouvé.");
}

// Si le formulaire de confirmation est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'oui') {
    $collection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
    header('Location: employees.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suppression Employé</title>
</head>
<body>
    <h1>Suppression définitive</h1>
    <p>Voulez-vous vraiment supprimer définitivement l'employé : <strong><?php echo htmlspecialchars($employee['name'] ?? ''); ?></strong> ?</p>
    <form method="post">
        <button type="submit" name="confirm" value="oui">Oui, supprimer</button>
        <a href="employees.php">Annuler</a>
    </form>
</body>
</html>