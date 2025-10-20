<?php
session_start();

// Vérification de connexion utilisateur
if (!isset($_SESSION['ut_id'])) {
    $_SESSION['erreur'] = "Vous devez être connecté pour publier une annonce.";
    header('Location: connexion.php');
    exit();
}

// Récupération des données du formulaire avec protection
$an_titre = htmlentities($_POST['an_titre']);
$an_description = htmlentities($_POST['an_description']);
$an_date_demenagement = htmlentities($_POST['an_date_demenagement']);
$an_ville_depart = htmlentities($_POST['an_ville_depart']);
$an_ville_arrivee = htmlentities($_POST['an_ville_arrivee']);
$an_type_logement_depart = htmlentities($_POST['an_type_logement_depart']);
$an_etage_depart = intval($_POST['an_etage_depart']);
$an_ascenseur_depart = intval($_POST['an_ascenseur_depart']);
$an_type_logement_arrivee = htmlentities($_POST['an_type_logement_arrivee']);
$an_etage_arrivee = intval($_POST['an_etage_arrivee']);
$an_ascenseur_arrivee = intval($_POST['an_ascenseur_arrivee']);
$an_statut = htmlentities($_POST['an_statut']);
$an_date_creation = htmlentities($_POST['an_date_creation']);
$an_id_client = intval($_SESSION['ut_id']); // depuis la session utilisateur

// Connexion BDD
require_once("param.inc.php");
$mysqli = new mysqli($host, $login, $passwd, $dbname);

if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Erreur de connexion à la base de données 😢";
    header('Location: form_nvlAnnonce.php');
    exit();
}

// Requête préparée d’insertion
$sql = "INSERT INTO annonce (
            an_id_client,
            an_titre,
            an_description,
            an_date_demenagement,
            an_ville_depart,
            an_ville_arrivee,
            an_type_logement_depart,
            an_etage_depart,
            an_ascenseur_depart,
            an_type_logement_arrivee,
            an_etage_arrivee,
            an_ascenseur_arrivee,
            an_statut,
            an_date_creation
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

if ($stmt = $mysqli->prepare($sql)) {

    $stmt->bind_param(
        "issssssiiisiis",
        $an_id_client,
        $an_titre,
        $an_description,
        $an_date_demenagement,
        $an_ville_depart,
        $an_ville_arrivee,
        $an_type_logement_depart,
        $an_etage_depart,
        $an_ascenseur_depart,
        $an_type_logement_arrivee,
        $an_etage_arrivee,
        $an_ascenseur_arrivee,
        $an_statut,
        $an_date_creation
    );

    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Annonce publiée avec succès !";
    } else {
        $_SESSION['erreur'] = "❌ Échec lors de l'enregistrement de l'annonce.";
    }

    $stmt->close();
} else {
    $_SESSION['erreur'] = "❌ Erreur lors de la préparation de la requête.";
}

$mysqli->close();

// Redirection
header('Location: annonces.php');
exit();
?>
