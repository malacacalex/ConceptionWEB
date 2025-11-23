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
$id_demenageur = isset($_POST['id_demenageur']) ? intval($_POST['id_demenageur']) : 0;
$note = isset($_POST['note']) ? intval($_POST['note']) : 0;
$commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';

// 2. Vérification des données
if ($id_annonce > 0 && $id_demenageur > 0 && $note >= 1 && $note <= 5) {
    
    $mysqli = new mysqli($host, $login, $passwd, $dbname);
    $mysqli->set_charset("utf8");

    // 3. Vérifier qu'on ne note pas deux fois LE MÊME DÉMÉNAGEUR
    // CORRECTION : On vérifie l'annonce ET le déménageur
    $check = $mysqli->prepare("SELECT ev_id FROM evaluation WHERE ev_id_annonce = ? AND ev_id_demenageur = ?");
    $check->bind_param("ii", $id_annonce, $id_demenageur);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $_SESSION['message'] = "Vous avez déjà noté ce déménageur.";
        $check->close();
        $mysqli->close();
        header("Location: detailAnnonce.php?id=$id_annonce");
        exit();
    }
    $check->close();

    // 4. Insertion
    $sql = "INSERT INTO evaluation (ev_id_annonce, ev_id_demenageur, ev_id_client, ev_note, ev_commentaire) 
            VALUES (?, ?, ?, ?, ?)";
            
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("iiiis", $id_annonce, $id_demenageur, $id_client, $note, $commentaire);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Merci ! Votre évaluation a été enregistrée.";
        } else {
            $_SESSION['message'] = "Erreur lors de l'enregistrement : " . $stmt->error;
        }
        $stmt->close();
    }
    $mysqli->close();
} else {
    $_SESSION['message'] = "Données invalides (Note ou ID manquant).";
}

header("Location: detailAnnonce.php?id=$id_annonce");
exit();
?>