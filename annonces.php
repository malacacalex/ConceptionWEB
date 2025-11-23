<?php
  session_start();
  $titre = "Annonces de déménagement"; 
  
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php');

  // Connexion BDD 
  require_once("param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);
  $mysqli->set_charset("utf8"); // Important pour les accents
  
  if ($mysqli->connect_error) {
    die("Erreur de connexion a la base de donnees");
  }
  
  // --- LOGIQUE DE FILTRAGE ---
  $ville_recherchee = isset($_GET['ville']) ? trim($_GET['ville']) : '';

  // Requête de base : On veut les annonces ouvertes + infos client
  $sql = "SELECT a.*, u.ut_prenom, u.ut_nom 
          FROM annonce a 
          JOIN utilisateur u ON a.an_id_client = u.ut_id 
          WHERE a.an_statut = 'ouverte'";

  // Si une ville est saisie, on ajoute le filtre
  if (!empty($ville_recherchee)) {
      // On cherche dans la ville de départ OU la ville d'arrivée
      $sql .= " AND (a.an_ville_depart LIKE ? OR a.an_ville_arrivee LIKE ?)";
  }

  $sql .= " ORDER BY a.an_date_creation DESC";

  // Préparation de la requête
  $stmt = $mysqli->prepare($sql);

  if (!empty($ville_recherchee)) {
      $param = "%" . $ville_recherchee . "%"; // Le % permet de chercher "Paris" si on tape "Par"
      $stmt->bind_param("ss", $param, $param);
  }

  $stmt->execute();
  $result = $stmt->get_result();
?>

<div class="container my-5">
  <h1 class="mb-4">Annonces de déménagement</h1>
  <p class="lead">Découvrez les demandes de déménagement en cours</p>

  <?php if (isset($_SESSION['ut_id']) && $_SESSION['ut_role'] == 'client'): ?>
    <div class="alert alert-dark">
      <strong>Vous êtes client :</strong> <a href="nvlDemande.php" class="alert-link">Créer une nouvelle demande de déménagement</a>
    </div>

  <?php elseif (isset($_SESSION['ut_id']) && ($_SESSION['ut_role'] == 'déménageur' || $_SESSION['ut_role'] == 'demenageur')): ?>
    <div class="alert alert-dark">
      <strong>Vous êtes déménageur :</strong> Consultez les annonces ci-dessous et proposez vos services !
    </div>

  <?php else: ?>
    <div class="alert alert-dark">
      <strong>Vous n'êtes pas connecté :</strong> <a href="connexion.php" class="alert-link">Connectez-vous</a> pour créer une demande ou proposer vos services.
    </div>
  <?php endif; ?>

  <!-- BARRE DE FILTRE -->
  <div class="card shadow-sm mb-4 border-dark">
    <div class="card-body">
        <form method="GET" action="annonces.php" class="row g-2 align-items-center">
            <div class="col-md-auto">
                <label for="ville" class="col-form-label fw-bold">Filtrer par ville :</label>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-map-marker-alt"></i></span>
                    <input type="text" class="form-control" id="ville" name="ville" 
                           placeholder="Ex: Paris, Lyon..." 
                           value="<?php echo htmlspecialchars($ville_recherchee); ?>">
                </div>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">Rechercher</button>
                <?php if (!empty($ville_recherchee)): ?>
                    <a href="annonces.php" class="btn btn-outline-secondary">Réinitialiser</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
  </div>

  <div class="row g-4 mt-2">
    <?php
    if ($result && $result->num_rows > 0) {
      while ($annonce = $result->fetch_assoc()) {
        // Compter les propositions pour cette annonce
        $count_query = "SELECT COUNT(*) as nb_propositions FROM proposition WHERE pr_id_annonce = ?";
        $count_stmt = $mysqli->prepare($count_query);
        $count_stmt->bind_param("i", $annonce['an_id']);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $nb_propositions = $count_result->fetch_assoc()['nb_propositions'];
        $count_stmt->close();
    ?>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title text-primary"><?php echo htmlspecialchars($annonce['an_titre']); ?></h5>
            <h6 class="card-subtitle mb-2 text-muted">
              <!-- Mise en évidence de la ville si recherchée -->
              <i class="fas fa-route"></i> 
              <?php echo htmlspecialchars($annonce['an_ville_depart']); ?> → <?php echo htmlspecialchars($annonce['an_ville_arrivee']); ?>
            </h6>
            
            <p class="card-text mt-3">
              <?php 
                $description = htmlspecialchars($annonce['an_description']);
                echo nl2br(strlen($description) > 150 ? substr($description, 0, 150) . '...' : $description); 
              ?>
            </p>
            
            <ul class="list-group list-group-flush mb-3">
              <li class="list-group-item"><strong>Date :</strong> <?php echo date('d/m/Y', strtotime($annonce['an_date_demenagement'])); ?></li>
              <li class="list-group-item"><strong>Heure :</strong> <?php echo ($annonce['an_heure_debut']) ? date('H:i', strtotime($annonce['an_heure_debut'])) : 'Non précisée'; ?></li>
              <li class="list-group-item"><strong>Déménageurs :</strong> <?php echo $annonce['an_nombre_demenageurs']; ?></li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                  Propositions reçues
                  <span class="badge bg-dark rounded-pill"><?php echo $nb_propositions; ?></span>
              </li>
            </ul>

            <div class="d-grid gap-2">
                <a href="detailAnnonce.php?id=<?php echo $annonce['an_id']; ?>" class="btn btn-outline-dark btn-sm">
                    Voir les détails
                </a>
                
                <?php
                  // Bouton Proposer visible uniquement pour les déménageurs connectés
                  if(isset($_SESSION['ut_role']) && ($_SESSION['ut_role'] == 'déménageur' || $_SESSION['ut_role'] == 'demenageur')) {
                ?>
                    <a href="detailAnnonce.php?id=<?php echo $annonce['an_id']; ?>" class="btn btn-primary btn-sm">
                      Proposer un prix
                    </a>
                <?php
                  }
                ?>
            </div>

          </div>
          <div class="card-footer text-muted text-end">
            <small>Publié le <?php echo date('d/m/Y', strtotime($annonce['an_date_creation'])); ?></small>
          </div>
        </div>
      </div>
    <?php
      }
    } else {
      echo '<div class="col-12"><div class="alert alert-info text-center">Aucune annonce trouvée' . (!empty($ville_recherchee) ? ' pour la ville "' . htmlspecialchars($ville_recherchee) . '"' : '') . '.</div></div>';
    }
    
    $stmt->close();
    $mysqli->close();
    ?>
  </div>
</div>

<?php
  include('footer.inc.php');
?>