<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Connexion – iLearn</title>
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
            </ul>
        </div>
    </div>
</nav>

<main class="container my-5">

    <?php if (isset($_SESSION['erreur'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['erreur']) ?>
            <?php unset($_SESSION['erreur']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <section class="col-12 col-lg-6 mx-auto">
            <h3 class="fw-bold mb-3 text-center ">Connexion</h3>

            <div class="p-4 bg-body-secondary border border-dark rounded">
                <form action="process_connection.php" method="POST">

                    <div class="mb-3">
                        <label for="matricule" class="form-label">Matricule</label>
                        <input
                            type="text"
                            id="matricule"
                            name="matricule"
                            class="form-control"
                            placeholder="ex : e2400340"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="••••••••"
                            required
                        >
                    </div>

                    <a href="#" class="text-decoration-none text-black d-block mb-4">
                        Mot de passe oublié ?
                    </a>

                    <button type="submit" class="btn btn-secondary w-100">
                        Se connecter
                    </button>

                </form>
            </div>
        </section>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
