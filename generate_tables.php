<?php
header('Content-Type: application/json');
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ecoride', 'root', '');
    $sql = "
CREATE TABLE IF NOT EXISTS admin (
  id INT NOT NULL AUTO_INCREMENT,
  email TEXT NOT NULL,
  nom VARCHAR(50) NOT NULL,
  password VARCHAR(100) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS avis (
  id INT NOT NULL AUTO_INCREMENT,
  id_chauffeur INT NOT NULL,
  id_passager INT NOT NULL,
  note FLOAT DEFAULT NULL,
  avis TEXT,
  probleme TEXT,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS covoiturage (
  id INT NOT NULL AUTO_INCREMENT,
  id_chauffeur INT NOT NULL,
  voiture_id INT NOT NULL,
  depart TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  arrivee TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  heure_depart DATETIME NOT NULL,
  heure_arrivee DATETIME NOT NULL,
  prix FLOAT NOT NULL,
  place INT NOT NULL,
  etat INT NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS passager (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT DEFAULT NULL,
  covoiturage_id INT DEFAULT NULL,
  date_participation TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  note_donne TINYINT(1) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY user_id (user_id, covoiturage_id),
  KEY covoiturage_id (covoiturage_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS user (
  id INT NOT NULL AUTO_INCREMENT,
  pseudo VARCHAR(55) NOT NULL,
  mail VARCHAR(100) NOT NULL,
  pass VARCHAR(60) NOT NULL,
  photo LONGBLOB NOT NULL,
  credit INT NOT NULL,
  category VARCHAR(30) NOT NULL,
  note FLOAT NOT NULL,
  role VARCHAR(30) NOT NULL,
  reset_code VARCHAR(6) DEFAULT NULL,
  reset_code_expire DATETIME DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS voiture (
  id INT NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  immatriculation VARCHAR(10) NOT NULL,
  date DATE NOT NULL,
  marque VARCHAR(30) NOT NULL,
  modele VARCHAR(30) NOT NULL,
  couleur VARCHAR(30) NOT NULL,
  energie VARCHAR(30) NOT NULL,
  nb_place INT NOT NULL,
  fumeur TINYINT(1) NOT NULL,
  animaux TINYINT(1) NOT NULL,
  preferences TEXT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY immatriculation (immatriculation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
";
    $pdo->exec($sql);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
