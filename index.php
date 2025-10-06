<?php
  session_start();
  $titre = "Accueil";
  include('header.inc.php');
  include('menu.inc.php');
  include('message.inc.php')
?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <h1>Projet démenagement &#129503 &#129484</h1>
    <div>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin aliquet rutrum justo, non lobortis neque scelerisque at. Sed cursus et ante mattis tristique. Nam dignissim in risus eu scelerisque. Nam non luctus purus. Phasellus fringilla eros vitae blandit pulvinar. Morbi volutpat dictum iaculis. Mauris et maximus arcu. Cras vehicula dolor dignissim dolor tempus consequat.</p>
        <p>Praesent quis sapien metus. Fusce eget aliquam mi, egestas auctor erat. Aenean leo nunc, elementum ornare augue vitae, efficitur ultricies mi. Integer id pretium sapien. Ut sagittis est vel leo vehicula mollis. Nulla facilisi. Nullam eget sodales lectus.</p>
    </div>

    <div class="maDiv">
        <a href="house1.php"><img src="images/house1.jpg"></a>
        <img src="images/house1.jpg" class ="col-sm">
        <img src="images/house1.jpg" class ="col-sm">
        <img src="images/house1.jpg" class ="col-sm">
    </div>
    <br>
    <div class="maDiv">
        <img src="images/house1.jpg" class ="col-sm">
        <img src="images/house1.jpg" class ="col-sm">
        <img src="images/house1.jpg" class ="col-sm">
        <img src="images/house1.jpg" class ="col-sm">
    </div>

<?php
  include('footer.inc.php');
?>