<?php
// Pas besoin de config.php ici si on n'interagit pas avec la BDD
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Vérifier si les données nécessaires sont présentes
    if (!isset($data['ticketId']) || !isset($data['imageData'])) {
        echo json_encode(['success' => false, 'error' => 'Données manquantes.']);
        exit;
    }

    $ticketId = $data['ticketId'];
    $imageData = $data['imageData'];
    
    // Supprimer le préfixe base64
    if (strpos($imageData, 'data:image/png;base64,') === 0) {
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
    }
    
    $imageData = base64_decode($imageData);
    
    if ($imageData === false) {
        echo json_encode(['success' => false, 'error' => 'Données d\'image invalides (base64).']);
        exit;
    }
    
    // Chemin de sauvegarde
    $directory = '../assets/tickets/';
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
    
    $filename = 'ticket_joreculep_' . $ticketId . '.png';
    $filePath = $directory . $filename;
    
    // Sauvegarder l'image
    if (file_put_contents($filePath, $imageData)) {
        // Retourner l'URL publique complète
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $publicUrl = $protocol . $host . '/assets/tickets/' . $filename; // URL absolue
        
        echo json_encode(['success' => true, 'url' => $publicUrl]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur de sauvegarde du fichier sur le serveur.']);
    }
}
?>