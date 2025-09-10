<?php
require_once('header.php');

if (!isset($_GET['id'])) {
    echo "Covoiturage introuvable.";
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=ecoride", "root", "");

$id = $_GET['id'];

$query = "
    SELECT c.*, u.pseudo, u.photo, u.note, u.id as user_id, v.marque, v.modele, v.energie, v.preferences
    FROM covoiturage c
    JOIN voiture v ON c.voiture_id = v.id
    JOIN user u ON v.user_id = u.id
    WHERE c.id = :id
";

$stmt = $pdo->prepare($query);
$stmt->execute([':id' => $id]);
$covoit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$covoit) {
    echo "Covoiturage non trouvé.";
    exit;
}

// Traitement de la participation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['participer'])) {
    if (!isset($_SESSION['user_logged_in']) || $_SESSION['category'] !== 'passager') {
        header("Location: connexion.php");
        exit;
    }

    if ($_SESSION['credit'] < $covoit['prix']) {
        echo "<p style='color:red;'>Crédit insuffisant.</p>";
    } elseif ($covoit['place'] <= 0) {
        echo "<p style='color:red;'>Plus de place disponible.</p>";
    } else {
        // Vérifier si l'utilisateur est déjà inscrit à ce covoiturage
        $check = $pdo->prepare("SELECT COUNT(*) FROM passager WHERE user_id = ? AND covoiturage_id = ?");
        $check->execute([$_SESSION['user_id'], $covoit['id']]);
        if ($check->fetchColumn() > 0) {
            echo "<p style='color:red;'>Vous êtes déjà inscrit à ce trajet.</p>";
        } else {
            $insert = $pdo->prepare("INSERT INTO passager (user_id, covoiturage_id) VALUES (?, ?)");
            $insert->execute([$_SESSION['user_id'], $covoit['id']]);

            $updateCredit = $pdo->prepare("UPDATE user SET credit = credit - ? WHERE id = ?");
            $updateCredit->execute([$covoit['prix'], $_SESSION['user_id']]);
            $_SESSION['credit'] -= $covoit['prix'];

            $updatePlace = $pdo->prepare("UPDATE covoiturage SET place = place - 1 WHERE id = ?");
            $updatePlace->execute([$covoit['id']]);

            echo "<p style='color:green;'>Participation confirmée !</p>";
            header("Refresh:1");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail du Covoiturage</title>
</head>
<body>
    <h1>Détail du trajet</h1>
    <div style="border:1px solid #ccc; padding:15px; margin-bottom:20px;">
        <h2>Conducteur : <?= htmlspecialchars($covoit['pseudo']) ?> (Note : <?= $covoit['note'] ?>/5)</h2>
        <img src="data:image/jpeg;base64,<?= base64_encode($covoit['photo']) ?>" width="100"><br><br>
        <p><strong>Note :</strong> <?= $covoit['note']. '/5' ?></p>

        <h3>Itinéraire</h3>
        <p><strong>Départ :</strong> <?= $covoit['depart'] ?> - <?= date('d/m/Y H:i', strtotime($covoit['heure_depart'])) ?></p>
        <p><strong>Arrivée :</strong> <?= $covoit['arrivee'] ?> - <?= date('d/m/Y H:i', strtotime($covoit['heure_arrivee'])) ?></p>
        <?php
        $duration = strtotime($covoit['heure_arrivee']) - strtotime($covoit['heure_depart']);
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        ?>
        <p><strong>Durée :</strong> <?= $hours ?>h<?= $minutes > 0 ? $minutes . 'min' : '' ?></p>
        <p><strong>Prix :</strong> <?= $covoit['prix'] ?> €</p>
        <p><strong>Places restantes :</strong> <?= $covoit['place'] ?></p>
        <p><strong>Voyage écologique :</strong> <?= strtolower($covoit['energie']) == 'electrique' ? 'Oui' : 'Non' ?></p>

        <h3>Véhicule</h3>
        <p><strong>Marque :</strong> <?= $covoit['marque'] ?></p>
        <p><strong>Modèle :</strong> <?= $covoit['modele'] ?></p>
        <p><strong>Énergie :</strong> <?= $covoit['energie'] ?></p>

        <h3>Préférences du conducteur</h3>
        <p><?= nl2br(htmlspecialchars($covoit['preferences'])) ?></p>

        <br>
        <a href="vue.php">← Retour à la recherche</a>

        <hr>
        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
            <?php if ($_SESSION['category'] == 'passager'): ?>
                <?php if ($_SESSION['credit'] >= $covoit['prix'] && $covoit['place'] > 0): ?>
                    <form method="post" onsubmit="return confirm('Voulez-vous utiliser <?= $covoit['prix'] ?> crédits pour participer à ce trajet ?');">
                        <input type="hidden" name="participer" value="1">
                        <button type="submit">Participer</button>
                    </form>
                <?php else: ?>
                    <p style="color:red;">
                        <?php if ($covoit['place'] <= 0) echo "Aucune place disponible."; ?>
                        <?php if ($_SESSION['credit'] < $covoit['prix']) echo "Crédit insuffisant."; ?>
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <p>Vous devez être un passager inscrit pour participer. <a href="inscription.php">Créer un compte</a></p>
            <?php endif; ?>
        <?php else: ?>
            <p><a href="connexion.php">Connectez-vous</a> pour participer à ce covoiturage.</p>
        <?php endif; ?>
    </div>
</body>
</html>
