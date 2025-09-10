<?php 
include_once('header.php'); // Inclut le fichier d'en-tête

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voiture_id = $_POST['voiture'];
    $depart = $_POST['depart'];
    $arrivee = $_POST['arrivee'];
    $heure_depart = $_POST['heure_depart'];
    $heure_arrivee = $_POST['heure_arrivee'];
    $prix = $_POST['prix'];

    // Vérifie la catégorie de l'utilisateur
    $categorie = $_SESSION['category'];
    $etat = 0;
    $id_chauffeur = null;
    if ($categorie === 'chauffeur' || $categorie === '2') {
        $etat = 1;
        $id_chauffeur = $_SESSION['user_id'];
    }

    // Récupère le nombre de places de la voiture sélectionnée
    $stmt = $pdo->prepare("SELECT nb_place FROM voiture WHERE id = ? AND user_id = ?");
    $stmt->execute([$voiture_id, $_SESSION['user_id']]);
    $voiture = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($voiture) {
        $nb_place = $voiture['nb_place'];

        // Insère dans la table covoiturage avec le nombre de places récupéré, l'état et l'id_chauffeur
        $insert = $pdo->prepare("INSERT INTO covoiturage (voiture_id, depart, arrivee, heure_depart, heure_arrivee, prix, place, etat, id_chauffeur) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([$voiture_id, $depart, $arrivee, $heure_depart, $heure_arrivee, $prix, $nb_place, $etat, $id_chauffeur]);

        echo "<p style='color: green;'>Covoiturage créé avec succès.</p>";
    } else {
        echo "<p style='color: red;'>Erreur : véhicule introuvable ou non autorisé.</p>";
    }
}

?>
<div>
    <form method="post">
        <h1>Proposer un covoiturage</h1>

        <label for="voiture">Sélectionnez votre véhicule:</label><br>
        <?php 
        $select = $pdo->prepare("SELECT * FROM voiture WHERE user_id = ?");
        $select->execute([$_SESSION['user_id']]);   
        ?>
        <div>
            <select name="voiture" id="voiture">
                <?php while ($row = $select->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?php echo htmlspecialchars($row['id']); ?>">
                        <?php echo htmlspecialchars($row['marque'] . ' ' . $row['modele'] . ' - ' . $row['immatriculation'] . ' - '. $row['nb_place'].' places'); ?>
                    </option>
                <?php endwhile; ?>
        </select> 
        </div>
       

        <label for="depart">Lieu de départ:</label><br>
        <input type="text" id="depart" name="depart" required><br><br>
        
        <label for="arrivee">Lieu d'arrivée:</label><br>
        <input type="text" id="arrivee" name="arrivee" required><br><br>
        
        <label for="heure_depart">Date et heure de départ:</label><br>
        <input type="datetime-local" id="heure_depart" name="heure_depart" required><br><br>
        
        <label for="heure_arrivee">Date et heure d'arrivée:</label><br>
        <input type="datetime-local" id="heure_arrivee" name="heure_arrivee" required><br><br>
        
        <label for="prix">Prix par passager:</label><br>
        <input type="number" id="prix" name="prix" required><br><br>


        <input type="submit" value="Proposer">
    </form>
</div>