<?php
session_start();

$host = 'localhost';
$dbname = 'projetphp';
$db_user = 'BDPatrickProjet25';
$db_pass = 'Samourai3';

try {
    $connexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_user, $db_pass);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if (
    !isset($_SESSION['est_connecte']) ||
    $_SESSION['est_connecte'] !== true ||
    !isset($_SESSION['est_admin']) ||
    $_SESSION['est_admin'] !== true
) {
    header("Location: connexion.php");
    exit();
}

$is_connected = isset($_SESSION['est_connecte']) && $_SESSION['est_connecte'] === true;

$user_first_name = $_SESSION['prenom'] ?? 'Invité';
$user_last_name = $_SESSION['nom'] ?? '';
$is_admin = $_SESSION['est_admin'];

$sql = "SELECT r.id, r.titre, r.type, r.contenu, r.date_ajout,
        u.nom, u.prenom,
        c.nom AS cours_nom
FROM ressources r
JOIN utilisateurs u ON r.utilisateur_id = u.id
JOIN cours c ON r.cours_id = c.id
WHERE r.statut = 'en_attente'
ORDER BY r.date_ajout ASC;";


$stmt = $connexion->query($sql);
$ressources = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Document</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container-fluid px-5">
            <a class="navbar-brand d-flex align-items-center fw-bold fs-4" href="index.php">
                <span class="bg-danger text-white p-3 me-2 rounded">iL</span>
                iLearn
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-4">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php">Cours</a></li>
                    <?php if($is_connected && $is_admin): ?>
                        <li class="nav-item"><a class="nav-link" href="admin_ressources.php">Gérer les ressources</a></li>
                    <?php endif;?> 
                    <?php if ($is_connected): ?>
                        <li class="nav-item"><a class="nav-link" href="#">Historique</a></li>
                    <?php endif; ?>
                    
                </ul>
                <?php if ($is_connected): ?>
                    <div class="d-flex flex-column flex-lg-row align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <img src="img/buste.jpg" class="rounded-circle" width="36" height="36" alt="avatar">
                                <div class="align-items-start">
                                    <div class="text-muted small"><?php echo htmlspecialchars($user_first_name); ?></div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($user_last_name); ?></div>
                                </div>
                            </div>
                        <a href="disconnected.php" class="btn btn-outline-dark rounded-pill px-4 text-decoration-none">Se déconnecter</a>
                    </div>
                <?php else: ?>
                    <a href="connecton.php" class="btn btn-outline-dark rounded-pill px-4 text-decoration-none">Connexion</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container my-5">
        <h2 class="fw-bold mb-4 text-center">Ressources en attente de validation</h2>
        <hr>
        <?php if (empty($ressources)): ?>
            <div class="alert alert-info">
                Aucune ressource en attente.
            </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($ressources as $r): ?>
                <div class="card mb-3 mx-4 mx-auto col-12 col-lg-5">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($r['titre']) ?></h5>
                        <p class="mb-1">
                            <strong>Cours :</strong> <?= htmlspecialchars($r['cours_nom']) ?>
                        </p>
                        <p class="mb-1">
                            <strong>Auteur :</strong> <?= htmlspecialchars($r['prenom']) ?> <?= htmlspecialchars($r['nom']) ?>
                        </p>
                        <p class="mb-2">
                            <strong>Type :</strong> <?= htmlspecialchars($r['type']) ?>
                        </p>

                        <a href="<?= htmlspecialchars($r['contenu']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                            Voir la ressource
                        </a>

                        <a href="process_validation.php?id=<?= $r['id'] ?>&action=valider"
                            class="btn btn-success btn-sm ms-2">
                            Valider
                        </a>

                        <a href="process_validation.php?id=<?= $r['id'] ?>&action=refuser"
                            class="btn btn-danger btn-sm ms-2">
                            Refuser
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </main>

</body>
</html>