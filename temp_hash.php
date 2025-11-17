<?php
// Choisissez un mot de passe fort pour votre admin
$mot_de_passe_admin = '1234'; 

$hash = password_hash($mot_de_passe_admin, PASSWORD_DEFAULT);

echo "Votre hash (à copier dans le SQL) : <br>";
echo $hash;
?>