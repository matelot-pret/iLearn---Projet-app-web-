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

$is_connected = isset($_SESSION['est_connecte']) && $_SESSION['est_connecte'] === true;

$user_first_name = $_SESSION['prenom'] ?? 'Invité';
$user_last_name = $_SESSION['nom'] ?? '';
$is_admin = $_SESSION['est_admin'] ?? false;

$sql = "SELECT 
    c.id,
    c.nom,
    c.bloc,
    c.section,
    COUNT(r.id) AS nb_ressources,
    MAX(r.date_ajout) AS derniere_ressource
FROM cours c
JOIN ressources r 
    ON r.cours_id = c.id
    AND r.statut = 'approuve'
GROUP BY c.id, c.nom, c.bloc, c.section
ORDER BY derniere_ressource DESC;";

$stmt = $connexion->query($sql);
$cours = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (
    isset($_POST['matricule'], $_POST['password']) &&
    $_POST['matricule'] !== '' &&
    $_POST['password'] !== ''
) {
    $matricule = $_POST['matricule'];
    $password = $_POST['password'];

    $prepQuery = $connexion->prepare("SELECT id, mot_de_passe, nom, prenom, est_admin
                                            FROM utilisateurs
                                            WHERE matricule = :matricule
                                            ");
    $prepQuery->bindValue(':matricule', $matricule, PDO::PARAM_STR);
    $prepQuery->execute();

    if($ligne = $prepQuery->fetch(PDO::FETCH_ASSOC)) {
        if (password_verify($password, $ligne['mot_de_passe'])) {
            $_SESSION['user_id']      = $ligne['id'];
            $_SESSION['nom']          = $ligne['nom'];
            $_SESSION['prenom']       = $ligne['prenom'];
            $_SESSION['est_admin']    = (bool) $ligne['est_admin'];
            $_SESSION['est_connecte'] = true;
            header("Location: index.php");
            exit();
        }
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
    <title>iLearn - Accueil</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container-fluid px-5">
            <a class="navbar-brand d-flex align-items-center fw-bold fs-4" href="index.html">
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
                    <a href="connexion.php" class="btn btn-outline-dark rounded-pill px-4 text-decoration-none">Connexion</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container py-5">
        <h1 class="fw-bold mb-4">Cours consultables anonymement</h1>
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
                <div class="card bg-secondary bg-opacity-25 border-0 rounded-4 h-100">
                    <a href="cour.php?id=<?= $c['id']?>" class="text-decoration-none text-black">
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
