<?php
function insertTicket($pdo, $data) {
    $sql = "INSERT INTO tickets (key_code, user_id, user_name, user_info, user_uns, phone_number) 
            VALUES (:key_code, :user_id, :user_name, :user_info, :user_uns, :phone_number)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    return $pdo->lastInsertId();
}

function findTicket($pdo, $key_code) {
    $sql = "SELECT * FROM tickets WHERE key_code = :key_code";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['key_code' => $key_code]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function markTicketAsUsed($pdo, $ticketId, $day) {
    $sql = "UPDATE tickets SET $day = 1 WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $ticketId]);
}

function addToAttendance($pdo, $table, $ticket) {
    $sql = "INSERT INTO $table (ticket_id, key_code, user_id, user_name, user_info, user_uns, phone_number) 
            VALUES (:ticket_id, :key_code, :user_id, :user_name, :user_info, :user_uns, :phone_number)";
    
    $data = [
        'ticket_id' => $ticket['id'],
        'key_code' => $ticket['key_code'],
        'user_id' => $ticket['user_id'],
        'user_name' => $ticket['user_name'],
        'user_info' => $ticket['user_info'],
        'user_uns' => $ticket['user_uns'],
        'phone_number' => $ticket['phone_number']
    ];
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
}

function getAllTickets($pdo) {
    $sql = "SELECT * FROM tickets";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllAttendance($pdo, $table) {
    $sql = "SELECT * FROM $table";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>