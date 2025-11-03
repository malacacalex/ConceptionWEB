<?php
session_start(); // Pour les messages

// --- Récupération et sécurisation du formulaire ---
$ut_nom = htmlentities($_POST['nom']);
$ut_prenom = htmlentities($_POST['prenom']);
$ut_email = htmlentities($_POST['email']);
$ut_mdp = htmlentities($_POST['password']);

// Rôles possibles : 'client', 'déménageur', 'admin'
$ut_role = htmlentities($_POST['role']);
if ($ut_role !== 'client' && $ut_role !== 'demenageur') {
    $_SESSION['erreur'] = "Aucun compte valide.";
    header('Location: inscription.php');
    exit();
}

$ut_statut = 1; // 1 = actif
$ut_date_inscription = date("Y-m-d");

// --- Cryptage du mot de passe ---
$options = ['cost' => 10];
$ut_mdp_crypt = password_hash($ut_mdp, PASSWORD_BCRYPT, $options);

// --- Connexion à la base ---
require_once("param.inc.php");
$mysqli = new mysqli($host, $login, $passwd, $dbname);

if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données 😢";
    header('Location: inscription.php');
    exit();
}

// --- Vérifier si l'email existe déjà ---
$check = $mysqli->prepare("SELECT ut_id FROM utilisateur WHERE ut_email = ?");
$check->bind_param("s", $ut_email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $_SESSION['erreur'] = "⚠️ Cet email est déjà utilisé !";
    $check->close();
    header('Location: inscription.php');
    exit();
}
$check->close();

// --- Insertion du nouvel utilisateur ---
$sql = "INSERT INTO utilisateur (ut_nom, ut_prenom, ut_email, ut_mdp, ut_role, ut_date_inscription, ut_statut)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("ssssssi", $ut_nom, $ut_prenom, $ut_email, $ut_mdp_crypt, $ut_role, $ut_date_inscription, $ut_statut);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ Inscription réussie ! Vous pouvez maintenant vous connecter.";
    } else {
        $_SESSION['erreur'] = "❌ Erreur lors de l'enregistrement de l'utilisateur.";
    }

    $stmt->close();
} else {
    $_SESSION['erreur'] = "❌ Erreur de préparation de la requête.";
}

$mysqli->close();
header('Location: index.php');
exit();
?>
