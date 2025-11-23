<?php
require_once 'config.php';
require_once 'db_operations.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $qrData = $data['qrData'];
    
    // Décodage des données QR
    list($key_code, $userId) = explode('|', $qrData);
    
    // Recherche du ticket dans la base
    $ticket = findTicket($pdo, $key_code);
    
    if (!$ticket) {
        echo json_encode(['success' => false, 'message' => 'Ticket non valide']);
        exit;
    }
    
    // Vérification de la date
    $currentDate = date('Y-m-d');
    $day1 = '2025-07-18';
    $day2 = '2025-07-19';
    
    $response = ['success' => true, 'user_name' => $ticket['user_name']];
    
    if ($currentDate === $day1) {
        if ($ticket['day_1']) {
            $response['already_used'] = true;
            $response['message'] = 'Ce ticket a déjà été utilisé pour le jour 1';
        } else {
            // Marquer comme utilisé pour jour 1
            markTicketAsUsed($pdo, $ticket['id'], 'day_1');
            // Ajouter à la présence jour 1
            addToAttendance($pdo, 'day1_attendance', $ticket);
            $response['message'] = 'Ticket validé pour le jour 1';
            
            // Envoi WhatsApp (simulé)
            sendWhatsApp($ticket['phone_number'], 'Votre code QR a été utilisé pour le jour 1 de la Joré-Culep. Il reste valable pour le jour 2.');
        }
    } elseif ($currentDate === $day2) {
        if ($ticket['day_2']) {
            $response['already_used'] = true;
            $response['message'] = 'Ce ticket a déjà été utilisé pour le jour 2';
        } else {
            // Marquer comme utilisé pour jour 2
            markTicketAsUsed($pdo, $ticket['id'], 'day_2');
            // Ajouter à la présence jour 2
            addToAttendance($pdo, 'day2_attendance', $ticket);
            $response['message'] = 'Ticket validé pour le jour 2';
            
            // Envoi WhatsApp (simulé)
            sendWhatsApp($ticket['phone_number'], 'Votre code QR a été utilisé pour le jour 2 de la Joré-Culep. Merci de votre participation!');
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'L\'événement n\'a pas lieu aujourd\'hui';
    }
    
    echo json_encode($response);
}

function sendWhatsApp($phone, $message) {
    // En production, utiliser l'API WhatsApp Business
    // Pour la démonstration, nous simulons l'envoi
    $log = "Envoi WhatsApp à $phone: $message\n";
    file_put_contents('whatsapp_log.txt', $log, FILE_APPEND);
    return true;
}
?>