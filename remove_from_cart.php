<?php

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'] ?? -1;

    if ($product_id !== -1) {
        $stmt = $conn->prepare("DELETE FROM carrello_prodotto WHERE id_prodotto = $product_id");
        $stmt->execute();
    }
    
    header('Location: cart.php');
    exit;
}