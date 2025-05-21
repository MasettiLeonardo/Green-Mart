<?php
/* #################################################################
 * #                                                               #
 * #                                                               #
 * #                                                               #
 * #                                                               #
 * #                                                               #
 * #                    FILE NON IMPLEMENTEATO                     #
 * #                                                               #
 * #                                                               #
 * #                                                               #
 * #                                                               #
 * #                                                               #
 * #################################################################
 */

session_start();

include('config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$special = $_SESSION['special_login'] ?? false;

// Esempio di codici predefiniti con coordinate
$trackingData = [
    "ABC123" => ["lat" => 41.9028, "lng" => 12.4964], // Roma
    "XYZ789" => ["lat" => 45.4642, "lng" => 9.19],    // Milano
    "DEF456" => ["lat" => 40.8518, "lng" => 14.2681], // Napoli
];

$showMap = false;
$errorMessage = "";
$trackingCode = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $trackingCode = strtoupper(trim($_POST['code']));
    if (isset($trackingData[$trackingCode])) {
        $showMap = true;
        $lat = $trackingData[$trackingCode]["lat"];
        $lng = $trackingData[$trackingCode]["lng"];
    } else {
        $errorMessage = "Codice di tracking non trovato. Verifica e riprova.";
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errorMessage = "Inserisci un codice di tracking valido.";
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Pacchi - <?= $special ? "Rent Gram" : "Green Mart" ?></title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/tracking.css">
    <?php if ($showMap): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php endif; ?>
</head>
<body>
    <header>
        <nav>
            <div class="nav-logo">
                <a href="index.php" id="title">
                    <img src="./img/logo.png" alt="Logo Green Mart" />
                    <?= $special ? "Rent Gram" : "Green Mart" ?>
                </a>

                <?php if ($special): ?>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            const originalText = "Rent Gram";
                            const title = document.getElementById("title");

                            let current = '';
                            let i = 0;

                            function scrambleStep() {
                                if (i < originalText.length) {
                                    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                                    title.childNodes[1].nodeValue = current + chars[Math.floor(Math.random() * chars.length)];
                                    setTimeout(scrambleStep, 50);
                                } else {
                                    title.childNodes[1].nodeValue = originalText;
                                }

                                if (i < originalText.length) {
                                    setTimeout(() => {
                                        current += originalText[i];
                                        i++;
                                    }, 50);
                                }
                            }

                            scrambleStep();
                        });
                    </script>
                <?php endif; ?>
            </div>

            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#novita">News</a></li>
                <li><a href="index.php#categoria">Categories</a></li>
                <li><a href="#">Contact</a></li>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="sign-up.php">Sign up</a></li>
                <?php else: ?>
                    <li><a href="userProfile.php"><img class="nav-user-icon" src="./img/user.png" alt="User Icon" style="width: 24px; height: 24px; border-radius: 50%; background: #fff"/></a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" action="tracking.php" class="search-box">
                    <input type="search" name="code" id="search" placeholder="Find your package" />
                    <i class="ri-search-line"></i>
                </form>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <h1 class="page-title">Traccia il tuo pacco</h1>
        
        <?php if ($errorMessage): ?>
            <div class="message error">
                <p><?= $errorMessage ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$showMap): ?>
            <div class="tracking-form">
                <form method="POST" action="tracking.php">
                    <div class="form-group">
                        <label for="tracking-code">Codice di tracking</label>
                        <input type="text" id="tracking-code" name="code" placeholder="Es. ABC123" value="<?= htmlspecialchars($trackingCode) ?>" required>
                    </div>
                    <button type="submit" class="btn">Traccia pacco</button>
                </form>
                <p style="margin-top: 1rem; color: #666;">
                    Inserisci il codice di tracking per visualizzare la posizione attuale del tuo pacco.
                    <br>Codici di esempio: ABC123 (Roma), XYZ789 (Milano), DEF456 (Napoli)
                </p>
            </div>
        <?php endif; ?>

        <?php if ($showMap): ?>
            <div class="map-container">
                <h2>Posizione del pacco: <?= htmlspecialchars($trackingCode) ?></h2>
                <div id="map"></div>
                <p style="margin-top: 1rem;">
                    <a href="tracking.php" class="btn" style="display: inline-block; margin-top: 1rem;">Cerca un altro pacco</a>
                </p>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var map = L.map('map').setView([<?= $lat ?>, <?= $lng ?>], 13);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(map);
                        L.marker([<?= $lat ?>, <?= $lng ?>]).addTo(map)
                            .bindPopup('Posizione del pacco: <?= htmlspecialchars($trackingCode) ?>')
                            .openPopup();
                    });
                </script>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section about">
                <h3><?= $special ? "Rent Gram" : "Green Mart" ?></h3>
                <p>
                    <?= $special ? "Rent Gram" : "Green Mart" ?> è il tuo marketplace di fiducia per acquisti rapidi, convenienti e intelligenti.
                    Offriamo una vasta gamma di prodotti per soddisfare ogni tua esigenza.
                </p>
            </div>
            <div class="footer-section links">
                <h3>Link Utili</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#novita">News</a></li>
                    <li><a href="index.php#categoria">Categorie</a></li>
                    <li><a href="#">Contatti</a></li>
                </ul>
            </div>
            <div class="footer-section contact">
                <h3>Contatti</h3>
                <p><i class="ri-map-pin-line"></i> Via Roma, 123, Milano</p>
                <p><i class="ri-phone-line"></i> +39 123 456 789</p>
                <p><i class="ri-mail-line"></i> support@<?= $special ? "rentgram" : "greenmart" ?>.com</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 <?= $special ? "Rent Gram" : "Green Mart" ?>. Tutti i diritti riservati.</p>
        </div>
    </footer>
</body>
</html>