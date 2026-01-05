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

$user_first_name = $_SESSION['prenom'] ?? 'Admin';
$user_last_name  = $_SESSION['nom'] ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        defer
    ></script>

    <title>Créer un compte – iLearn</title>
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
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="createAccount.php">Créer un compte</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_ressources.php">Gérer les ressources</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <img src="img/buste.jpg" class="rounded-circle" width="36" height="36" alt="avatar">
                    <div>
                        <div class="text-muted"><?= htmlspecialchars($user_first_name) ?></div>
                        <div class="fw-semibold"><?= htmlspecialchars($user_last_name) ?></div>
                    </div>
                </div>
                <a href="disconnected.php" class="btn btn-outline-dark rounded-pill px-4">
                    Se déconnecter
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="container my-5 col-lg-6">

    <?php if (isset($_SESSION['succes'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['succes']) ?>
            <?php unset($_SESSION['succes']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['erreur'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['erreur']) ?>
            <?php unset($_SESSION['erreur']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <header class="mb-3">
        <h3 class="fw-bold text-center">Création de compte utilisateur</h3>
    </header>

    <section class="bg-body-secondary border border-dark rounded p-4">

        <form action="process_account_creation.php"
              method="POST"
              enctype="multipart/form-data">

            <div class="mb-3">
                <label class="form-label">Nom *</label>
                <input type="text" name="nom" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Prénom *</label>
                <input type="text" name="prenom" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Matricule *</label>
                <input type="text" name="matricule" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Type de compte *</label>
                <select name="est_admin" class="form-select">
                    <option value="0">Utilisateur</option>
                    <option value="1">Administrateur</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Mot de passe *</label>
                <input type="password" name="password" class="form-control" minlength="8" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirmation du mot de passe *</label>
                <input type="password" name="password_confirm" class="form-control" minlength="8" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Avatar (optionnel)</label>
                <input type="file" name="avatar" class="form-control" accept="image/jpeg,image/png">
                <small class="text-muted">JPG / PNG – max 2 Mo</small>
            </div>

            <button type="submit" class="btn btn-danger w-100">
                Créer le compte
            </button>
        </form>
    </section>
</main>
</body>
</html>
