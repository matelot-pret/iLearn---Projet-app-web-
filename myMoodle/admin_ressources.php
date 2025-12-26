<?php
session_start();

require_once __DIR__ . '/class/Database.php';

if (
    !isset($_SESSION['est_connecte']) ||
    $_SESSION['est_connecte'] !== true ||
    !isset($_SESSION['est_admin']) ||
    $_SESSION['est_admin'] !== true
) {
    header("Location: connexion.php");
    exit();
}

$db = new Database();

$is_connected = true;
$is_admin     = true;

$user_first_name = $_SESSION['prenom'] ?? '';
$user_last_name  = $_SESSION['nom'] ?? '';
$avatar = (
    !empty($_SESSION['avatar']) && file_exists($_SESSION['avatar'])
)
    ? $_SESSION['avatar']
    : 'img/buste.jpg';

$ressources = $db->get_pending_ressources();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <title>Validation des ressources - iLearn</title>
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
                <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link active" href="admin_ressources.php">Gérer les ressources</a></li>
                <li class="nav-item"><a class="nav-link" href="createAccount.php">Créer un compte</a></li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <img src="<?= htmlspecialchars($avatar) ?>" class="rounded-circle" width="36" height="36" alt="avatar">
                    <div>
                        <div class="text-muted small"><?= htmlspecialchars($user_first_name) ?></div>
                        <div class="fw-semibold"><?= htmlspecialchars($user_last_name) ?></div>
                    </div>
                </div>
                <a href="disconnected.php" class="btn btn-outline-dark rounded-pill px-4">Se déconnecter</a>
            </div>
        </div>
    </div>
</nav>

<main class="container my-5">
    <h2 class="fw-bold mb-4 text-center">📥 Ressources en attente de validation</h2>
    <hr>

    <?php if (empty($ressources)): ?>
        <div class="alert alert-info text-center">
            Aucune ressource en attente.
        </div>
    <?php else: ?>
        <div class="row g-4 justify-content-center">
            <?php foreach ($ressources as $r): ?>
                <div class="col-12 col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($r['titre']) ?></h5>

                            <p class="mb-1"><strong>Cours :</strong> <?= htmlspecialchars($r['cours_nom']) ?></p>
                            <p class="mb-1"><strong>Auteur :</strong>
                                <?= htmlspecialchars($r['prenom']) ?>
                                <?= htmlspecialchars($r['nom']) ?>
                            </p>
                            <p class="mb-2"><strong>Type :</strong> <?= htmlspecialchars($r['type']) ?></p>

                            <a href="<?= htmlspecialchars($r['contenu']) ?>"
                                target="_blank"
                                class="btn btn-outline-primary btn-sm">
                                Voir la ressource
                            </a>

                            <a href="process_validation.php?id=<?= $r['id'] ?>&action=approuve"
                                class="btn btn-success btn-sm ms-2">
                                Valider
                            </a>

                            <a href="process_validation.php?id=<?= $r['id'] ?>&action=rejete"
                                class="btn btn-danger btn-sm ms-2">
                                Refuser
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
