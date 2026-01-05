<?php
session_start();

require_once __DIR__ . '/class/Database.php';

if (
    !isset($_SESSION['est_connecte']) ||
    $_SESSION['est_connecte'] !== true
) {
    header("Location: connexion.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$db = new Database();


$cours_id = 0;
$redirect_after = 'add_ressources.php';

if (isset($_POST['cours_id']) && is_numeric($_POST['cours_id'])) {
    $cours_id = (int) $_POST['cours_id'];
    $redirect_after = "add_ressources.php?cours_id=" . $cours_id;
}

else {
    if (!isset($_POST['cours_select']) || $_POST['cours_select'] === '') {
        $_SESSION['erreur'] = "Veuillez sélectionner un cours.";
        header("Location: add_ressources.php");
        exit();
    }

    if ($_POST['cours_select'] === 'autre') {

        $nom     = trim($_POST['new_nom'] ?? '');
        $bloc    = trim($_POST['new_bloc'] ?? '');
        $section = trim($_POST['new_section'] ?? '');

        if ($nom === '' || $bloc === '' || $section === '') {
            $_SESSION['erreur'] = "Tous les champs du nouveau cours sont obligatoires.";
            header("Location: add_ressources.php");
            exit();
        }

        $cours_id = $db->create_cours($nom, $bloc, $section);

    } else {
        $cours_id = (int) $_POST['cours_select'];
    }
}

if ($cours_id <= 0) {
    $_SESSION['erreur'] = "Cours invalide.";
    header("Location: add_ressources.php");
    exit();
}

$titre = trim($_POST['titre'] ?? '');
$type  = $_POST['type'] ?? '';

if ($titre === '' || $type === '') {
    $_SESSION['erreur'] = "Titre et type de ressource obligatoires.";
    header("Location: " . $redirect_after);
    exit();
}

$types_valides = ['pdf', 'jpg', 'png', 'url'];
if (!in_array($type, $types_valides, true)) {
    $_SESSION['erreur'] = "Type de ressource invalide.";
    header("Location: " . $redirect_after);
    exit();
}

$utilisateur_id = (int) ($_SESSION['user_id'] ?? 0);
if ($utilisateur_id <= 0) {
    $_SESSION['erreur'] = "Session utilisateur invalide.";
    header("Location: connexion.php");
    exit();
}

$contenu = null;
$chemin_fichier = null;

if ($type === 'url') {

    $url = trim($_POST['url'] ?? '');

    if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
        $_SESSION['erreur'] = "URL invalide.";
        header("Location: " . $redirect_after);
        exit();
    }

    $contenu = $url;

} else {

    if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['erreur'] = "Erreur lors de l’upload du fichier.";
        header("Location: " . $redirect_after);
        exit();
    }

    if ($_FILES['fichier']['size'] > 10 * 1024 * 1024) {
        $_SESSION['erreur'] = "Fichier trop volumineux (10 Mo max).";
        header("Location: " . $redirect_after);
        exit();
    }

    $upload_dir = 'uploads/ressources/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $extension = strtolower(pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION));
    $filename  = uniqid('ressource_', true) . '.' . $extension;
    $chemin_fichier = $upload_dir . $filename;

    if (!move_uploaded_file($_FILES['fichier']['tmp_name'], $chemin_fichier)) {
        $_SESSION['erreur'] = "Impossible d’enregistrer le fichier.";
        header("Location: " . $redirect_after);
        exit();
    }

    $contenu = $chemin_fichier;
}


try {

    $db->add_ressource(
        $titre,
        $type,
        $contenu,
        $cours_id,
        $utilisateur_id
    );

    $_SESSION['succes'] = "Ressource envoyée avec succès (en attente de validation).";
    header("Location: " . $redirect_after);
    exit();

} catch (Exception $e) {

    if ($chemin_fichier && file_exists($chemin_fichier)) {
        unlink($chemin_fichier);
    }

    $_SESSION['erreur'] = "Erreur lors de l’enregistrement.";
    header("Location: " . $redirect_after);
    exit();
}
