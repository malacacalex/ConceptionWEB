<?php
session_start();

// --- Récupération des champs ---
$ut_email = htmlentities($_POST['email']);
$ut_mdp = htmlentities($_POST['password']);

// --- Connexion à la base ---
require_once("param.inc.php");
$mysqli = new mysqli($host, $login, $passwd, $dbname);

if ($mysqli->connect_error) {
    $_SESSION['erreur'] = "Problème de connexion à la base de données 😢";
    header('Location: connexion.php');
    exit();
}

// --- Recherche de l'utilisateur ---
$sql = "SELECT ut_id, ut_nom, ut_prenom, ut_mdp, ut_role, ut_statut FROM utilisateur WHERE ut_email = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("s", $ut_email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($ut_id, $ut_nom, $ut_prenom, $ut_mdp_hash, $ut_role, $ut_statut);
        $stmt->fetch();

        // Vérification du mot de passe
        if (password_verify($ut_mdp, $ut_mdp_hash)) {
            if ($ut_statut == 1) {
                // Authentification réussie
                $_SESSION['ut_id'] = $ut_id;
                $_SESSION['ut_nom'] = $ut_nom;
                $_SESSION['ut_prenom'] = $ut_prenom;
                $_SESSION['ut_role'] = $ut_role;

                $_SESSION['message'] = "✅ Bienvenue $ut_prenom $ut_nom !";
                header('Location: index.php');
                exit();
            } else {
                $_SESSION['erreur'] = "⚠️ Votre compte est désactivé.";
            }
        } else {
            $_SESSION['erreur'] = "❌ Mot de passe incorrect.";
        }
    } else {
        $_SESSION['erreur'] = "❌ Aucun compte trouvé avec cet email.";
    }

    $stmt->close();
} else {
    $_SESSION['erreur'] = "❌ Erreur de requête.";
}

$mysqli->close();
header('Location: connexion.php');
exit();
?>
