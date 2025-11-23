<?php
session_start();
require_once("param.inc.php");

// Vérification : L'utilisateur doit être un client
if (!isset($_SESSION['ut_id']) || $_SESSION['ut_role'] != 'client') {
    $_SESSION['erreur'] = "Accès refusé.";
    header('Location: index.php');
    exit();
}

if (isset($_POST['id_question']) && isset($_POST['id_annonce']) && isset($_POST['reponse']) && !empty($_POST['reponse'])) {
    $mysqli = new mysqli($host, $login, $passwd, $dbname);
    $mysqli->set_charset("utf8");

    $id_question = intval($_POST['id_question']);
    $id_annonce = intval($_POST['id_annonce']); 
    $reponse = htmlspecialchars($_POST['reponse']);

    // Mise à jour de la question avec la réponse
    $sql = "UPDATE question SET reponse = ?, date_reponse = NOW() WHERE id = ?";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("si", $reponse, $id_question);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Votre réponse a été publiée.";
        } else {
            $_SESSION['erreur'] = "Erreur lors de l'enregistrement de la réponse.";
        }
        $stmt->close();
    }
    $mysqli->close();
    
    header("Location: detailAnnonce.php?id=" . $id_annonce);
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>