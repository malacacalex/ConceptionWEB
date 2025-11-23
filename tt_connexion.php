<?php
  session_start();

  // Récupération des données
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';

  // Connexion BDD
  require_once("param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);

  if ($mysqli->connect_error) {
    $_SESSION['message'] = "Erreur de connexion.";
    header('Location: connexion.php');
    exit();
  }

  $mysqli->set_charset("utf8"); 

  // Requête
  $stmt = $mysqli->prepare("SELECT ut_id, ut_nom, ut_prenom, ut_role, ut_mdp, ut_email, ut_statut FROM utilisateur WHERE ut_email = ?");

  if ($stmt === false) {
    $_SESSION['message'] = "Erreur technique.";
    $mysqli->close();
    header('Location: connexion.php');
    exit();
  }
  
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($user = $result->fetch_assoc()) {
    
    if (password_verify($password, $user['ut_mdp'])) { 
        
        if ($user['ut_statut'] == 0) {
            $_SESSION['message'] = "Compte désactivé.";
            $mysqli->close();
            header('Location: connexion.php');
            exit();
        }

        // Session
        $_SESSION['ut_id'] = $user['ut_id'];
        $_SESSION['ut_nom'] = $user['ut_nom'];
        $_SESSION['ut_prenom'] = $user['ut_prenom'];
        $_SESSION['ut_email'] = $user['ut_email']; 
        $_SESSION['ut_role'] = $user['ut_role'];
        
        $_SESSION['message'] = "Connexion réussie !";

        
        $redirection_page = 'index.php'; 
        
        
        $mysqli->close();
        header("Location: $redirection_page");
        exit();

    } else {
      $_SESSION['message'] = "Mot de passe incorrect.";
    }
  } else {
    $_SESSION['message'] = "Identifiant incorrect.";
  }

  $mysqli->close();
  header('Location: connexion.php');
  exit();
?>