<?php
require_once('header.php');

// Récupération des données du formulaire
$depart = $_GET['depart'] ?? '';
$arrivee = $_GET['arrivee'] ?? '';
$date = $_GET['date'] ?? '';
$ecolo = $_GET['ecolo'] ?? '';
$prix_max = $_GET['prix_max'] ?? '';
$duree_max_heures = $_GET['duree_max_heures'] ?? '';
$duree_max_minutes = $_GET['duree_max_minutes'] ?? '';
$note_min = $_GET['note_min'] ?? '';

// Calcul de la durée maximale en minutes
$duree_max = 0;
if ($duree_max_heures !== '' || $duree_max_minutes !== '') {
    $duree_max = ($duree_max_heures * 60) + $duree_max_minutes;
}

// Construction de la requête
$query = "
    SELECT c.*, u.pseudo, u.photo, u.note, v.energie, v.marque, v.modele, v.preferences, u.id as user_id
    FROM covoiturage c
    JOIN voiture v ON c.voiture_id = v.id
    JOIN user u ON v.user_id = u.id
    WHERE c.place > 0
";

$params = [];

if ($depart) {
    $query .= " AND c.depart = :depart";
    $params[':depart'] = $depart;
}
if ($arrivee) {
    $query .= " AND c.arrivee = :arrivee";
    $params[':arrivee'] = $arrivee;
}
if ($date) {
    $query .= " AND DATE(c.heure_depart) = :date";
    $params[':date'] = $date;
}
if ($ecolo === '1') {
    $query .= " AND v.energie = 'electrique'";
}
if ($prix_max !== '') {
    $query .= " AND c.prix <= :prix_max";
    $params[':prix_max'] = $prix_max;
}
if ($duree_max > 0) {
    $query .= " AND TIMESTAMPDIFF(MINUTE, c.heure_depart, c.heure_arrivee) <= :duree_max";
    $params[':duree_max'] = $duree_max;
}
if ($note_min !== '') {
    $query .= " AND u.note >= :note_min";
    $params[':note_min'] = $note_min;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche de Covoiturage</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .card-covoit {
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            border-radius: 1rem;
            margin-bottom: 2rem;
            transition: box-shadow 0.2s;
        }
        .card-covoit:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.13);
        }
        .driver-photo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #0d6efd;
        }
        .badge-eco {
            background: #198754;
        }
        @media (max-width: 600px) {
            .card-covoit {
                padding: 1rem !important;
            }
            .driver-photo {
                width: 60px;
                height: 60px;
            }
        }
        .search-form {
            background: #fff;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .filters-bar {
            flex: 2 1 100%;
            background: #f4f7fb;
            border-radius: 0.8rem;
            padding: 1rem 1.2rem;
            margin: 0.5rem 0 1.5rem 0;
            box-shadow: 0 1px 6px rgba(37,99,235,0.06);
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
            position: relative;
        }
        .filters-toggle {
            display: none;
            background: none;
            border: none;
            color: #2563eb;
            font-weight: 600;
            font-size: 1.1rem;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 0;
            margin-bottom: 0.7rem;
        }
        .filters-toggle svg {
            transition: transform 0.2s;
        }
        .filters-toggle[aria-expanded="true"] svg {
            transform: rotate(180deg);
        }
        .filters-content {
            display: flex;
            gap: 1.2rem;
            flex-wrap: wrap;
            align-items: flex-end;
            transition: max-height 0.3s;
        }
        .filter-item {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            min-width: 120px;
        }
        .filter-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            cursor: pointer;
        }
        @media (max-width: 900px) {
            .filters-content {
                flex-direction: column;
                gap: 0.8rem;
            }
        }
        @media (max-width: 700px) {
            .filters-bar {
                padding: 0.7rem 0.7rem;
            }
            .filters-toggle {
                display: flex;
            }
            .filters-content {
                flex-direction: column;
                gap: 0.8rem;
                max-height: 0;
                overflow: hidden;
                padding: 0;
                margin: 0;
            }
            .filters-bar.open .filters-content {
                max-height: 800px;
                margin-top: 0.7rem;
                padding-bottom: 0.5rem;
            }
        }
        @media (max-width: 600px) {
            .filters-bar {
                margin: 0.2rem 0 1rem 0;
            }
        }
        .form-btn {
            margin-top: 1rem;
            width: 100%;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 0.4rem;
            padding: 0.7rem 0;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background 0.2s;
        }
        .form-btn:hover {
            background: #1746a2;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4 text-center text-primary">Rechercher un covoiturage</h1>
        <form method="get" class="search-form" id="searchForm">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Départ</label>
                    <input type="text" name="depart" class="form-control" value="<?= htmlspecialchars($depart) ?>" required placeholder="Ville de départ">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Arrivée</label>
                    <input type="text" name="arrivee" class="form-control" value="<?= htmlspecialchars($arrivee) ?>" required placeholder="Ville d'arrivée">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date) ?>" required>
                </div>
            </div>
            <div class="filters-bar mt-3">
                <button type="button" class="filters-toggle" aria-expanded="false" aria-controls="filters-content">
                    <span>Filtres avancés</span>
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M6 8l4 4 4-4" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div class="filters-content" id="filters-content">
                    <div class="filter-item">
                        <label class="filter-label">
                            <input type="checkbox" id="ecolo" name="ecolo" value="1" <?= $ecolo === '1' ? 'checked' : '' ?>>
                            <span>Voyage écologique (électrique)</span>
                        </label>
                    </div>
                    <div class="filter-item">
                        <label>Prix max (€)</label>
                        <input type="number" name="prix_max" step="0.01" min="0" class="form-control" value="<?= htmlspecialchars($prix_max) ?>" placeholder="Ex: 20">
                    </div>
                    <div class="filter-item">
                        <label>Durée max</label>
                        <div style="display:flex;gap:0.4rem;">
                            <input type="number" name="duree_max_heures" placeholder="Heures" min="0" style="width:60px;" class="form-control" value="<?= htmlspecialchars($duree_max_heures) ?>">
                            <span style="align-self:center;">h</span>
                            <input type="number" name="duree_max_minutes" placeholder="Minutes" min="0" max="59" style="width:60px;" class="form-control" value="<?= htmlspecialchars($duree_max_minutes) ?>">
                            <span style="align-self:center;">min</span>
                        </div>
                    </div>
                    <div class="filter-item">
                        <label>Note min. conducteur</label>
                        <input type="number" name="note_min" min="0" max="5" step="0.1" class="form-control" value="<?= htmlspecialchars($note_min) ?>" placeholder="Ex: 4.5">
                    </div>
                </div>
            </div>
            <button type="submit" class="form-btn">Rechercher</button>
        </form>

        <?php if ($depart && $arrivee && $date): ?>
            <h2 class="mb-4 text-center">Résultats</h2>
            <?php if (count($results) > 0): ?>
                <div class="row">
                <?php foreach ($results as $r): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-covoit p-3">
                            <div class="d-flex align-items-center mb-3">
                                <img src="data:image/jpeg;base64,<?= base64_encode($r['photo']) ?>" alt="Photo conducteur" class="driver-photo me-3">
                                <div>
                                    <h5 class="mb-0"><?= htmlspecialchars($r['pseudo']) ?></h5>
                                    <div>
                                        <span class="badge bg-warning text-dark"><?= number_format($r['note'], 1) ?> ★</span>
                                    </div>
                                </div>
                            </div>
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item">
                                    <strong>Départ :</strong> <?= htmlspecialchars($r['depart']) ?> <br>
                                    <span class="text-muted"><?= date('d/m/Y', strtotime($r['heure_depart'])) ?> à <?= date('H:i', strtotime($r['heure_depart'])) ?></span>
                                </li>
                                <li class="list-group-item">
                                    <strong>Arrivée :</strong> <?= htmlspecialchars($r['arrivee']) ?> <br>
                                    <span class="text-muted"><?= date('H:i', strtotime($r['heure_arrivee'])) ?></span>
                                </li>
                                <li class="list-group-item">
                                    <?php
                                        $duration = strtotime($r['heure_arrivee']) - strtotime($r['heure_depart']);
                                        $hours = floor($duration / 3600);
                                        $minutes = floor(($duration % 3600) / 60);
                                    ?>
                                    <strong>Durée :</strong> <?= $hours ?>h<?= $minutes > 0 ? $minutes : '00' ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Prix :</strong> <span class="text-success"><?= number_format($r['prix'], 2) ?> €</span>
                                </li>
                                <li class="list-group-item">
                                    <strong>Places restantes :</strong> <?= $r['place'] ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Écologique :</strong>
                                    <?php if (strtolower($r['energie']) == 'electrique'): ?>
                                        <span class="badge badge-eco">Oui</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Non</span>
                                    <?php endif; ?>
                                </li>
                            </ul>
                            <form method="get" action="detail.php" class="d-grid">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button type="submit" class="btn btn-outline-primary">Détail</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center">
                    Aucun covoiturage trouvé à cette date.
                </div>
                <?php
                // Proposition de la prochaine date possible
                $stmt2 = $pdo->prepare("
                    SELECT DATE(heure_depart) as next_date
                    FROM covoiturage
                    WHERE depart = :depart AND arrivee = :arrivee AND place > 0 AND DATE(heure_depart) > :date
                    ORDER BY heure_depart ASC
                    LIMIT 1
                ");
                $stmt2->execute([':depart' => $depart, ':arrivee' => $arrivee, ':date' => $date]);
                $suggestion = $stmt2->fetch();
                if ($suggestion):
                ?>
                    <div class="alert alert-info text-center">
                        Voulez-vous essayer le <strong><?= date('d/m/Y', strtotime($suggestion['next_date'])) ?></strong> à la place ?
                        <form method="get" class="d-inline">
                            <input type="hidden" name="depart" value="<?= htmlspecialchars($depart) ?>">
                            <input type="hidden" name="arrivee" value="<?= htmlspecialchars($arrivee) ?>">
                            <input type="hidden" name="date" value="<?= $suggestion['next_date'] ?>">
                            <button type="submit" class="btn btn-link p-0 align-baseline">Voir cette date</button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-secondary text-center">
                Veuillez remplir le formulaire pour afficher les covoiturages disponibles.
            </div>
        <?php endif; ?>
    </div>
    <!-- Bootstrap JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.querySelector('.filters-toggle');
        const bar = document.querySelector('.filters-bar');
        if(toggle && bar) {
            toggle.addEventListener('click', function() {
                const expanded = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', !expanded);
                bar.classList.toggle('open');
            });
        }
    });
    </script>
</body>
</html>
