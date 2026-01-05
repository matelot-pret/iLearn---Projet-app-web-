<?php
session_start();

$create_account_path = "/myMoodle/public/createAccount.php";

require_once __DIR__ . '/../class/Database.php';

/* =========================
   SÉCURITÉ
========================= */
if (
    !isset($_SESSION['est_connecte']) ||
    $_SESSION['est_connecte'] !== true ||
    !isset($_SESSION['est_admin']) ||
    $_SESSION['est_admin'] !== true
) {
    header("Location: $create_account_path");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $create_account_path");
    exit();
}

/* =========================
   DONNÉES FORMULAIRE
========================= */
$nom              = trim($_POST['nom'] ?? '');
$prenom           = trim($_POST['prenom'] ?? '');
$matricule        = trim($_POST['matricule'] ?? '');
$password         = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$est_admin        = ($_POST['est_admin'] ?? '0') === '1' ? 1 : 0;

if ($nom === '' || $prenom === '' || $matricule === '' || $password === '' || $password_confirm === '') {
    $_SESSION['erreur'] = "Tous les champs obligatoires doivent être remplis.";
    header("Location: $create_account_path");
    exit();
}

if ($password !== $password_confirm) {
    $_SESSION['erreur'] = "Les mots de passe ne correspondent pas.";
    header("Location: $create_account_path");
    exit();
}

if (strlen($password) < 8) {
    $_SESSION['erreur'] = "Le mot de passe doit contenir au moins 8 caractères.";
    header("Location: $create_account_path");
    exit();
}

/* =========================
   BASE DE DONNÉES
========================= */
$db = new Database();

if ($db->get_user_by_matricule($matricule)) {
    $_SESSION['erreur'] = "Ce matricule existe déjà.";
    header("Location: $create_account_path");
    exit();
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

/* =========================
   UPLOAD AVATAR (OPTIONNEL)
========================= */
$avatar_path = null;

if (!empty($_FILES['avatar']['name'])) {

    if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['erreur'] = "Erreur lors de l’upload de l’avatar.";
        header("Location: $create_account_path");
        exit();
    }

    if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
        $_SESSION['erreur'] = "Avatar trop volumineux (max 2 Mo).";
        header("Location: $create_account_path");
        exit();
    }

    $original_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png'];

    if (!in_array($original_extension, $allowed_extensions, true)) {
        $_SESSION['erreur'] = "Extension de fichier non autorisée.";
        header("Location: $create_account_path");
        exit();
    }

    $mime = mime_content_type($_FILES['avatar']['tmp_name']);
    if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
        $_SESSION['erreur'] = "Type de fichier invalide.";
        header("Location: $create_account_path");
        exit();
    }

    $extension = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
    };

    if (
        ($extension === 'jpg' && !in_array($original_extension, ['jpg', 'jpeg'], true)) ||
        ($extension === 'png' && $original_extension !== 'png')
    ) {
        $_SESSION['erreur'] = "Incohérence entre le type du fichier et son extension.";
        header("Location: $create_account_path");
        exit();
    }

    $dir = 'uploads/avatars/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $filename = uniqid('avatar_', true) . '.' . $extension;
    $avatar_path = $dir . $filename;

    if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
        $_SESSION['erreur'] = "Échec de l’enregistrement de l’avatar.";
        header("Location: $create_account_path");
        exit();
    }
}

/* =========================
   CRÉATION UTILISATEUR
========================= */
$db->create_user(
    $nom,
    $prenom,
    $matricule,
    $password_hash,
    $est_admin,
    $avatar_path
);

$_SESSION['succes'] = "Compte créé avec succès.";
header("Location: $create_account_path");
exit();
