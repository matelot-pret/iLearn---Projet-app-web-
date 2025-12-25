<?php 
session_start();

if (!isset($_SESSION['est_connecte']) || $_SESSION['est_connecte'] !== true || !isset($_SESSION['est_admin']) || $_SESSION['est_admin'] !== true) {
    header("Location: connexion.php");
    exit();
}

$host = 'localhost';
$dbname = 'projetphp';
$db_user = 'BDPatrickProjet25';
$db_pass = 'Samourai3';

try {
    $connexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_user, $db_pass);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    die("Erreur de connexion : ".$$e->getMessage());
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $matricule = trim($_POST['matricule'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'];

    if (empty($nom) || empty($prenom) || empty($matricule) || empty($password)) {
        $_SESSION['erreur'] = "Tous les champs marqués (*) sont obligatoires";
        header("Location: createAccount.php");
        exit();
    }

    if ($password !== $password_confirm) {
        $_SESSION['erreur'] = "Les mots de passe ne correspondent pas";
        header("Location: createAccounts.php");
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['erreur'] = "Le mot de passe doit contenir au moins 8 caractères";
        header("Location: createAccounts.php");
        exit();
    }

    $check = $connexion->prepare("SELECT id FROM utilisateurs WHERE matricule = :matricule");
    $check->bindValue(':matricule', $matricule, PDO::PARAM_STR);
    $check->execute();

    if ($check->fetch()) {
        $_SESSION['erreur'] = "Ce nom d'utilisateur existe déjà";
        header("Location: createAccounts.php");
        exit();
    }

    $avatar_path = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $max_size = 2 * 1024 * 1024;
        
        if (!in_array($_FILES['avatar']['type'], $allowed_types)) {
            $_SESSION['erreur'] = "Format d'avatar invalide. Utilisez JPG ou PNG uniquement.";
            header("Location: createAccounts.php");
            exit();
        }
        
        if ($_FILES['avatar']['size'] > $max_size) {
            $_SESSION['erreur'] = "L'avatar est trop volumineux (max 2Mo)";
            header("Location: createAccounts.php");
            exit();
        }
        
        $upload_dir = 'uploads/avatars/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatar_filename = uniqid('avatar_') . '.' . $extension;
        $avatar_path = $upload_dir . $avatar_filename;
        
        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
            $_SESSION['erreur'] = "Erreur lors de l'upload de l'avatar";
            header("Location: createAccounts.php");
            exit();
        }
    }

    $hash_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $insert = $connexion->prepare("INSERT INTO utilisateurs (nom, prenom, matricule, hashMDP, avatar, est_admin) 
                                        VALUES (:nom, :prenom, :matricule, :hashMDP, :avatar, 0)");
        $insert->bindValue(':nom', $nom, PDO::PARAM_STR);
        $insert->bindValue(':prenom', $prenom, PDO::PARAM_STR);
        $insert->bindValue(':matricule', $matricule, PDO::PARAM_STR);
        $insert->bindValue(':hashMDP', $hash_password, PDO::PARAM_STR);
        $insert->bindValue(':avatar', $avatar_path, PDO::PARAM_STR);
        $insert->execute();
        
        $_SESSION['succes'] = "Le compte pour $prenom $nom a été créé avec succès !";
        header("Location: createAccounts.php");
        exit();
        
    } catch(PDOException $e) {
        $_SESSION['erreur'] = "Erreur lors de la création du compte : " . $e->getMessage();
        header("Location: createAccounts.php");
        exit();
    }
}

?>