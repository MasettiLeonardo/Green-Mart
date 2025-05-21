<?php
session_start();

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $termine = isset($_GET['product']) ? trim($_GET['product']) : '';

    if (empty($termine)) {
        $_SESSION['error'] = "Nessun termine di ricerca inserito.";
        header('Location: index.php');
        die;
    }

    // query per trovare prodotti
    $stmt = $conn->prepare("SELECT ID, nome, prezzo, descrizione, 
                                         img_url, q_disponibile 
                                    FROM prodotti WHERE nome LIKE ?");
    $likeTerm = "%" . $termine . "%";
    $stmt->bind_param("s", $likeTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Nessun prodotto trovato.";
        header('Location: index.php');
        die;
    }

    $prodotto = $result->fetch_assoc();

    $_SESSION['prod_id'] = $prodotto['ID'];
    $_SESSION['prod_nome'] = $prodotto['nome'];
    $_SESSION['prod_prezzo'] = $prodotto['prezzo'];
    $_SESSION['prod_descrizione'] = $prodotto['descrizione'];
    $_SESSION['prod_img_url'] = $prodotto['img_url'];
    $_SESSION['prod_q_disponibile'] = $prodotto['q_disponibile'];

    header('Location: product.php');
    exit;
}
