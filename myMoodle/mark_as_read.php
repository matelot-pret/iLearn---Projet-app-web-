<?php
require_once 'class/Database.php';

session_start();

if(!isset($_SESSION['user_id'], $_GET['id'])){
    header("Location: index.php");
    exit();
}

$db = new Database();
$db->mark_ressource_as_read((int) $_GET['id'], (int) $_SESSION['user_id']);

header("Location: lesson.php?id=".(int) $_GET['cours_id']);
exit();