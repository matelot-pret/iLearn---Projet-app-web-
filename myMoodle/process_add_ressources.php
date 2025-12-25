<?php
session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['est_connecte']) || $_SESSION['est_connecte'] !== true) {
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
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cours_id = (int) $_POST['cours_id'];
    if ($cours_id <= 0) {
        $_SESSION['erreur'] = "Cours invalide.";
        header("Location: index.php");
        exit();
    }

    $titre = trim($_POST['titre'] ?? '');
    $type = $_POST['type'] ?? '';
    $url = trim($_POST['contenu'] ?? '');
    
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['erreur'] = "Session invalide, veuillez vous reconnecter.";
        header("Location: connexion.php");
        exit();
    }
    $utilisateur_id = $_SESSION['user_id'];
    
    // Validation des champs
    if (empty($titre) || empty($type)) {
        $_SESSION['erreur'] = "Tous les champs obligatoires doivent être remplis";
        header("Location: add_ressources.php?cours_id=" . $cours_id);
        exit();
    }
    
    // Vérifier que le type est valide
    $types_valides = ['pdf', 'jpg', 'png', 'url'];
    if (!in_array($type, $types_valides)) {
        $_SESSION['erreur'] = "Type de ressource invalide";
        header("Location: add_ressources.php?cours_id=" . $cours_id);
        exit();
    }
    
    $chemin = null;
    $url_finale = null;
    
    if ($type === 'url') {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            $_SESSION['erreur'] = "URL invalide";
            header("Location: add_ressources.php?cours_id=" . $cours_id);
            exit();
        }
        $url_finale = $url;
        
    } else {
        if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['erreur'] = "Erreur lors de l'upload du fichier";
            header("Location: add_ressources.php?cours_id=" . $cours_id);
            exit();
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['fichier']['tmp_name']);
        finfo_close($finfo);
        
        $mimes_valides = [
            'pdf' => 'application/pdf',
            'jpg' => ['image/jpeg', 'image/jpg'],
            'png' => 'image/png'
        ];
        
        $mime_attendu = $mimes_valides[$type];
        if (is_array($mime_attendu)) {
            if (!in_array($mime, $mime_attendu)) {
                $_SESSION['erreur'] = "Le fichier n'est pas du bon type";
                header("Location: add_ressources.php?cours_id=" . $cours_id);
                exit();
            }
        } else {
            if ($mime !== $mime_attendu) {
                $_SESSION['erreur'] = "Le fichier n'est pas du bon type";
                header("Location: add_ressources.php?cours_id=" . $cours_id);
                exit();
            }
        }
        
        $max_size = 10 * 1024 * 1024;
        if ($_FILES['fichier']['size'] > $max_size) {
            $_SESSION['erreur'] = "Le fichier est trop volumineux (max 10 Mo)";
            header("Location: add_ressources.php?cours_id=" . $cours_id);
            exit();
        }
        
        $upload_dir = 'uploads/ressources/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $extension = pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('ressource_') . '.' . $extension;
        $chemin = $upload_dir . $filename;
        
        if (!move_uploaded_file($_FILES['fichier']['tmp_name'], $chemin)) {
            $_SESSION['erreur'] = "Erreur lors de l'enregistrement du fichier";
            header("Location: add_ressources.php?cours_id=" . $cours_id);
            exit();
        }
    }

    $contenu = ($type === 'url') ? $url_finale : $chemin;
    
    try {
        $insert = $connexion->prepare("INSERT INTO ressources 
                                        (titre, type, contenu, cours_id, utilisateur_id, statut, date_ajout)
                                        VALUES (:titre, :type, :contenu, :cours_id, :utilisateur_id, 'en_attente', NOW())");

        $insert->bindValue(':titre', $titre, PDO::PARAM_STR);
        $insert->bindValue(':type', $type, PDO::PARAM_STR);
        $insert->bindValue(':contenu', $contenu, PDO::PARAM_STR);
        $insert->bindValue(':cours_id', $cours_id, PDO::PARAM_INT);
        $insert->bindValue(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);

        $insert->execute();
        
        $_SESSION['succes'] = "Votre ressource a été soumise avec succès ! Elle sera visible après validation par un administrateur.";
        header("Location: add_ressources.php?cours_id=" . $cours_id);
        exit();
        
    } catch(PDOException $e) {
        if ($chemin && file_exists($chemin)) {
            unlink($chemin);
        }
        $_SESSION['erreur'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        header("Location: add_ressources.php?cours_id=" . $cours_id);
        exit();
    }
}
?>