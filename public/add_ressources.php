<?php
session_start();

require_once __DIR__ . '/../class/Database.php';

$connection_path = "/myMoodle/public/connexion.php";
$index_path          = "/myMoodle/public/index.php";
$create_account_path = "/myMoodle/public/createAccount.php";
$admin_path          = "/myMoodle/public/admin_ressources.php";
$historique_path     = "/myMoodle/public/historique.php";
$logout_path         = "/myMoodle/public/disconnected.php";


if (empty($_SESSION['est_connecte'])) {
    header("Location: $connection_path");
    exit();
}

$db = new Database();

$is_connected = true;
$is_admin     = $_SESSION['est_admin'] ?? false;

$user_first_name = $_SESSION['prenom'] ?? '';
$user_last_name  = $_SESSION['nom'] ?? '';

$from_cours = false;
$cours_id   = null;
$nom_cours  = null;

/* si on vient d’un cours précis */
if (isset($_GET['cours_id']) && is_numeric($_GET['cours_id'])) {
    $from_cours = true;
    $cours_id   = (int) $_GET['cours_id'];

    $cours = $db->get_cours_by_id($cours_id);
    if (!$cours) {
        die("Cours introuvable");
    }

    $nom_cours = $cours['nom'];
}

/* liste complète des cours */
$cours_liste = $db->get_all_cours();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    
    <title>Ajouter une ressource - iLearn</title>
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
    
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <?php
                // Afficher les messages de succès ou d'erreur
                if (isset($_SESSION['succes'])):
                ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo htmlspecialchars($_SESSION['succes']); 
                        unset($_SESSION['succes']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php
                if (isset($_SESSION['erreur'])):
                ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo htmlspecialchars($_SESSION['erreur']); 
                        unset($_SESSION['erreur']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h3 class="mb-0">📤 Ajouter une ressource</h3>
                        <small>Selectionnez ou créer un cours</small>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Votre ressource sera soumise à validation par un administrateur avant d'être publiée.
                        </p>
                        
                        

                        <form action="process_add_ressources.php" method="POST" enctype="multipart/form-data">
                            <?php if ($from_cours): ?>
                            <!-- Cours imposé -->
                            <input type="hidden" name="cours_id" value="<?= $cours_id ?>">
                            <div class="mb-3">
                                <label class="form-label">Cours</label>
                                <input type="text"
                                    class="form-control"
                                    value="<?php echo $nom_cours; ?>"
                                    disabled>
                            </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <label class="form-label">Cours *</label>
                                    <select class="form-select" name="cours_select" id="cours_select" required>
                                        <option value="">-- Sélectionner un cours --</option>
                                        <?php foreach ($cours_liste as $c): ?>
                                            <option value="<?= $c['id'] ?>">
                                            <?= htmlspecialchars($c['nom']) ?> (<?= $c['bloc'] ?> – <?= $c['section'] ?>)
                                        </option>
                                        <?php endforeach; ?>
                                        <option value="autre">Autre (nouveau cours)</option>
                                    </select>
                                </div>
                                <div id="new-course-zone" style="display:none;">
                                    <div class="mb-3">
                                        <label class="form-label">Nom du cours *</label>
                                        <input type="text" name="new_nom" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Bloc *</label>
                                        <input type="text" name="new_bloc" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Section *</label>
                                        <input type="text" name="new_section" class="form-control">
                                    </div>
                                </div>
                            <?php endif; ?>
                            <!-- Titre de la ressource -->
                            <div class="mb-3">
                                <label for="titre" class="form-label">Titre de la ressource *</label>
                                <input type="text" class="form-control" id="titre" name="titre" required placeholder="Ex: Chapitre 1 - Introduction">
                            </div>
                            
                            <!-- Type de ressource -->
                            <div class="mb-3">
                                <label for="type" class="form-label">Type de ressource *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">-- Sélectionnez un type --</option>
                                    <option value="pdf">PDF</option>
                                    <option value="jpg">Image JPG</option>
                                    <option value="png">Image PNG</option>
                                    <option value="url">Lien URL</option>
                                </select>
                            </div>
                            
                            <!-- Zone qui change selon le type -->
                            <div id="upload-zone" class="mb-3" style="display: none;">
                                <label for="fichier" class="form-label">Fichier *</label>
                                <input type="file" class="form-control" id="fichier" name="fichier" accept="">
                                <small class="text-muted">Taille maximale : 10 Mo</small>
                            </div>
                            
                            <div id="url-zone" class="mb-3" style="display: none;">
                                <label for="url" class="form-label">URL *</label>
                                <input type="url" class="form-control" id="url" name="url" placeholder="https://example.com/video">
                                <small class="text-muted">Entrez un lien complet (ex: YouTube, Google Drive, etc.)</small>
                            </div>
                            
                            <p class="text-muted mt-3">
                                <small>Les champs marqués d'un (*) sont obligatoires</small>
                            </p>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" href="add_ressources.php" class="btn btn-danger">Soumettre la ressource</button>
                                <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Informations supplémentaires -->
                <div class="alert alert-info mt-4">
                    <strong>ℹ️ À savoir :</strong>
                    <ul class="mb-0 mt-2">
                        <li>Votre ressource sera vérifiée par un administrateur avant publication</li>
                        <li>Vous pouvez suivre le statut de vos ressources dans "Historique"</li>
                        <li>Formats acceptés : PDF, JPG, PNG ou lien URL</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
    <script>
        const coursSelect = document.getElementById('cours_select');
        const newCourseZone = document.getElementById('new-course-zone');

        if (coursSelect) {
            coursSelect.addEventListener('change', function () {
                newCourseZone.style.display =
                    (this.value === 'autre') ? 'block' : 'none';
            });
        }
    </script>
    <script>
        // Afficher/cacher les zones selon le type sélectionné
        document.getElementById('type').addEventListener('change', function() {
            const type = this.value;
            const uploadZone = document.getElementById('upload-zone');
            const urlZone = document.getElementById('url-zone');
            const fichierInput = document.getElementById('fichier');
            const urlInput = document.getElementById('url');
            
            // Réinitialiser
            uploadZone.style.display = 'none';
            urlZone.style.display = 'none';
            fichierInput.required = false;
            urlInput.required = false;
            
            if (type === 'url') {
                // Type URL : afficher champ URL
                urlZone.style.display = 'block';
                urlInput.required = true;
            } else if (type === 'pdf' || type === 'jpg' || type === 'png') {
                // Type fichier : afficher upload
                uploadZone.style.display = 'block';
                fichierInput.required = true;
                
                // Changer l'accept selon le type
                if (type === 'pdf') {
                    fichierInput.accept = '.pdf';
                } else if (type === 'jpg') {
                    fichierInput.accept = '.jpg,.jpeg';
                } else if (type === 'png') {
                    fichierInput.accept = '.png';
                }
            }
        });
    </script>
</body>
</html>