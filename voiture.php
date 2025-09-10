<?php
require_once('header.php'); // Assurez-vous que la session et $pdo sont bien inclus

// Gestion de l'enregistrement du véhicule
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['immatriculation'])) {
    // Vérification des clés dans $_POST
    $immatriculation = $_POST['immatriculation'] ?? null;
    $premiere_immat = $_POST['1er'] ?? null;
    $marque = $_POST['marque'] ?? null;
    $modele = $_POST['modele'] ?? null;
    $couleur = $_POST['couleur'] ?? null;
    $energie = isset($_POST['energie']) && $_POST['energie'] === 'autre' ? ($_POST['autre_energie'] ?? null) : ($_POST['energie'] ?? null);
    $nb_place = $_POST['nb_place'] ?? null;
    $fumeur = $_POST['fumeur'] ?? null;
    $animaux = $_POST['animaux'] ?? null;

    // Vérification des champs obligatoires
    if (!$immatriculation || !$premiere_immat || !$marque || !$modele || !$couleur || !$energie || !$nb_place || !$fumeur || !$animaux) {
        $message = "<p style='color: red;'>Veuillez remplir tous les champs obligatoires.</p>";
    } else {
        // Récupère les préférences
        $preferences = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'pref_') === 0 && !empty($value)) {
                $preferences[] = $value;
            }
        }
        $preferences_user = implode(', ', $preferences);

        // Insertion dans la base de données
        try {
            $stmt = $pdo->prepare("INSERT INTO voiture (user_id, immatriculation, date, marque, modele, couleur, energie, nb_place, fumeur, animaux, preferences) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $immatriculation, $premiere_immat, $marque, $modele, $couleur, $energie, $nb_place, $fumeur, $animaux, $preferences_user]);
            $message = "<p style='color: green;'>Véhicule et préférences enregistrés avec succès !</p>";
        } catch (PDOException $e) {
            $message = "<p style='color: red;'>Erreur lors de l'enregistrement : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

// Affichage du message après soumission du formulaire d'enregistrement du véhicule
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['immatriculation']) && isset($message)) {
    echo $message;
}

// Récupération des véhicules de l'utilisateur
$voiture_user_id = $pdo->prepare("SELECT * FROM voiture WHERE user_id = ?");
$voiture_user_id->execute([$_SESSION['user_id']]);
$voitures = $voiture_user_id->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Affichage des véhicules -->
<?php if (count($voitures) > 0): ?>
    <button id="show-cars-div">Afficher mes vehicules enregistrés</button>
    <div class="card">
        <h3><?php echo count($voitures) === 1 ? "Votre véhicule enregistré" : "Vos véhicules enregistrés"; ?></h3>
        <?php foreach ($voitures as $row): ?>
            <p><strong>Immatriculation:</strong> <?php echo htmlspecialchars($row['immatriculation']); ?></p>
            <p><strong>Date de première immatriculation:</strong> <?php echo htmlspecialchars($row['date']); ?></p>
            <p><strong>Marque:</strong> <?php echo htmlspecialchars($row['marque']); ?></p>
            <p><strong>Modèle:</strong> <?php echo htmlspecialchars($row['modele']); ?></p>
            <p><strong>Couleur:</strong> <?php echo htmlspecialchars($row['couleur']); ?></p>
            <p><strong>Énergie:</strong> <?php echo htmlspecialchars($row['energie']); ?></p>
            <p><strong>Fumeur:</strong> <?php echo htmlspecialchars($row['fumeur']) === 'fumeur' ? "Oui" : "Non"; ?></p>
            <p><strong>Animaux:</strong> <?php echo htmlspecialchars($row['animaux']) === 'oui' ? "Oui" : "Non"; ?></p>
            <p><strong>Vos préférences:</strong>
                <?php if (empty($row['preferences'])): ?>
                    Aucune préférence enregistrée.
                <?php else: ?>
                    <ul>
                        <?php foreach (explode(', ', $row['preferences']) as $preference): ?>
                            <li><?php echo htmlspecialchars($preference); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </p>
            <p><strong>Nombre de places disponibles:</strong> <?php echo htmlspecialchars($row['nb_place']); ?></p>
            <hr>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="card">
        <h3>Aucun véhicule enregistré</h3>
        <p>Veuillez enregistrer un véhicule pour proposer un covoiturage.</p>
    </div>
<?php endif; ?>

<!-- Formulaire d'enregistrement de véhicule -->
 <button id="register-cars">Enregistrer un vehicule</button>
<div class="register-div">
    <form method="post">
        <h3>Votre véhicule</h3>
        <div>
            <label for="immatriculation">Votre plaque d'immatriculation</label>
            <input type="text" id="immatriculation" name="immatriculation" required>
        </div>
        <div>
            <label for="1er">Date de votre première immatriculation</label>
            <input type="date" id="1er" name="1er" required>
        </div>
        <div>
            <label for="marque">Marque de votre véhicule</label>
            <input type="text" id="marque" name="marque" required>
        </div>
        <div>
            <label for="modele">Modèle de votre véhicule</label>
            <input type="text" id="modele" name="modele" required>
        </div>
        <div>
            <label for="couleur">Couleur de votre véhicule</label>
            <input type="text" id="couleur" name="couleur" required>
        </div>
        <div>
            <label for="energie">Énergie</label>
            <select name="energie" id="energie">
                <option value="essence">Essence</option>
                <option value="diesel">Diesel</option>
                <option value="electrique">Électrique</option>
                <option value="hybride">Hybride</option>
                <option value="autre">Autre</option>
            </select>
        </div>
        <div id="autre-energie-div" style="display: none;">
            <label for="autre_energie">Veuillez préciser l'énergie</label>
            <input type="text" id="autre_energie" name="autre_energie">
        </div>
        <div>
            <label for="nb_place">Nombre de places disponibles</label>
            <input type="number" id="nb_place" name="nb_place" required>
        </div>
        <div>
            <p>Vos préférences</p>
            <div>
                <label for="fumeur">Votre trajet est :</label>
                <input type="radio" id="fumeur" name="fumeur" value="fumeur" required> Fumeur
                <input type="radio" id="non-fumeur" name="fumeur" value="non-fumeur"> Non-Fumeur
            </div>
            <div>
                <label for="animaux">Acceptez-vous les animaux ?</label>
                <input type="radio" id="oui" name="animaux" value="oui" required> Oui
                <input type="radio" id="non" name="animaux" value="non"> Non
            </div>
            <button class="ajout-preferences">Ajouter des préférences</button>
            <div class="vos-preferences">
                <div>
                    <label for="pref_1">Préférence 1</label>
                    <input type="text" id="pref_1" name="pref_1">
                </div>
            </div>
            <br>
            <button type="submit">Enregistrer</button>
        </div>
    </form>
</div>

<!-- Scripts JS -->
<script>
    document.getElementById('energie').addEventListener('change', function() {
        const autreEnergieDiv = document.getElementById('autre-energie-div');
        if (this.value === 'autre') {
            autreEnergieDiv.style.display = 'block';
        } else {
            autreEnergieDiv.style.display = 'none';
        }
    });
    // Afficher les vehicules
    $(document).ready(function() {
        $('.card').hide();
        $('#show-cars-div').click(function() {
            $('.card').show();
            $('#show-cars-div').hide();
        });
        $('.register-div').hide();
        $('#register-cars').click(function() {
            $('.register-div').show();
            $('#register-cars').hide();
        });
    });
</script>

