<?php
session_start();
require_once("param.inc.php");

// Vérification : L'utilisateur doit être connecté et être un déménageur
if (!isset($_SESSION['ut_id']) || ($_SESSION['ut_role'] != 'demenageur' && $_SESSION['ut_role'] != 'déménageur')) {
    $_SESSION['erreur'] = "Accès refusé. Seuls les déménageurs peuvent poser des questions.";
    header('Location: annonces.php');
    exit();
}

if (isset($_POST['id_annonce']) && isset($_POST['question']) && !empty($_POST['question'])) {
    $mysqli = new mysqli($host, $login, $passwd, $dbname);
    $mysqli->set_charset("utf8");

    $id_annonce = intval($_POST['id_annonce']);
    $question = htmlspecialchars($_POST['question']);
    $id_demenageur = $_SESSION['ut_id'];

    $sql = "INSERT INTO question (id_annonce, id_demenageur, question, date_question) VALUES (?, ?, ?, NOW())";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("iis", $id_annonce, $id_demenageur, $question);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Votre question a été envoyée au client.";
        } else {
            $_SESSION['erreur'] = "Erreur lors de l'envoi de la question.";
        }
        $stmt->close();
    }
    $mysqli->close();
    
    header("Location: detailAnnonce.php?id=" . $id_annonce);
    exit();
} else {
    header("Location: annonces.php");
    exit();
}
?>