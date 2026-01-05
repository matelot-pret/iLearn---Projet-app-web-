<?php
session_start();

require_once __DIR__ . '/../class/Database.php';

$connection_path = "/myMoodle/public/connexion.php";
$avatar_fs_base  = __DIR__ . '/../uploads/avatars/';   
$avatar_web_base = '/myMoodle/uploads/avatars/';  

if (
    !isset($_SESSION['est_connecte']) ||
    $_SESSION['est_connecte'] !== true ||
    !isset($_SESSION['user_id'])
) {
    header("Location: $connection_path");
    exit();
}


$db = new Database();
$user_id = (int) $_SESSION['user_id'];


$ressources = $db->get_ressources_by_user($user_id);


$is_connected     = true;
$is_admin         = $_SESSION['est_admin'] ?? false;
$user_first_name  = $_SESSION['prenom'] ?? 'Invité';
$user_last_name   = $_SESSION['nom'] ?? '';

$avatar = 'img/buste.jpg';

if (!empty($_SESSION['avatar'])) {
    $avatar_fs = $avatar_fs_base . $_SESSION['avatar'];

    if (is_file($avatar_fs)) {
        $avatar = $avatar_web_base . $_SESSION['avatar'];
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Historique des ressources</title>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container-fluid px-5">
        <a class="navbar-brand d-flex align-items-center fw-bold fs-4" href="index.php">
            <span class="bg-danger text-white p-3 me-2 rounded">iL</span>
            iLearn
        </a>

        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto me-4">
                <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>

                <?php if ($is_admin): ?>
                    <li class="nav-item"><a class="nav-link" href="createAccount.php">Créer un compte</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_ressources.php">Gérer les ressources</a></li>
                <?php endif; ?>

                <li class="nav-item"><a class="nav-link active" href="historique.php">Historique</a></li>
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

<div class="container my-5">
    <h2 class="fw-bold mb-4 text-center">📚 Mes ressources proposées</h2>

    <?php if (empty($ressources)): ?>
        <div class="alert alert-info">
            Vous n’avez encore proposé aucune ressource.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle bg-white">
                <thead class="table-light">
                    <tr>
                        <th>Titre</th>
                        <th>Cours</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($ressources as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['titre']) ?></td>
                        <td><?= htmlspecialchars($r['cours_nom']) ?></td>
                        <td><?= strtoupper(htmlspecialchars($r['type'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['date_ajout'])) ?></td>
                        <td>
                            <?php
                            switch ($r['statut']) {
                                case 'en_attente':
                                    echo '<span class="badge bg-warning text-dark">🕒 En attente</span>';
                                    break;
                                case 'approuve':
                                    echo '<span class="badge bg-success">✅ Validée</span>';
                                    break;
                                case 'rejete':
                                    echo '<span class="badge bg-danger">❌ Refusée</span>';
                                    break;
                                default:
                                    echo '<span class="badge bg-secondary">Inconnu</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <a href="index.php" class="btn btn-outline-dark mt-3">← Retour</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
