<?php
session_start();

// 1. Vérification des droits d'accès
$role = $_SESSION['ut_role'] ?? '';
// Utilisation d'une vérification plus robuste
if (!isset($_SESSION['ut_id']) || ($role != 'admin' && $role != 'administrateur')) {
    $_SESSION['message'] = "Accès refusé.";
    header('Location: index.php');
    exit();
}

// 2. Connexion à la BDD
require_once("param.inc.php");
$mysqli = new mysqli($host, $login, $passwd, $dbname);

if ($mysqli->connect_error) {
    $_SESSION['message'] = "Erreur de connexion BDD.";
    header('Location: gestionUtilisateurs.php'); 
    exit();
}

// 3. Récupération des données POST
$action = $_POST['action'] ?? '';
$user_id = $_POST['user_id'] ?? 0;
$admin_id = $_SESSION['ut_id']; // ID de l'admin qui fait l'action

// 4. Sécurité : Vérifier que l'admin n'essaie pas de s'auto-modifier
if ($user_id == $admin_id) {
    $_SESSION['message'] = "Erreur : Vous ne pouvez pas modifier votre propre compte depuis cette interface.";
    header('Location: gestionUtilisateurs.php');
    $mysqli->close();
    exit();
}

// 5. Exécution de l'action demandée
switch ($action) {
    case 'toggle_status':
        $new_status = $_POST['new_status'] ?? 0;
        // S'assurer que le statut est bien 0 ou 1
        $new_status_validated = ($new_status == 1) ? 1 : 0; 
        
        $stmt = $mysqli->prepare("UPDATE utilisateur SET ut_statut = ? WHERE ut_id = ?");
        // Vérification de la préparation de la requête
        if ($stmt === false) {
          $_SESSION['message'] = "Erreur SQL (Prepare Status) : " . $mysqli->error;
          break; // Sortir du switch
        }
        $stmt->bind_param("ii", $new_status_validated, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Le statut de l'utilisateur a été mis à jour.";
        } else {
            $_SESSION['message'] = "Erreur lors de la mise à jour du statut : " . $stmt->error;
        }
        $stmt->close();
        break;

    case 'delete_user':
        $stmt = $mysqli->prepare("DELETE FROM utilisateur WHERE ut_id = ?");
        // Vérification de la préparation de la requête
        if ($stmt === false) {
          $_SESSION['message'] = "Erreur SQL (Prepare Delete) : " . $mysqli->error;
          break; // Sortir du switch
        }
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "L'utilisateur a été supprimé avec succès.";
        } else {
            $_SESSION['message'] = "Erreur lors de la suppression de l'utilisateur : " . $stmt->error;
        }
        $stmt->close();
        break;

    default:
        $_SESSION['message'] = "Action non reconnue.";
        break;
}

// 6. Redirection vers la page de gestion
$mysqli->close();
header('Location: gestionUtilisateurs.php');
exit();

?>