<?php
require_once 'mongo_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    if ($name && $email) {
        $db->employees->insertOne(['name' => $name, 'email' => $email]);
        header('Location: employees.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style-admin.css">
    <title>Ajouter un Employé</title>
</head>
<body>
    <h1>Ajouter un Employé</h1>
    <form method="post">
        <label>Nom : <input type="text" name="name" required></label><br>
        <label>Email : <input type="email" name="email" required></label><br>
        <button type="submit">Ajouter</button>
    </form>
    <a href="employees.php">Retour</a>
</body>
</html>