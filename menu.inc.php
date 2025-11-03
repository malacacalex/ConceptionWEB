<nav class="mb-2 navbar navbar-expand-md bg-dark border-bottom border-body" data-bs-theme="dark">
  <div class="container-fluid">

    <!-- Partie gauche de la barre -->
    <a class="navbar-brand" href="index.php" style="color:white">Esigelec</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
      
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" aria-current="page" href="index.php" style="color:white">Accueil</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="demenagement.php" style="color:white">Déménagement</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="tdbClient.php" style="color:white">Mes déménagements en cours</a>
        </li>
      </ul>

      <!-- Partie droite -->
      <ul class="navbar-nav">
        <?php
        // Vérification connexion user
        if (isset($_SESSION['ut_id'])):
        ?>
          <li class="nav-item">
            <span class="nav-link" style="color:white">
              Bonjour, <?php echo htmlspecialchars($_SESSION['ut_prenom']); ?>
            </span>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="deconnexion.php" style="color:white">Deconnexion</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" aria-current="page" href="inscription.php" style="color:white">Inscription</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="connexion.php" style="color:white">Connexion</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container">