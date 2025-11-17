<?php
session_start();

if (!isset($_SESSION['ut_id']) || $_SESSION['ut_role'] !== 'client') {
    $_SESSION['erreur'] = "Acces non autorise.";
    header('Location: index.php');
    exit();
}

$an_titre = htmlentities($_POST['an_titre']);
$an_description = htmlentities($_POST['an_description']);
$an_date_demenagement = htmlentities($_POST['an_date_demenagement']);
$an_ville_depart = htmlentities($_POST['an_ville_depart']);
$an_ville_arrivee = htmlentities($_POST['an_ville_arrivee']);
$an_ascenseur_depart = intval($_POST['an_ascenseur_depart']);
$an_ascenseur_arrivee = intval($_POST['an_ascenseur_arrivee']);
$an_heure_debut = htmlentities($_POST['an_heure_debut']);
$an_nombre_demenageurs = intval($_POST['an_nombre_demenageurs']);
$an_type_logement_depart = $_POST['an_type_logement_depart'];
$an_type_logement_arrivee = $_POST['an_type_logement_arrivee'];
$logements_valides = ['maison', 'appartement'];
if (!in_array($an_type_logement_depart, $logements_valides) || !in_array($an_type_logement_arrivee, $logements_valides)) {
    $_SESSION['erreur'] = "Type de logement invalide.";
    header('Location: nvlDemande.php');
    exit();
}
$an_etage_depart = empty($_POST['an_etage_depart']) ? NULL : intval($_POST['an_etage_depart']);
$an_etage_arrivee = empty($_POST['an_etage_arrivee']) ? NULL : intval($_POST['an_etage_arrivee']);
$an_volume = empty($_POST['an_volume']) ? NULL : floatval($_POST['an_volume']);
$an_poids = empty($_POST['an_poids']) ? NULL : floatval($_POST['an_poids']);
$an_id_client = intval($_SESSION['ut_id']);
$an_statut = 'ouverte';
$an_date_creation = date("Y-m-d");

require_once("param.inc.php");
$mysqli = new mysqli($host, $login, $passwd, $dbname);

if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Erreur de connexion a la base de donnees ";
    header('Location: nvlDemande.php');
    exit();
}


$mysqli->set_charset("utf8");
$sql = "INSERT INTO annonce (
            an_id_client, an_titre, an_description, an_date_demenagement, an_heure_debut,
            an_ville_depart, an_ville_arrivee, an_type_logement_depart, an_etage_depart, an_ascenseur_depart,
            an_type_logement_arrivee, an_etage_arrivee, an_ascenseur_arrivee, an_volume, an_poids,
            an_nombre_demenageurs, an_statut, an_date_creation
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param(
        "isssssssiisiiddiss",
        $an_id_client, $an_titre, $an_description, $an_date_demenagement, $an_heure_debut,
        $an_ville_depart, $an_ville_arrivee, $an_type_logement_depart,
        $an_etage_depart, $an_ascenseur_depart,
        $an_type_logement_arrivee,
        $an_etage_arrivee, $an_ascenseur_arrivee, $an_volume, $an_poids,
        $an_nombre_demenageurs, 
        $an_statut, 
        $an_date_creation 
    );


    if ($stmt->execute()) {
        $annonce_id = $mysqli->insert_id;
        $stmt->close();

        $upload_dir = 'uploads/';
        $images_upload_succes = true;

        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; 

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] == 0) {
                    $file_type = $_FILES['images']['type'][$key];
                    $file_size = $_FILES['images']['size'][$key];

                    if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                        
                        $file_extension = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                        $new_filename = 'annonce_' . $annonce_id . '_' . uniqid() . '.' . $file_extension;
                        $destination = $upload_dir . $new_filename;

                        if (move_uploaded_file($tmp_name, $destination)) {
                            $img_sql = "INSERT INTO demenagement_image (img_id_annonce, img_nom_fichier, img_chemin) 
                                        VALUES (?, ?, ?)";
                            if ($img_stmt = $mysqli->prepare($img_sql)) {
                                $img_stmt->bind_param("iss", $annonce_id, $new_filename, $destination);
                                $img_stmt->execute();
                                $img_stmt->close();
                            }
                        } else {
                            $images_upload_succes = false; 
                        }
                    } else {
                        $images_upload_succes = false; 
                    }
                }
            }
        }

        if ($images_upload_succes) {
            $_SESSION['message'] = "Annonce publiee avec succes !";
        } else {
            $_SESSION['message'] = "Annonce publiee, mais une erreur est survenue lors du telechargement des images.";
        }
        
    } else {
        $_SESSION['erreur'] = "echec lors de l'enregistrement : " . $stmt->error;
        $stmt->close();
    }
} else {
    $_SESSION['erreur'] = "Erreur lors de la preparation de la requete : " . $mysqli->error;
}

$mysqli->close();
header('Location: tdbClient.php'); 
exit();
?>