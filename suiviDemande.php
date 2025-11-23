<?php
  session_start();

  // Vérification si l'user est un client
  if (!isset($_SESSION['ut_id']) || $_SESSION['ut_role'] !== 'client') {
    $_SESSION['erreur'] = "Accès refusé. Page réservée aux clients.";
    header('Location: index.php');
    exit();
  }

  $titre = "Suivi de mes demandes";
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php');

  $id_client = $_SESSION['ut_id'];
?>

<div class="container my-5">
  <h1 class="mb-4 text-center">Suivi de vos déménagements</h1>
  
  <div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
      <h5 class="mb-0">Vos annonces en cours</h5>
    </div>
    <div class="card-body">
      <div class="list-group">
        <?php
          require_once("param.inc.php");
          $mysqli = new mysqli($host, $login, $passwd, $dbname);
          $mysqli->set_charset("utf8"); 

          // Récupération des annonces du client
          $sql = "SELECT an_id, an_titre, an_statut, an_date_demenagement FROM annonce WHERE an_id_client = ? ORDER BY an_date_demenagement DESC";

          if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("i", $id_client);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                
                // --- LOGIQUE DE STATUT CALCULÉ EN PHP ---
                $statut_bdd = $row['an_statut'];
                $date_demenagement = new DateTime($row['an_date_demenagement']);
                $date_aujourdhui = new DateTime(date('Y-m-d')); // Date du jour à 00:00:00

                $statut_affiche = ucfirst($statut_bdd);
                $bouton_terminer = false;

                if ($statut_bdd == 'ouverte') {
                    $badge_color = 'bg-success';
                } elseif ($statut_bdd == 'en cours') {
                    // CORRECTION ICI : <= au lieu de <
                    if ($date_demenagement <= $date_aujourdhui) {
                        // Si la date est passée OU si c'est aujourd'hui -> On peut terminer
                        $statut_affiche = 'À finaliser';
                        $badge_color = 'bg-warning text-dark';
                        $bouton_terminer = true; 
                    } else {
                        $statut_affiche = 'En cours';
                        $badge_color = 'bg-primary';
                    }
                } elseif ($statut_bdd == 'terminée' || $statut_bdd == 'terminee') {
                    $statut_affiche = 'Terminée';
                    $badge_color = 'bg-dark';
                }
                // -----------------------------------------
        ?>      
                <div class="list-group-item list-group-item-action">
                  <div class="d-flex w-100 justify-content-between align-items-center">
                    <h5 class="mb-1"><?php echo htmlspecialchars($row['an_titre']); ?></h5>
                    <span class="badge <?php echo $badge_color; ?> rounded-pill"><?php echo $statut_affiche; ?></span>
                  </div>
                  <p class="mb-1">Prévu le : <strong><?php echo date('d/m/Y', strtotime($row['an_date_demenagement'])); ?></strong></p>
                  
                  <div class="mt-2 d-flex gap-2">
                    <a href="detailAnnonce.php?id=<?php echo $row['an_id']; ?>" class="btn btn-outline-dark btn-sm">
                      Voir les détails et propositions
                    </a>

                    <?php if ($bouton_terminer): ?>
                        <form action="tt_terminer_demenagement.php" method="POST" onsubmit="return confirm('Confirmez-vous que le déménagement est terminé ? Cela permettra de laisser une évaluation.');">
                            <input type="hidden" name="id_annonce" value="<?php echo $row['an_id']; ?>">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i> Marquer comme Terminé
                            </button>
                        </form>
                    <?php endif; ?>

                  </div>
                </div>
        <?php
              }
            } else {
              echo '<div class="alert alert-info text-center">Vous n\'avez publié aucune annonce pour le moment.</div>';
            }
            $stmt->close();
          }
          $mysqli->close();
        ?>
      </div>
    </div>
  </div>
  
  <div class="mt-4 text-center">
    <a href="tdbClient.php" class="btn btn-secondary">Retour au tableau de bord</a>
  </div>
</div>

<?php
  include('footer.inc.php');
?>