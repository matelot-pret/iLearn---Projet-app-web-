<?php
session_start();

if (!isset($_SESSION['est_connecte']) || $_SESSION['est_connecte'] !== true) {
    header("Location: connexion.php");
    exit();
}

$host = 'localhost';
$dbname = 'projetphp';
$db_user = 'BDPatrickProjet25';
$db_pass = 'Samourai3';

try {
    $connexion = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}

$user_id = (int) $_SESSION['user_id'];

/* 🔎 Récupération des ressources proposées par l'utilisateur */
$stmt = $connexion->prepare("
    SELECT r.titre,
           r.type,
           r.statut,
           r.date_ajout,
           c.nom AS cours_nom
    FROM ressources r
    JOIN cours c ON r.cours_id = c.id
    WHERE r.utilisateur_id = :uid
    ORDER BY r.date_ajout DESC
");
$stmt->execute([':uid' => $user_id]);
$ressources = $stmt->fetchAll(PDO::FETCH_ASSOC);

$is_connected = isset($_SESSION['est_connecte']) && $_SESSION['est_connecte'] === true;

$user_first_name = $_SESSION['prenom'] ?? 'Invité';
$user_last_name = $_SESSION['nom'] ?? '';
$is_admin = $_SESSION['est_admin'] ?? false;
$avatar = (
    !empty($_SESSION['avatar']) &&
    file_exists($_SESSION['avatar'])
)
    ? $_SESSION['avatar']
    : 'img/buste.jpg';
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-4">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Accueil</a></li>
                    <?php if($is_connected && $is_admin): ?>
                        <li class="nav-item"><a class="nav-link" href="createAccount.php">Créer un compte</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin_ressources.php">Gérer les ressources</a></li>
                    <?php endif;?> 
                    <?php if ($is_connected): ?>
                        <li class="nav-item"><a class="nav-link" href="historique.php">Historique</a></li>
                    <?php endif; ?>
                    
                </ul>
                <?php if ($is_connected): ?>
                    <div class="d-flex flex-column flex-lg-row align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= htmlspecialchars($avatar) ?>" class="rounded-circle" width="36" height="36" alt="avatar">
                                <div class="align-items-start">
                                    <div class="text-muted small"><?php echo htmlspecialchars($user_first_name); ?></div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($user_last_name); ?></div>
                                </div>
                            </div>
                        <a href="disconnected.php" class="btn btn-outline-dark rounded-pill px-4 text-decoration-none">Se déconnecter</a>
                        </div>
                <?php else: ?>
                    <a href="connexion.php" class="btn btn-outline-dark rounded-pill px-4 text-decoration-none">Connexion</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="fw-bold mb-4">📚 Mes ressources proposées</h2>

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
                            <td><?= strtoupper($r['type']) ?></td>
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
