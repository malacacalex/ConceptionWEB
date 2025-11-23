<?php
session_start();
  // Verification si l'user est un client 
  if (!isset($_SESSION['ut_id']) || $_SESSION['ut_role'] !== 'client') {
    $_SESSION['erreur'] = "Vous n'etes pas un client, vous ne pouvez pas creer de demande.";
    header('Location: index.php');
    exit();
  }

$titre = "Nouvelle Demande";
include('header.inc.php');
include('menu.inc.php');
include('message.inc.php');
?>

<div class="row">
  <div class="col-lg-10 mx-auto">
    <h1>Creer une demande de demenagement</h1>
    <p class="lead">Remplissez le formulaire ci-dessous pour publier votre annonce</p>
    
    <form method="POST" action="tt_nvlAnnonce.php" enctype="multipart/form-data">
      
      <div class="card mb-4">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0">Informations generales</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="an_titre" class="form-label">Titre de l'annonce *</label>
            <input type="text" class="form-control" id="an_titre" name="an_titre" 
                   placeholder="Ex: Demenagement appartement 3 pieces" required>
          </div>
          
          <div class="mb-3">
            <label for="an_description" class="form-label">Description *</label>
            <textarea class="form-control" id="an_description" name="an_description" rows="4" 
                      placeholder="Decrivez votre demenagement en detail..." required></textarea>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="an_date_demenagement" class="form-label">Date du demenagement *</label>
              <input type="date" class="form-control" id="an_date_demenagement" name="an_date_demenagement" 
                     min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="an_heure_debut" class="form-label">Heure de debut *</label>
              <input type="time" class="form-control" id="an_heure_debut" name="an_heure_debut" required>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="an_ville_depart" class="form-label">Ville de depart *</label>
              <input type="text" class="form-control" id="an_ville_depart" name="an_ville_depart" 
                     placeholder="Ex: Paris" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="an_ville_arrivee" class="form-label">Ville d'arrivee *</label>
              <input type="text" class="form-control" id="an_ville_arrivee" name="an_ville_arrivee" 
                     placeholder="Ex: Lyon" required>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0">Lieu de depart</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Type de logement *</label>
            <div class="btn-group w-100" role="group">
              <input type="radio" class="btn-check" name="an_type_logement_depart" id="depart_maison" 
                     value="maison" autocomplete="off" required>
              <label class="btn btn-outline-dark" for="depart_maison">Maison</label>
              
              <input type="radio" class="btn-check" name="an_type_logement_depart" id="depart_appartement" 
                     value="appartement" autocomplete="off" required>
              <label class="btn btn-outline-dark" for="depart_appartement">Appartement</label>
            </div>
          </div>
          
          <div id="depart_details" style="display:none;">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="an_etage_depart" class="form-label">etage</label>
                <input type="number" class="form-control" id="an_etage_depart" name="an_etage_depart" 
                       min="0" max="50" placeholder="0 pour rez-de-chaussee">
              </div>
              <div class="col-md-6 mb-3">
                <label for="an_ascenseur_depart" class="form-label">Ascenseur disponible ?</label>
                <select class="form-select" id="an_ascenseur_depart" name="an_ascenseur_depart">
                  <option value="0">Non</option>
                  <option value="1">Oui</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0">Lieu d'arrivee</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Type de logement *</label>
            <div class="btn-group w-100" role="group">
              <input type="radio" class="btn-check" name="an_type_logement_arrivee" id="arrivee_maison" 
                     value="maison" autocomplete="off" required>
              <label class="btn btn-outline-dark" for="arrivee_maison">Maison</label>
              
              <input type="radio" class="btn-check" name="an_type_logement_arrivee" id="arrivee_appartement" 
                     value="appartement" autocomplete="off" required>
              <label class="btn btn-outline-dark" for="arrivee_appartement">Appartement</label>
            </div>
          </div>
          
          <div id="arrivee_details" style="display:none;">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="an_etage_arrivee" class="form-label">etage</label>
                <input type="number" class="form-control" id="an_etage_arrivee" name="an_etage_arrivee" 
                       min="0" max="50" placeholder="0 pour rez-de-chaussee">
              </div>
              <div class="col-md-6 mb-3">
                <label for="an_ascenseur_arrivee" class="form-label">Ascenseur disponible ?</label>
                <select class="form-select" id="an_ascenseur_arrivee" name="an_ascenseur_arrivee">
                  <option value="0">Non</option>
                  <option value="1">Oui</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0">Volume et equipe</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="an_volume" class="form-label">Volume approximatif (m³)</label>
              <input type="number" step="0.1" class="form-control" id="an_volume" name="an_volume" 
                     placeholder="Ex: 30">
              <div class="form-text">Facultatif</div>
            </div>
            <div class="col-md-4 mb-3">
              <label for="an_poids" class="form-label">Poids approximatif (kg)</label>
              <input type="number" step="0.1" class="form-control" id="an_poids" name="an_poids" 
                     placeholder="Ex: 500">
              <div class="form-text">Facultatif</div>
            </div>
            <div class="col-md-4 mb-3">
              <label for="an_nombre_demenageurs" class="form-label">Nombre de demenageurs *</label>
              <input type="number" class="form-control" id="an_nombre_demenageurs" name="an_nombre_demenageurs" 
                     min="1" max="10" value="2" required>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0">Photos (optionnel)</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="images" class="form-label">Ajouter des photos</label>
            <input type="file" class="form-control" id="images" name="images[]" 
                   accept="image/*" multiple>
            <div class="form-text">Vous pouvez ajouter plusieurs photos (5 Mo max).</div>
          </div>
        </div>
      </div>
      
      <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
        <a href="tdbClient.php" class="btn btn-outline-dark me-md-2">Annuler</a>
        <button type="submit" class="btn btn-dark">Publier l'annonce</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Afficher/masquer les details selon le type de logement
  document.querySelectorAll('input[name="an_type_logement_depart"]').forEach(radio => {
    radio.addEventListener('change', function() {
      const details = document.getElementById('depart_details');
      if (this.value === 'appartement') {
        details.style.display = 'block';
        document.getElementById('an_etage_depart').required = true;
      } else {
        details.style.display = 'none';
        document.getElementById('an_etage_depart').required = false;
        document.getElementById('an_etage_depart').value = '';
      }
    });
  });
  
  // Afficher/masquer les details selon le type de logement
  document.querySelectorAll('input[name="an_type_logement_arrivee"]').forEach(radio => {
    radio.addEventListener('change', function() {
      const details = document.getElementById('arrivee_details');
      if (this.value === 'appartement') {
        details.style.display = 'block';
        document.getElementById('an_etage_arrivee').required = true;
      } else {
        details.style.display = 'none';
        document.getElementById('an_etage_arrivee').required = false;
        document.getElementById('an_etage_arrivee').value = '';
      }
    });
  });
</script>

<?php
  include('footer.inc.php');
?>