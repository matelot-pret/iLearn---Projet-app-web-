<?php
session_start();

require_once __DIR__ . '/../class/Database.php';

$connection_path = "/myMoodle/public/connexion.php";
$index_path = "/myMoodle/public/index.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $connection_path");
    exit();
}

$matricule = trim($_POST['matricule'] ?? '');
$password  = $_POST['password'] ?? '';

if ($matricule === '' || $password === '') {
    $_SESSION['erreur'] = "Veuillez remplir tous les champs.";
    header("Location: $connection_path");
    exit();
}

$db = new Database();

$user = $db->get_user_by_matricule($matricule);

if (!$user || !password_verify($password, $user['mot_de_passe'])) {
    $_SESSION['erreur'] = "Identifiants incorrects.";
    header("Location: $connection_path");
    exit();
}

session_regenerate_id(true);

$_SESSION['user_id']      = $user['id'];
$_SESSION['matricule']    = $user['matricule'];
$_SESSION['nom']          = $user['nom'];
$_SESSION['prenom']       = $user['prenom'];
$_SESSION['est_admin']    = (bool) $user['est_admin'];
$_SESSION['est_connecte'] = true;

$_SESSION['avatar'] = $user['avatar'] ?? null;

header("Location: $index_path");
exit();
