<?php
  session_start();
  $titre = "Gestion des Utilisateurs";
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php'); // Pour afficher les messages de succès/erreur

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

  // Vérification de l'erreur de connexion
  if ($mysqli->connect_error) {
    $_SESSION['message'] = "Erreur de connexion à la base de données : " . $mysqli->connect_error;
    header('Location: tdbAdmin.php');
    exit();
  }

  // Récupération de l'ID de l'admin connecté (pour ne pas s'auto-afficher)
  $admin_id = $_SESSION['ut_id'];
  
  // Gestion de la recherche
  $search = isset($_GET['search']) ? trim($_GET['search']) : '';
  
  // Construction de la requête SQL de base
  $sql = "SELECT ut_id, ut_nom, ut_prenom, ut_email, ut_role, ut_statut FROM utilisateur WHERE ut_id != ?";

  // Si une recherche est effectuée, on ajoute les conditions
  if (!empty($search)) {
      $sql .= " AND (ut_nom LIKE ? OR ut_prenom LIKE ? OR ut_email LIKE ?)";
  }

  // Préparation de la requête
  $stmt = $mysqli->prepare($sql);
  
  // Vérification robuste de l'erreur de préparation
  if ($stmt === false) {
    $_SESSION['message'] = "Erreur SQL (Prepare) : " . $mysqli->error;
    $mysqli->close();
    header('Location: tdbAdmin.php');
    exit();
  }

  // Liaison des paramètres dynamique
  if (!empty($search)) {
      $searchTerm = "%" . $search . "%";
      // "isss" signifie : integer (admin_id), string (nom), string (prenom), string (email)
      $stmt->bind_param("isss", $admin_id, $searchTerm, $searchTerm, $searchTerm);
  } else {
      // "i" signifie : integer (admin_id)
      $stmt->bind_param("i", $admin_id);
  }

  $stmt->execute();
  $result = $stmt->get_result();
?>

<div class="container my-5">
  <h1 class="mb-4 text-center">Gestion des Utilisateurs</h1>

  <!-- BARRE DE RECHERCHE -->
  <div class="card shadow-sm mb-4">
      <div class="card-body">
          <form method="GET" action="gestionUtilisateurs.php" class="row g-3 align-items-center justify-content-center">
              <div class="col-md-6">
                  <div class="input-group">
                      <span class="input-group-text"><i class="fas fa-search"></i>Recherche</span>
                      <input type="text" class="form-control" name="search" placeholder="Nom, prénom ou email..." value="<?php echo htmlspecialchars($search); ?>">
                  </div>
              </div>
              <div class="col-auto">
                  <button type="submit" class="btn btn-primary">Rechercher</button>
                  <?php if (!empty($search)): ?>
                      <a href="gestionUtilisateurs.php" class="btn btn-secondary">Réinitialiser</a>
                  <?php endif; ?>
              </div>
          </form>
      </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th scope="col">ID</th>
              <th scope="col">Nom</th>
              <th scope="col">Prénom</th>
              <th scope="col">Email</th>
              <th scope="col">Rôle</th>
              <th scope="col">Statut</th>
              <th scope="col" class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Boucle d'affichage des utilisateurs
            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
            ?>
                <tr>
                  <th scope="row"><?php echo $row['ut_id']; ?></th>
                  <td><?php echo htmlspecialchars($row['ut_nom']); ?></td>
                  <td><?php echo htmlspecialchars($row['ut_prenom']); ?></td>
                  <td><?php echo htmlspecialchars($row['ut_email']); ?></td>
                  <td>
                    <span class="badge 
                      <?php echo $row['ut_role'] == 'admin' ? 'bg-danger' : ($row['ut_role'] == 'client' ? 'bg-info' : 'bg-secondary'); ?>">
                      <?php echo htmlspecialchars($row['ut_role']); ?>
                    </span>
                  </td>
                  <td>
                    <?php if ($row['ut_statut'] == 1) : ?>
                      <span class="badge bg-success">Actif</span>
                    <?php else : ?>
                      <span class="badge bg-danger">Inactif</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <!-- Formulaire pour les actions (activer/désactiver/supprimer) -->
                    <form action="tt_gestionUtilisateurs.php" method="POST" class="d-inline-block m-1">
                      <input type="hidden" name="user_id" value="<?php echo $row['ut_id']; ?>">
                      
                      <?php if ($row['ut_statut'] == 1) : ?>
                        <!-- Bouton pour Désactiver -->
                        <input type="hidden" name="new_status" value="0">
                        <button type="submit" name="action" value="toggle_status" class="btn btn-warning btn-sm" title="Désactiver">
                          Désactiver
                        </button>
                      <?php else : ?>
                        <!-- Bouton pour Activer -->
                        <input type="hidden" name="new_status" value="1">
                        <button type="submit" name="action" value="toggle_status" class="btn btn-success btn-sm" title="Activer">
                          Activer
                        </button>
                      <?php endif; ?>
                    </form>

                    <!-- Formulaire pour la Suppression -->
                    <form action="tt_gestionUtilisateurs.php" method="POST" class="d-inline-block m-1" 
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.');">
                      <input type="hidden" name="user_id" value="<?php echo $row['ut_id']; ?>">
                      <button type="submit" name="action" value="delete_user" class="btn btn-danger btn-sm" title="Supprimer">
                        Supprimer
                      </button>
                    </form>
                  </td>
                </tr>
            <?php
              }
            } else {
              echo '<tr><td colspan="7" class="text-center">Aucun utilisateur trouvé' . (!empty($search) ? ' pour cette recherche.' : '.') . '</td></tr>';
            }
            // Fermeture des connexions
            $stmt->close();
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