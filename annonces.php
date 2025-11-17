<?php
  session_start();
  $titre = "Annonces de demenagement"; 
  
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php');

  // Connexion BDD 
  require_once("param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    die("Erreur de connexion a la base de donnees");
  }
  
  $query = "SELECT a.*, u.ut_prenom, u.ut_nom 
            FROM annonce a 
            JOIN utilisateur u ON a.an_id_client = u.ut_id 
            WHERE a.an_statut = 'ouverte' 
            ORDER BY a.an_date_creation DESC";
  $result = $mysqli->query($query);
?>

<h1>Annonces de demenagement</h1>
<p class="lead">Decouvrez les demandes de demenagement en cours</p>

<?php if (isset($_SESSION['ut_id']) && $_SESSION['ut_role'] == 'client'): ?>
  <div class="alert alert-dark">
    <strong>Vous êtes client :</strong> <a href="nvlDemande.php" class="alert-link">Creer une nouvelle demande de demenagement</a>
  </div>
<?php elseif (isset($_SESSION['ut_id']) && $_SESSION['ut_role'] == 'demenageur'): ?>
  <div class="alert alert-dark">
    <strong>Vous êtes demenageur :</strong> Consultez les annonces ci-dessous et proposez vos services !
  </div>
<?php else: ?>
  <div class="alert alert-dark">
    <strong>Vous n'êtes pas connecte :</strong> <a href="connexion.php" class="alert-link">Connectez-vous</a> pour creer une demande ou proposer vos services.
  </div>
<?php endif; ?>
<div class="row g-4 mt-2">
  <?php
  if ($result && $result->num_rows > 0) {
    while ($annonce = $result->fetch_assoc()) {
      $count_query = "SELECT COUNT(*) as nb_propositions FROM proposition WHERE pr_id_annonce = ?";
      $count_stmt = $mysqli->prepare($count_query);
      $count_stmt->bind_param("i", $annonce['an_id']);
      $count_stmt->execute();
      $count_result = $count_stmt->get_result();
      $nb_propositions = $count_result->fetch_assoc()['nb_propositions'];
      $count_stmt->close();
  ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title"><?php echo htmlspecialchars($annonce['an_titre']); ?></h5>
          <h6 class="card-subtitle mb-2 text-muted">
            <?php echo htmlspecialchars($annonce['an_ville_depart']); ?> → <?php echo htmlspecialchars($annonce['an_ville_arrivee']); ?>
          </h6>
          <p class="card-text">
            <?php echo nl2br(htmlspecialchars(substr($annonce['an_description'], 0, 150))); ?>
            <?php if(strlen($annonce['an_description']) > 150) echo '...'; ?>
          </p>
          <ul class="list-unstyled">
            <li><strong>Date :</strong> <?php echo date('d/m/Y', strtotime($annonce['an_date_demenagement'])); ?></li>
            <li><strong>Heure :</strong> <?php echo date('H:i', strtotime($annonce['an_heure_debut'])); ?></li>
            <li><strong>Demenageurs :</strong> <?php echo $annonce['an_nombre_demenageurs']; ?></li>
            <li><strong>Propositions :</strong> <span class="badge bg-dark"><?php echo $nb_propositions; ?></span></li>
          </ul>
          <a href="detailAnnonce.php?id=<?php echo $annonce['an_id']; ?>" class="btn btn-dark btn-sm">Voir les details</a>
        </div>
        <div class="card-footer text-muted">
          <small>Publie le <?php echo date('d/m/Y', strtotime($annonce['an_date_creation'])); ?></small>
        </div>
      </div>
    </div>
  <?php
    }
  } else {
    echo '<div class="col-12"><div class="alert alert-dark">Aucune annonce disponible pour le moment.</div></div>';
  }
  
  $mysqli->close();
  ?>
</div>

<?php
  include('footer.inc.php');
?>