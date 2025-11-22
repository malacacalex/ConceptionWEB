<?php
  session_start();
  $titre = "Gestion des Annonces";
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

  // Connexion à la BDD
  require_once("param.inc.php");
  $mysqli = new mysqli($host, $login, $passwd, $dbname);

  if ($mysqli->connect_error) {
    $_SESSION['message'] = "Erreur de connexion à la base de données : " . $mysqli->connect_error;
    header('Location: tdbAdmin.php');
    exit();
  }
  
  // Récupération de toutes les annonces avec le nom du client
  $sql = "SELECT 
            a.an_id, a.an_titre, a.an_ville_depart, a.an_ville_arrivee, a.an_date_demenagement, a.an_statut, a.an_volume,
            u.ut_nom, u.ut_prenom, u.ut_email
          FROM 
            annonce a
          JOIN 
            utilisateur u ON a.an_id_client = u.ut_id
          ORDER BY 
            a.an_date_creation DESC";
            
  $result = $mysqli->query($sql);

  // Vérifier si la requête a réussi
  if ($result === false) {
    $_SESSION['message'] = "Erreur SQL lors de la récupération des annonces : " . $mysqli->error;
    $mysqli->close();
    header('Location: tdbAdmin.php');
    exit();
  }

?>

<div class="container my-5">
  <h1 class="mb-4 text-center">Gestion des Annonces</h1>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th scope="col">ID</th>
              <th scope="col">Titre</th>
              <th scope="col">Client</th>
              <th scope="col">Départ / Arrivée</th>
              <th scope="col">Date Déménagement</th>
              <th scope="col">Volume (m³)</th>
              <th scope="col">Statut</th>
              <th scope="col" class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                
                $status_class = 'bg-primary'; 
                switch ($row['an_statut']) {
                    case 'ouverte':
                        $status_class = 'bg-success';
                        break;
                    case 'en cours':
                        $status_class = 'bg-warning text-dark';
                        break;
                    case 'terminée':
                        $status_class = 'bg-secondary';
                        break;
                }
            ?>
                <tr>
                  <th scope="row"><?php echo $row['an_id']; ?></th>
                  <td><?php echo htmlspecialchars($row['an_titre']); ?></td>
                  <td><?php echo htmlspecialchars($row['ut_prenom'] . ' ' . $row['ut_nom']); ?></td>
                  <td>
                    <?php echo htmlspecialchars($row['an_ville_depart']); ?> → <?php echo htmlspecialchars($row['an_ville_arrivee']); ?>
                  </td>
                  <td><?php echo date('d/m/Y', strtotime($row['an_date_demenagement'])); ?></td>
                  <td><?php echo htmlspecialchars($row['an_volume'] ?? 'N/A'); ?></td>
                  <td>
                    <span class="badge <?php echo $status_class; ?>">
                      <?php echo htmlspecialchars(ucfirst($row['an_statut'])); ?>
                    </span>
                  </td>
                  <td class="text-center">
                    <!-- Bouton pour Consulter/Modifier -->
                    <a href="modifierAnnonce.php?id=<?php echo $row['an_id']; ?>" class="btn btn-info btn-sm m-1" title="Consulter/Modifier">
                      Consulter
                    </a>

                    <!-- Formulaire pour la Suppression -->
                    <form action="tt_gestionAnnonces.php" method="POST" class="d-inline-block m-1" 
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce et toutes ses propositions ? Cette action est irréversible.');">
                      <input type="hidden" name="annonce_id" value="<?php echo $row['an_id']; ?>">
                      <button type="submit" name="action" value="delete_annonce" class="btn btn-danger btn-sm" title="Supprimer">
                        Supprimer
                      </button>
                    </form>
                  </td>
                </tr>
            <?php
              }
            } else {
              echo '<tr><td colspan="8" class="text-center">Aucune annonce trouvée dans la base de données.</td></tr>';
            }
            $mysqli->close();
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php
  include('footer.inc.php');
?>