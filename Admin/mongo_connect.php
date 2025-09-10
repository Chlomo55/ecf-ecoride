<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require 'vendor/autoload.php'; // Assurez-vous que Composer et mongodb/mongodb sont installés

try {
    $mongoClient = new MongoDB\Client("mongodb://localhost:27017");
    $db = $mongoClient->selectDatabase('admin'); // Remplacez par le nom de votre base
} catch (Exception $e) {
    die('Erreur de connexion à MongoDB : ' . $e->getMessage());
}