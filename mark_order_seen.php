<?php
require 'db.php'; // Use db.php as per project structure

if (isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);

    $stmt = $pdo->prepare("
        UPDATE orders
        SET is_viewed = 1
        WHERE id = ?
    ");

    $stmt->execute([$order_id]);

    echo "success";
}
?>