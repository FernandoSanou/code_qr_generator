<?php
session_start();
require_once 'database.php'; // Utilise votre fichier de connexion existant

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header('Location: login.php?error=Veuillez remplir tous les champs');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification sécurisée du mot de passe avec password_verify
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: ../index.php'); // Redirige vers la page principale
            exit;
        } else {
            header('Location: login.php?error=Identifiants incorrects');
            exit;
        }

    } catch (PDOException $e) {
        die("Erreur de base de données: " . $e->getMessage());
    }
} else {
    header('Location: login.php');
    exit;
}
?>