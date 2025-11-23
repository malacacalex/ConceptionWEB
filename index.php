<?php
  session_start();
  $titre = "Accueil";
  
  // Connexion BDD pour afficher quelques annonces
  require_once("param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  $annonces_recentes = [];
  if (!$mysqli->connect_error) {
    $query = "SELECT an_id, an_titre, an_ville_depart, an_ville_arrivee, an_date_demenagement 
              FROM annonce 
              WHERE an_statut = 'ouverte' 
              ORDER BY an_date_creation DESC 
              LIMIT 3";
    $result = $mysqli->query($query);
    if ($result) {
      $annonces_recentes = $result->fetch_all(MYSQLI_ASSOC);
    }
  }
  
  include('header.inc.php');
  include('menu.inc.php'); 
  include('message.inc.php');
?>

<h1>Bienvenue</h1>
<p class="lead">Outil d'aide aux déménagements !</p>

<?php if (!isset($_SESSION['ut_id'])): ?>
  <!-- ======================================================= -->
  <!-- BLOC VISITEUR (Non connecté) - Visible comme sur la photo -->
  <!-- ======================================================= -->
  <div class="row g-4 mb-4">
    <div class="col-md-4">
      <div class="card h-100 text-center">
        <div class="card-body">
          <h5 class="card-title">Clients</h5>
          <p class="card-text">Trouvez facilement des déménageurs qualifiés</p>
          <a href="inscription.php" class="btn btn-dark">Création demande</a>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="card h-100 text-center">
        <div class="card-body">
          <h5 class="card-title">Déménageurs</h5>
          <p class="card-text">Inscrivez-vous comme déménageur</p>
          <a href="inscription.php" class="btn btn-dark">Rejoindre le réseau</a>
        </div>
      </div>
    </div>
  </div>

<?php else: ?>

  <div class="alert alert-light border shadow-sm mb-5">
      <h4 class="alert-heading">Bonjour <?php echo htmlspecialchars($_SESSION['ut_prenom']); ?> !</h4>
      <p>Heureux de vous revoir sur la plateforme.</p>
      <hr>
      <p class="mb-0">
        Accédez directement à votre espace : 
        <?php if($_SESSION['ut_role'] == 'client'): ?>
            <a href="tdbClient.php" class="btn btn-success ms-2">Mon Tableau de Bord Client</a>
        <?php elseif($_SESSION['ut_role'] == 'déménageur' || $_SESSION['ut_role'] == 'demenageur'): ?>
            <a href="tdbDemenageur.php" class="btn btn-success ms-2">Mon Tableau de Bord Déménageur</a>
        <?php elseif($_SESSION['ut_role'] == 'admin' || $_SESSION['ut_role'] == 'administrateur'): ?>
            <a href="tdbAdmin.php" class="btn btn-danger ms-2">Administration</a>
        <?php endif; ?>
      </p>
  </div>
<?php endif; ?>


<?php if (count($annonces_recentes) > 0): ?>
<div class="mt-5">
  <h2 class="mb-4">Dernières annonces de déménagement</h2>
  <div class="row g-4">
    <?php foreach ($annonces_recentes as $annonce): ?>
    <div class="col-md-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h5 class="card-title text-primary"><?php echo htmlspecialchars($annonce['an_titre']); ?></h5>
          <p class="card-text">
            <strong>Trajet :</strong> <?php echo htmlspecialchars($annonce['an_ville_depart']); ?> 
             &rarr; <?php echo htmlspecialchars($annonce['an_ville_arrivee']); ?><br>
            <strong>Date :</strong> <?php echo date('d/m/Y', strtotime($annonce['an_date_demenagement'])); ?>
          </p>
          <a href="detailAnnonce.php?id=<?php echo $annonce['an_id']; ?>" class="btn btn-outline-dark btn-sm">Voir les détails</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="text-center mt-4">
    <a href="annonces.php" class="btn btn-dark">Voir toutes les annonces</a>
  </div>
</div>
<?php endif; ?>

<?php
  if (!$mysqli->connect_error) {
    $mysqli->close();
  }
  include('footer.inc.php');
?>