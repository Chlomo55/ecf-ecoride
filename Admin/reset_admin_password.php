<?php
require 'vendor/autoload.php';

$mongoClient = new MongoDB\Client("mongodb://localhost:27017");
$db = $mongoClient->selectDatabase('admin');

$email = 'admin@example.com';
$newPassword = 'motdepasse123';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

$result = $db->admins->updateOne(
    ['email' => $email],
    ['$set' => ['password' => $hash]]
);

if ($result->getModifiedCount() > 0) {
    echo "Mot de passe admin mis à jour avec succès.";
} else {
    echo "Aucun compte admin mis à jour (vérifiez l'email).";
}
