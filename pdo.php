<?php 
$pdo = new PDO('mysql:host=localhost;dbname=ecoride', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

if (!$pdo) {
    echo "<div style='color: red; font-weight: bold; font-family: Arial, sans-serif;'>
            Erreur : Impossible de se connecter à la base de données.
          </div>";
} 
?>