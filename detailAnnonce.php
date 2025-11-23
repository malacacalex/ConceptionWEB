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
    die("Erreur de connexion a la base de donnees");
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
  // On stocke les questions pour les afficher plus bas
  $questions_list = $res_questions->fetch_all(MYSQLI_ASSOC);
  $stmt_q->close();

  // propositions propriétaire
  $propositions = [];
  if (isset($_SESSION['ut_id']) && $_SESSION['ut_id'] == $annonce['an_id_client']) {
    $prop_query = "SELECT p.*, u.ut_nom, u.ut_prenom, u.ut_email 
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
  
  // proposition déménageur
  $user_proposition = null;
  // On vérifie le rôle avec ou sans accent pour la sécurité
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
  
  $titre = $annonce['an_titre'];
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php');
?>

<div class="row">
  <div class="col-lg-8">
    <h1><?php echo htmlspecialchars($annonce['an_titre']); ?></h1>
    
    <div class="card mb-4">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Informations générales</h5>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Date du déménagement :</strong><br>
            <?php echo date('d/m/Y', strtotime($annonce['an_date_demenagement'])); ?>
          </div>
          <div class="col-md-6">
            <strong>Heure de début :</strong><br>
            <?php echo date('H:i', strtotime($annonce['an_heure_debut'])); ?>
          </div>
        </div>
        
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>Ville de départ :</strong><br>
            <?php echo htmlspecialchars($annonce['an_ville_depart']); ?>
          </div>
          <div class="col-md-6">
            <strong>Ville d'arrivée :</strong><br>
            <?php echo htmlspecialchars($annonce['an_ville_arrivee']); ?>
          </div>
        </div>
        
        <div class="mb-3">
          <strong>Description :</strong><br>
          <p><?php echo nl2br(htmlspecialchars($annonce['an_description'])); ?></p>
        </div>
        
        <div class="row mb-3">
          <div class="col-md-4">
            <strong>Nombre de déménageurs :</strong><br>
            <?php echo $annonce['an_nombre_demenageurs']; ?>
          </div>
          <?php if ($annonce['an_volume']): ?>
          <div class="col-md-4">
            <strong>Volume :</strong><br>
            <?php echo $annonce['an_volume']; ?> m³
          </div>
          <?php endif; ?>
          <?php if ($annonce['an_poids']): ?>
          <div class="col-md-4">
            <strong>Poids :</strong><br>
            <?php echo $annonce['an_poids']; ?> kg
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <div class="card mb-4">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Détails du logement</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6>Départ</h6>
            <ul>
              <li><strong>Type :</strong> <?php echo ucfirst($annonce['an_type_logement_depart']); ?></li>
              <?php if ($annonce['an_type_logement_depart'] == 'appartement'): ?>
                <li><strong>Étage :</strong> <?php echo $annonce['an_etage_depart']; ?></li>
                <li><strong>Ascenseur :</strong> <?php echo $annonce['an_ascenseur_depart'] ? 'Oui' : 'Non'; ?></li>
              <?php endif; ?>
            </ul>
          </div>
          <div class="col-md-6">
            <h6>Arrivée</h6>
            <ul>
              <li><strong>Type :</strong> <?php echo ucfirst($annonce['an_type_logement_arrivee']); ?></li>
              <?php if ($annonce['an_type_logement_arrivee'] == 'appartement'): ?>
                <li><strong>Étage :</strong> <?php echo $annonce['an_etage_arrivee']; ?></li>
                <li><strong>Ascenseur :</strong> <?php echo $annonce['an_ascenseur_arrivee'] ? 'Oui' : 'Non'; ?></li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
    
    <?php if (count($images) > 0): ?>
    <div class="card mb-4">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Photos</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <?php foreach($images as $image): ?>
          <div class="col-md-4">
            <img src="<?php echo htmlspecialchars($image['img_chemin']); ?>" 
                 class="img-fluid rounded" 
                 alt="Photo du déménagement">
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="card mb-4 border-dark">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Questions / Réponses</h5>
      </div>
      <div class="card-body">
        
        <?php if (count($questions_list) > 0): ?>
            <div class="list-group mb-3">
            <?php foreach ($questions_list as $q): ?>
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1 fw-bold">
                            Question de <?php echo htmlspecialchars($q['ut_prenom']); ?> :
                        </h6>
                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($q['date_question'])); ?></small>
                    </div>
                    <p class="mb-1 fst-italic">"<?php echo nl2br(htmlspecialchars($q['question'])); ?> "</p>

                    <?php if (!empty($q['reponse'])): ?>
                        <div class="mt-2 ps-3 border-start border-3 border-dark">
                            <strong>Réponse du client :</strong>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($q['reponse'])); ?></p>
                            <small class="text-muted">Le <?php echo date('d/m/Y H:i', strtotime($q['date_reponse'])); ?></small>
                        </div>
                    
                    <?php elseif (isset($_SESSION['ut_id']) && $_SESSION['ut_id'] == $annonce['an_id_client']): ?>
                        <form action="tt_Aquestion.php" method="POST" class="mt-2">
                            <input type="hidden" name="id_question" value="<?php echo $q['id']; ?>">
                            <input type="hidden" name="id_annonce" value="<?php echo $annonce_id; ?>">
                            <div class="input-group input-group-sm">
                                <input type="text" name="reponse" class="form-control" placeholder="Votre réponse..." required>
                                <button class="btn btn-dark" type="submit">Répondre</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <p class="text-muted small mt-1"><em>En attente de réponse...</em></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">Aucune question pour le moment.</p>
        <?php endif; ?>

        <?php if (isset($_SESSION['ut_id']) && ($_SESSION['ut_role'] == 'demenageur' || $_SESSION['ut_role'] == 'déménageur') && $_SESSION['ut_id'] != $annonce['an_id_client']): ?>
            <hr>
            <h6>Poser une question au client :</h6>
            <form action="tt_Qquestion.php" method="POST">
                <input type="hidden" name="id_annonce" value="<?php echo $annonce_id; ?>">
                <div class="mb-3">
                    <textarea name="question" class="form-control" rows="2" placeholder="Ex: Y a-t-il un stationnement facile devant l'immeuble ?" required></textarea>
                </div>
                <button type="submit" class="btn btn-outline-dark btn-sm">Envoyer la question</button>
            </form>
        <?php endif; ?>

      </div>
    </div>
    <?php if (isset($_SESSION['ut_id']) && ($_SESSION['ut_role'] == 'demenageur' || $_SESSION['ut_role'] == 'déménageur') && $_SESSION['ut_id'] != $annonce['an_id_client']): ?>
    <div class="card mb-4 border-dark">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Faire une proposition</h5>
      </div>
      <div class="card-body">
        <?php if ($user_proposition): ?>
          <div class="alert alert-dark">
            <strong>Vous avez déjà fait une proposition pour ce déménagement :</strong><br>
            Prix : <?php echo $user_proposition['pr_prix_propose']; ?> €<br>
            Statut : 
            <?php 
              switch($user_proposition['pr_statut']) {
                case 'en attente': echo '<span class="badge bg-secondary">En attente</span>'; break;
                case 'acceptée': echo '<span class="badge bg-success">Acceptée</span>'; break;
                case 'refusée': echo '<span class="badge bg-danger">Refusée</span>'; break;
              }
            ?>
          </div>
        <?php else: ?>
          <form method="POST" action="tt_proposition.php">
            <input type="hidden" name="id_annonce" value="<?php echo $annonce['an_id']; ?>">
            
            <div class="mb-3">
              <label for="prix" class="form-label">Votre prix (€) *</label>
              <input type="number" step="0.01" class="form-control" id="prix" name="prix" required>
            </div>
            
            <div class="mb-3">
              <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
              <textarea class="form-control" id="commentaire" name="commentaire" rows="3" 
                        placeholder="Ajoutez des informations complémentaires..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-dark">Envoyer ma proposition</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['ut_id']) && $_SESSION['ut_id'] == $annonce['an_id_client']): ?>
    <div class="card mb-4 border-dark">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Propositions reçues (<?php echo count($propositions); ?>)</h5>
      </div>
      <div class="card-body">
        <?php if (count($propositions) > 0): ?>
          <?php foreach($propositions as $prop): ?>
          <div class="card mb-3">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h6><?php echo htmlspecialchars($prop['ut_prenom'] . ' ' . $prop['ut_nom']); ?></h6>
                  <p class="mb-1"><strong>Prix proposé :</strong> <?php echo $prop['pr_prix_propose']; ?> €</p>
                  <p class="mb-0"><small class="text-muted">Proposé le <?php echo date('d/m/Y à H:i', strtotime($prop['pr_date_proposition'])); ?></small></p>
                </div>
                <div>
                  <?php if ($prop['pr_statut'] == 'en attente'): ?>
                    <form method="POST" action="tt_accepter_proposition.php" class="d-inline">
                      <input type="hidden" name="id_proposition" value="<?php echo $prop['pr_id']; ?>">
                      <input type="hidden" name="id_annonce" value="<?php echo $annonce['an_id']; ?>">
                      <button type="submit" class="btn btn-dark btn-sm">Accepter</button>
                    </form>
                  <?php elseif ($prop['pr_statut'] == 'acceptee' || $prop['pr_statut'] == 'accepte' || $prop['pr_statut'] == 'acceptée'): ?>
                    <span class="badge bg-success">Acceptée</span>
                  <?php else: ?>
                    <span class="badge bg-danger">Refusée</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-muted">Aucune proposition pour le moment.</p>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
  
  <div class="col-lg-4">
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Informations</h5>
      </div>
      <div class="card-body">
        <p><strong>Statut :</strong> 
          <?php 
            switch($annonce['an_statut']) {
              case 'ouverte': echo '<span class="badge bg-secondary">En attente</span>'; break;
              case 'en cours': echo '<span class="badge bg-warning text-dark">En cours</span>'; break;
              case 'terminee': 
              case 'terminée': echo '<span class="badge bg-success">Terminé</span>'; break;
            }
          ?>
        </p>
        <p><strong>Publié le :</strong><br><?php echo date('d/m/Y à H:i', strtotime($annonce['an_date_creation'])); ?></p>
        
        <?php if (isset($_SESSION['ut_id']) && $_SESSION['ut_id'] == $annonce['an_id_client']): ?>
        <hr>
        <div class="d-grid gap-2">
          <a href="tdbClient.php" class="btn btn-outline-dark">Mes déménagements</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
    
    <div class="card">
      <div class="card-body">
        <a href="annonces.php" class="btn btn-dark w-100">← Retour aux annonces</a>
      </div>
    </div>
  </div>
</div>

<?php
  $mysqli->close();
  include('footer.inc.php');
?>