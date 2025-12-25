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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_ressources.php");
    exit();
}

$ressource_id = (int) ($_POST['ressource_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($ressource_id <= 0 || !in_array($action, ['approuve', 'rejete'])) {
    header("Location: admin_ressources.php");
    exit();
}

$statut = $action; // approuve ou rejete

$stmt = $connexion->prepare("
    UPDATE ressources
    SET statut = :statut
    WHERE id = :id
");

$stmt->bindValue(':statut', $statut, PDO::PARAM_STR);
$stmt->bindValue(':id', $ressource_id, PDO::PARAM_INT);
$stmt->execute();

header("Location: admin_ressources.php");
exit();
?>