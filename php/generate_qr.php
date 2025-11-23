<?php
require_once 'config.php';
require_once 'db_operations.php';
require_once '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

header('Content-Type: application/json');

// Créer le dossier pour les QR codes bruts si inexistant
$qrDir = '../qr_codes';
if (!is_dir($qrDir)) mkdir($qrDir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // 1. Génération du key_code et insertion dans la base de données
    $key_code = md5(uniqid($data['userId'], true));
    
    $ticketData = [
        'key_code' => $key_code,
        'user_id' => $data['userId'],
        'user_name' => $data['userName'],
        'user_info' => $data['userInfo'],
        'user_uns' => $data['userUns'],
        'phone_number' => $data['phoneNumber']
    ];
    
    $ticketId = insertTicket($pdo, $ticketData);

    // 2. Génération du contenu et du fichier QR code
    $qrContent = $key_code . '|' . $data['userId'];
    $qrFilename = $qrDir . '/ticket_qr_' . $ticketId . '.png';
    $qrPublicUrl = 'qr_codes/ticket_qr_' . $ticketId . '.png';

    $qrCode = QrCode::create($qrContent);
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    $result->saveToFile($qrFilename);

    // 3. Retourner les informations nécessaires au front-end
    echo json_encode([
        'success' => true,
        'ticketId' => $ticketId,
        'qrCodeUrl' => $qrPublicUrl, // URL du QR code brut
        'userData' => $data // Renvoyer les données de l'utilisateur
    ]);
    exit;
}
?>