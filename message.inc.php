<?php
  if(isset($_SESSION['message'])) {

    // On utilise 'alert-dark' pour les messages de succes
    echo "<div class='alert alert-dark alert-dismissible fade show' role='alert'>";
      
    echo $_SESSION["message"];
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    echo "</div>";

    unset($_SESSION['message']);
  }
  if(isset($_SESSION['erreur'])) {

    // On garde 'alert-danger' (rouge) pour les erreurs, c'est important
    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>";
      
    echo $_SESSION["erreur"];
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    echo "</div>";

    unset($_SESSION['erreur']);
  }
?>