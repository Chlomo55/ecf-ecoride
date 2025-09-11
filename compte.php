<?php
include_once('header.php'); // Inclut le fichier d'en-tête

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php'); // Redirige vers la page de connexion
    exit;
}
?>
<!-- Styles personnalisés -->
<style>
body {
    background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
    font-family: 'Segoe UI', Arial, sans-serif;
    margin: 0;
    padding: 0;
}
.compte-container {
    max-width: 500px;
    margin: 40px auto 0 auto;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(44, 62, 80, 0.15);
    padding: 36px 32px 32px 32px;
    text-align: center;
    position: relative;
}
.compte-container h1 {
    color: #009688;
    margin-bottom: 24px;
    font-size: 2.2rem;
    letter-spacing: 1px;
}
form {
    margin-bottom: 28px;
}
.input-green, select {
    padding: 10px 16px;
    border-radius: 8px;
    border: 1px solid #b2dfdb;
    background: #e0f2f1;
    color: #00695c;
    font-size: 1rem;
    margin-right: 10px;
    transition: border 0.2s;
}
.input-green:focus, select:focus {
    border: 1.5px solid #009688;
    outline: none;
}
button[type="submit"] {
    background: linear-gradient(90deg, #009688 60%, #26c6da 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 22px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(44, 62, 80, 0.08);
    transition: background 0.2s, transform 0.1s;
}
button[type="submit"]:hover {
    background: linear-gradient(90deg, #26c6da 60%, #009688 100%);
    transform: translateY(-2px) scale(1.03);
}
.compte-info {
    margin-bottom: 30px;
}
.compte-info p {
    color: #333;
    font-size: 1.08rem;
    margin: 10px 0;
}
.compte-info span {
    color: #009688;
    font-weight: 600;
}
.links {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 10px;
}
.links a {
    display: inline-block;
    background: #009688;
    color: #fff;
    text-decoration: none;
    padding: 10px 0;
    border-radius: 7px;
    font-weight: 500;
    font-size: 1.05rem;
    transition: background 0.2s, box-shadow 0.2s, color 0.2s;
    box-shadow: 0 2px 8px rgba(44, 62, 80, 0.07);
}
.links a:hover {
    background: #26c6da;
    color: #fff;
    box-shadow: 0 4px 16px rgba(44, 62, 80, 0.13);
}
.success-message {
    background: #e0f2f1;
    color: #009688;
    border: 1px solid #b2dfdb;
    border-radius: 7px;
    padding: 10px;
    margin-bottom: 18px;
    font-weight: 500;
}
.error-message {
    background: #ffebee;
    color: #c62828;
    border: 1px solid #ffcdd2;
    border-radius: 7px;
    padding: 10px;
    margin-bottom: 18px;
    font-weight: 500;
}
@media (max-width: 600px) {
    .compte-container {
        padding: 18px 8px 18px 8px;
        max-width: 98vw;
    }
    .links a {
        font-size: 1rem;
        padding: 9px 0;
    }
}
</style>
<!-- Inclusion des scripts -->
<script crossorigin="anonymous" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script crossorigin="anonymous" integrity="sha256-uto3j0v5x+6gk4m7c5q8f5z5f5f5f5f5f5f5f5f5=" src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<div class="compte-container">
    <h1>Bienvenue sur votre compte</h1>
    <form action="" method="post">
        <select class="input-green" id="category" name="category">
            <option value="chauffeur" <?php if(isset($_SESSION['category']) && $_SESSION['category']=='chauffeur') echo 'selected'; ?>>Chauffeur</option>
            <option value="passager" <?php if(isset($_SESSION['category']) && $_SESSION['category']=='passager') echo 'selected'; ?>>Passager</option>
            <option value="2" <?php if(isset($_SESSION['category']) && $_SESSION['category']=='2') echo 'selected'; ?>>Chauffeur et Passager</option>
        </select>
        <button name="update_category" type="submit">Mettre à jour</button>
    </form>
    <?php
    if (isset($_POST['update_category'])) {
        $newCategory = $_POST['category'];
        $userId = $_SESSION['user_id'];

        // Connexion à la base de données
        $conn = new mysqli('localhost', 'root', '', 'ecoride');

        // Vérification de la connexion
        if ($conn->connect_error) {
            echo '<div class="error-message">Échec de la connexion : ' . $conn->connect_error . '</div>';
        } else {
            // Mise à jour de la catégorie
            $stmt = $conn->prepare("UPDATE user SET category = ? WHERE id = ?");
            $stmt->bind_param("si", $newCategory, $userId);

            if ($stmt->execute()) {
                echo '<div class="success-message">Catégorie mise à jour avec succès.</div>';
                $_SESSION['category'] = $newCategory; // Met à jour la session
            } else {
                echo '<div class="error-message">Erreur lors de la mise à jour de la catégorie.</div>';
            }

            $stmt->close();
            $conn->close();
        }
    }
    ?>
    <div class="compte-info">
        <p>Vous êtes connecté en tant que <span><?php echo htmlspecialchars($_SESSION['username']); ?></span></p>
        <p>Votre catégorie est <span><?php echo htmlspecialchars($_SESSION['category']); ?></span></p>
        <p>Votre email est <span><?php echo htmlspecialchars($_SESSION['mail']); ?></span></p>
        <p>Actuellement il vous reste <span><?php echo htmlspecialchars($_SESSION['credit']); ?></span> crédit(s)</p>
    </div>
    <div class="links">
        <a href="deconnexion.php">Déconnexion</a>
        <a href="covoiturage.php">Proposer un covoiturage</a>
        <a href="vue.php">Vue</a>
        <a href="historique.php">Historique</a>
        <?php
        // Affiche le bouton Mes Trajets si l'utilisateur est chauffeur et a au moins un trajet
        if (in_array($_SESSION['category'], ['chauffeur','2'])) {
            $stmt = $pdo->prepare('SELECT * FROM covoiturage WHERE id_chauffeur = ?');
            $stmt->execute([$_SESSION['user_id']]);
            if ($stmt->rowCount() > 0) {
                echo '<button id="showTrajetsBtn" style="background:#009688;color:#fff;padding:10px 18px;border:none;border-radius:7px;cursor:pointer;">Mes Trajets</button>';
            }
        }
        ?>
    </div>
    <div id="mesTrajets" style="display:none;margin-top:30px;text-align:left;">
        <h2>Mes Trajets</h2>
        <?php
        if (in_array($_SESSION['category'], ['chauffeur','2'])) {
            $stmt = $pdo->prepare('SELECT * FROM covoiturage WHERE id_chauffeur = ? ORDER BY heure_depart DESC');
            $stmt->execute([$_SESSION['user_id']]);
            $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($trajets) {
                echo '<ul style="list-style:none;padding:0;">';
                foreach ($trajets as $t) {
                    $etatTxt = ($t['etat']==0) ? 'En attente' : (($t['etat']==2) ? 'Commencé' : (($t['etat']==3) ? 'Terminé' : 'Annulé'));
                    echo '<li style="margin-bottom:18px;padding:14px;background:#e0f2f1;border-radius:8px;">';
                    echo '<b>'.htmlspecialchars($t['depart']).' → '.htmlspecialchars($t['arrivee']).'</b> le '.date('d/m/Y H:i',strtotime($t['heure_depart']));
                    // Récupérer la voiture
                    $vstmt = $pdo->prepare('SELECT marque, modele, immatriculation FROM voiture WHERE id = ?');
                    $vstmt->execute([$t['voiture_id']]);
                    $v = $vstmt->fetch(PDO::FETCH_ASSOC);
                    if($v){
                        echo '<br><span style="color:#333;">Véhicule : <b>'.htmlspecialchars($v['marque'].' '.$v['modele'].' ('.$v['immatriculation'].')</b></span>');
                    }
                    echo '<br><span style="color:#333;">Heure d\'arrivée : <b>'.date('d/m/Y H:i',strtotime($t['heure_arrivee'])).'</b></span>';
                    echo '<br><span style="color:#333;">Places restantes : <b>'.$t['place'].'</b></span>';
                    echo '<br>État : <span style="color:#009688;font-weight:bold;">'.$etatTxt.'</span>';
                    if ($t['etat']==0) {
                        echo ' <button class="trajet-action-btn" data-id="'.$t['id'].'" data-action="commencer" style="margin-left:10px;background:#26c6da;color:#fff;border:none;padding:7px 14px;border-radius:6px;cursor:pointer;">Commencer</button>';
                    } elseif ($t['etat']==2) {
                        echo ' <button class="trajet-action-btn" data-id="'.$t['id'].'" data-action="finir" style="margin-left:10px;background:#c62828;color:#fff;border:none;padding:7px 14px;border-radius:6px;cursor:pointer;">Finir le trajet</button>';
                    }
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p>Aucun trajet trouvé.</p>';
            }
        }
        ?>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded',function(){
        var btn = document.getElementById('showTrajetsBtn');
        var bloc = document.getElementById('mesTrajets');
        if(btn && bloc){
            btn.addEventListener('click',function(){
                bloc.style.display = bloc.style.display==='none' ? 'block' : 'none';
            });
        }
        document.querySelectorAll('.trajet-action-btn').forEach(function(b){
            b.addEventListener('click',function(){
                var id = this.getAttribute('data-id');
                var action = this.getAttribute('data-action');
                this.disabled = true;
                this.textContent = '...';
                fetch('trajet_action.php',{
                    method:'POST',
                    headers:{'Content-Type':'application/x-www-form-urlencoded'},
                    body:'trajet_id='+id+'&action='+action
                })
                .then(r=>r.json())
                .then(data=>{location.reload();})
                .catch(()=>{alert('Erreur serveur');location.reload();});
            });
        });
    });
    </script>
</div>
<?php
if (isset($_SESSION['category']) && ($_SESSION['category'] == 'chauffeur' || $_SESSION['category'] == '2')) {
    include_once('voiture.php');
}
?>
