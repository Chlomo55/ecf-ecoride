<?php 
require_once('header.php');
?>
<style>
    form > div {
        margin-bottom: 20px;
    }
</style>
<div class="inscription-container" style="text-align: center;">
    <h1 class="inscription-title">Créer un compte</h1>
    <form class="inscription-form" action="inscription.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="pseudo">Pseudo</label>
            <input type="text" id="pseudo" name="pseudo" placeholder="Votre pseudo" required>
        </div>
        <div class="photo-upload-group">
            <label for="photo" class="photo-upload-label">
            <span id="photoLabelText">Importer une photo</span></label>
            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.gif" required onchange="previewPhoto(event)">
            <div class="photo-preview" id="photoPreview">
                <img id="photoPreviewImg" src="https://ui-avatars.com/api/?name=?" alt="Prévisualisation" style="display:none; width: 100px; height: 100px; border-radius: 50%;">
            </div>
        </div>
        
        <div class="form-group">
            <label for="mail">Adresse mail</label>
            <input type="email" id="mail" name="mail" placeholder="exemple@email.com" required>
        </div>
        <div class="form-group">
            <label for="pass">Mot de passe</label>
            <input type="password" id="pass" name="pass" placeholder="Votre mot de passe" required>
        </div>
        <input type="submit" value="S'inscrire" class="btn">
    </form>

    <p class="inscription-link">Déjà inscrit ? <a href="connexion.php">Connectez-vous ici</a></p>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $pseudo = htmlspecialchars($_POST['pseudo']);
        $mail = filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL);
        $pass = password_hash($_POST['pass'], PASSWORD_BCRYPT);

        if (!$mail) {
            echo "<div class='error-message'>Adresse mail invalide.</div>";
            exit;
        }

        // Traitement de l'image
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $photoTmpPath = $_FILES['photo']['tmp_name'];
            $photoName = $_FILES['photo']['name'];
            $photoExtension = strtolower(pathinfo($photoName, PATHINFO_EXTENSION));

            if (in_array($photoExtension, $allowedExtensions)) {
                $photoData = file_get_contents($photoTmpPath);
            } else {
                echo "<div class='error-message'>Extension non autorisée. Formats acceptés : jpg, jpeg, png, gif.</div>";
                exit;
            }
        } else {
            echo "<div class='error-message'>Erreur lors du téléchargement de la photo.</div>";
            exit;
        }

        // Insertion dans la base de données
        $stmt = $pdo->prepare("INSERT INTO user (pseudo, mail, pass, photo, credit, role) VALUES (?, ?, ?, ?, ?, ?)");
        $credit = 25;
        $role = 'user';

        try {
            $stmt->execute([$pseudo, $mail, $pass, $photoData, $credit, $role]);
            echo "<div class='success-message'>Inscription réussie !</div>";
        } catch (PDOException $e) {
            echo "<div class='error-message'>Erreur lors de l'inscription : " . $e->getMessage() . "</div>";
        }
    }
    ?>
</div>

<script>
function previewPhoto(event) {
    const preview = document.getElementById('photoPreview');
    const previewImg = document.getElementById('photoPreviewImg');
    preview.style.display = 'block';
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        previewImg.src = '';
        previewImg.style.display = 'none';
    }
}
</script>
