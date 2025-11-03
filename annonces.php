<?php
  session_start();
  $titre = "Annonces";
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php');
?>
  
  <h1>Annonces de déménagements :</h1>
  <p>Retrouvez ici toutes les demandes de déménagement en cours.</p>

  <div class="row">
    <?php

      require_once("param.inc.php");
      $mysqli = new mysqli($host, $login, $passwd, $dbname);

      $sql = "select an_id, an_titre, an_description, an_date_demenagement, an_ville_depart, an_ville_arrivee from annonce where an_statut = 'ouverte'";
      
      $result = $mysqli->query($sql);

      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
    ?>
              <div class="col-md-6 col-lg-4 mb-3">
                  <div class="card h-100">
                      <div class="card-body">
                          <h5 class="card-title"><?php echo htmlspecialchars($row['an_titre']); ?></h5>
                          <h6 class="card-subtitle mb-2 text-muted">
                            <?php echo htmlspecialchars($row['an_ville_depart']); ?> → <?php echo htmlspecialchars($row['an_ville_arrivee']); ?>
                          </h6>
                          <p class="card-text"><?php echo htmlspecialchars($row['an_description']); ?></p>
                      </div>
                      <ul class="list-group list-group-flush">
                          <li class="list-group-item">Date: <?php echo date('d/m/Y', strtotime($row['an_date_demenagement'])); ?></li>
                      </ul>
                      <div class="card-body">
                        <?php
                          // On affiche le bouton "Proposer un prix" SEULEMENT si l'utilisateur
                          [cite_start]// est connecté ET qu'il a le rôle "déménageur" [cite: 149]
                          if (isset($_SESSION['ut_role']) && $_SESSION['ut_role'] == 'déménageur') {
                        ?>
                            <a href="form_proposition.php?id_annonce=<?php echo $row['an_id']; ?>" class="btn btn-primary">
                              Proposer un prix
                            </a>
                        <?php
                          } else {
                        ?>
                            <a href="connexion.php" class="btn btn-outline-primary btn-sm">
                              Connectez-vous (en déménageur) pour proposer un prix
                            </a>
                        <?php
                          }
                        ?>
                      </div>
                  </div>
              </div>
    <?php
          }
      } else {
          echo "<div class='col'><p>Il n'y a aucune annonce ouverte pour le moment.</p></div>";
      }
      $mysqli->close();
    ?>
  </div>

<?php
  include('footer.inc.php');
?>