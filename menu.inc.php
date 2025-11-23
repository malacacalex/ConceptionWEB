<?php
  if (session_status() == PHP_SESSION_NONE) {
    session_start();
  }
?>
<nav class="mb-2 navbar navbar-expand-md bg-dark border-bottom border-body" data-bs-theme="dark">
  <div class="container-fluid">

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
          <a class="nav-link" href="annonces.php" style="color:white">Les Annonces</a>
        </li>
        
        <?php 
        $role = isset($_SESSION['ut_role']) ? $_SESSION['ut_role'] : 'public';

        if ($role === 'client'): 
        ?>
          <li class="nav-item">
            <a class="nav-link" href="tdbClient.php" style="color:white">Tableau de bord Client</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="nvlDemande.php" style="color:white">Création demande</a>
          </li>

        <?php elseif ($role === 'déménageur' || $role === 'demenageur'): ?>
          <li class="nav-item">
            <a class="nav-link" href="tdbDemenageur.php" style="color:white">Tableau de bord Déménageur</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="tdbDemenageur.php" style="color:white">Mes Missions</a>
          </li>

        <?php elseif ($role === 'admin' || $role === 'administrateur'): ?>
          <li class="nav-item">
            <a class="nav-link fw-bold text-info" href="tdbAdmin.php" style="color:white">Tableau de bord Admin</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestionUtilisateurs.php" style="color:white">Gestion utilisateurs</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="gestionAnnonces.php" style="color:white">Gestion demandes</a>
          </li>

        <?php endif; ?>
      </ul>
      
      <ul class="navbar-nav">
          <?php if (isset($_SESSION['ut_id'])): ?>
            <li class="nav-item">
                <span class="nav-link" style="color:white;">
                  Bonjour, <?php echo htmlspecialchars($_SESSION['ut_prenom']); ?> !
                </span>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="deconnexion.php" style="color:white">Déconnexion</a>
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