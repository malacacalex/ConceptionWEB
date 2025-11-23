<?php
session_start();
require_once("param.inc.php");

// Vérifier que l'utilisateur est un client
if (!isset($_SESSION['ut_id']) || $_SESSION['ut_role'] != 'client') {
    $_SESSION['erreur'] = "Accès refusé.";
    header('Location: index.php');
    exit();
}

// RÉCUPÉRATION ET VÉRIFICATION DES DONNÉES
if (isset($_POST['id_proposition']) && isset($_POST['id_annonce'])) {
    
    $id_proposition = intval($_POST['id_proposition']);
    $id_annonce = intval($_POST['id_annonce']);
    $id_client_connecte = $_SESSION['ut_id'];

    // Connexion BDD
    $mysqli = new mysqli($host, $login, $passwd, $dbname);
    if ($mysqli->connect_error) {
        $_SESSION['erreur'] = "Erreur de connexion BDD.";
        header("Location: detailAnnonce.php?id=" . $id_annonce);
        exit();
    }
    $mysqli->set_charset("utf8");

    // VÉRIFICATION DE PROPRIÉTÉ
    $check_sql = "SELECT an_id FROM annonce WHERE an_id = ? AND an_id_client = ?";
    $stmt_check = $mysqli->prepare($check_sql);
    $stmt_check->bind_param("ii", $id_annonce, $id_client_connecte);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows == 0) {
        $_SESSION['erreur'] = "Vous n'êtes pas autorisé à modifier cette annonce.";
        $stmt_check->close();
        $mysqli->close();
        header("Location: index.php");
        exit();
    }
    $stmt_check->close();

    // MISE À JOUR DE LA PROPOSITION (Statut -> acceptée)
    $sql_prop = "UPDATE proposition SET pr_statut = 'acceptée' WHERE pr_id = ?";
    if ($stmt_prop = $mysqli->prepare($sql_prop)) {
        $stmt_prop->bind_param("i", $id_proposition);
        $stmt_prop->execute();
        $stmt_prop->close();
    }

    // MISE À JOUR DE L'ANNONCE (Statut -> en cours)
    $sql_annonce = "UPDATE annonce SET an_statut = 'en cours' WHERE an_id = ?";
    if ($stmt_annonce = $mysqli->prepare($sql_annonce)) {
        $stmt_annonce->bind_param("i", $id_annonce);
        $stmt_annonce->execute();
        $stmt_annonce->close();
    }


        // REFUSER LES AUTRES PROPOSITIONS ?

    $_SESSION['message'] = "Proposition acceptée ! L'annonce est passée 'En cours'.";
    $mysqli->close();
    
    header("Location: detailAnnonce.php?id=" . $id_annonce);
    exit();

} else {
    header("Location: index.php");
    exit();
}
?>