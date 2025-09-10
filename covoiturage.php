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
    $id_chauffeur = $_SESSION['user_id'];
    if ($categorie === 'chauffeur' || $categorie === '2') {
        $etat = 1;
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
    <form method="post" id="formCovoit" style="opacity:0;transform:scale(0.97);">
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
    <input type="text" id="depart" name="depart" autocomplete="off" required style="width:250px;font-size:1.1em;padding:6px;">
    <div id="suggestions-depart" class="suggestions-list" style="display:none;position:absolute;z-index:10;background:#fff;border:1px solid #ccc;width:250px;"></div>
    <br><br>
    <label for="arrivee">Lieu d'arrivée:</label><br>
    <input type="text" id="arrivee" name="arrivee" autocomplete="off" required style="width:250px;font-size:1.1em;padding:6px;">
    <div id="suggestions-arrivee" class="suggestions-list" style="display:none;position:absolute;z-index:10;background:#fff;border:1px solid #ccc;width:250px;"></div>
    <br><br>
    <label for="heure_depart">Date et heure de départ:</label><br>
    <input type="datetime-local" id="heure_depart" name="heure_depart" required style="width:200px;font-size:1.1em;padding:6px;text-align:center;">
    <br><br>
    <label for="heure_arrivee">Date et heure d'arrivée:</label><br>
    <input type="datetime-local" id="heure_arrivee" name="heure_arrivee" required style="width:200px;font-size:1.1em;padding:6px;text-align:center;">
    <br><br>
        <label for="prix">Prix par passager:</label><br>
        <input type="number" id="prix" name="prix" required><br><br>
        <input type="submit" value="Proposer">
    </form>
</div>
<style>
.suggestions-list {
    max-height: 180px;
    overflow-y: auto;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
}
.suggestion-item {
    padding: 8px 12px;
    cursor: pointer;
    font-size: 1.05em;
}
.suggestion-item:hover {
    background: #e9ecef;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function(){
    setTimeout(function(){
        var form = document.getElementById('formCovoit');
        if(form){
            form.style.transition = 'opacity 0.7s, transform 0.7s';
            form.style.opacity = 1;
            form.style.transform = 'scale(1)';
        }
    }, 200);
    // Animation feedback sur création
    var greenMsg = document.querySelector("p[style*='color: green']");
    if(greenMsg){
        greenMsg.style.opacity = 0;
        greenMsg.style.transition = 'opacity 0.7s';
        setTimeout(function(){greenMsg.style.opacity = 1;}, 100);
    }
    var redMsg = document.querySelector("p[style*='color: red']");
    if(redMsg){
        redMsg.style.opacity = 0;
        redMsg.style.transition = 'opacity 0.7s';
        setTimeout(function(){redMsg.style.opacity = 1;}, 100);
    }

    // Autocomplétion villes départ/arrivée
    function setupAutocomplete(inputId, suggestionsId) {
        var input = document.getElementById(inputId);
        var suggestions = document.getElementById(suggestionsId);
        var timer;
        input.addEventListener('input', function(){
            var val = input.value.trim();
            if(val.length >= 3){
                clearTimeout(timer);
                timer = setTimeout(function(){
                    fetch('https://geo.api.gouv.fr/communes?nom='+encodeURIComponent(val)+'&fields=nom&boost=population&limit=10')
                        .then(r=>r.json())
                        .then(data=>{
                            suggestions.innerHTML = '';
                            if(data.length > 0){
                                data.forEach(function(city){
                                    var div = document.createElement('div');
                                    div.className = 'suggestion-item';
                                    div.textContent = city.nom;
                                    div.onclick = function(){
                                        input.value = city.nom;
                                        suggestions.style.display = 'none';
                                    };
                                    suggestions.appendChild(div);
                                });
                                suggestions.style.display = 'block';
                            }else{
                                suggestions.style.display = 'none';
                            }
                        });
                }, 250);
            }else{
                suggestions.style.display = 'none';
            }
        });
        document.addEventListener('click', function(e){
            if(!suggestions.contains(e.target) && e.target !== input){
                suggestions.style.display = 'none';
            }
        });
    }
    setupAutocomplete('depart','suggestions-depart');
    setupAutocomplete('arrivee','suggestions-arrivee');
});
</script>