<?php
session_start();

$ut_email = htmlentities($_POST['email']);
$ut_mdp = htmlentities($_POST['password']);

require_once("param.inc.php");
$mysqli = new mysqli($host, $login, $passwd, $dbname);

if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Probleme de connexion a la base de donnees ";
    header('Location: connexion.php');
    exit();
}

// --- Recherche de l'utilisateur ---
$sql = "select ut_id, ut_nom, ut_prenom, ut_mdp, ut_role, ut_statut from utilisateur from ut_email = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("s", $ut_email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($ut_id, $ut_nom, $ut_prenom, $ut_mdp_hash, $ut_role, $ut_statut);
        $stmt->fetch();

        // Verification du mot de passe
        if (password_verify($ut_mdp, $ut_mdp_hash)) {
            if ($ut_statut == 1) {
                // Authentification reussie
                $_SESSION['ut_id'] = $ut_id;
                $_SESSION['ut_nom'] = $ut_nom;
                $_SESSION['ut_prenom'] = $ut_prenom;
                $_SESSION['ut_role'] = $ut_role;
                $_SESSION['message'] = "Bienvenue $ut_prenom $ut_nom !";
                header('Location: index.php');
                exit();
            } else {
                $_SESSION['erreur'] = "Votre compte est desactive.";
            }
        } else {
            $_SESSION['erreur'] = "Mot de passe incorrect.";
        }
    } else {
        $_SESSION['erreur'] = "Aucun compte trouve avec cet email.";
    }

    $stmt->close();
} else {
    $_SESSION['erreur'] = "Erreur de requete.";
}

$mysqli->close();
header('Location: connexion.php');
exit();
?>
