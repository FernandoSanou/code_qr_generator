<?php
// Utiliser les mêmes noms de variables partout
$host = 'sql304.infinityfree.com';
$dbname = 'if0_39217314_event_qr_system'; // Renommé de $db à $dbname
$username = 'if0_39217314'; // Renommé de $user à $username
$password = 'gre84ger5g41te6'; // Renommé de $pass à $password
$charset = 'utf8mb4';

try {
    // Utiliser les variables correctes ici
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Cette ligne génère l'erreur que vous voyez
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}
?>