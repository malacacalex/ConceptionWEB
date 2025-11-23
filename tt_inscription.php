<?php
session_start(); 
date_default_timezone_set('Europe/Paris'); 

if (isset($_SESSION['ut_id'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_POST['email'])) {
    header('Location: inscription.php');
    exit();
}

$ut_nom = htmlentities($_POST['nom']);
$ut_prenom = htmlentities($_POST['prenom']);
$ut_email = htmlentities($_POST['email']);
$ut_mdp = htmlentities($_POST['password']);
$ut_role = $_POST['role']; 

// Vérification du rôle (avec ou sans accent)
if ($ut_role !== 'client' && $ut_role !== 'déménageur' && $ut_role !== 'demenageur') {
    $_SESSION['erreur'] = "Type de compte invalide.";
    header('Location: inscription.php');
    exit();
}

$ut_statut = 1; 
$ut_date_inscription = date("Y-m-d");
$options = ['cost' => 10];
$ut_mdp_crypt = password_hash($ut_mdp, PASSWORD_BCRYPT, $options);

require_once("param.inc.php");
$mysqli = new mysqli($host, $login, $passwd, $dbname);

if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Erreur BDD.";
    header('Location: inscription.php');
    exit();
}

$mysqli->set_charset("utf8");

// Vérification doublon
$check = $mysqli->prepare("SELECT ut_id FROM utilisateur WHERE ut_email = ?");
$check->bind_param("s", $ut_email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $_SESSION['erreur'] = "Email déjà utilisé !";
    $check->close();
    header('Location: inscription.php');
    exit();
}
$check->close();

// Insertion
$sql = "INSERT INTO utilisateur (ut_nom, ut_prenom, ut_email, ut_mdp, ut_role, ut_date_inscription, ut_statut)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("ssssssi", $ut_nom, $ut_prenom, $ut_email, $ut_mdp_crypt, $ut_role, $ut_date_inscription, $ut_statut);
    
    if ($stmt->execute()) {
        $new_user_id = $stmt->insert_id;
        $_SESSION['ut_id'] = $new_user_id;
        $_SESSION['ut_nom'] = $ut_nom;
        $_SESSION['ut_prenom'] = $ut_prenom;
        $_SESSION['ut_role'] = $ut_role;
        $_SESSION['message'] = "Compte créé avec succès !";
    } else {
        $_SESSION['erreur'] = "Erreur SQL.";
    }
    $stmt->close();
}
$mysqli->close();

header('Location: index.php');
exit();
?>