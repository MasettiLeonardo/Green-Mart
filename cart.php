<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$carrelloVuoto = false;
$totale = 0;

// recupero carrello
$stmt = $conn->prepare("SELECT ID FROM carrello WHERE id_utente = ? ORDER BY data_creazione DESC LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO carrello (id_utente, data_creazione) VALUES (?, NOW())");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $cart_id = $stmt->insert_id;
    $stmt->close();
}

$carrello = $result->fetch_assoc();
$carrelloId = $carrello['ID'];

$stmt = $conn->prepare("
        SELECT 
            p.ID AS prodotto_id,
            p.nome,
            p.descrizione,
            p.prezzo,
            p.img_url,
            p.unit,
            cp.quantità
        FROM carrello_prodotto cp
        JOIN prodotti p ON cp.id_prodotto = p.ID
        WHERE cp.id_carrello = ?
    ");

$stmt->bind_param("i", $carrelloId);
$stmt->execute();
$prodotti = $stmt->get_result();
$stmt->close();

if ($prodotti->num_rows === 0) {
    $carrelloVuoto = true;
}

$stmt = $conn->prepare('SELECT ID, nome, prezzo, img_url FROM prodotti LIMIT 4');
$stmt->execute();
$consigliati = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Carrello - Green Mart</title>
    <link rel="stylesheet" href="css/index.css"/>
    <link rel="stylesheet" href="css/categoria.css"/>
    <link rel="stylesheet" href="css/cart.css"/>
    <link rel="stylesheet" href="css/navbar.css"/>
    <link rel="stylesheet" href="css/footer.css"/>
</head>
<body>
<header>
    <?php include 'navbar.php'; ?>
</header>

<main class="cart-container">
    <div class="cart-content">
        <div class="cart-items">
            <?php if ($carrelloVuoto): ?>
                <div class="empty-cart-message">
                    <h2>Il tuo carrello è vuoto</h2>
                    <p>Scopri i nostri prodotti e aggiungili al carrello!</p>
                    <a href="index.php" class="btn-primary">Vai allo Shop</a>
                </div>
            <?php else: ?>
                <?php while ($row = $prodotti->fetch_assoc()):
                    $subtotal = $row['prezzo'] * $row['quantità'];
                    $totale += $subtotal;
                    ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="<?= htmlspecialchars($row['img_url']) ?>"
                                 alt="<?= htmlspecialchars($row['nome']) ?>" height="150px" width="150px"
                                 class="cart-img"/>
                        </div>
                        <div class="cart-item-details">
                            <h3><?= htmlspecialchars($row['nome']) ?></h3>
                            <p class="item-description"><?= htmlspecialchars($row['descrizione']) ?></p>
                            <p class="item-category"><?= htmlspecialchars($row['unit'] ?? '') ?></p>
                        </div>
                        <div class="cart-item-price">
                            <p>€<?= number_format($row['prezzo'], 2, ',', '') ?></p>
                        </div>
                        <div class="cart-item-quantity">
                            <form method="POST" action="update_cart.php">
                                <input type="hidden" name="product_id" value="<?= $row['prodotto_id'] ?>">
                                <button class="quantity-btn minus" name="action" value="decrease">-</button>
                                <input type="number" value="<?= $row['quantità'] ?>" readonly>
                                <button class="quantity-btn plus" name="action" value="increase">+</button>
                            </form>
                        </div>
                        <div class="cart-item-total">
                            <p>€<?= number_format($subtotal, 2, ',', '') ?></p>
                        </div>
                        <div class="cart-item-remove">
                            <form method="POST" action="remove_from_cart.php">
                                <input type="hidden" name="product_id" value="<?= $row['prodotto_id'] ?>">
                                <button class="remove-btn">×</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <?php if (!$carrelloVuoto): ?>
            <?php
            $iva = $totale * 0.22;
            $totale_con_iva = $totale + $iva;
            ?>
            <div class="cart-summary">
                <h2>Riepilogo Ordine</h2>
                <div class="summary-row">
                    <span>Subtotale</span>
                    <span>€<?= number_format($totale, 2, ',', '') ?></span>
                </div>
                <div class="summary-row">
                    <span>IVA (22%)</span>
                    <span>€<?= number_format($iva, 2, ',', '') ?></span>
                </div>
                <div class="summary-total">
                    <span>Totale</span>
                    <span>€<?= number_format($totale_con_iva, 2, ',', '') ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="recommended-products">
        <h2>Prodotti <b>Consigliati</b></h2>
        <div class="recommended-items">
            <?php if (!empty($consigliati) && is_array($consigliati)): ?>
                <?php foreach ($consigliati as $c): ?>
                    <a href="#" class="recommended-item">
                        <img class="rec-image-placeholder"
                             src="<?= htmlspecialchars($c['img_url'] ?? '') ?>"
                             alt="<?= htmlspecialchars($c['nome'] ?? '') ?>"/>
                        <h3><?= htmlspecialchars($c['nome'] ?? '') ?></h3>
                        <p>€<?= htmlspecialchars($c['prezzo'] ?? '') ?></p>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nessun prodotto consigliato disponibile.</p>
            <?php endif; ?>
        </div>
    </div>

</main>

<?php include 'footer.php'; ?>
</body>
</html>