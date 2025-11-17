<?php
  session_start();

  // Verification si l'user est un client
  if (!isset($_SESSION['ut_id']) || $_SESSION['ut_role'] !== 'client') {
    $_SESSION['erreur'] = "Vous n'etes pas un client, vous ne pouvez pas creer de demande.";
    header('Location: index.php');
    exit();
  }

  $titre = "Mes demenagements";
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php');

  $id_client = $_SESSION['ut_id'];
?>
  <h1>Voici vos demenagements en cours :</h1>

<a href="nvlDemande.php" class="btn btn-dark mb-3">Poster une nouvelle demande</a>

<h3>Mes annonces en cours :</h3>
  <div class="list-group">
    <?php
      require_once("param.inc.php");
      $mysqli = new mysqli($host, $login, $passwd, $dbname);
      $mysqli->set_charset("utf8"); 

      $sql = "select an_id, an_titre, an_statut, an_date_demenagement from annonce where an_id_client = ?";

      if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_client);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            $statut_class = '';
                  if ($row['an_statut'] == 'ouverte') $statut_class = 'list-group-item-dark';
                  if ($row['an_statut'] == 'en cours') $statut_class = 'list-group-item-dark';
                  if ($row['an_statut'] == 'terminee') $statut_class = 'list-group-item-dark';
    ?>      
                  <div class="list-group-item list-group-item-action <?php echo $statut_class; ?>">
                    <div class="d-flex w-100 justify-content-between">
                      <h5 class="mb-1"><?php echo htmlspecialchars($row['an_titre']); ?></h5>
                      <small>Pour le: <?php echo date('d/m/Y', strtotime($row['an_date_demenagement'])); ?></small>
                    </div>
                    <p class="mb-1">Statut : <strong><?php echo htmlspecialchars($row['an_statut']); ?></strong></p>
                    
                    <a href="detailAnnonce.php?id=<?php echo $row['an_id']; ?>" class="btn btn-dark btn-sm">
                      Voir les propositions
                    </a>
                    </div>
    <?php
              }
          } else {
              echo "<p>Vous n'avez publie aucune annonce pour le moment.</p>";
          }
          $stmt->close();
      }
      $mysqli->close();
    ?>
  </div>




<?php
  include('footer.inc.php');
?>