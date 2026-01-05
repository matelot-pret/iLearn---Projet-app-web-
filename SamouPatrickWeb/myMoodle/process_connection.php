<?php
session_start();

require_once __DIR__ . '/class/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: connexion.php");
    exit();
}

$matricule = trim($_POST['matricule'] ?? '');
$password  = $_POST['password'] ?? '';

if ($matricule === '' || $password === '') {
    $_SESSION['erreur'] = "Veuillez remplir tous les champs.";
    header("Location: connexion.php");
    exit();
}

$db = new Database();

$user = $db->get_user_by_matricule($matricule);

if (!$user || !password_verify($password, $user['mot_de_passe'])) {
    $_SESSION['erreur'] = "Identifiants incorrects.";
    header("Location: connexion.php");
    exit();
}

$_SESSION['user_id']      = $user['id'];
$_SESSION['matricule']    = $user['matricule'];
$_SESSION['nom']          = $user['nom'];
$_SESSION['prenom']       = $user['prenom'];
$_SESSION['est_admin']    = (bool) $user['est_admin'];
$_SESSION['est_connecte'] = true;

$_SESSION['avatar'] = $user['avatar'] ?? null;

header("Location: index.php");
exit();
