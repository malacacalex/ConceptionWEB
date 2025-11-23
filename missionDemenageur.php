<?php
  session_start();
 
  // Vérification du rôle
  if (!isset($_SESSION['ut_id']) || ($_SESSION['ut_role'] !== 'déménageur' && $_SESSION['ut_role'] !== 'demenageur')) {
    $_SESSION['erreur'] = "Accès refusé. Page réservée aux déménageurs.";
    header('Location: connexion.php');
    exit();
  }
 
  $titre = "Mes Missions";
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php');
 
  $demenageur_id = $_SESSION['ut_id'];

  require_once("param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données");
  }
  $mysqli->set_charset("utf8");

  // 1. Missions en cours
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

  // 2. Propositions en attente
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

  // 3. Historique + ÉVALUATIONS (MODIFIÉ)
  // On fait une jointure GAUCHE (LEFT JOIN) avec la table evaluation
  // car une mission terminée n'a pas forcément encore été notée.
  $sql_termines = "SELECT a.*, p.pr_prix_propose, e.ev_note, e.ev_commentaire
                   FROM annonce a
                   JOIN proposition p ON a.an_id = p.pr_id_annonce
                   LEFT JOIN evaluation e ON a.an_id = e.ev_id_annonce AND e.ev_id_demenageur = p.pr_id_demenageur
                   WHERE p.pr_id_demenageur = ?
                   AND p.pr_statut = 'acceptée'
                   AND (a.an_statut = 'terminée' OR a.an_statut = 'terminee')
                   ORDER BY a.an_date_demenagement DESC";
                   
  $stmt_termines = $mysqli->prepare($sql_termines);
  $stmt_termines->bind_param("i", $demenageur_id);
  $stmt_termines->execute();
  $result_termines = $stmt_termines->get_result();
  $jobs_termines = $result_termines->fetch_all(MYSQLI_ASSOC);
  $stmt_termines->close();
  
  $mysqli->close();
?>
 
<div class="container my-5">
  <h1 class="mb-4 text-center">Gestion de vos Missions</h1>
  
  <div class="card shadow-sm">
    <div class="card-header">
      <ul class="nav nav-tabs card-header-tabs" id="demenageurTab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="encours-tab" data-bs-toggle="tab" data-bs-target="#encours-tab-pane" type="button" role="tab">
            Missions en cours <span class="badge bg-primary rounded-pill"><?php echo count($jobs_encours); ?></span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="attente-tab" data-bs-toggle="tab" data-bs-target="#attente-tab-pane" type="button" role="tab">
            En attente <span class="badge bg-secondary rounded-pill"><?php echo count($prop_attente); ?></span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="historique-tab" data-bs-toggle="tab" data-bs-target="#historique-tab-pane" type="button" role="tab">
            Historique <span class="badge bg-dark rounded-pill"><?php echo count($jobs_termines); ?></span>
          </button>
        </li>
      </ul>
    </div>
    
    <div class="card-body">
      <div class="tab-content" id="demenageurTabContent">
        
        <!-- ONGLET EN COURS -->
        <div class="tab-pane fade show active" id="encours-tab-pane" role="tabpanel">
          <?php if (count($jobs_encours) > 0): ?>
            <div class="row g-3">
              <?php foreach ($jobs_encours as $job): ?>
                <div class="col-md-6">
                  <div class="card border-primary h-100">
                    <div class="card-header bg-primary text-white">
                      <?php echo htmlspecialchars($job['an_titre']); ?>
                    </div>
                    <div class="card-body">
                      <p class="card-text">
                        <strong><i class="fas fa-route"></i> Trajet :</strong><br> 
                        <?php echo htmlspecialchars($job['an_ville_depart']); ?> &rarr; <?php echo htmlspecialchars($job['an_ville_arrivee']); ?>
                      </p>
                      <p class="card-text">
                        <strong><i class="far fa-calendar-alt"></i> Date :</strong> <?php echo date('d/m/Y', strtotime($job['an_date_demenagement'])); ?><br>
                        <strong><i class="far fa-clock"></i> Heure :</strong> <?php echo ($job['an_heure_debut']) ? date('H:i', strtotime($job['an_heure_debut'])) : 'Non précisée'; ?>
                      </p>
                      <div class="alert alert-success py-2">
                        <strong>Prix validé : <?php echo $job['pr_prix_propose']; ?> €</strong>
                      </div>
                      <a href="detailAnnonce.php?id=<?php echo $job['an_id']; ?>" class="btn btn-primary w-100">Voir les détails</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="alert alert-info">
              Vous n'avez aucune mission validée en cours. <a href="annonces.php" class="alert-link">Voir les annonces disponibles</a>.
            </div>
          <?php endif; ?>
        </div>
      
        <!-- ONGLET EN ATTENTE -->
        <div class="tab-pane fade" id="attente-tab-pane" role="tabpanel">
          <?php if (count($prop_attente) > 0): ?>
            <div class="list-group">
              <?php foreach ($prop_attente as $prop): ?>
                <a href="detailAnnonce.php?id=<?php echo $prop['an_id']; ?>" class="list-group-item list-group-item-action">
                  <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1"><?php echo htmlspecialchars($prop['an_titre']); ?></h5>
                    <small class="text-muted">Le <?php echo date('d/m/Y', strtotime($prop['an_date_demenagement'])); ?></small>
                  </div>
                  <p class="mb-1">
                    <?php echo htmlspecialchars($prop['an_ville_depart']); ?> &rarr; <?php echo htmlspecialchars($prop['an_ville_arrivee']); ?>
                  </p>
                  <div class="d-flex justify-content-between align-items-center mt-2">
                    <small>Votre proposition : <strong><?php echo $prop['pr_prix_propose']; ?> €</strong></small>
                    <span class="badge bg-warning text-dark">En attente du client</span>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="alert alert-light border text-center">
              Vous n'avez aucune proposition en attente de réponse.
            </div>
          <?php endif; ?>
        </div>
      
        <!-- ONGLET HISTORIQUE (MODIFIÉ POUR AFFICHER LA NOTE) -->
        <div class="tab-pane fade" id="historique-tab-pane" role="tabpanel">
          <?php if (count($jobs_termines) > 0): ?>
            <div class="list-group">
              <?php foreach ($jobs_termines as $job): ?>
                <div class="list-group-item list-group-item-action list-group-item-light">
                  <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1 text-decoration-line-through text-muted"><?php echo htmlspecialchars($job['an_titre']); ?></h5>
                    
                    <!-- Affichage de la note -->
                    <?php if (!empty($job['ev_note'])): ?>
                        <div class="text-warning fs-5">
                            <?php 
                            for($i=0; $i<5; $i++) {
                                echo ($i < $job['ev_note']) ? '★' : '☆';
                            }
                            ?>
                        </div>
                    <?php else: ?>
                        <small class="text-muted fst-italic">Pas encore noté</small>
                    <?php endif; ?>
                  </div>
                  
                  <div class="row mt-2">
                      <div class="col-md-8">
                          <p class="mb-1">
                              <i class="fas fa-route text-secondary"></i> <?php echo htmlspecialchars($job['an_ville_depart']); ?> &rarr; <?php echo htmlspecialchars($job['an_ville_arrivee']); ?>
                          </p>
                          <small class="text-muted">
                              Effectué le <?php echo date('d/m/Y', strtotime($job['an_date_demenagement'])); ?> 
                              - Prix : <strong><?php echo $job['pr_prix_propose']; ?> €</strong>
                          </small>
                      </div>
                      <div class="col-md-4 text-end">
                          <a href="detailAnnonce.php?id=<?php echo $job['an_id']; ?>" class="btn btn-outline-dark btn-sm">Revoir la mission</a>
                      </div>
                  </div>

                  <!-- Affichage du commentaire client s'il existe -->
                  <?php if (!empty($job['ev_commentaire'])): ?>
                      <div class="mt-3 p-2 bg-white border rounded text-secondary">
                          <strong><i class="fas fa-quote-left"></i> Avis du client :</strong><br>
                          <em>"<?php echo nl2br(htmlspecialchars($job['ev_commentaire'])); ?>"</em>
                      </div>
                  <?php endif; ?>

                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="alert alert-light border text-center">
              Vous n'avez encore terminé aucune mission.
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
  
  <div class="mt-4 text-center">
    <a href="tdbDemenageur.php" class="btn btn-outline-dark">Retour au tableau de bord</a>
  </div>
</div>
 
<?php
  include('footer.inc.php');
?>