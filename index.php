<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php';

$stmt = $conn->prepare('SELECT ID, nome, img_url FROM prodotti LIMIT 5');
$stmt->execute();
$novita = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Pagina iniziale</title>
    <link id="style" rel="stylesheet" href="css/index.css"/>
    <link rel="stylesheet" href="css/categoria.css"/>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/footer.css">
</head>

<body>
<header>
    <?php include 'navbar.php' ?>
</header>

<main class="container">
    <section class="container-left">
        <div class="content">
            <h1>Il Marketplace <b>Intelligente</b><br/>per Acquisti <b>Rapidi</b> e <b>Convenienti</b></h1>
            <br><br>
            <div class="btns">
                <button class="read-more">Read More</button>
                <button class="our-blogs">Our blogs</button>
            </div>

            <div class="chevrons">
                <span class="left-chevrons"><i class="ri-arrow-left-s-line"></i></span>
                <span class="right-chevrons"><i class="ri-arrow-right-s-line"></i></span>
            </div>
        </div>
    </section>

    <section class="container-right">
        <form action="search.php" method="GET">
            <h4>Cosa vuoi comprare?</h4><br>
            <div class="form-group">
                <label for="product-name">Nome del prodotto:</label>
                <input type="text" name="product" placeholder="Enter your product" required/>
            </div>
            <p class="error-message">
                <?php
                if (isset($_SESSION['error'])) {
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                }
                ?>
            </p>
            <!--    <div class="form-group">
                    <label for="category">Categoria:</label>
                    <input type="text" name="category" placeholder="Enter your category" required/>
                </div> -->
            <input class="submit-travel" type="submit" value="Check the product"/>
        </form>
    </section>
</main>

<div class="products" id="novita">
    <?php if (count($novita) === 0): ?>
        <h2>Non ci sono Novità</h2>
    <?php else: ?>
        <h2>Ultime<br><b>NOVITÀ:</b></h2>
        <?php foreach ($novita as $n): ?>
            <!-- <a href="product.php?id=<?= urlencode($n['ID']) ?>">
                <img src="<?= htmlspecialchars($n['img_url']) ?>" alt="immagine novità"/>
            </a> -->
            <img src="<?= htmlspecialchars($n['img_url']) ?>" alt="<?= htmlspecialchars($n['nome']) ?>"/>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="products" id="categoria">
    <h2 id="titoloCat">Le nostre<br><b>CATEGORIE:</b></h2>
    <div class="card-container">
        <div class="card">
            <div class="card-background" style="background-image: url(./img/background2.png);"></div>
            <div class="contentCategory">
                <div class="card-category">Elettronica</div>
                <h3 class="card-heading">No Plans For Today Tee</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-background" style="background-image: url(./img/background.jpg);"></div>
            <div class="contentCategory">
                <div class="card-category">Frutta</div>
                <h3 class="card-heading">No Plans For Today Tee</h3>
            </div>
        </div>
        <div class="card">
            <div class="card-background" style="background-image: url(./img/logo.png);"></div>
            <div class="contentCategory">
                <div class="card-category">Vestiti</div>
                <h3 class="card-heading">No Plans For Today Tee</h3>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php' ?>
</body>

</html>