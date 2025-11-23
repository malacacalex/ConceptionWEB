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
                // Style conditionnel selon le statut
                $badge_color = 'bg-secondary';
                if ($row['an_statut'] == 'ouverte') $badge_color = 'bg-success';
                if ($row['an_statut'] == 'en cours') $badge_color = 'bg-warning text-dark';
                if ($row['an_statut'] == 'terminée' || $row['an_statut'] == 'terminee') $badge_color = 'bg-dark';
        ?>      
                <div class="list-group-item list-group-item-action">
                  <div class="d-flex w-100 justify-content-between align-items-center">
                    <h5 class="mb-1"><?php echo htmlspecialchars($row['an_titre']); ?></h5>
                    <span class="badge <?php echo $badge_color; ?> rounded-pill"><?php echo ucfirst($row['an_statut']); ?></span>
                  </div>
                  <p class="mb-1">Prévu le : <strong><?php echo date('d/m/Y', strtotime($row['an_date_demenagement'])); ?></strong></p>
                  
                  <div class="mt-2">
                    <a href="detailAnnonce.php?id=<?php echo $row['an_id']; ?>" class="btn btn-outline-dark btn-sm">
                      Voir les détails et propositions
                    </a>
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