<?php
  session_start();
 
  // On vérifie si l'utilisateur est connecté ET s'il a le bon rôle
  if (!isset($_SESSION['ut_id']) || $_SESSION['ut_role'] !== 'déménageur') {
    $_SESSION['erreur'] = "Vous devez être connecté en tant que déménageur pour accéder à cette page.";
    header('Location: connexion.php');
    exit();
  }
 
  $titre = "Tableau de bord Déménageur";
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php');
 
  // On récupère l'ID du déménageur
  $demenageur_id = $_SESSION['ut_id'];

  require_once("param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données");
  }
  $mysqli->set_charset("utf8");

  $sql_encours = "SELECT a.*, p.pr_prix_propose
                  FROM annonce a
                  JOIN proposition p ON a.an_id = p.pr_id_annonce
                  WHERE p.pr_id_demenageur = ?
                  AND p.pr_statut = 'acceptée'
                  AND a.an_statut = 'en cours'
                  ORDER BY a.an_date_demenagement ASC";
  $stmt_encours = $mysqli->prepare($sql_encours);
  $stmt_encours->bind_param("i", $demenageur_id);
  $stmt_encours->execute();
  $result_encours = $stmt_encours->get_result();
  $jobs_encours = $result_encours->fetch_all(MYSQLI_ASSOC);
  $stmt_encours->close();

  $sql_attente = "SELECT a.*, p.pr_prix_propose
                  FROM annonce a
                  JOIN proposition p ON a.an_id = p.pr_id_annonce
                  WHERE p.pr_id_demenageur = ?
                  AND p.pr_statut = 'en attente'
                  AND a.an_statut = 'ouverte'
                  ORDER BY a.an_date_demenagement ASC";
  $stmt_attente = $mysqli->prepare($sql_attente);
  $stmt_attente->bind_param("i", $demenageur_id);
  $stmt_attente->execute();
  $result_attente = $stmt_attente->get_result();
  $prop_attente = $result_attente->fetch_all(MYSQLI_ASSOC);
  $stmt_attente->close();

  $sql_termines = "SELECT a.*, p.pr_prix_propose
                   FROM annonce a
                   JOIN proposition p ON a.an_id = p.pr_id_annonce
                   WHERE p.pr_id_demenageur = ?
                   AND p.pr_statut = 'acceptée'
                   AND a.an_statut = 'terminée'
                   ORDER BY a.an_date_demenagement DESC";
  $stmt_termines = $mysqli->prepare($sql_termines);
  $stmt_termines->bind_param("i", $demenageur_id);
  $stmt_termines->execute();
  $result_termines = $stmt_termines->get_result();
  $jobs_termines = $result_termines->fetch_all(MYSQLI_ASSOC);
  $stmt_termines->close();
  
  $mysqli->close();
?>
 
<h1>Tableau de bord Déménageur</h1>
<p class="lead">Bienvenue, <?php echo htmlspecialchars($_SESSION['ut_prenom']); ?>. Gérez vos missions et propositions.</p>
 
<ul class="nav nav-tabs" id="demenageurTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="encours-tab" data-bs-toggle="tab" data-bs-target="#encours-tab-pane" type="button" role="tab">
      Missions en cours <span class="badge bg-dark"><?php echo count($jobs_encours); ?></span>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="attente-tab" data-bs-toggle="tab" data-bs-target="#attente-tab-pane" type="button" role="tab">
      Propositions en attente <span class="badge bg-dark"><?php echo count($prop_attente); ?></span>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="historique-tab" data-bs-toggle="tab" data-bs-target="#historique-tab-pane" type="button" role="tab">
      Historique <span class="badge bg-dark"><?php echo count($jobs_termines); ?></span>
    </button>
  </li>
</ul>
<div class="tab-content" id="demenageurTabContent">
  
  <div class="tab-pane fade show active p-3" id="encours-tab-pane" role="tabpanel">
    <?php if (count($jobs_encours) > 0): ?>
      <div class="row g-3">
        <?php foreach ($jobs_encours as $job): ?>
          <div class="col-md-6">
            <div class="card border-dark">
              <div class="card-header bg-dark text-white">
                <?php echo htmlspecialchars($job['an_titre']); ?>
              </div>
              <div class="card-body">
                <p>
                  <strong>Trajet :</strong> <?php echo htmlspecialchars($job['an_ville_depart']); ?> → <?php echo htmlspecialchars($job['an_ville_arrivee']); ?><br>
                  <strong>Date :</strong> <?php echo date('d/m/Y', strtotime($job['an_date_demenagement'])); ?> à <?php echo date('H:i', strtotime($job['an_heure_debut'])); ?>
                </p>
                <p class="card-text"><strong>Votre prix (accepté) : <span class="text-success fw-bold"><?php echo $job['pr_prix_propose']; ?> €</span></strong></p>
                <a href="detailAnnonce.php?id=<?php echo $job['an_id']; ?>" class="btn btn-dark btn-sm">Voir les détails de la mission</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-dark mt-3">
        Vous n'avez aucune mission en cours. <a href="annonces.php" class="alert-link">Faire de nouvelles propositions ?</a>
      </div>
    <?php endif; ?>
  </div>
 
  <div class="tab-pane fade p-3" id="attente-tab-pane" role="tabpanel">
    <?php if (count($prop_attente) > 0): ?>
      <div class="list-group">
        <?php foreach ($prop_attente as $prop): ?>
          <a href="detailAnnonce.php?id=<?php echo $prop['an_id']; ?>" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
              <h5 class="mb-1"><?php echo htmlspecialchars($prop['an_titre']); ?></h5>
              <small>Votre prix : <?php echo $prop['pr_prix_propose']; ?> €</small>
            </div>
            <p class="mb-1"><?php echo htmlspecialchars($prop['an_ville_depart']); ?> → <?php echo htmlspecialchars($prop['an_ville_arrivee']); ?></p>
            <small>Pour le <?php echo date('d/m/Y', strtotime($prop['an_date_demenagement'])); ?>. Statut : <span class="badge bg-dark">En attente</span></small>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-dark mt-3">
        Vous n'avez aucune proposition en attente.
      </div>
    <?php endif; ?>
  </div>
 
  <div class="tab-pane fade p-3" id="historique-tab-pane" role="tabpanel">
    <?php if (count($jobs_termines) > 0): ?>
      <div class="list-group">
        <?php foreach ($jobs_termines as $job): ?>
          <a href="detailAnnonce.php?id=<?php echo $job['an_id']; ?>" class="list-group-item list-group-item-action list-group-item-secondary">
            <div class="d-flex w-100 justify-content-between">
              <h5 class="mb-1"><?php echo htmlspecialchars($job['an_titre']); ?></h5>
              <small>Votre prix : <?php echo $job['pr_prix_propose']; ?> €</small>
            </div>
            <p class="mb-1"><?php echo htmlspecialchars($job['an_ville_depart']); ?> → <?php echo htmlspecialchars($job['an_ville_arrivee']); ?></p>
            <small>Effectué le <?php echo date('d/m/Y', strtotime($job['an_date_demenagement'])); ?>. Statut : <span class="badge bg-dark">Terminé</span></small>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-dark mt-3">
        Vous n'avez encore terminé aucune mission.
      </div>
    <?php endif; ?>
  </div>
</div>
 
<?php
  include('footer.inc.php');
?>