<?php
  session_start();

  // Récupération des données du formulaire
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';

  // Connexion à la base de données
  require_once("param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);

  if ($mysqli->connect_error) {
    // Gestion de l'erreur de connexion à la BDD (Erreur Système)
    $_SESSION['message'] = "Une erreur de connexion est survenue. Veuillez réessayer.";
    header('Location: connexion.php');
    exit();
  }

  // Préparation de la requête sécurisée
  $stmt = $mysqli->prepare("SELECT ut_id, ut_nom, ut_prenom, ut_role, ut_mdp, ut_email, ut_statut FROM utilisateur WHERE ut_email = ?");

  if ($stmt === false) {
    
    $_SESSION['message'] = "Une erreur technique est survenue. Veuillez contacter l'administrateur.";
    
    $mysqli->close();
    header('Location: connexion.php');
    exit();
  }
  
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  // Vérification de l'utilisateur (Erreur Utilisateur)
  if ($user = $result->fetch_assoc()) {
    
    // Vérification du mot de passe (Erreur Utilisateur)
    // CORRECTION : $user['ut_motdepasse'] a été remplacé par $user['ut_mdp']
    if (password_verify($password, $user['ut_mdp'])) { 
        
        // VÉRIFICATION DU STATUT DU COMPTE
        if ($user['ut_statut'] == 0) {
            $_SESSION['message'] = "Votre compte est désactivé. Veuillez contacter l'administrateur.";
            $mysqli->close();
            header('Location: connexion.php');
            exit();
        }

        // Connexion réussie et compte actif : Stockage des informations en session
        $_SESSION['ut_id'] = $user['ut_id'];
        $_SESSION['ut_nom'] = $user['ut_nom'];
        $_SESSION['ut_prenom'] = $user['ut_prenom'];
        $_SESSION['ut_email'] = $user['ut_email']; 
        $_SESSION['ut_role'] = $user['ut_role'];
        $_SESSION['message'] = "Connexion réussie ! Bienvenue, " . htmlspecialchars($user['ut_prenom']);

        // Redirection en fonction du rôle
        $redirection_page = 'index.php'; 
        switch ($user['ut_role']) {
            case 'administrateur':
            case 'admin': // Ajout pour correspondre au ENUM de la BDD
                $redirection_page = 'tdbAdmin.php'; 
                break;
            case 'client':
                $redirection_page = 'tdbClient.php';
                break;
            case 'déménageur':
                $redirection_page = 'tdbDemenageur.php';
                break;
        }
        
        $mysqli->close();
        header("Location: $redirection_page");
        exit();

    } else {
      // Mot de passe incorrect (Message générique)
      $_SESSION['message'] = "Identifiant ou mot de passe incorrect.";
    }
  } else {
    // Utilisateur non trouvé (Message générique)
    $_SESSION['message'] = "Identifiant ou mot de passe incorrect.";
  }

  // Si la connexion a échoué (ou compte inactif), rediriger vers la page de connexion
  $mysqli->close();
  header('Location: connexion.php');
  exit();
?>