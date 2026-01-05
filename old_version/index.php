<?php
session_start();

require_once __DIR__ . '/class/Database.php';

$db = new Database();

$is_connected = !empty($_SESSION['est_connecte']);
$is_admin     = $_SESSION['est_admin'] ?? false;

$user_first_name = $_SESSION['prenom'] ?? 'Invité';
$user_last_name  = $_SESSION['nom'] ?? '';
$avatar = (
    !empty($_SESSION['avatar']) && file_exists($_SESSION['avatar'])
)
    ? $_SESSION['avatar']
    : 'img/buste.jpg';

/* récupération des cours avec ressources approuvées */
$cours = $db->get_cours_with_ressources(); // méthode à ajouter

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>iLearn - Accueil</title>
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
    <div class="container py-5">
        <h1 class="fw-bold mb-4">Cours consultables</h1>
        <?php if($is_connected): ?>
            <a target="_blank" href="add_ressources.php" class="btn btn-outline-dark mt-2">
                Ajouter une ressource +
            </a>
        <?php endif; ?>
        <hr>
        <div class="mb-5">
            <h4 class="mb-3">Effectuer une recherche</h4>
            <input type="text" class="form-control bg-light border-0 rounded-3 p-3 w-50" placeholder="Ex: Services réseaux">
            
        </div>

        <div class="row g-4 mb-5">
            <?php

            $couleurs = ['secondary', 'danger', 'success', 'primary', 'warning', 'info']; # des couleurs randoms pour que ca soit joli lol
            $index = 0;

            foreach($cours as $c) {
                $couleur = $couleurs[$index % count($couleurs)];
                $index++;
            ?>
            
            <div class="col-md-4">
                <div class="card bg-<?php echo $couleur; ?> bg-opacity-25 border-0 rounded-4 h-100">
                    <a href="lesson.php?id=<?= $c['id']?>" class="text-decoration-none text-black">
                        <div class="card-body p-4 d-flex flex-column justify-content-between" style="min-height: 300px;">
                            <h3 class="card-title fw-bold fs-2"><?php echo $c['nom'] ?></h3>
                            <div>
                                <p class="card-text mb-1 fs-5">Bloc <?php echo $c['bloc'] ?></p>
                                <p class="card-text mb-1 fs-5">Section <?php echo $c['section'] ?>
                                <p class="card-text fs-5">Nombre de ressources : <?php echo $c['nb_ressources'] ?>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            
            <?php }?>

            <?php if(empty($cours)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        Aucun cours disponible pour le moment.
                    </div>
                </div>
            <?php endif;?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
