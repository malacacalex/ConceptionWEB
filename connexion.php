<?php
  session_start(); 
  $titre = "Connexion";

  include('header.inc.php');
  include('menu.inc.php');
  
  include('message.inc.php');
?>

  <h1 class="text-center">Connexion à votre compte</h1>
  
  <div class="row justify-content-center">
    <div class="col-md-6">
      <form  method="POST" action="tt_connexion.php">
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="Votre email..." required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Mot de passe</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Votre mot de passe..." required>
        </div>
        <div class="d-grid">
          <button class="btn btn-outline-primary" type="submit">Connexion</button>
        </div>
      </form>
    </div>
  </div>

<?php
  include('footer.inc.php');
?>