<?php
session_start();
require_once("param.inc.php");

// 1. Vérification Client Connecté
if (!isset($_SESSION['ut_id']) || $_SESSION['ut_role'] != 'client') {
    $_SESSION['message'] = "Accès refusé.";
    header('Location: index.php');
    exit();
}

$id_client = $_SESSION['ut_id'];
$id_annonce = isset($_POST['id_annonce']) ? intval($_POST['id_annonce']) : 0;

// 2. Vérification de l'ID
if ($id_annonce <= 0) {
    $_SESSION['message'] = "ID d'annonce invalide.";
    header('Location: suiviDemande.php');
    exit();
}

// 3. Connexion à la base
$mysqli = new mysqli($host, $login, $passwd, $dbname);
if ($mysqli->connect_error) {
    $_SESSION['message'] = "Erreur de connexion à la base de données.";
    header('Location: suiviDemande.php');
    exit();
}
$mysqli->set_charset("utf8");

// 4. Mise à jour du statut
// On passe le statut à 'terminée' si l'annonce appartient bien au client et qu'elle était 'en cours'
$sql = "UPDATE annonce 
        SET an_statut = 'terminée' 
        WHERE an_id = ? AND an_id_client = ? AND an_statut = 'en cours'";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("ii", $id_annonce, $id_client);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Déménagement terminé ! Vous pouvez maintenant noter la prestation.";
        } else {
            // Cas rare : Annonce déjà terminée entre temps ou erreur d'ID
            // On ne met pas de message d'erreur bloquant, on redirige juste.
        }
    } else {
        $_SESSION['message'] = "Erreur technique lors de la mise à jour.";
    }
    $stmt->close();
}

$mysqli->close();

// 5. Redirection vers le détail pour afficher le formulaire d'évaluation
header("Location: detailAnnonce.php?id=$id_annonce");
exit();
?>