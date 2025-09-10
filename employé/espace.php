<?php
session_start();
if (!isset($_SESSION['employee_logged_in']) || !$_SESSION['employee_logged_in']) {
    header('Location: ../connexion.php');
    exit;
}

$host = 'localhost';
$dbname = 'ecoride';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Traitement validation/refus d'avis
    if (isset($_POST['avis_id'], $_POST['action'])) {
        $avis_id = intval($_POST['avis_id']);
        if ($_POST['action'] === 'valider') {
            $stmt = $pdo->prepare("UPDATE avis SET valide = 1 WHERE id = ?");
            $stmt->execute([$avis_id]);
        } elseif ($_POST['action'] === 'refuser') {
            $stmt = $pdo->prepare("UPDATE avis SET valide = -1 WHERE id = ?");
            $stmt->execute([$avis_id]);
        }
    }

    // Récupérer les avis à valider
    $stmt = $pdo->query("SELECT a.id, a.commentaire, a.note, u.pseudo AS participant, c.pseudo AS chauffeur
                         FROM avis a
                         JOIN user u ON a.participant_id = u.id
                         JOIN user c ON a.chauffeur_id = c.id
                         WHERE a.valide = 0");
    $avis_a_valider = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les covoiturages problématiques
    $stmt = $pdo->query("SELECT c.id, u1.pseudo AS passager, u1.mail AS mail_passager, u2.pseudo AS chauffeur, u2.mail AS mail_chauffeur,
                                c.lieu_depart, c.lieu_arrivee, c.date_depart, c.date_arrivee, c.description
                         FROM covoiturage c
                         JOIN user u1 ON c.passager_id = u1.id
                         JOIN user u2 ON c.chauffeur_id = u2.id
                         WHERE c.probleme = 1");
    $covoiturages_pb = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Employé</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #eee; }
        form.inline { display: inline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Espace Employé</h1>

        <h2>Validation des avis sur les chauffeurs</h2>
        <?php if (empty($avis_a_valider)): ?>
            <p>Aucun avis à valider.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Participant</th>
                        <th>Chauffeur</th>
                        <th>Note</th>
                        <th>Commentaire</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($avis_a_valider as $avis): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($avis['participant']); ?></td>
                        <td><?php echo htmlspecialchars($avis['chauffeur']); ?></td>
                        <td><?php echo htmlspecialchars($avis['note']); ?></td>
                        <td><?php echo htmlspecialchars($avis['commentaire']); ?></td>
                        <td>
                            <form method="post" class="inline">
                                <input type="hidden" name="avis_id" value="<?php echo $avis['id']; ?>">
                                <button type="submit" name="action" value="valider">Valider</button>
                                <button type="submit" name="action" value="refuser" style="background:#f55;">Refuser</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>Covoiturages problématiques</h2>
        <?php if (empty($covoiturages_pb)): ?>
            <p>Aucun covoiturage signalé comme problématique.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Passager</th>
                        <th>Mail Passager</th>
                        <th>Chauffeur</th>
                        <th>Mail Chauffeur</th>
                        <th>Départ</th>
                        <th>Arrivée</th>
                        <th>Date Départ</th>
                        <th>Date Arrivée</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($covoiturages_pb as $covoit): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($covoit['id']); ?></td>
                        <td><?php echo htmlspecialchars($covoit['passager']); ?></td>
                        <td><?php echo htmlspecialchars($covoit['mail_passager']); ?></td>
                        <td><?php echo htmlspecialchars($covoit['chauffeur']); ?></td>
                        <td><?php echo htmlspecialchars($covoit['mail_chauffeur']); ?></td>
                        <td><?php echo htmlspecialchars($covoit['lieu_depart']); ?></td>
                        <td><?php echo htmlspecialchars($covoit['lieu_arrivee']); ?></td>
                        <td><?php echo htmlspecialchars($covoit['date_depart']); ?></td>
                        <td><?php echo htmlspecialchars($covoit['date_arrivee']); ?></td>
                        <td><?php echo htmlspecialchars($covoit['description']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>