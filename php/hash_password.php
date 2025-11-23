<?php
// hash_password.php
$plain_password = 'jore-culep_Admin_user79220@SG-BUE-2025';
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
echo "Mot de passe original: " . $plain_password . "<br>";
echo "Mot de passe haché (à copier dans la BDD): " . $hashed_password;
?>