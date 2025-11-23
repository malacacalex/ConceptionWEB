<?php
session_start();
require_once("param.inc.php");

// Vérification de la connexion
if (!isset($_SESSION['ut_id'])) {
    $_SESSION['message'] = "Vous devez être connecté pour effectuer cette action.";
    header('Location: connexion.php');
    exit();
}

// Vérification de la présence de l'ID
if (!isset($_POST['id_annonce']) || empty($_POST['id_annonce'])) {
    $_SESSION['message'] = "Annonce invalide.";
    header('Location: tdbClient.php');
    exit();
}

$id_annonce = intval($_POST['id_annonce']);
$user_id = $_SESSION['ut_id'];

//Connexion à la base
$mysqli = new mysqli($host, $login, $passwd, $dbname);
if ($mysqli->connect_error) {
    $_SESSION['message'] = "Erreur de connexion à la base de données.";
    header('Location: tdbClient.php');
    exit();
}

// Vérifier que l'utilisateur est bien le propriétaire de l'annonce
$check_query = "SELECT an_id FROM annonce WHERE an_id = ? AND an_id_client = ?";
$check_stmt = $mysqli->prepare($check_query);
$check_stmt->bind_param("ii", $id_annonce, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $_SESSION['message'] = "Vous n'avez pas le droit de supprimer cette annonce.";
    $check_stmt->close();
    $mysqli->close();
    header('Location: tdbClient.php');
    exit();
}
$check_stmt->close();


// Récupérer les chemins des images associées
$img_query = "SELECT img_chemin FROM demenagement_image WHERE img_id_annonce = ?";
$img_stmt = $mysqli->prepare($img_query);
$img_stmt->bind_param("i", $id_annonce);
$img_stmt->execute();
$img_result = $img_stmt->get_result();

while ($row = $img_result->fetch_assoc()) {
    $file_path = $row['img_chemin'];
    
    // Vérifier si le fichier existe sur le serveur avant de tenter de le supprimer
    if (file_exists($file_path)) {
        unlink($file_path); // Supprime le fichier du dossier
    }
}
$img_stmt->close();

// --------------------------------------------------

// annonce proposition supprimées automatiquement par la BDD.
$delete_query = "DELETE FROM annonce WHERE an_id = ?";
$delete_stmt = $mysqli->prepare($delete_query);
$delete_stmt->bind_param("i", $id_annonce);

if ($delete_stmt->execute()) {
    $_SESSION['message'] = "Votre annonce et ses images ont été supprimées avec succès.";
} else {
    $_SESSION['message'] = "Erreur lors de la suppression de l'annonce : " . $delete_stmt->error;
}

$delete_stmt->close();
$mysqli->close();

// 6. Redirection
header('Location: tdbClient.php');
exit();
?>