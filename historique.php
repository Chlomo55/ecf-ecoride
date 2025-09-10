<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

include_once('header.php');

// Gestion de l'annulation d'une course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'annuler_course' && isset($_POST['passager_id'])) {
        $passager_id = intval($_POST['passager_id']);

        // Récupérer les infos du passager et du covoiturage
        $stmt = $pdo->prepare("SELECT p.user_id, p.covoiturage_id, c.prix FROM passager p JOIN covoiturage c ON p.covoiturage_id = c.id WHERE p.id = :passager_id");
        $stmt->execute(['passager_id' => $passager_id]);
        $info = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($info && $info['user_id'] == $_SESSION['user_id']) {
            // Supprimer la participation
            $stmt = $pdo->prepare("DELETE FROM passager WHERE id = :passager_id");
            $stmt->execute(['passager_id' => $passager_id]);

            // Recréditer le crédit du passager
            $stmt = $pdo->prepare("UPDATE user SET credit = credit + :prix WHERE id = :user_id");
            $stmt->execute(['prix' => $info['prix'], 'user_id' => $info['user_id']]);
            if (isset($_SESSION['credit'])) {
                $_SESSION['credit'] += $info['prix'];
            }

            // Réajouter une place au covoiturage
            $stmt = $pdo->prepare("UPDATE covoiturage SET place = place + 1 WHERE id = :covoiturage_id");
            $stmt->execute(['covoiturage_id' => $info['covoiturage_id']]);
        }

        header('Location: historique.php');
        exit;
    }

    // Gestion du changement d'état
    if ($_POST['action'] === 'changer_etat' && isset($_POST['covoiturage_id'], $_POST['nouvel_etat'])) {
        $covoiturage_id = intval($_POST['covoiturage_id']);
        $nouvel_etat = intval($_POST['nouvel_etat']);

        // Vérifier que l'utilisateur est bien le chauffeur du trajet
        $stmt_chauffeur = $pdo->prepare("SELECT id_chauffeur FROM covoiturage WHERE id = ?");
        $stmt_chauffeur->execute([$covoiturage_id]);
        $id_chauffeur = $stmt_chauffeur->fetchColumn();

        if ($id_chauffeur == $_SESSION['user_id']) {
            $stmt = $pdo->prepare("UPDATE covoiturage SET etat = :nouvel_etat WHERE id = :covoiturage_id");
            $stmt->execute(['nouvel_etat' => $nouvel_etat, 'covoiturage_id' => $covoiturage_id]);

            // Envoi de mail aux passagers si état = 0 (annulé) ou 3 (terminé)
            if (in_array($nouvel_etat, [0, 3])) {
                $stmt_passagers = $pdo->prepare("SELECT u.mail FROM passager p JOIN user u ON p.user_id = u.id WHERE p.covoiturage_id = ?");
                $stmt_passagers->execute([$covoiturage_id]);
                $emails = $stmt_passagers->fetchAll(PDO::FETCH_COLUMN);

                foreach ($emails as $email) {
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'ecoride.ecf.studi@gmail.com';
                        $mail->Password = 'jsdglhptfbkgmwzg';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        $mail->setFrom('ecoride.ecf.studi@gmail.com', 'EcoRide');
                        $mail->addAddress($email);

                        // Encodage UTF-8 pour éviter les symboles bizarres
                        $mail->CharSet = 'UTF-8';

                        // Style commun pour le mail
                        $mailStyle = "
                            <div style='
                                font-family: Arial, Helvetica, sans-serif;
                                background: #e8f5e9;
                                border-radius: 12px;
                                padding: 24px 18px;
                                color: #222;
                                max-width: 480px;
                                margin: 0 auto;
                                border: 1px solid #c8e6c9;
                                box-shadow: 0 2px 12px #c8e6c9;
                            '>
                                <div style='font-size: 1.25rem; font-weight: bold; color: #388e3c; margin-bottom: 12px;'>
                                    EcoRide - Notification
                                </div>
                                <div style='font-size: 1rem;'>
                                    %s
                                </div>
                                <div style='margin-top: 22px; font-size: 0.95rem; color: #388e3c;'>
                                    L'équipe EcoRide 🌱
                                </div>
                            </div>
                        ";

                        if ($nouvel_etat == 0) {
                            $mail->Subject = "Trajet annulé";
                            $body = "Bonjour,<br><br>
                                Le trajet auquel vous étiez inscrit a été <strong style='color:#d84315;'>annulé</strong> par le chauffeur.<br>
                                Merci pour votre compréhension.";
                            $mail->Body = sprintf($mailStyle, $body);
                        } elseif ($nouvel_etat == 3) {
                            $mail->Subject = "Trajet terminé";
                            $body = "Bonjour,<br><br>
                                Le trajet auquel vous étiez inscrit est <strong style='color:#43a047;'>terminé</strong>.<br>
                                Vous pouvez maintenant noter votre expérience sur la plateforme.<br>
                                Merci pour votre participation&nbsp;!";
                            $mail->Body = sprintf($mailStyle, $body);
                        }

                        $mail->isHTML(true);
                        $mail->send();
                    } catch (Exception $e) {
                        error_log("Erreur mail à $email : {$mail->ErrorInfo}");
                    }
                }
            }
        }
        header('Location: historique.php');
        exit;
    }
}

// Gestion des avis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_passager_id'])) {
    $passager_id = intval($_POST['review_passager_id']);
    $ok = $_POST['review_ok'] ?? '';

    // On récupère l'id du passager et du covoiturage concerné
    $stmt = $pdo->prepare("SELECT user_id, covoiturage_id FROM passager WHERE id = :id");
    $stmt->execute(['id' => $passager_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $id_passager = $row['user_id'];

        // Récupérer l'id du chauffeur pour ce covoiturage
        $stmt2 = $pdo->prepare("SELECT id_chauffeur FROM covoiturage WHERE id = :covoiturage_id");
        $stmt2->execute(['covoiturage_id' => $row['covoiturage_id']]);
        $id_chauffeur = $stmt2->fetchColumn();

        if ($ok === 'oui') {
            $note = floatval($_POST['review_note']);
            $avis = trim($_POST['review_avis']);
            if ($note >= 1 && $note <= 5 && $avis !== '') {
                // Insérer l'avis positif dans la table avis
                $stmt3 = $pdo->prepare("INSERT INTO avis (id_chauffeur, id_passager, note, avis, probleme) VALUES (:id_chauffeur, :id_passager, :note, :avis, NULL)");
                $stmt3->execute([
                    'id_chauffeur' => $id_chauffeur,
                    'id_passager' => $id_passager,
                    'note' => $note,
                    'avis' => $avis
                ]);

                // Mettre à jour la note du chauffeur
                $stmtUpdateNote = $pdo->prepare("
                    UPDATE user
                    SET note = (
                        SELECT ROUND(AVG(note),2)
                        FROM avis
                        WHERE id_chauffeur = :id_chauffeur AND note IS NOT NULL
                    )
                    WHERE id = :id_chauffeur
                ");
                $stmtUpdateNote->execute(['id_chauffeur' => $id_chauffeur]);

                // Mettre à jour note_donne à 1
                $stmt4 = $pdo->prepare("UPDATE passager SET note_donne = 1 WHERE id = :id");
                $stmt4->execute(['id' => $passager_id]);

                echo "<script>alert('Merci pour votre avis !');window.location='historique.php';</script>";
                exit;
            }
        } elseif ($ok === 'non') {
            $comment = trim($_POST['review_comment']);
            if ($comment !== '') {
                // Insérer le problème dans la table avis
                $stmt3 = $pdo->prepare("INSERT INTO avis (id_chauffeur, id_passager, note, avis, probleme) VALUES (:id_chauffeur, :id_passager, NULL, NULL, :probleme)");
                $stmt3->execute([
                    'id_chauffeur' => $id_chauffeur,
                    'id_passager' => $id_passager,
                    'probleme' => $comment
                ]);

                // Mettre à jour la note du chauffeur
                $stmtUpdateNote = $pdo->prepare("
                    UPDATE user
                    SET note = (
                        SELECT ROUND(AVG(note),2)
                        FROM avis
                        WHERE id_chauffeur = :id_chauffeur AND note IS NOT NULL
                    )
                    WHERE id = :id_chauffeur
                ");
                $stmtUpdateNote->execute(['id_chauffeur' => $id_chauffeur]);

                // Mettre à jour note_donne à 1
                $stmt4 = $pdo->prepare("UPDATE passager SET note_donne = 1 WHERE id = :id");
                $stmt4->execute(['id' => $passager_id]);

                echo "<script>alert('Merci pour votre retour, nous allons étudier votre problème.');window.location='historique.php';</script>";
                exit;
            }
        }
    }
}
?>

<!-- Style tableau responsive et écolo -->
<style>
.historique-container {
    max-width: 98vw;
    margin: 40px auto 0 auto;
    background: #f9fff9;
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(56, 142, 60, 0.13);
    padding: 32px 18px 24px 18px;
}
.historique-title {
    color: #388e3c;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 28px;
    text-align: center;
    letter-spacing: 1px;
}
.responsive-table {
    width: 100%;
    border-collapse: collapse;
    background: #e8f5e9;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 2px 12px #c8e6c9;
    margin-bottom: 18px;
}
.responsive-table th, .responsive-table td {
    padding: 14px 10px;
    text-align: center;
    border-bottom: 1.5px solid #c8e6c9;
}
.responsive-table th {
    background: linear-gradient(90deg, #43a047 60%, #81c784 100%);
    color: #fff;
    font-size: 1.08rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}
.responsive-table tr:nth-child(even) {
    background: #f9fff9;
}
.responsive-table tr:hover {
    background: #c8e6c9;
    transition: background 0.2s;
}
.btn, .btn-danger, .btn-primary, .btn-success {
    border: none;
    border-radius: 8px;
    padding: 7px 18px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    margin: 2px 0;
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    box-shadow: 0 1px 6px #c8e6c9;
}
.btn-danger {
    background: linear-gradient(90deg, #d84315 60%, #ff7043 100%);
    color: #fff;
}
.btn-danger:hover {
    background: linear-gradient(90deg, #b71c1c 60%, #ff7043 100%);
}
.btn-primary {
    background: linear-gradient(90deg, #388e3c 60%, #81c784 100%);
    color: #fff;
}
.btn-primary:hover {
    background: linear-gradient(90deg, #2e7d32 60%, #66bb6a 100%);
}
.btn-success {
    background: linear-gradient(90deg, #43a047 60%, #81c784 100%);
    color: #fff;
}
.btn-success:hover {
    background: linear-gradient(90deg, #388e3c 60%, #66bb6a 100%);
}
@media (max-width: 900px) {
    .responsive-table thead {
        display: none;
    }
    .responsive-table, .responsive-table tbody, .responsive-table tr, .responsive-table td {
        display: block;
        width: 100%;
    }
    .responsive-table tr {
        margin-bottom: 18px;
        border-radius: 12px;
        box-shadow: 0 1px 6px #c8e6c9;
        background: #e8f5e9;
        padding: 10px 0;
    }
    .responsive-table td {
        text-align: left;
        padding: 10px 16px;
        position: relative;
    }
    .responsive-table td:before {
        content: attr(data-label);
        font-weight: 700;
        color: #388e3c;
        display: block;
        margin-bottom: 4px;
        font-size: 0.98rem;
    }
}
</style>

<div class="historique-container">
    <div class="historique-title">Mon historique de covoiturages</div>
    <?php
    // Requête avec jointure entre passager et covoiturage
    $historique = $pdo->prepare("
        SELECT 
            passager.id AS passager_id,
            passager.date_participation,
            covoiturage.id AS covoiturage_id,
            covoiturage.depart,
            covoiturage.arrivee,
            covoiturage.heure_depart,
            covoiturage.heure_arrivee,
            covoiturage.prix,
            covoiturage.etat,
            covoiturage.id_chauffeur
        FROM covoiturage
        LEFT JOIN passager ON passager.covoiturage_id = covoiturage.id
        WHERE passager.user_id = :user_id OR covoiturage.id_chauffeur = :user_id
        ORDER BY passager.date_participation DESC
    ");
    $historique->execute(['user_id' => $_SESSION['user_id']]);

    if ($historique->rowCount() > 0) {
        echo "<div style='overflow-x:auto;'>";
        echo "<table class='responsive-table'>";
        echo "<thead>
                <tr>
                    <th>Date</th>
                    <th>Départ</th>
                    <th>Arrivée</th>
                    <th>Heure départ</th>
                    <th>Heure arrivée</th>
                    <th>Prix</th>
                    <th>État</th>
                    <th>Actions</th>
                </tr>
              </thead>";
        echo "<tbody>";
        while ($row = $historique->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td data-label='Date'>" . htmlspecialchars($row['date_participation']) . "</td>";
            echo "<td data-label='Départ'>" . htmlspecialchars($row['depart']) . "</td>";
            echo "<td data-label='Arrivée'>" . htmlspecialchars($row['arrivee']) . "</td>";
            echo "<td data-label='Heure départ'>" . htmlspecialchars($row['heure_depart']) . "</td>";
            echo "<td data-label='Heure arrivée'>" . htmlspecialchars($row['heure_arrivee']) . "</td>";
            echo "<td data-label='Prix'>" . htmlspecialchars($row['prix']) . " €</td>";
            switch ($row['etat']) {
                case 0:
                    $etat = "<span style='color:#d84315;font-weight:600;'>Annulée</span>";
                    break;
                case 1:
                    $etat = "<span style='color:#fbc02d;font-weight:600;'>En attente</span>";
                    break;
                case 2:
                    $etat = "<span style='color:#388e3c;font-weight:600;'>Actif</span>";
                    break;
                case 3:
                    $etat = "<span style='color:#43a047;font-weight:600;'>Terminé</span>";
                    break;
                default:
                    $etat = "<span style='color:#757575;font-weight:600;'>Inconnu</span>";
                    break;
            }
            echo "<td data-label='État'>$etat</td>";
            echo "<td data-label='Actions'>";
            if ($_SESSION['user_id'] !== $row['id_chauffeur']) {
                // Afficher le bouton Annuler uniquement si état = 1
                if ($row['etat'] == 1) {
                    echo "<form method='POST' style='display:inline;'>
                            <input type='hidden' name='action' value='annuler_course'>
                            <input type='hidden' name='passager_id' value='" . htmlspecialchars($row['passager_id']) . "'>
                            <button type='submit' class='btn btn-danger btn-sm'>Annuler</button>
                          </form>";
                }
                // Afficher le bouton avis si état = 3
                if ($row['etat'] == 3 && $row['passager_id']) {
                    // Récupérer note_donne pour ce passager
                    $stmtAvis = $pdo->prepare("SELECT note_donne FROM passager WHERE id = ?");
                    $stmtAvis->execute([$row['passager_id']]);
                    $note_donne = $stmtAvis->fetchColumn();
                    if ($note_donne != 1) {
                        echo "<button type='button' class='btn btn-success btn-sm leave-review-btn' data-passager='" . htmlspecialchars($row['passager_id']) . "'>Donner un avis</button>";
                    } else {
                        echo "<span style='color:#388e3c;font-weight:600;'>Avis donné, merci</span>";
                    }
                }
            } else {
                // Si l'utilisateur est le chauffeur
                if ($row['etat'] == 1) {
                    // Bouton Annuler
                    echo "<form method='POST' style='display:inline;margin-right:5px;'>
                            <input type='hidden' name='action' value='changer_etat'>
                            <input type='hidden' name='covoiturage_id' value='" . htmlspecialchars($row['covoiturage_id']) . "'>
                            <input type='hidden' name='nouvel_etat' value='0'>
                            <button type='submit' class='btn btn-danger btn-sm'>Annuler</button>
                          </form>";
                    // Bouton Commencer
                    echo "<form method='POST' style='display:inline;'>
                            <input type='hidden' name='action' value='changer_etat'>
                            <input type='hidden' name='covoiturage_id' value='" . htmlspecialchars($row['covoiturage_id']) . "'>
                            <input type='hidden' name='nouvel_etat' value='2'>
                            <button type='submit' class='btn btn-primary btn-sm'>Commencer</button>
                          </form>";
                } elseif ($row['etat'] == 2) {
                    echo "<form method='POST' style='display:inline;'>
                            <input type='hidden' name='action' value='changer_etat'>
                            <input type='hidden' name='covoiturage_id' value='" . htmlspecialchars($row['covoiturage_id']) . "'>
                            <input type='hidden' name='nouvel_etat' value='3'>
                            <button type='submit' class='btn btn-success btn-sm'>Terminer</button>
                          </form>";
                }
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    } else {
        echo "<p style='text-align:center;color:#388e3c;font-weight:600;'>Aucun historique trouvé.</p>";
    }
    ?>
</div>

<!-- Modal d'avis -->
<div id="review-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(44,62,80,0.25);z-index:9999;align-items:center;justify-content:center;">
    <form id="review-form" method="post" style="background:#fff;border-radius:18px;box-shadow:0 4px 24px #c8e6c9;padding:32px 28px;max-width:400px;width:90%;margin:auto;position:relative;">
        <h2 style="color:#388e3c;margin-bottom:18px;">Donner un avis</h2>
        <input type="hidden" name="review_passager_id" id="review_passager_id">
        <label style="font-weight:600;color:#388e3c;">Tout s'est-il bien passé ?</label>
        <div style="margin-bottom:18px;">
            <label><input type="radio" name="review_ok" value="oui" required> Oui</label>
            <label style="margin-left:18px;"><input type="radio" name="review_ok" value="non"> Non</label>
        </div>
        <div id="review-note-block" style="display:none;">
            <label for="review_note" style="font-weight:600;">Note sur 5 :</label>
            <select name="review_note" id="review_note" style="width:100%;padding:8px;border-radius:8px;border:1.5px solid #a5d6a7;margin-bottom:14px;">
                <option value="">Choisir une note</option>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Très bien</option>
                <option value="3">3 - Moyen</option>
                <option value="2">2 - Passable</option>
                <option value="1">1 - Mauvais</option>
            </select>
            <label for="review_avis" style="font-weight:600;">Votre avis :</label>
            <textarea name="review_avis" id="review_avis" rows="3" style="width:100%;padding:8px;border-radius:8px;border:1.5px solid #a5d6a7;"></textarea>
        </div>
        <div id="review-comment-block" style="display:none;">
            <label for="review_comment" style="font-weight:600;">Expliquez ce qui s'est mal passé :</label>
            <textarea name="review_comment" id="review_comment" rows="3" style="width:100%;padding:8px;border-radius:8px;border:1.5px solid #a5d6a7;"></textarea>
        </div>
        <div style="margin-top:18px;text-align:right;">
            <button type="button" id="review-cancel" class="btn btn-danger" style="margin-right:8px;">Annuler</button>
            <button type="submit" class="btn btn-success">Envoyer</button>
        </div>
    </form>
</div>
<script>
document.querySelectorAll('.leave-review-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('review_passager_id').value = this.dataset.passager;
        document.getElementById('review-modal').style.display = 'flex';
    });
});
document.getElementById('review-cancel').onclick = function() {
    document.getElementById('review-modal').style.display = 'none';
};
document.querySelectorAll('input[name="review_ok"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'oui') {
            document.getElementById('review-note-block').style.display = '';
            document.getElementById('review_note').required = true;
            document.getElementById('review_avis').required = true;
            document.getElementById('review-note-block').querySelector('select').disabled = false;
            document.getElementById('review_avis').disabled = false;

            document.getElementById('review-comment-block').style.display = 'none';
            document.getElementById('review_comment').required = false;
            document.getElementById('review_comment').disabled = true;
        } else {
            document.getElementById('review-note-block').style.display = 'none';
            document.getElementById('review_note').required = false;
            document.getElementById('review_avis').required = false;
            document.getElementById('review-note-block').querySelector('select').disabled = true;
            document.getElementById('review_avis').disabled = true;

            document.getElementById('review-comment-block').style.display = '';
            document.getElementById('review_comment').required = true;
            document.getElementById('review_comment').disabled = false;
        }
    });
});
</script>
<?php
?>

