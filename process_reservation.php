<?php
session_start();
require_once 'config.php';
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        $stmt = $conn->prepare("INSERT INTO reservations (table_id, customer_name, customer_email, customer_phone, reservation_date, reservation_time, party_size, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        
        $stmt->execute([
            $_POST['table_id'],
            $_POST['customer_name'],
            $_POST['customer_email'],
            $_POST['customer_phone'],
            $_POST['reservation_date'],
            $_POST['reservation_time'],
            $_POST['party_size']
        ]);
        
      
        
        header("Location: index.php?success=1");
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
