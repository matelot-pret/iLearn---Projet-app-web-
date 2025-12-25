<?php
session_start();

$host = 'localhost';
$dbname = 'projetphp';
$db_user = 'BDPatrickProjet25';
$db_pass = 'Samourai3';

try {
    $connexion = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $db_user,
        $db_pass
    );
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $matricule = trim($_POST['matricule'] ?? '');
    $password  = $_POST['password'] ?? '';

    if (empty($matricule) || empty($password)) {
        $_SESSION['erreur'] = "Veuillez remplir tous les champs";
        header("Location: connexion.php");
        exit();
    }

    $stmt = $connexion->prepare("
        SELECT id, matricule, mot_de_passe, nom, prenom, est_admin
        FROM utilisateurs
        WHERE matricule = :matricule
    ");
    $stmt->bindValue(':matricule', $matricule, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['mot_de_passe'])) {

        // Session = source de vérité
        $_SESSION['user_id']      = $user['id'];
        $_SESSION['matricule']    = $user['matricule'];
        $_SESSION['nom']          = $user['nom'];
        $_SESSION['prenom']       = $user['prenom'];
        $_SESSION['est_admin']    = (bool)$user['est_admin'];
        $_SESSION['est_connecte'] = true;

        header("Location: index.php");
        exit();
    }

    $_SESSION['erreur'] = "Identifiants incorrects";
    header("Location: connexion.php");
    exit();
}
