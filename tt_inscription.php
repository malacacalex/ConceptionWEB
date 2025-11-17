<?php
session_start(); 

if (isset($_SESSION['ut_id'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_POST['email'])) {
    $_SESSION['erreur'] = "Veuillez passer par le formulaire d'inscription.";
    header('Location: inscription.php');
    exit();
}

$ut_nom = htmlentities($_POST['nom']);
$ut_prenom = htmlentities($_POST['prenom']);
$ut_email = htmlentities($_POST['email']);
$ut_mdp = htmlentities($_POST['password']);
$ut_role = $_POST['role']; 

if ($ut_role !== 'client' && $ut_role !== 'déménageur') {
    $_SESSION['erreur'] = "Le type de compte n'est pas valide.";
    header('Location: inscription.php');
    exit();
}

$ut_statut = 1; // 1 = actif
$ut_date_inscription = date("Y-m-d");

// Cryptage du mot de passe 
$options = ['cost' => 10];
$ut_mdp_crypt = password_hash($ut_mdp, PASSWORD_BCRYPT, $options);

// Connexion a la base 
require_once("param.inc.php");
$mysqli = new mysqli($host, $login, $passwd, $dbname);

if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Probleme de connexion a la base de donnees ";
    header('Location: inscription.php');
    exit();
}

// Verifier si l'email existe deja
$check = $mysqli->prepare("SELECT ut_id FROM utilisateur WHERE ut_email = ?");
$check->bind_param("s", $ut_email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $_SESSION['erreur'] = "Cet email est deja utilise !";
    $check->close();
    header('Location: inscription.php');
    exit();
}
$check->close();

// Insertion du nouvel utilisateur
$sql = "INSERT INTO utilisateur (ut_nom, ut_prenom, ut_email, ut_mdp, ut_role, ut_date_inscription, ut_statut)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("ssssssi", $ut_nom, $ut_prenom, $ut_email, $ut_mdp_crypt, $ut_role, $ut_date_inscription, $ut_statut);
    
    if ($stmt->execute()) {
        // Connexion automatique 
        $new_user_id = $stmt->insert_id;
        $_SESSION['ut_id'] = $new_user_id;
        $_SESSION['ut_nom'] = $ut_nom;
        $_SESSION['ut_prenom'] = $ut_prenom;
        $_SESSION['ut_role'] = $ut_role;
        $_SESSION['message'] = "Bienvenue $ut_prenom ! Votre compte est cree et vous etes connecte.";

    } else {
        $_SESSION['erreur'] = "Erreur lors de l'enregistrement de l'utilisateur.";
    }
    $stmt->close();
} else {
    $_SESSION['erreur'] = "Erreur de preparation de la requete.";
}

$mysqli->close();

// On redirige vers l'accueil.
header('Location: index.php');
exit();
?>