<?php
session_start();

// Vérification des droits d'accès
$role = $_SESSION['ut_role'] ?? '';
if (!isset($_SESSION['ut_id']) || ($role != 'admin' && $role != 'administrateur')) {
    $_SESSION['message'] = "Accès refusé.";
    header('Location: index.php');
    exit();
}

// Vérification des données POST
$annonce_id = $_POST['an_id'] ?? 0;
$annonce_id = intval($annonce_id);
$titre = trim($_POST['an_titre'] ?? '');
$ville_depart = trim($_POST['an_ville_depart'] ?? '');
$ville_arrivee = trim($_POST['an_ville_arrivee'] ?? '');
$date_demenagement = $_POST['an_date_demenagement'] ?? '';
$volume = $_POST['an_volume'] ?? 0;
$statut = $_POST['an_statut'] ?? '';
$description = trim($_POST['an_description'] ?? '');

if ($annonce_id <= 0 || empty($titre) || empty($ville_depart) || empty($ville_arrivee) || empty($date_demenagement) || empty($statut) || empty($description)) {
    $_SESSION['message'] = "Erreur : Tous les champs obligatoires ne sont pas remplis ou l'ID est invalide.";
    header('Location: gestionAnnonces.php');
    exit();
}

// Validation simple du statut
$statuts_possibles = ['ouverte', 'en cours', 'terminée', 'annulée'];
if (!in_array($statut, $statuts_possibles)) {
    $_SESSION['message'] = "Erreur : Statut non valide.";
    header('Location: gestionAnnonces.php');
    exit();
}

// Connexion à la BDD
require_once("param.inc.php");
$mysqli = new mysqli($host, $login, $passwd, $dbname);

if ($mysqli->connect_error) {
    $_SESSION['message'] = "Erreur de connexion BDD.";
    header('Location: gestionAnnonces.php');
    exit();
}

// Préparation et exécution de la mise à jour
$stmt = $mysqli->prepare("
    UPDATE annonce 
    SET 
        an_titre = ?, 
        an_ville_depart = ?, 
        an_ville_arrivee = ?, 
        an_date_demenagement = ?, 
        an_volume = ?, 
        an_statut = ?, 
        an_description = ?
    WHERE an_id = ?
");

if ($stmt === false) {
    $_SESSION['message'] = "Erreur SQL (Prepare Update) : " . $mysqli->error;
} else {
    // an_titre(s), an_ville_depart(s), an_ville_arrivee(s), an_date_demenagement(s), an_volume(d), an_statut(s), an_description(s), an_id(i)
    $stmt->bind_param(
        "ssssdssi", 
        $titre, 
        $ville_depart, 
        $ville_arrivee, 
        $date_demenagement, 
        $volume, 
        $statut, 
        $description, 
        $annonce_id
    );
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "L'annonce n°{$annonce_id} a été modifiée avec succès.";
    } else {
        $_SESSION['message'] = "Erreur lors de la modification de l'annonce : " . $stmt->error;
    }
    $stmt->close();
}

// Redirection
$mysqli->close();
header('Location: gestionAnnonces.php');
exit();
?>