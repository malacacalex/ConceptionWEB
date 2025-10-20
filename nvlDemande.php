<?php
session_start();
$titre = "Nouvelle Demande";
include('header.inc.php');
include('menu.inc.php');
include('message.inc.php')
    ?>

<h1>Création d'une demande</h1>
<form method="POST" action="tt_nvlAnnonce.php">

    <!-- Titre et Description -->
    <div class="row my-3">
        <div class="col-md-6">
            <label for="an_titre" class="form-label">Titre de l'annonce</label>
            <input type="text" class="form-control" id="an_titre" name="an_titre" placeholder="Ex : Déménagement Rouen → Paris" required>
        </div>
        <div class="col-md-6">
            <label for="an_description" class="form-label">Description</label>
            <textarea class="form-control" id="an_description" name="an_description" rows="2" placeholder="Décrivez brièvement votre déménagement..." required></textarea>
        </div>
    </div>

    <!-- Dates -->
    <div class="row my-3">
        <div class="col-md-6">
            <label for="an_date_demenagement" class="form-label">Date du déménagement</label>
            <input type="date" class="form-control" id="an_date_demenagement" name="an_date_demenagement" required>
        </div>
        <div class="col-md-6">
            <label for="an_date_creation" class="form-label">Date de création</label>
            <input type="date" class="form-control" id="an_date_creation" name="an_date_creation" required>
        </div>
    </div>

    <!-- Villes -->
    <div class="row my-3">
        <div class="col-md-6">
            <label for="an_ville_depart" class="form-label">Ville de départ</label>
            <input type="text" class="form-control" id="an_ville_depart" name="an_ville_depart" placeholder="Rouen" required>
        </div>
        <div class="col-md-6">
            <label for="an_ville_arrivee" class="form-label">Ville d'arrivée</label>
            <input type="text" class="form-control" id="an_ville_arrivee" name="an_ville_arrivee" placeholder="Paris" required>
        </div>
    </div>

    <!-- Logement de départ -->
    <h5 class="mt-4">Logement de départ</h5>
    <div class="row my-2">
        <div class="col-md-4">
            <label for="an_type_logement_depart" class="form-label">Type de logement</label>
            <select class="form-select" id="an_type_logement_depart" name="an_type_logement_depart" required>
                <option value="">-- Sélectionnez --</option>
                <option value="maison">Maison</option>
                <option value="appartement">Appartement</option>
            </select>
        </div>
        <div class="col-md-4">
            <label for="an_etage_depart" class="form-label">Étage</label>
            <input type="number" class="form-control" id="an_etage_depart" name="an_etage_depart" min="0" placeholder="Ex : 2">
        </div>
        <div class="col-md-4">
            <label for="an_ascenseur_depart" class="form-label">Ascenseur</label>
            <select class="form-select" id="an_ascenseur_depart" name="an_ascenseur_depart" required>
                <option value="1">Oui</option>
                <option value="0">Non</option>
            </select>
        </div>
    </div>

    <!-- Logement d’arrivée -->
    <h5 class="mt-4">Logement d'arrivée</h5>
    <div class="row my-2">
        <div class="col-md-4">
            <label for="an_type_logement_arrivee" class="form-label">Type de logement</label>
            <select class="form-select" id="an_type_logement_arrivee" name="an_type_logement_arrivee" required>
                <option value="">-- Sélectionnez --</option>
                <option value="maison">Maison</option>
                <option value="appartement">Appartement</option>
            </select>
        </div>
        <div class="col-md-4">
            <label for="an_etage_arrivee" class="form-label">Étage</label>
            <input type="number" class="form-control" id="an_etage_arrivee" name="an_etage_arrivee" min="0" placeholder="Ex : 3">
        </div>
        <div class="col-md-4">
            <label for="an_ascenseur_arrivee" class="form-label">Ascenseur</label>
            <select class="form-select" id="an_ascenseur_arrivee" name="an_ascenseur_arrivee" required>
                <option value="1">Oui</option>
                <option value="0">Non</option>
            </select>
        </div>
    </div>


    <!-- Bouton -->
    <div class="row my-4">
        <div class="d-grid d-md-block">
            <button class="btn btn-outline-success" type="submit">Publier l'annonce</button>
        </div>
    </div>

</form>






<?php
include('footer.inc.php');
?>