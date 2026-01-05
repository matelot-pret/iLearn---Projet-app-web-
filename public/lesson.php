<?php
session_start();

require_once __DIR__ . '/../class/Database.php';

$avatar_fs_base  = __DIR__ . '/../uploads/avatars/';   
$avatar_web_base = '/myMoodle/uploads/avatars/';  

$db = new Database();

$is_connected = !empty($_SESSION['est_connecte']);
$is_admin     = $_SESSION['est_admin'] ?? false;

$user_first_name = $_SESSION['prenom'] ?? 'Invité';
$user_last_name  = $_SESSION['nom'] ?? '';
$user_id         = $_SESSION['user_id'] ?? 0;
$avatar = 'img/buste.jpg';

if (!empty($_SESSION['avatar'])) {
    $avatar_fs = $avatar_fs_base . $_SESSION['avatar'];

    if (is_file($avatar_fs)) {
        $avatar = $avatar_web_base . $_SESSION['avatar'];
    }
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Cours invalide");
}

$cours_id = (int) $_GET['id'];

$cours = $db->get_cours_by_id($cours_id);
if (!$cours) {
    die("Cours introuvable");
}

$lesson_name = $cours['nom'];

$ressources = $db->get_ressources_by_cours($cours_id, $user_id);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title><?php echo htmlspecialchars($lesson_name); ?> - iLearn</title>
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
    <main class="container my-5">
        <header class="mb-4">
            <h2 class="fw-bold"><?php echo htmlspecialchars($lesson_name); ?></h2>
            <?php if ($is_connected): ?>
                <a target="_blank" href="add_ressources.php?cours_id=<?php echo $cours_id; ?>" class="btn btn-outline-dark mt-2">
                    Ajouter une ressource +
                </a>
            <?php endif; ?>
        </header>
        <hr>
        
        <div class="container">
            <h3 class="text-center fw-semibold mb-4">Ressources disponibles</h3>
            
            <?php if (!$is_connected): ?>
                <div class="alert alert-info text-center">
                    <strong>ℹ️ Information :</strong> Vous pouvez voir la liste des ressources, mais vous devez être connecté pour les consulter.
                    <a href="connexion.php" class="alert-link">Se connecter</a>
                </div>
            <?php endif; ?>
            
            <?php if (empty($ressources)): ?>
                <div class="alert alert-info text-center">
                    Aucune ressource disponible pour ce cours.
                </div>
            
            <?php else: ?>
                <?php foreach($ressources as $ressource): 

                    $contenu = $ressource['contenu'];
                    $lien = null;

                    if ($ressource['type'] === 'url') {
                        $url_valide = filter_var($contenu, FILTER_VALIDATE_URL);
                        if ($url_valide) {
                            $lien = $contenu;
                        }
                    } else {
                        $lien = $contenu;
                    }

                    if ($lien !== null) {
                        $lien = htmlspecialchars($lien);
                    }
                    $est_lu = (bool) $ressource['est_lue'];
                    $if_consulted = $est_lu ? "Lu" : "Non lu";
                    $button_style = $est_lu ? "btn-success" : "btn-outline-secondary";

                ?>
                
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center border-top border-3 border-danger-subtle pt-3 pb-2">
                    <div class="mb-2 mb-md-0 flex-grow-1">
                        <?php if ($is_connected && $lien !== null): ?>
                            <a href="<?= $lien ?>" target="_blank" class="text-decoration-none text-dark fw-semibold">
                                <?= htmlspecialchars($ressource['titre']); ?>
                            </a>
                        <?php else: ?>
                            <span class="fw-semibold text-muted">
                                <?= htmlspecialchars($ressource['titre']); ?>
                            </span>
                        <?php endif; ?>
                        
                        <div class="small text-muted">
                            Type: <?php echo htmlspecialchars($ressource['type']); ?> | 
                            Ajouté le: <?php echo date('d/m/Y', strtotime($ressource['date_ajout'])); ?>
                        </div>
                    </div>
                    
                    <?php if ($is_connected): ?>
                        <?php if (!$est_lu): ?>
                            <a href="mark_as_read.php?id=<?= $ressource['id'] ?>&cours_id=<?= $cours_id ?>"
                            class="btn btn-outline-success btn-sm rounded-pill">
                            ✔ Marquer comme lu
                            </a>
                        <?php else: ?>
                            <span class="badge bg-success rounded-pill">
                                ✔ Lu
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge bg-secondary">Connexion requise</span>
                    <?php endif; ?>
                </div>
                
                <?php endforeach; ?>
                
                <?php if (!$is_connected): ?>
                    <div class="text-center mt-5 py-4 bg-light rounded">
                        <h5 class="mb-3">🔐 Vous voulez accéder aux ressources ?</h5>
                        <a href="connexion.php" class="btn btn-primary btn-lg">Se connecter maintenant</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <a href="index.php" class="btn btn-outline-dark mt-3">← Retour</a>
        </div>
    </main>
</body>
</html>