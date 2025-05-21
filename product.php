<?php
session_start();

if (!isset($_SESSION['prod_nome']) || !isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

$prod_id = $_SESSION['prod_id'];
$nome = $_SESSION['prod_nome'];
$prezzo = number_format($_SESSION['prod_prezzo'], 2, ',', '.');
$descrizione = $_SESSION['prod_descrizione'];
$img_url = $_SESSION['prod_img_url'] ?? './img/background2.png'; // fallback
$disponibilita = $_SESSION['prod_q_disponibile'] ?? 0;


$user_id = $_SESSION['user_id'];

// aggiunta al carrello
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // recupero carrello
    $stmt = $conn->prepare("SELECT ID FROM carrello WHERE id_utente = ? ORDER BY data_creazione DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $cart_id = $result->fetch_assoc()['ID'];
    } else {
        // nessun carrello trovato
        $stmt = $conn->prepare("INSERT INTO carrello (id_utente, data_creazione) VALUES (?, NOW())");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $cart_id = $stmt->insert_id;
        $stmt->close();
    }

    // verifica se il prodotto è già nel carrello
    $stmt = $conn->prepare("SELECT quantità FROM carrello_prodotto WHERE id_carrello = ? AND id_prodotto = ?");
    $stmt->bind_param("ii", $cart_id, $prod_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        // aggiorna la quantità
        $stmt = $conn->prepare("UPDATE carrello_prodotto SET quantità = quantità + 1 WHERE id_carrello = ? AND id_prodotto = ?");
        $stmt->bind_param("ii", $cart_id, $prod_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO carrello_prodotto (id_carrello, id_prodotto, quantità) VALUES (?, ?, 1)");
        $stmt->bind_param("ii", $cart_id, $prod_id);
    }

    $stmt->execute();
    $stmt->close();

    header('Location: cart.php');
    exit;
}

$stmt = $conn->prepare('SELECT ID, nome, prezzo, img_url FROM prodotti LIMIT 4');
$stmt->execute();
$correlati = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Green Mart - Dettaglio Prodotto</title>
    <link rel="stylesheet" href="css/product-detail.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/footer.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/color-thief/2.3.2/color-thief.umd.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>

<body>
<header>
    <?php include 'navbar.php' ?>
</header>


<div class="product-container">
    <div class="product-card">
        <div class="product-image">
            <img id="main-product-image" src="<?= htmlspecialchars($img_url) ?>" alt="<?= htmlspecialchars($nome) ?>"
                 class="main-image">
            <div class="thumbnail-container">
                <img src="<?= htmlspecialchars($img_url) ?>" alt="Prodotto vista 1" class="thumbnail active"
                     onclick="changeImage(this)">
                <img src="./img/background.jpg" alt="Prodotto vista 2" class="thumbnail" onclick="changeImage(this)">
                <img src="./img/logo.png" alt="Prodotto vista 3" class="thumbnail" onclick="changeImage(this)">
                <img src="<?= htmlspecialchars($img_url) ?>" alt="Prodotto vista 4" class="thumbnail"
                     onclick="changeImage(this)">
            </div>
        </div>
        <div class="product-info">
            <span class="product-category">Categoria</span>
            <h1 class="product-title"><?= htmlspecialchars($nome) ?></h1>
            <div class="product-price">
                <span class="current-price">€<?= $prezzo ?></span>
            </div>
            <p class="product-description"><?= nl2br(htmlspecialchars($descrizione)) ?></p>

            <form method="post">
                <div class="quantity-selector">
                    <h3 class="option-title">Quantità:</h3>
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="decreaseQuantity()">-</button>
                        <input type="number" name="quantity" min="1" value="1" id="quantity" class="quantity-input">
                        <button class="quantity-btn" onclick="increaseQuantity()">+</button>
                    </div>
                    <p class="stock-info">Disponibilità: <?= intval($disponibilita) ?> unità</p>
                </div>

                <div class="product-actions">
                    <button type="submit" class="add-to-cart-btn">
                        <i class="ri-shopping-cart-line"></i> Aggiungi al Carrello
                    </button>
                    <button class="wishlist-btn">
                        <i class="ri-heart-line"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="related-products">
        <h2 class="section-title">Prodotti Correlati</h2>
        <div class="product-grid">
            <?php if (!empty($correlati) && is_array($correlati)): ?>
                <?php foreach ($correlati as $c): ?>
                    <form method="post">
                        <div class="related-product-card">
                            <div class="related-image">
                                <img src="<?= htmlspecialchars($c['img_url'] ?? '') ?>"
                                     alt="<?= htmlspecialchars($c['nome'] ?? '') ?>">
                            </div>
                            <div class="related-info">
                                <h3 class="related-title"><?= htmlspecialchars($c['nome'] ?? '') ?>/h3>
                                    <div class="related-price">
                                        <span class="related-original-price">€<?= htmlspecialchars($c['prezzo'] ?? '') ?></span>
                                    </div>
                            </div>
                            <button type="submit" class="related-add-btn">Aggiungi al Carrello</button>
                        </div>
                    </form>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nessun prodotto correlato disponibile.</p>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php include 'footer.php' ?>

<script>
    // Funzione per cambiare l'immagine principale
    function changeImage(thumbnail) {
        document.getElementById('main-product-image').src = thumbnail.src;

        // Aggiorna la thumbnail attiva
        let thumbnails = document.querySelectorAll('.thumbnail');
        thumbnails.forEach(item => {
            item.classList.remove('active');
        });
        thumbnail.classList.add('active');
    }

    // Funzione per selezionare il colore
    function selectColor(colorElement) {
        let colors = document.querySelectorAll('.color-circle');
        colors.forEach(color => {
            color.classList.remove('active');
        });
        colorElement.classList.add('active');
    }

    // Funzione per selezionare la taglia
    function selectSize(sizeElement) {
        let sizes = document.querySelectorAll('.size-option');
        sizes.forEach(size => {
            size.classList.remove('active');
        });
        sizeElement.classList.add('active');
    }

    // Funzioni per gestire la quantità
    function increaseQuantity() {
        let quantityInput = document.getElementById('quantity');
        let currentValue = parseInt(quantityInput.value);
        if (currentValue < 10) {
            quantityInput.value = currentValue + 1;
        }
    }

    function decreaseQuantity() {
        let quantityInput = document.getElementById('quantity');
        let currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    }

    // Funzione per gestire le tab
    function openTab(tabId) {
        let tabContents = document.querySelectorAll('.tab-pane');
        tabContents.forEach(content => {
            content.classList.remove('active');
        });

        let tabButtons = document.querySelectorAll('.tab-btn');
        tabButtons.forEach(button => {
            button.classList.remove('active');
        });

        document.getElementById(tabId).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    // Effetto hover per i bottoni correlati
    document.addEventListener('DOMContentLoaded', function () {
        const relatedButtons = document.querySelectorAll('.related-add-btn');

        relatedButtons.forEach(button => {
            const card = button.closest('.related-product-card');

            card.addEventListener('mouseenter', () => {
                button.style.opacity = '1';
            });

            card.addEventListener('mouseleave', () => {
                button.style.opacity = '0.9';
            });
        });
    });
</script>
</body>

</html>