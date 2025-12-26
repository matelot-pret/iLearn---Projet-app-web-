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

if (!isset($_GET['id'], $_GET['action'])) {
    header("Location: admin_ressources.php");
    exit();
}

$ressource_id = (int) $_GET['id'];
$action = $_GET['action'];

if ($ressource_id <= 0 || !in_array($action, ['approuve', 'rejete'], true)) {
    header("Location: admin_ressources.php");
    exit();
}

$db = new Database();

$db->update_ressource_status($ressource_id, $action);


header("Location: admin_ressources.php");
exit();
