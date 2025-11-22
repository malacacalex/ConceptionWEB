<?php
  session_start();
  $titre = "Modifier Annonce";
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php');

  // Vérification des droits d'accès
  $role = $_SESSION['ut_role'] ?? '';
  if (!isset($_SESSION['ut_id']) || ($role != 'admin' && $role != 'administrateur')) {
    $_SESSION['message'] = "Accès refusé. Vous devez être connecté en tant qu'administrateur.";
    header('Location: index.php');
    exit();
  }

  // Récupération de l'ID de l'annonce
  $annonce_id = $_GET['id'] ?? 0;
  $annonce_id = intval($annonce_id);

  if ($annonce_id <= 0) {
      $_SESSION['message'] = "ID d'annonce non valide.";
      header('Location: gestionAnnonces.php');
      exit();
  }

  // Connexion à la BDD et récupération des données
  require_once("param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);

  if ($mysqli->connect_error) {
    $_SESSION['message'] = "Erreur de connexion à la base de données.";
    header('Location: gestionAnnonces.php');
    exit();
  }

  // Requête pour récupérer toutes les données de l'annonce, y compris le nom du client
  $stmt = $mysqli->prepare("
    SELECT 
      a.*, u.ut_nom, u.ut_prenom, u.ut_email 
    FROM 
      annonce a 
    JOIN 
      utilisateur u ON a.an_id_client = u.ut_id
    WHERE 
      a.an_id = ?
  ");
  
  if ($stmt === false) {
      $_SESSION['message'] = "Erreur SQL lors de la préparation (Select).";
      $mysqli->close();
      header('Location: gestionAnnonces.php');
      exit();
  }

  $stmt->bind_param("i", $annonce_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 0) {
      $_SESSION['message'] = "Annonce introuvable.";
      $stmt->close();
      $mysqli->close();
      header('Location: gestionAnnonces.php');
      exit();
  }

  $annonce = $result->fetch_assoc();
  $stmt->close();
  $mysqli->close();
  
  // Liste des statuts possibles pour le menu déroulant
  $statuts_possibles = ['ouverte', 'en cours', 'terminée', 'annulée'];
?>

<div class="container my-5">
  <h1 class="mb-4 text-center">Modification de l'Annonce n°<?php echo $annonce['an_id']; ?></h1>
  
  <div class="card shadow-lg p-4">
    <div class="card-header bg-primary text-white mb-4">
      <h5 class="mb-0">Client : <?php echo htmlspecialchars($annonce['ut_prenom'] . ' ' . $annonce['ut_nom']); ?> (<?php echo htmlspecialchars($annonce['ut_email']); ?>)</h5>
    </div>

    <form method="POST" action="tt_modifierAnnonce.php"> <!-- Lien mis à jour -->
      <input type="hidden" name="an_id" value="<?php echo $annonce['an_id']; ?>">

      <div class="row mb-3">
        <div class="col-md-12">
          <label for="an_titre" class="form-label">Titre de l'Annonce</label>
          <input type="text" class="form-control" id="an_titre" name="an_titre" 
                 value="<?php echo htmlspecialchars($annonce['an_titre']); ?>" required>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <label for="an_ville_depart" class="form-label">Ville de Départ</label>
          <input type="text" class="form-control" id="an_ville_depart" name="an_ville_depart" 
                 value="<?php echo htmlspecialchars($annonce['an_ville_depart']); ?>" required>
        </div>
        <div class="col-md-6">
          <label for="an_ville_arrivee" class="form-label">Ville d'Arrivée</label>
          <input type="text" class="form-control" id="an_ville_arrivee" name="an_ville_arrivee" 
                 value="<?php echo htmlspecialchars($annonce['an_ville_arrivee']); ?>" required>
        </div>
      </div>
      
      <div class="row mb-3">
        <div class="col-md-4">
          <label for="an_date_demenagement" class="form-label">Date de Déménagement</label>
          <input type="date" class="form-control" id="an_date_demenagement" name="an_date_demenagement" 
                 value="<?php echo htmlspecialchars($annonce['an_date_demenagement']); ?>" required>
        </div>
        <div class="col-md-4">
          <label for="an_volume" class="form-label">Volume (m³)</label>
          <input type="number" step="0.1" class="form-control" id="an_volume" name="an_volume" 
                 value="<?php echo htmlspecialchars($annonce['an_volume']); ?>" required min="0.1">
        </div>
        <div class="col-md-4">
          <label for="an_statut" class="form-label">Statut de l'Annonce</label>
          <select class="form-select" id="an_statut" name="an_statut" required>
            <?php foreach ($statuts_possibles as $statut): ?>
                <option value="<?php echo $statut; ?>" 
                    <?php echo ($statut == $annonce['an_statut'] ? 'selected' : ''); ?>>
                    <?php echo ucfirst($statut); ?>
                </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-md-12">
          <label for="an_description" class="form-label">Description Détaillée</label>
          <textarea class="form-control" id="an_description" name="an_description" rows="5" required><?php echo htmlspecialchars($annonce['an_description']); ?></textarea>
        </div>
      </div>

      <div class="d-flex justify-content-between">
        <button type="submit" class="btn btn-success btn-lg">Enregistrer les Modifications</button>
        <a href="gestionAnnonces.php" class="btn btn-secondary btn-lg">Annuler et Retour</a>
      </div>
    </form>
  </div>
</div>

<?php
  include('footer.inc.php');
?>