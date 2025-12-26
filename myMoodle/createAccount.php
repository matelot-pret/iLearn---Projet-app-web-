<?php 
session_start();

if (
    !isset($_SESSION['est_connecte']) ||
    $_SESSION['est_connecte'] !== true ||
    !isset($_SESSION['est_admin']) ||
    $_SESSION['est_admin'] !== true
) {
    header("Location: connexion.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Creer un compte : iLearn</title>
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
                    <li class="nav-item"><a class="nav-link active" href="createAccounts.php">Créer un compte</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_ressources.php">Gérer les ressources</a></li>
                </ul>
                <div class="d-flex flex-column flex-lg-row align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <img src="img/buste.jpg" class="rounded-circle" width="36" height="36" alt="avatar">
                        <div>
                            <div class="text-muted"><?php echo htmlspecialchars($_SESSION['prenom'] ?? 'Admin'); ?></div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['nom'] ?? ''); ?></div>
                        </div>
                    </div>
                    <a href="disconnected.php" class="btn btn-outline-dark rounded-pill px-4 text-decoration-none">Se déconnecter</a>
                </div>
            </div>
        </div>
    </nav>
    <main class="container my-5 col-lg-6">
        <?php
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

        <header class="">
            <h3 class="fw-bold ">Création de compte utilisateurs</h3>
        </header>
        <section class="mt-2 pb-5 bg-body-secondary border border-dark rounded">
            <form action="process_account_creation.php" method="POST" enctype="multipart/form-data" class="mx-3">
                <label for="nom" class="mt-5 col-12" >Nom*<br>
                    <input type="text" id="nom" class=" form-control rounded" name="nom" required>
                </label>
                <label for="prenom" class="col-12" >Prenom*<br>
                    <input type="text" id="prenom" class="form-control rounded" name="prenom" required>
                </label>
                <label for="matricule" class="mb-5 col-12">Matricule*<br>
                    <input type="text" id="matricule" class="form-control rounded" name="matricule" required>
                </label>
                <span>Type de compte :</span>
                <select name="est_admin" class="form-select"> 
                    <option value="0">Utilisateur</option>
                    <option value="1">Administrateur</option>
                </select>

                <label for="Password" class=" col-12">Mot de passe*<br>
                    <input type="password" id="password" class="form-control rounded mb-2" name="password" minlength="8" required>
                </label>
                <label for="password_confirm" class=" col-12">Resaisissez le mot de passe*<br>
                    <input type="password" id="password_confirm" class="rounded mb-3" name="password_confirm" minlength="8" required>
                </label> 
                
                <label for="avatar" class="col-12 mb-5">Avatar (optionnel)<br>
                    <input type="file" id="avatar" name="avatar" class="form-control" accept="image/jpeg,image/png,image/jpg">
                    <small class="text-muted">Formats acceptés : JPG, PNG (max 2Mo)</small>
                </label>

                <p class="mb-5 text-muted"><small>Les éléments avec (*) sont obligatoire</small></p>                
                <button type="submit" class="btn btn-outline-danger d-block mt-5 mx-auto">Creer le compte</button>
            </form>
        </section>
    </main>
</body>
</html>