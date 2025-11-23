<?php
require_once 'config.php';
require_once 'db_operations.php';

$tickets = getAllTickets($pdo);
$day1Attendees = getAllAttendance($pdo, 'day1_attendance');
$day2Attendees = getAllAttendance($pdo, 'day2_attendance');

$data = [
    'tickets' => $tickets,
    'day1_attendance' => $day1Attendees,
    'day2_attendance' => $day2Attendees
];

echo json_encode($data);
?>