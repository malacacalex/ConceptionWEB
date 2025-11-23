<?php
  session_start();
  $titre = "Tableau de bord Déménageur";
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php');
 
  // Vérification du rôle
  if (!isset($_SESSION['ut_id']) || ($_SESSION['ut_role'] !== 'déménageur' && $_SESSION['ut_role'] !== 'demenageur')) {
    $_SESSION['erreur'] = "Accès refusé. Vous devez être connecté en tant que déménageur.";
    header('Location: index.php');
    exit();
  }
?>

<div class="container my-5">
  <h1 class="mb-5 text-center text-primary-emphasis">
    Tableau de Bord Déménageur
  </h1>

  <div class="row g-4 justify-content-center">
    <!-- Section des Informations Personnelles -->
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-lg h-100 border-dark">
        <div class="card-header bg-dark text-white d-flex align-items-center">
          <h5 class="mb-0">Vos Informations</h5>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>Prénom :</strong> <?php echo htmlspecialchars($_SESSION['ut_prenom'] ?? 'N/A'); ?></li>
            <li class="list-group-item"><strong>Nom :</strong> <?php echo htmlspecialchars($_SESSION['ut_nom'] ?? 'N/A'); ?></li>
            <li class="list-group-item"><strong>Email :</strong> <?php echo htmlspecialchars($_SESSION['ut_email'] ?? 'N/A'); ?></li>
            <li class="list-group-item">
                <strong>Rôle :</strong> 
                <span class="badge bg-success fs-6">Déménageur professionnel</span>
            </li>
          </ul>
          <div class="mt-3 text-end">
            <small class="text-muted">Session active.</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Section des Outils de Gestion -->
    <div class="col-md-6 col-lg-7">
      <div class="card shadow-lg h-100 border-dark">
        <div class="card-header bg-dark text-white d-flex align-items-center">
          <h5 class="mb-0">Votre Activité</h5>
        </div>
        <div class="card-body d-grid gap-4">
          
          <!-- Bouton vers la gestion des missions -->
          <a href="missionDemenageur.php" class="btn btn-lg btn-success shadow-sm py-3 border-0">
            Gérer mes missions & propositions
          </a>

          <!-- Bouton vers la recherche d'annonces -->
          <a href="annonces.php" class="btn btn-lg btn-outline-dark shadow-sm py-3">
            Trouver de nouvelles annonces
          </a>

        </div>
        <div class="card-footer text-muted text-center">
            <small>Gérez vos plannings et trouvez de nouveaux clients.</small>
        </div>
      </div>
    </div>
  </div>
</div>
 
<?php
  include('footer.inc.php');
?>