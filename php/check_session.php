<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // L'utilisateur n'est pas connecté, le rediriger vers la page de connexion
    // Assurez-vous que le chemin vers login.php est correct
    header('Location: php/login.php'); 
    exit;
}
?>