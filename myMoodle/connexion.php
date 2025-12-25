<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Page de connexion</title>
</head>
<body>
    <nav>
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
                    <li class="nav-item"><a class="nav-link" href="index.php">Cours</a></li>
                </ul>
            </div>
        </div>
    </nav>
    </nav>
    <main class="container my-5 ">
        <?php
        session_start();
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

        <div class="row">
            <section class="col-12 col-lg-6">
                <div class="mb-3">
                    <h3 class="fw-bold ">Connexion</h3>
                </div>
                <div class="mt-2 pb-5 bg-body-secondary border border-dark rounded">
                    <form action="process_connection.php" method="POST" class="mx-5">
                        <label for="matricule" class="my-5 col-12" >Matricule<br>
                            <input type="text" id="matricule" class="rounded" placeholder="ex : e2400340" name="matricule">
                        </label>
                        <label for="Password" class=" col-12" >Mot de passe<br>
                            <input type="password" id="Password" class="rounded mb-2" placeholder="••••••••" name="password">
                        </label>
                        <a class="text-decoration-none text-black d-block mt-3" href="#">mot de passe oublié?</a>
                        <button type="submit" class="btn btn-secondary d-block mt-5 mx-auto" >Se connecter</button>
                    </form>
                </div>
            </section>
            <section class="col-12 col-lg-6 mt-5 d-none d-lg-block "><!--ca peut dégager-->
                <p>En vous engageant à rejoindre l’apprentissage en ligne, vous ne faites pas seulement le choix de travailler sérieusement : 
                    vous choisissez de réussir, d’avancer et de vous dépasser dans tout ce que vous entreprendrez. Chaque module que vous suivrez, 
                    chaque effort que vous fournirez, chaque nouvelle compétence que vous développerez deviendra une pierre de plus dans la construction de votre avenir.</p>
                <p>
                    Apprendre en ligne, c’est aussi décider de prendre votre destin en main, à votre rythme, selon vos objectifs, avec la volonté constante de vous améliorer. 
                    C’est croire en votre potentiel, miser sur vous-même et vous donner les moyens de transformer vos ambitions en réalité.</p>
                <p>
                    Rejoindre cette démarche, c’est choisir la progression, la discipline et la réussite. Et surtout, c’est croire que vous 
                    êtes capable de bien plus que ce que vous imaginez.</p>
            </section>
        </div>
    </main>
</body>
</html>