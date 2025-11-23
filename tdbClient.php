<?php
  session_start();

  // Vérification si l'user est un client
  if (!isset($_SESSION['ut_id']) || $_SESSION['ut_role'] !== 'client') {
    $_SESSION['erreur'] = "Vous n'êtes pas un client, vous ne pouvez pas accéder à cette page.";
    header('Location: index.php');
    exit();
  }

  $titre = "Tableau de bord Client";
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php');
?>

<div class="container my-5">
  <h1 class="mb-5 text-center text-primary-emphasis">
    Tableau de Bord Client
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
                <span class="badge bg-info text-dark fs-6">Client</span>
            </li>
          </ul>
          <div class="mt-3 text-end">
            <small class="text-muted">Bienvenue sur votre espace personnel.</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Section des Outils de Gestion -->
    <div class="col-md-6 col-lg-7">
      <div class="card shadow-lg h-100 border-dark">
        <div class="card-header bg-dark text-white d-flex align-items-center">
          <h5 class="mb-0">Vos Actions</h5>
        </div>
        <div class="card-body d-grid gap-4">
          
          <!-- Bouton Création de demande -->
          <a href="creationDemande.php" class="btn btn-lg btn-dark shadow-sm py-3 border-0">
            <i class="fas fa-plus-circle me-2"></i> Poster une nouvelle demande
          </a>

          <!-- Bouton Suivi des demandes -->
          <a href="suiviDemande.php" class="btn btn-lg btn-outline-dark shadow-sm py-3">
            <i class="fas fa-list-alt me-2"></i> Suivre mes déménagements
          </a>

        </div>
        <div class="card-footer text-muted text-center">
            <small>Gérez vos projets de déménagement en toute simplicité.</small>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
  include('footer.inc.php');
?>