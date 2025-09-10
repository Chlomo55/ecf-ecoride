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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .card-covoit { box-shadow: 0 2px 8px rgba(0,0,0,0.07); border-radius: 1rem; margin-bottom: 2rem; transition: box-shadow 0.2s; }
        .card-covoit:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.13); }
        .driver-photo { width: 90px; height: 90px; object-fit: cover; border-radius: 50%; border: 2px solid #0d6efd; }
        .badge-eco { background: #198754; }
        .list-group-item { background: #fff; }
        .back-link { margin-bottom: 1.5rem; display: inline-block; color: #2563eb; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container py-4">
        <a href="vue.php" class="back-link">&larr; Retour à la recherche</a>
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card card-covoit p-4">
                    <div class="d-flex align-items-center mb-3">
                        <img src="data:image/jpeg;base64,<?= base64_encode($covoit['photo']) ?>" alt="Photo conducteur" class="driver-photo me-3">
                        <div>
                            <h4 class="mb-0">Conducteur : <?= htmlspecialchars($covoit['pseudo']) ?></h4>
                            <div>
                                <span class="badge bg-warning text-dark">Note : <?= number_format($covoit['note'], 1) ?> ★</span>
                            </div>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item">
                            <strong>Départ :</strong> <?= htmlspecialchars($covoit['depart']) ?> <br>
                            <span class="text-muted"><?= date('d/m/Y', strtotime($covoit['heure_depart'])) ?> à <?= date('H:i', strtotime($covoit['heure_depart'])) ?></span>
                        </li>
                        <li class="list-group-item">
                            <strong>Arrivée :</strong> <?= htmlspecialchars($covoit['arrivee']) ?> <br>
                            <span class="text-muted"><?= date('d/m/Y', strtotime($covoit['heure_arrivee'])) ?> à <?= date('H:i', strtotime($covoit['heure_arrivee'])) ?></span>
                        </li>
                        <li class="list-group-item">
                            <?php
                                $duration = strtotime($covoit['heure_arrivee']) - strtotime($covoit['heure_depart']);
                                $hours = floor($duration / 3600);
                                $minutes = floor(($duration % 3600) / 60);
                            ?>
                            <strong>Durée :</strong> <?= $hours ?>h<?= $minutes > 0 ? $minutes : '00' ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Prix :</strong> <span class="text-success"><?= number_format($covoit['prix'], 2) ?> €</span>
                        </li>
                        <li class="list-group-item">
                            <strong>Places restantes :</strong> <?= $covoit['place'] ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Écologique :</strong>
                            <?php if (strtolower($covoit['energie']) == 'electrique'): ?>
                                <span class="badge badge-eco">Oui</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Non</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                    <div class="mb-3">
                        <h5>Véhicule</h5>
                        <p><strong>Marque :</strong> <?= htmlspecialchars($covoit['marque']) ?> <br>
                        <strong>Modèle :</strong> <?= htmlspecialchars($covoit['modele']) ?> <br>
                        <strong>Énergie :</strong> <?= htmlspecialchars($covoit['energie']) ?></p>
                    </div>
                    <div class="mb-3">
                        <h5>Préférences du conducteur</h5>
                        <p><?= nl2br(htmlspecialchars($covoit['preferences'])) ?></p>
                    </div>
                    <div class="mb-3">
                        <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                            <?php if ($_SESSION['category'] == 'passager'): ?>
                                <?php if ($_SESSION['credit'] >= $covoit['prix'] && $covoit['place'] > 0): ?>
                                    <form method="post" onsubmit="return confirm('Voulez-vous utiliser <?= $covoit['prix'] ?> crédits pour participer à ce trajet ?');">
                                        <input type="hidden" name="participer" value="1">
                                        <button type="submit" class="btn btn-outline-primary w-100">Participer</button>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-danger text-center">
                                        <?php if ($covoit['place'] <= 0) echo "Aucune place disponible."; ?>
                                        <?php if ($_SESSION['credit'] < $covoit['prix']) echo "Crédit insuffisant."; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-info text-center">Vous devez être un passager inscrit pour participer. <a href="inscription.php">Créer un compte</a></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-secondary text-center"><a href="connexion.php">Connectez-vous</a> pour participer à ce covoiturage.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
