<?php
session_start();
require_once __DIR__. '/../class/Database.php';


if(!isset($_SESSION['user_id'], $_GET['id'])){
    header("Location: /myMoodle/public/index.php");
    exit();
}

$db = new Database();
$db->mark_ressource_as_read((int) $_GET['id'], (int) $_SESSION['user_id']);

header("Location: /myMoodle/public/lesson.php?id=".(int) $_GET['cours_id']);
exit();