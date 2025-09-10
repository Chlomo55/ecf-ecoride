<?php
require 'vendor/autoload.php';

$mongoClient = new MongoDB\Client("mongodb://localhost:27017");
$db = $mongoClient->selectDatabase('admin'); // Remplace par le nom de ta base si besoin

// Création d'un compte admin (à faire une seule fois, mot de passe à changer ensuite)
$db->admins->insertOne([
    'email' => 'admin@example.com',
    'password' => password_hash('motdepasse123', PASSWORD_DEFAULT)
]);

// Exemple d'utilisateur
$db->users->insertOne([
    'name' => 'Jean Dupont',
    'email' => 'jean.dupont@example.com',
    'suspended' => false
]);

// Exemple d'employé
$db->employees->insertOne([
    'name' => 'Marie Martin',
    'email' => 'marie.martin@example.com',
    'suspended' => false
]);

// Exemple de covoiturage (rides)
$db->rides->insertOne([
    'user_id' => null, // à remplacer par l'_id d'un user
    'employee_id' => null, // à remplacer par l'_id d'un employé si besoin
    'date' => date('Y-m-d'),
    'from' => 'Paris',
    'to' => 'Lyon'
]);

// Exemple de crédits gagnés
$db->credits->insertOne([
    'user_id' => null, // à remplacer par l'_id d'un user
    'amount' => 10,
    'date' => date('Y-m-d')
]);

echo "Base initialisée avec collections et exemples.";