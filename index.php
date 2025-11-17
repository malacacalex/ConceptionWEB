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
<p class="lead">Outil d'aide aux demenagements !</p>

<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="card h-100 text-center">
      <div class="card-body">
        <h5 class="card-title">Clients</h5>
        <p class="card-text">Trouvez facilement des demenageurs qualifies</p>
        <?php
          if (isset($_SESSION['ut_id']) && $_SESSION['ut_role'] == 'client') {
            // Si connecte en TANT QUE CLIENT : lien vers "Nouvelle Demande"
            echo '<a href="nvlDemande.php" class="btn btn-dark">Creer une demande</a>';
          } elseif (isset($_SESSION['ut_id'])) {
            // Si connecte (mais pas client) : on desactive le bouton
            echo '<a href="#" class="btn btn-dark disabled" aria-disabled="true">Creation demande</a>';
          } else {
            // Si non connecte : lien vers "Inscription"
            echo '<a href="inscription.php" class="btn btn-dark">Creation demande</a>';
          }
        ?>
      </div>
    </div>
  </div>
  
  <div class="col-md-4">
    <div class="card h-100 text-center">
      <div class="card-body">
        <h5 class="card-title">Demenageurs</h5>
        <p class="card-text">Inscrivez-vous comme demenageur</p>
        <?php
          if (isset($_SESSION['ut_id']) && $_SESSION['ut_role'] == 'demenageur') {
            // Si connecte en TANT QUE DeMeNAGEUR : lien vers les annonces
            echo '<a href="annonces.php" class="btn btn-dark">Voir les annonces</a>';
          } elseif (isset($_SESSION['ut_id'])) {
            // Si connecte (mais pas demenageur) : on desactive le bouton
            echo '<a href="#" class="btn btn-dark disabled" aria-disabled="true">Rejoindre le reseau</a>';
          } else {
            // Si non connecte : lien vers "Inscription"
            echo '<a href="inscription.php" class="btn btn-dark">Rejoindre le reseau</a>';
          }
        ?>
      </div>
    </div>
  </div>
</div>
<?php if (count($annonces_recentes) > 0): ?>
<div class="mt-5">
  <h2 class="mb-4">Dernieres annonces de demenagement</h2>
  <div class="row g-4">
    <?php foreach ($annonces_recentes as $annonce): ?>
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title"><?php echo htmlspecialchars($annonce['an_titre']); ?></h5>
          <p class="card-text">
            <strong>Trajet :</strong> <?php echo htmlspecialchars($annonce['an_ville_depart']); ?> 
             <?php echo htmlspecialchars($annonce['an_ville_arrivee']); ?><br>
            <strong>Date :</strong> <?php echo date('d/m/Y', strtotime($annonce['an_date_demenagement'])); ?>
          </p>
          <a href="detailAnnonce.php?id=<?php echo $annonce['an_id']; ?>" class="btn btn-outline-dark btn-sm">Voir les details</a>
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