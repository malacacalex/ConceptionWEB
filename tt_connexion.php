<?php
  session_start(); // Pour les massages

  // Contenu du formulaire :
  $email =  htmlentities($_POST['email']);
  $password = htmlentities($_POST['password']);
  $role = 0; 
  // Définir des valeurs pour le role :
  // 0 : le compte n'est pas activé,
  // 1 : tuteur entreprise,
  // 2 : Responsable Ping,
  // 3 : admin

  // Option pour bcrypt (voir le lien du cours vers le site de PHP) :
  $options = [
        'cost' => 10,
  ];
  // On crypte le mot de passe
  $password_crypt = password_hash($password, PASSWORD_BCRYPT, $options);

  // Connexion :
  require_once("param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  if ($mysqli->connect_error) {
    $_SESSION['erreur']="Problème de connexion à la base de données ! &#128557;";
      // die('Erreur de connexion (' . $mysqli->connect_errno . ') '
              // . $mysqli->connect_error);
  }

  // À faire : vérifier si l'email existe déjà !


  // Modifier la requête en fonction de la table et/ou des attributs :
  if ($stmt = $mysqli->prepare("INSERT INTO compte(nom, prenom, email, password, role) VALUES (?, ?, ?, ?, ?)")) {

    $stmt->bind_param("ssssi", $nom, $prenom, $email, $password_crypt, $role);
    // Le message est mis dans la session, il est préférable de séparer message normal et message d'erreur.
    if($stmt->execute()) {
        // Requête exécutée correctement 
        $_SESSION['message'] = "Enregistrement réussi";

    } else {
        // Il y a eu une erreur
        $_SESSION['erreur'] =  "Impossible d'enregistrer";
    }
  }
 


  header('Location: index.php');


?>