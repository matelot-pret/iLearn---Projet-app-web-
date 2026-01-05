<?php
session_start();
require_once __DIR__ . '/../app/class/Database.php';

if (!isset($_SESSION['est_connecte'])) {
    http_response_code(403);
    exit("Accès interdit");
}

$db = new Database();
$ressource = $db->get_ressource_by_id((int)$_GET['id']);

$path = $ressource['contenu'];

if (!file_exists($path)) {
    http_response_code(404);
    exit("Fichier introuvable");
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($path) . '"');
readfile($path);
exit;
