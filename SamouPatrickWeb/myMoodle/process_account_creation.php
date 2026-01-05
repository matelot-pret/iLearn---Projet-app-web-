<?php
session_start();

require_once __DIR__ . '/class/Database.php';

if (
    !isset($_SESSION['est_connecte']) ||
    $_SESSION['est_connecte'] !== true ||
    !isset($_SESSION['est_admin']) ||
    $_SESSION['est_admin'] !== true
) {
    header("Location: connexion.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: createAccount.php");
    exit();
}

$nom               = trim($_POST['nom'] ?? '');
$prenom            = trim($_POST['prenom'] ?? '');
$matricule         = trim($_POST['matricule'] ?? '');
$password          = $_POST['password'] ?? '';
$password_confirm  = $_POST['password_confirm'] ?? '';
$est_admin         = ($_POST['est_admin'] ?? '0') === '1' ? 1 : 0;

if ($nom === '' || $prenom === '' || $matricule === '' || $password === '' || $password_confirm === '') {
    $_SESSION['erreur'] = "Tous les champs obligatoires doivent être remplis.";
    header("Location: createAccount.php");
    exit();
}

if ($password !== $password_confirm) {
    $_SESSION['erreur'] = "Les mots de passe ne correspondent pas.";
    header("Location: createAccount.php");
    exit();
}

if (strlen($password) < 8) {
    $_SESSION['erreur'] = "Le mot de passe doit contenir au moins 8 caractères.";
    header("Location: createAccount.php");
    exit();
}

$db = new Database();

if ($db->get_user_by_matricule($matricule)) {
    $_SESSION['erreur'] = "Ce matricule existe déjà.";
    header("Location: createAccount.php");
    exit();
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$avatar_path = null;

if (!empty($_FILES['avatar']['name'])) {

    if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['erreur'] = "Erreur lors de l’upload de l’avatar.";
        header("Location: createAccount.php");
        exit();
    }

    if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
        $_SESSION['erreur'] = "Avatar trop volumineux (max 2 Mo).";
        header("Location: createAccount.php");
        exit();
    }

    $mime = mime_content_type($_FILES['avatar']['tmp_name']);
    if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
        $_SESSION['erreur'] = "Format d’avatar invalide.";
        header("Location: createAccount.php");
        exit();
    }

    $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $filename  = uniqid('avatar_', true) . '.' . $extension;
    $dir       = 'uploads/avatars/';

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $avatar_path = $dir . $filename;
    move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path);
}

$db->create_user(
    $nom,
    $prenom,
    $matricule,
    $password_hash,
    $est_admin,
    $avatar_path
);

$_SESSION['succes'] = "Compte créé avec succès.";
header("Location: createAccount.php");
exit();
