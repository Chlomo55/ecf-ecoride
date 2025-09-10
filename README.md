# Déploiement de l'application EcoRide en local

## Prérequis

- **WAMP** ou **XAMPP** installé (Apache, PHP, MySQL)
- **MongoDB** installé et en service sur `localhost:27017`
- **Composer** installé (pour les dépendances PHP)
- Navigateur web récent

---

## 1. Cloner ou copier le projet

Placer le dossier `ECF` dans le répertoire `www` de WAMP (`c:\wamp64\www\ECF`).

---

## 2. Installer les dépendances PHP pour l’admin

Ouvrir un terminal dans `c:\wamp64\www\ECF\Admin` puis exécute :

```bash
composer install
```

Cela installera la bibliothèque MongoDB pour PHP.

---

## 3. Configurer la base de données MySQL (partie User)

1. Ouvrir **phpMyAdmin** ou un terminal MySQL.
2. Créer la base de données :

```sql
CREATE DATABASE ecoride CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Importe le script SQL fourni (si tu en as un) pour créer les tables nécessaires.

---

## 4. Configurer la base MongoDB (partie Admin)

- Par défaut, la connexion se fait sur `mongodb://localhost:27017` et la base s’appelle `your_database_name`.
- Modifie le nom de la base dans [`Admin/mongo_connect.php`](Admin/mongo_connect.php) selon ta configuration MongoDB.

---

## 5. Configurer les accès administrateur

- Les identifiants admin sont définis dans [`Admin/login.php`](Admin/login.php) (variables `$admin_email` et `$admin_password`).
- Modifie-les pour plus de sécurité.

---

## 6. Lancer les serveurs

- **WAMP/XAMPP** : Démarre Apache et MySQL.
- **MongoDB** : Démarre le service MongoDB.

---

## 7. Accéder à l’application

- **Partie Utilisateur** : [http://localhost/ECF/User/index.php](http://localhost/ECF/User/index.php)
- **Partie Admin** : [http://localhost/ECF/Admin/login.php](http://localhost/ECF/Admin/login.php)

---

## 8. Fonctionnalités principales

- **User** : Inscription, connexion, gestion du compte, covoiturages, historique, avis, etc.
- **Admin** : Connexion sécurisée, gestion des utilisateurs (MongoDB), statistiques, ajout/suppression/modification d’utilisateurs.

---

## 9. Problèmes fréquents

- **Erreur MongoDB** : Vérifie que le service MongoDB est bien lancé et que l’extension PHP MongoDB est installée.
- **Erreur MySQL** : Vérifie les identifiants dans `User/pdo.php` et que la base `ecoride` existe.
- **Composer non reconnu** : Installe Composer et ajoute-le à la variable d’environnement PATH.

---

## 10. Sécurité

- Change les identifiants admin par défaut.
- Pour un usage en production, sécurise les mots de passe et les accès à la base.

---

**Bon déploiement !**
