<?php
session_start();

// Vérification des droits d'accès
$role = $_SESSION['ut_role'] ?? '';
if (!isset($_SESSION['ut_id']) || ($role != 'admin' && $role != 'administrateur')) {
    $_SESSION['message'] = "Accès refusé.";
    header('Location: index.php');
    exit();
}

// Connexion à la BDD
require_once("param.inc.php");
$mysqli = new mysqli($host, $login, $passwd, $dbname);

if ($mysqli->connect_error) {
    $_SESSION['message'] = "Erreur de connexion BDD.";
    header('Location: gestionAnnonces.php'); // Redirection vers le nouveau nom
    exit();
}

// Récupération des données POST
$action = $_POST['action'] ?? '';
$annonce_id = $_POST['annonce_id'] ?? 0;

// Exécution de l'action demandée
if ($action === 'delete_annonce' && $annonce_id > 0) {
    
    $stmt = $mysqli->prepare("DELETE FROM annonce WHERE an_id = ?");
    
    if ($stmt === false) {
      $_SESSION['message'] = "Erreur SQL (Prepare Delete) : " . $mysqli->error;
    } else {
        $stmt->bind_param("i", $annonce_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "L'annonce n°{$annonce_id} a été supprimée avec succès (les propositions associées ont également été effacées).";
        } else {
            $_SESSION['message'] = "Erreur lors de la suppression de l'annonce : " . $stmt->error;
        }
        $stmt->close();
    }
} else {
    $_SESSION['message'] = "Action ou annonce non valide.";
}

// Redirection vers la page de gestion
$mysqli->close();
header('Location: gestionAnnonces.php'); // Redirection vers le nouveau nom
exit();

?>