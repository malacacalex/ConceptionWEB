<?php
  session_start();
  $titre = "Tableau de Bord Administrateur";
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php');

  $role = $_SESSION['ut_role'] ?? ''; // Récupère le rôle ou une chaîne vide
  if (!isset($_SESSION['ut_id']) || ($role != 'admin' && $role != 'administrateur')) {
    $_SESSION['message'] = "Accès refusé. Vous devez être connecté en tant qu'administrateur.";
    header('Location: index.php');
    exit();
  }

?>
<div class="container my-5">
  <h1 class="mb-5 text-center text-primary-emphasis">
    Tableau de Bord Administrateur
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
                <span class="badge bg-danger fs-6"><?php echo htmlspecialchars($_SESSION['ut_role'] ?? 'Non défini'); ?></span>
            </li>
          </ul>
          
        </div>
      </div>
    </div>

    <!-- Section des Outils de Gestion -->
    <div class="col-md-6 col-lg-7">
      <div class="card shadow-lg h-100 border-dark">
        <div class="card-header bg-dark text-white d-flex align-items-center">
          <h5 class="mb-0">Outils de Gestion du Site</h5>
        </div>
        <div class="card-body d-grid gap-4">
          
          <!-- Bouton Gestion des Utilisateurs -->
          <a href="gestion_utilisateurs.php" class="btn btn-lg btn-dark shadow-sm py-3 border-0">
            Gestion des Utilisateurs
          </a>

          <!-- Bouton Gestion des Annonces -->
          <a href="gestion_annonces.php" class="btn btn-lg btn-dark shadow-sm py-3 border-0">
            Gestion des Annonces
          </a>

        </div>
        
      </div>
    </div>
  </div>
</div>

<?php
  include('footer.inc.php');
?>