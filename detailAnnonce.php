<?php
  session_start();
  
  // Vérification de l'ID
  if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['erreur'] = "Annonce non trouvée.";
    header('Location: annonces.php');
    exit();
  }
  
  $annonce_id = intval($_GET['id']);
  
  // Connexion BDD 
  require_once("param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  
  if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données");
  }
  $mysqli->set_charset("utf8"); 

  // Infos Annonce
  $query = "SELECT a.*, u.ut_prenom, u.ut_nom, u.ut_email 
            FROM annonce a 
            JOIN utilisateur u ON a.an_id_client = u.ut_id 
            WHERE a.an_id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("i", $annonce_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows == 0) {
    $_SESSION['erreur'] = "Annonce non trouvée.";
    $stmt->close();
    $mysqli->close();
    header('Location: annonces.php');
    exit();
  }
  
  $annonce = $result->fetch_assoc();
  $stmt->close();

  // Images
  $images_query = "SELECT * FROM demenagement_image WHERE img_id_annonce = ?";
  $images_stmt = $mysqli->prepare($images_query);
  $images_stmt->bind_param("i", $annonce_id);
  $images_stmt->execute();
  $images_result = $images_stmt->get_result();
  $images = $images_result->fetch_all(MYSQLI_ASSOC);
  $images_stmt->close();

  // Questions / Réponses 
  $sql_questions = "SELECT q.*, u.ut_nom, u.ut_prenom 
                    FROM question q 
                    JOIN utilisateur u ON q.id_demenageur = u.ut_id 
                    WHERE q.id_annonce = ? 
                    ORDER BY q.date_question ASC";
  $stmt_q = $mysqli->prepare($sql_questions);
  $stmt_q->bind_param("i", $annonce_id);
  $stmt_q->execute();
  $res_questions = $stmt_q->get_result();
  $questions_list = $res_questions->fetch_all(MYSQLI_ASSOC);
  $stmt_q->close();

  // Propositions AVEC MOYENNE AVIS
  $propositions = [];
  if (isset($_SESSION['ut_id']) && $_SESSION['ut_id'] == $annonce['an_id_client']) {
    $prop_query = "SELECT p.*, u.ut_nom, u.ut_prenom, u.ut_email,
                   (SELECT AVG(ev_note) FROM evaluation WHERE ev_id_demenageur = p.pr_id_demenageur) as moyenne_avis,
                   (SELECT COUNT(*) FROM evaluation WHERE ev_id_demenageur = p.pr_id_demenageur) as nb_avis
                   FROM proposition p 
                   JOIN utilisateur u ON p.pr_id_demenageur = u.ut_id 
                   WHERE p.pr_id_annonce = ? 
                   ORDER BY p.pr_date_proposition DESC";
                   
    $prop_stmt = $mysqli->prepare($prop_query);
    $prop_stmt->bind_param("i", $annonce_id);
    $prop_stmt->execute();
    $prop_result = $prop_stmt->get_result();
    $propositions = $prop_result->fetch_all(MYSQLI_ASSOC);
    $prop_stmt->close();
  }
  
  // Proposition déménageur (pour voir sa propre offre)
  $user_proposition = null;
  if (isset($_SESSION['ut_id']) && ($_SESSION['ut_role'] == 'demenageur' || $_SESSION['ut_role'] == 'déménageur')) {
    $user_prop_query = "SELECT * FROM proposition WHERE pr_id_annonce = ? AND pr_id_demenageur = ?";
    $user_prop_stmt = $mysqli->prepare($user_prop_query);
    $user_prop_stmt->bind_param("ii", $annonce_id, $_SESSION['ut_id']);
    $user_prop_stmt->execute();
    $user_prop_result = $user_prop_stmt->get_result();
    if ($user_prop_result->num_rows > 0) {
      $user_proposition = $user_prop_result->fetch_assoc();
    }
    $user_prop_stmt->close();
  }

  // --- LOGIQUE ÉVALUATION (MISE À JOUR POUR MULTI-DÉMÉNAGEURS) ---
  $demenageurs_acceptes = [];
  $evaluations_par_demenageur = [];

  // 1. On récupère TOUS les déménageurs acceptés
  $sql_accepted = "SELECT p.pr_id_demenageur, u.ut_nom, u.ut_prenom 
                   FROM proposition p
                   JOIN utilisateur u ON p.pr_id_demenageur = u.ut_id
                   WHERE pr_id_annonce = ? AND (pr_statut = 'acceptée' OR pr_statut = 'acceptee')";
  $stmt_acc = $mysqli->prepare($sql_accepted);
  $stmt_acc->bind_param("i", $annonce_id);
  $stmt_acc->execute();
  $res_acc = $stmt_acc->get_result();
  
  while ($row = $res_acc->fetch_assoc()) {
      $demenageurs_acceptes[] = $row;
      
      // 2. Pour chaque déménageur, on regarde s'il y a une note
      $sql_eval = "SELECT * FROM evaluation WHERE ev_id_annonce = ? AND ev_id_demenageur = ?";
      $stmt_eval = $mysqli->prepare($sql_eval);
      $stmt_eval->bind_param("ii", $annonce_id, $row['pr_id_demenageur']);
      $stmt_eval->execute();
      $res_eval = $stmt_eval->get_result();
      if ($res_eval->num_rows > 0) {
          $evaluations_par_demenageur[$row['pr_id_demenageur']] = $res_eval->fetch_assoc();
      }
      $stmt_eval->close();
  }
  $stmt_acc->close();
  
  $titre = $annonce['an_titre'];
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php');
?>

<div class="container my-5">
  <div class="row">
    <!-- COLONNE GAUCHE -->
    <div class="col-lg-8">
      <h1 class="mb-4"><?php echo htmlspecialchars($annonce['an_titre']); ?></h1>
      
      <!-- INFO GÉNÉRALES -->
      <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Informations générales</h5>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <strong>Date :</strong> <?php echo date('d/m/Y', strtotime($annonce['an_date_demenagement'])); ?>
            </div>
            <div class="col-md-6">
              <strong>Heure :</strong> <?php echo ($annonce['an_heure_debut']) ? date('H:i', strtotime($annonce['an_heure_debut'])) : 'Non précisée'; ?>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <strong>De :</strong> <?php echo htmlspecialchars($annonce['an_ville_depart']); ?>
            </div>
            <div class="col-md-6">
              <strong>Vers :</strong> <?php echo htmlspecialchars($annonce['an_ville_arrivee']); ?>
            </div>
          </div>
          <p><?php echo nl2br(htmlspecialchars($annonce['an_description'])); ?></p>
          <div class="row">
            <div class="col-md-4"><strong>Déménageurs :</strong> <?php echo $annonce['an_nombre_demenageurs']; ?></div>
            <?php if ($annonce['an_volume']): ?><div class="col-md-4"><strong>Volume :</strong> <?php echo $annonce['an_volume']; ?> m³</div><?php endif; ?>
            <?php if ($annonce['an_poids']): ?><div class="col-md-4"><strong>Poids :</strong> <?php echo $annonce['an_poids']; ?> kg</div><?php endif; ?>
          </div>
        </div>
      </div>
      
      <!-- DÉTAILS LOGEMENT -->
      <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0">Détails du logement</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <h6 class="text-primary">Départ</h6>
              <ul>
                <li>Type : <?php echo ucfirst($annonce['an_type_logement_depart']); ?></li>
                <?php if ($annonce['an_type_logement_depart'] == 'appartement'): ?>
                  <li>Étage : <?php echo $annonce['an_etage_depart']; ?></li>
                  <li>Ascenseur : <?php echo $annonce['an_ascenseur_depart'] ? 'Oui' : 'Non'; ?></li>
                <?php endif; ?>
              </ul>
            </div>
            <div class="col-md-6">
              <h6 class="text-primary">Arrivée</h6>
              <ul>
                <li>Type : <?php echo ucfirst($annonce['an_type_logement_arrivee']); ?></li>
                <?php if ($annonce['an_type_logement_arrivee'] == 'appartement'): ?>
                  <li>Étage : <?php echo $annonce['an_etage_arrivee']; ?></li>
                  <li>Ascenseur : <?php echo $annonce['an_ascenseur_arrivee'] ? 'Oui' : 'Non'; ?></li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
      
      <!-- PHOTOS -->
      <?php if (count($images) > 0): ?>
      <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0">Photos</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <?php foreach($images as $image): ?>
            <div class="col-md-4">
              <img src="<?php echo htmlspecialchars($image['img_chemin']); ?>" class="img-fluid rounded shadow-sm" alt="Photo">
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- BLOC AVIS DU CLIENT (POUR LE DÉMÉNAGEUR CONNECTÉ) -->
      <?php 
        // On récupère l'évaluation concernant le déménageur connecté s'il y en a une
        $mon_evaluation = null;
        if (isset($_SESSION['ut_id']) && isset($evaluations_par_demenageur[$_SESSION['ut_id']])) {
            $mon_evaluation = $evaluations_par_demenageur[$_SESSION['ut_id']];
        }

        if ($mon_evaluation): 
      ?>
      <div class="card mb-4 border-warning shadow">
        <div class="card-header bg-warning text-dark">
          <h5 class="mb-0"><i class="fas fa-star"></i> Avis du client</h5>
        </div>
        <div class="card-body text-center">
            <h6 class="card-subtitle mb-3 text-muted">Le client vous a laissé une note :</h6>
            <div class="display-6 text-warning mb-2">
                <?php for($i=0; $i<5; $i++) echo ($i < $mon_evaluation['ev_note']) ? '★' : '☆'; ?>
            </div>
            <div class="alert alert-light border d-inline-block">
                <i class="fas fa-quote-left text-muted me-2"></i>
                <span class="fst-italic fs-5"><?php echo nl2br(htmlspecialchars($mon_evaluation['ev_commentaire'])); ?></span>
                <i class="fas fa-quote-right text-muted ms-2"></i>
            </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- BLOC ÉVALUATION (POUR LE CLIENT - MULTI DÉMÉNAGEURS) -->
      <?php 
        if (isset($_SESSION['ut_id']) && $_SESSION['ut_id'] == $annonce['an_id_client'] && 
           ($annonce['an_statut'] == 'terminee' || $annonce['an_statut'] == 'terminée') && count($demenageurs_acceptes) > 0): 
      ?>
      <div class="card mb-4 border-warning shadow">
        <div class="card-header bg-warning text-dark">
          <h5 class="mb-0">Évaluation des déménageurs</h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Le déménagement est terminé. Merci de noter les prestataires :</p>
            
            <?php foreach($demenageurs_acceptes as $dem): 
                $id_dem = $dem['pr_id_demenageur'];
                $eval = isset($evaluations_par_demenageur[$id_dem]) ? $evaluations_par_demenageur[$id_dem] : null;
            ?>
            <div class="card mb-3 border-secondary">
                <div class="card-body">
                    <h6 class="card-title fw-bold">
                        <i class="fas fa-user-check"></i> <?php echo htmlspecialchars($dem['ut_prenom'].' '.$dem['ut_nom']); ?>
                    </h6>
                    <hr>
                    
                    <?php if ($eval): ?>
                        <!-- DÉJÀ NOTÉ -->
                        <div class="row align-items-center">
                            <div class="col-md-4 text-warning fs-5">
                                <?php for($i=0; $i<5; $i++) echo ($i < $eval['ev_note']) ? '★' : '☆'; ?>
                            </div>
                            <div class="col-md-8">
                                <em class="text-muted">"<?php echo nl2br(htmlspecialchars($eval['ev_commentaire'])); ?>"</em>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- À NOTER -->
                        <form action="tt_evaluation.php" method="POST">
                            <input type="hidden" name="id_annonce" value="<?php echo $annonce['an_id']; ?>">
                            <input type="hidden" name="id_demenageur" value="<?php echo $id_dem; ?>">
                            
                            <div class="row g-2 align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                        <div class="form-check form-check-inline m-0">
                                            <input class="form-check-input" type="radio" name="note" value="<?php echo $i; ?>" id="n_<?php echo $id_dem.'_'.$i; ?>" required>
                                            <label class="form-check-label text-warning" for="n_<?php echo $id_dem.'_'.$i; ?>"><?php echo $i; ?>★</label>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <textarea class="form-control form-control-sm" name="commentaire" rows="1" placeholder="Votre avis..." required></textarea>
                                        <button type="submit" class="btn btn-dark btn-sm">Noter</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
        </div>
      </div>
      <?php endif; ?>

      <!-- QUESTIONS / RÉPONSES -->
      <div class="card mb-4 border-dark">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0">Questions / Réponses</h5>
        </div>
        <div class="card-body">
            <?php if (count($questions_list) > 0): ?>
                <div class="list-group mb-3">
                <?php foreach ($questions_list as $q): ?>
                    <div class="list-group-item">
                        <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($q['ut_prenom']); ?> :</h6>
                        <p class="mb-1 fst-italic">"<?php echo nl2br(htmlspecialchars($q['question'])); ?>"</p>
                        <?php if (!empty($q['reponse'])): ?>
                            <div class="p-2 bg-light border-start border-3 border-success rounded">
                                <strong>Réponse :</strong> <?php echo nl2br(htmlspecialchars($q['reponse'])); ?>
                            </div>
                        <?php elseif (isset($_SESSION['ut_id']) && $_SESSION['ut_id'] == $annonce['an_id_client']): ?>
                            <form action="tt_Aquestion.php" method="POST" class="mt-2">
                                <input type="hidden" name="id_question" value="<?php echo $q['id']; ?>">
                                <input type="hidden" name="id_annonce" value="<?php echo $annonce_id; ?>">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="reponse" class="form-control" placeholder="Répondre..." required>
                                    <button class="btn btn-dark" type="submit">Envoyer</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">Aucune question pour le moment.</p>
            <?php endif; ?>

            <?php if (isset($_SESSION['ut_id']) && ($_SESSION['ut_role'] == 'demenageur' || $_SESSION['ut_role'] == 'déménageur') && $_SESSION['ut_id'] != $annonce['an_id_client']): ?>
                <hr>
                <form action="tt_Qquestion.php" method="POST">
                    <input type="hidden" name="id_annonce" value="<?php echo $annonce_id; ?>">
                    <div class="input-group">
                        <input name="question" class="form-control" placeholder="Poser une question..." required>
                        <button type="submit" class="btn btn-outline-dark">Envoyer</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
      </div>

      <!-- PROPOSITIONS (DÉMÉNAGEUR) -->
      <?php if (isset($_SESSION['ut_id']) && ($_SESSION['ut_role'] == 'demenageur' || $_SESSION['ut_role'] == 'déménageur') && $_SESSION['ut_id'] != $annonce['an_id_client']): ?>
      <div class="card mb-4 border-dark">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0">Faire une proposition</h5>
        </div>
        <div class="card-body">
          <?php if ($user_proposition): ?>
            <div class="alert alert-info mb-0">
              <strong>Proposition envoyée :</strong> <?php echo $user_proposition['pr_prix_propose']; ?> €<br>
              Statut : <span class="badge bg-secondary"><?php echo htmlspecialchars($user_proposition['pr_statut']); ?></span>
            </div>
          <?php else: ?>
            <form method="POST" action="tt_proposition.php">
              <input type="hidden" name="id_annonce" value="<?php echo $annonce['an_id']; ?>">
              <div class="mb-3">
                <label for="prix" class="form-label fw-bold">Votre prix (€)</label>
                <input type="number" step="0.01" class="form-control" id="prix" name="prix" required>
              </div>
              <button type="submit" class="btn btn-dark">Envoyer ma proposition</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
      
      <!-- BLOC 6 : PROPOSITIONS REÇUES (CLIENT) -->
      <?php if (isset($_SESSION['ut_id']) && $_SESSION['ut_id'] == $annonce['an_id_client']): ?>
      <div class="card mb-4 border-dark">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0">Propositions reçues</h5>
        </div>
        <div class="card-body">
          <?php if (count($propositions) > 0): ?>
            <?php foreach($propositions as $prop): ?>
            <div class="card mb-3">
              <div class="card-body d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="fw-bold">
                        <?php echo htmlspecialchars($prop['ut_prenom'] . ' ' . $prop['ut_nom']); ?>
                        <!-- Affichage de la note moyenne -->
                        <?php if (!empty($prop['moyenne_avis'])): ?>
                            <span class="text-warning ms-2" title="Note moyenne : <?php echo round($prop['moyenne_avis'], 1); ?>/5 (sur <?php echo $prop['nb_avis']; ?> avis)">
                                <i class="fas fa-star"></i> <?php echo number_format($prop['moyenne_avis'], 1); ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark ms-2 border">Nouveau</span>
                        <?php endif; ?>
                    </h6>
                    <strong><?php echo $prop['pr_prix_propose']; ?> €</strong>
                  </div>
                  <div>
                    <?php if ($prop['pr_statut'] == 'en attente'): ?>
                      <form method="POST" action="tt_accepter_proposition.php" class="d-inline">
                        <input type="hidden" name="id_proposition" value="<?php echo $prop['pr_id']; ?>">
                        <input type="hidden" name="id_annonce" value="<?php echo $annonce['an_id']; ?>">
                        <button type="submit" class="btn btn-dark btn-sm" onclick="return confirm('Accepter ce devis ?')">Accepter</button>
                      </form>
                    <?php else: ?>
                      <span class="badge bg-secondary"><?php echo htmlspecialchars($prop['pr_statut']); ?></span>
                    <?php endif; ?>
                  </div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-muted">Aucune proposition.</p>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
    
    <!-- COLONNE DROITE -->
    <div class="col-lg-4">
      <div class="card mb-4 shadow-sm">
        <div class="card-header bg-secondary text-white">
          <h5 class="mb-0">Statut</h5>
        </div>
        <div class="card-body text-center">
          <span class="badge bg-dark fs-5 mb-3"><?php echo ucfirst($annonce['an_statut']); ?></span>
          <p class="text-muted">Publié le <?php echo date('d/m/Y', strtotime($annonce['an_date_creation'])); ?></p>
          
          <?php if (isset($_SESSION['ut_id']) && $_SESSION['ut_id'] == $annonce['an_id_client']): ?>
          <hr>
          <div class="d-grid gap-2">
            <a href="tdbClient.php" class="btn btn-outline-dark">Mes déménagements</a>
            
            <?php 
              $date_demenagement = new DateTime($annonce['an_date_demenagement']);
              $date_aujourdhui = new DateTime(date('Y-m-d'));
              if ($annonce['an_statut'] == 'en cours' && $date_demenagement <= $date_aujourdhui):
            ?>
              <form action="tt_terminer_demenagement.php" method="POST" onsubmit="return confirm('Confirmer que le déménagement est terminé ?');">
                  <input type="hidden" name="id_annonce" value="<?php echo $annonce['an_id']; ?>">
                  <button type="submit" class="btn btn-success w-100"><i class="fas fa-check"></i> Terminer</button>
              </form>
            <?php endif; ?>

            <form action="tt_supprimer_annonce_client.php" method="POST" onsubmit="return confirm('Supprimer cette annonce ?');">
              <input type="hidden" name="id_annonce" value="<?php echo $annonce['an_id']; ?>">
              <button type="submit" class="btn btn-danger w-100">Supprimer</button>
            </form>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <a href="annonces.php" class="btn btn-dark w-100">← Retour aux annonces</a>
    </div>
  </div>
</div>

<?php
  $mysqli->close();
  include('footer.inc.php');
?>