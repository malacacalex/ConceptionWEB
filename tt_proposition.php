<?php
session_start();
require_once("param.inc.php");

// Vérifier que l'utilisateur est un déménageur
if (!isset($_SESSION['ut_id']) || ($_SESSION['ut_role'] != 'déménageur' && $_SESSION['ut_role'] != 'demenageur')) {
    $_SESSION['erreur'] = "Accès refusé. Seuls les déménageurs peuvent faire des propositions.";
    header('Location: annonces.php');
    exit();
}

// RÉCUPÉRATION DES DONNÉES
if (isset($_POST['id_annonce']) && isset($_POST['prix'])) {
    
    $id_annonce = intval($_POST['id_annonce']);
    $prix = floatval($_POST['prix']);
    $id_demenageur = $_SESSION['ut_id'];
    
    // Connexion BDD
    $mysqli = new mysqli($host, $login, $passwd, $dbname);
    if ($mysqli->connect_error) {
        $_SESSION['erreur'] = "Erreur de connexion BDD.";
        header("Location: detailAnnonce.php?id=" . $id_annonce);
        exit();
    }
    $mysqli->set_charset("utf8");

    // INSERTION DE LA PROPOSITION

    $sql = "INSERT INTO proposition (pr_id_annonce, pr_id_demenageur, pr_prix_propose, pr_date_proposition, pr_statut) 
            VALUES (?, ?, ?, NOW(), 'en attente')";
    
    if ($stmt = $mysqli->prepare($sql)) {
        // 'i' = int, 'i' = int, 'd' = double (prix)
        $stmt->bind_param("iid", $id_annonce, $id_demenageur, $prix);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Votre proposition de " . $prix . " € a bien été envoyée !";
        } else {
            $_SESSION['erreur'] = "Erreur lors de l'enregistrement de la proposition.";
        }
        $stmt->close();
    } else {
        $_SESSION['erreur'] = "Erreur SQL : " . $mysqli->error;
    }
    
    $mysqli->close();
    
    // Retour à la page de l'annonce
    header("Location: detailAnnonce.php?id=" . $id_annonce);
    exit();

} else {
    // Si on arrive ici sans données
    header("Location: annonces.php");
    exit();
}
?>