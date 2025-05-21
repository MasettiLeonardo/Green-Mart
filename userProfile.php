<?php
session_start();

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// query dati utente
$userStmt = $conn->prepare("
    SELECT nome, email, indirizzo, telefono, tipo_account, 
           badge_vip, badge_cliente_fedele, badge_verificato
    FROM utenti 
    WHERE ID = ?
");

$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

// query ordini
$ordStmt = $conn->prepare("
    SELECT ordine.ID, ordine.data_ordine, ordine.stato, prodotti.nome, prodotti.img_url 
    FROM ordine 
    JOIN carrello ON ordine.id_carrello = carrello.ID 
    JOIN carrello_prodotto ON carrello.ID = carrello_prodotto.id_carrello 
    JOIN prodotti ON carrello_prodotto.id_prodotto = prodotti.ID 
    WHERE carrello.id_utente = ?
");

$ordStmt->bind_param("i", $user_id);
$ordStmt->execute();
$ordini = $ordStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$ordStmt->close();

// query preferiti
$prefStmt = $conn->prepare("SELECT COUNT(*) as tot FROM preferiti WHERE id_utente = ?");
$prefStmt->bind_param("i", $user_id);
$prefStmt->execute();
$preferiti = $prefStmt->get_result()->fetch_assoc()['tot'] ?? 0;
$prefStmt->close();

// query recensioni
$recStmt = $conn->prepare("
    SELECT prodotti.nome, recensioni.valutazione, recensioni.commento, recensioni.data 
    FROM recensioni 
    JOIN prodotti ON recensioni.id_prodotto = prodotti.ID 
    WHERE recensioni.id_utente = ? 
    ORDER BY recensioni.data DESC 
    LIMIT 3
");

$recStmt->bind_param("i", $user_id);
$recStmt->execute();
$recensioni = $recStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recStmt->close();

function statoOrdine($stato)
{
    switch ($stato) {
        case 'spedito':
            return ['Spedito', 'status-shipped'];
        case 'in_elaborazione':
            return ['In Elaborazione', 'status-processing'];
        case 'annullato':
            return ['Annullato', 'status-cancelled'];
        default:
            return ['Sconosciuto', 'status-unknown'];
    }
}

?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Profilo Utente | Green Mart</title>
    <link rel="stylesheet" href="css/userProfile.css"/>
    <link rel="stylesheet" href="css/responsive.css"/>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
          rel="stylesheet"/>
</head>

<body>
<header>
    <?php include 'navbar.php' ?>
</header>
<main>
    <section class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <img src="./img/logo.png" alt="Foto Profilo Utente"/>
            </div>

            <div class="profile-info">
                <h2><?= htmlspecialchars($user['nome']) ?></h2>
                <p class="user-title">Membro Premium</p>
                <div class="user-badges">
                    <?php if ($user['badge_vip']) : ?>
                        <span class="badge"><i class="fas fa-star"></i> VIP</span>
                    <?php endif; ?>
                    <?php if ($user['badge_cliente_fedele']) : ?>
                        <span class="badge"><i class="fas fa-award"></i> Cliente Fedele</span>
                    <?php endif; ?>
                    <?php if ($user['badge_verificato']) : ?>
                        <span class="badge"><i class="fas fa-check-circle"></i> Verificato</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="profile-stats">
            <div class="stat-box">
                <i class="fas fa-shopping-cart"></i>
                <span class="stat-number"><?= count($ordini) ?></span>
                <span class="stat-label">Ordini</span>
            </div>
            <div class="stat-box">
                <i class="fas fa-heart"></i>
                <span class="stat-number"><?= $preferiti ?></span>
                <span class="stat-label">Preferiti</span>
            </div>
        </div>

        <div class="profile-tabs">
            <input type="radio" id="tab1" name="tabs" checked/>
            <label for="tab1"><i class="fas fa-user"></i> Informazioni Personali</label>

            <input type="radio" id="tab2" name="tabs"/>
            <label for="tab2"><i class="fas fa-chart-line"></i> Attività</label>

            <input type="radio" id="tab4" name="tabs"/>
            <label for="tab4"><i class="fas fa-cog"></i> Impostazioni</label>

            <div class="tab-content" id="content1">
                <div class="info-card">
                    <h3>Informazioni Personali</h3>

                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-user"></i> Nome Completo</div>
                        <div class="info-value"><?= htmlspecialchars($user['nome']) ?></div>
                    </div>

                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-envelope"></i> Email</div>
                        <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                    </div>

                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-phone"></i> Telefono</div>
                        <div class="info-value"><?= htmlspecialchars($user['telefono'] ?? 'N/D') ?></div>
                    </div>

                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-map-marker-alt"></i> Località</div>
                        <div class="info-value"><?= htmlspecialchars($user['indirizzo'] ?? 'N/D') ?></div>
                    </div>
                </div>
            </div>

            <div class="tab-content" id="content2">
                <div class="info-card">
                    <h3>Ordini Recenti</h3>

                    <?php if (count($ordini) === 0): ?>
                        <p>Nessun ordine recente.</p>
                    <?php else: ?>
                        <?php foreach ($ordini as $ordine):
                            list($statoLabel, $statoClass) = statoOrdine($ordine['stato']);
                            ?>
                            <div class="activity-item">
                                <div class="activity-icon <?= $statoClass ?>">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="activity-details">
                                    <h4><?= htmlspecialchars($ordine['prodotto_nome']) ?></h4>
                                    <p><?= htmlspecialchars($ordine['descrizione']) ?></p>
                                    <span class="activity-date"><?= date("d/m/Y", strtotime($ordine['data_ordine'])) ?></span>
                                </div>
                                <div class="activity-status"><?= $statoLabel ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="info-card">
                    <h3>Recensioni Recenti</h3>

                    <?php if (count($recensioni) === 0): ?>
                        <p>Nessuna recensione recente.</p>
                    <?php else: ?>
                        <?php foreach ($recensioni as $rec): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <h4><?= htmlspecialchars($rec['prodotto_nome']) ?></h4>
                                    <div class="review-rating">
                                        <?php
                                        $stars = (int)round($rec['valutazione']);
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $stars ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <p class="review-text"><?= htmlspecialchars($rec['commento']) ?></p>
                                <span class="review-date"><?= date("d/m/Y", strtotime($rec['data'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-content" id="content4">
                <div class="info-card">
                    <h3>Impostazioni Account</h3>

                    <div class="settings-group">
                        <h4>Preferenze di Notifica</h4>
                        <div class="setting-item">
                            <div class="setting-label">Email Promozionali</div>
                            <label class="toggle">
                                <input type="checkbox" checked/>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="setting-item">
                            <div class="setting-label">Notifiche Ordini</div>
                            <label class="toggle">
                                <input type="checkbox" checked/>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="setting-item">
                            <div class="setting-label">Newsletter</div>
                            <label class="toggle">
                                <input type="checkbox" checked/>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-group">
                        <h4>Privacy</h4>
                        <div class="setting-item">
                            <div class="setting-label">Profilo Pubblico</div>
                            <label class="toggle">
                                <input type="checkbox"/>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="setting-item">
                            <div class="setting-label">Mostra Recensioni</div>
                            <label class="toggle">
                                <input type="checkbox" checked/>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-group">
                        <h4>Preferenze di Pagamento</h4>
                        <div class="setting-item">
                            <div class="setting-label">Salva Metodi di Pagamento</div>
                            <label class="toggle">
                                <input type="checkbox" checked/>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="setting-item">
                            <div class="setting-label">Checkout Rapido</div>
                            <label class="toggle">
                                <input type="checkbox" checked/>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-group">
                        <button class="btn-primary" style="margin-right: 10px;">Salva Impostazioni</button>
                        <button class="btn-secondary">Annulla</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php' ?>
</body>

</html>
