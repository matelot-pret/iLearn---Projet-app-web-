<?php
session_start();

require_once __DIR__ . '/../class/Database.php';

$connection_path = "/myMoodle/public/connexion.php";
$index_path = "/myMoodle/public/index.php";
$add_ressources_path = "/myMoodle/public/add_ressources.php";

/* =========================
   SÉCURITÉ
========================= */
if (
    !isset($_SESSION['est_connecte']) ||
    $_SESSION['est_connecte'] !== true
) {
    header("Location: $connection_path");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $index_path");
    exit();
}

$db = new Database();

/* =========================
   DÉTERMINATION DU COURS
========================= */
$cours_id = 0;
$redirect_after = $add_ressources_path;

if (isset($_POST['cours_id']) && is_numeric($_POST['cours_id'])) {
    $cours_id = (int) $_POST['cours_id'];
    $redirect_after = $add_ressources_path . "?cours_id=" . $cours_id;
} else {

    if (empty($_POST['cours_select'])) {
        $_SESSION['erreur'] = "Veuillez sélectionner un cours.";
        header("Location: $add_ressources_path");
        exit();
    }

    if ($_POST['cours_select'] === 'autre') {

        $nom     = trim($_POST['new_nom'] ?? '');
        $bloc    = trim($_POST['new_bloc'] ?? '');
        $section = trim($_POST['new_section'] ?? '');

        if ($nom === '' || $bloc === '' || $section === '') {
            $_SESSION['erreur'] = "Tous les champs du nouveau cours sont obligatoires.";
            header("Location: $add_ressources_path");
            exit();
        }

        $cours_id = $db->create_cours($nom, $bloc, $section);

    } else {
        $cours_id = (int) $_POST['cours_select'];
    }
}

if ($cours_id <= 0) {
    $_SESSION['erreur'] = "Cours invalide.";
    header("Location: $add_ressources_path");
    exit();
}

/* =========================
   DONNÉES RESSOURCE
========================= */
$titre = trim($_POST['titre'] ?? '');
$type  = $_POST['type'] ?? '';

if ($titre === '' || $type === '') {
    $_SESSION['erreur'] = "Titre et type de ressource obligatoires.";
    header("Location: $redirect_after");
    exit();
}

$types_valides = ['pdf', 'jpg', 'png', 'url'];
if (!in_array($type, $types_valides, true)) {
    $_SESSION['erreur'] = "Type de ressource invalide.";
    header("Location: $redirect_after");
    exit();
}

$utilisateur_id = (int) ($_SESSION['user_id'] ?? 0);
if ($utilisateur_id <= 0) {
    $_SESSION['erreur'] = "Session utilisateur invalide.";
    header("Location: $connection_path");
    exit();
}

/* =========================
   CONTENU
========================= */
$contenu = null;
$chemin_fichier = null;

if ($type === 'url') {

    $url = trim($_POST['url'] ?? '');

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $_SESSION['erreur'] = "URL invalide.";
        header("Location: $redirect_after");
        exit();
    }

    $contenu = $url;

} else {

    if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['erreur'] = "Erreur lors de l’upload du fichier.";
        header("Location: $redirect_after");
        exit();
    }

    if ($_FILES['fichier']['size'] > 10 * 1024 * 1024) {
        $_SESSION['erreur'] = "Fichier trop volumineux (10 Mo max).";
        header("Location: $redirect_after");
        exit();
    }

    $original_ext = strtolower(pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION));

    $mime = mime_content_type($_FILES['fichier']['tmp_name']);

    $map = [
        'pdf' => ['application/pdf', ['pdf']],
        'jpg' => ['image/jpeg', ['jpg','jpeg']],
        'png' => ['image/png', ['png']],
    ];

    if (!isset($map[$type]) ||
        $mime !== $map[$type][0] ||
        !in_array($original_ext, $map[$type][1], true)
    ) {
        $_SESSION['erreur'] = "Type de fichier non conforme.";
        header("Location: $redirect_after");
        exit();
    }

    $upload_dir = 'uploads/ressources/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $filename = uniqid('ressource_', true) . '.' . $original_ext;
    $chemin_fichier = $upload_dir . $filename;

    if (!move_uploaded_file($_FILES['fichier']['tmp_name'], $chemin_fichier)) {
        $_SESSION['erreur'] = "Impossible d’enregistrer le fichier.";
        header("Location: $redirect_after");
        exit();
    }

    $contenu = $chemin_fichier;
}

/* =========================
   INSERTION
========================= */
try {

    $db->add_ressource(
        $titre,
        $type,
        $contenu,
        $cours_id,
        $utilisateur_id
    );

    $_SESSION['succes'] = "Ressource envoyée avec succès (en attente de validation).";
    header("Location: $redirect_after");
    exit();

} catch (Exception $e) {

    if ($chemin_fichier && file_exists($chemin_fichier)) {
        unlink($chemin_fichier);
    }

    $_SESSION['erreur'] = "Erreur lors de l’enregistrement.";
    header("Location: $redirect_after");
    exit();
}
