<?php
session_start();
include 'connessione.php';

if (!isset($_SESSION['id_utente'])) {
    header('Location: login.php');
    exit;
}

$id_utente = $_SESSION['id_utente'];

// Recupero nome utente senza join
$sql_utente = "SELECT nome FROM utenti WHERE id_utente = '$id_utente'";
$result_utente = mysqli_query($conn, $sql_utente);
$utente = mysqli_fetch_assoc($result_utente);

// Recupero ordini dell utente senza join
$sql_ordini = "SELECT * FROM ordini WHERE id_utente = '$id_utente' ORDER BY data_acquisto DESC";
$result_ordini = mysqli_query($conn, $sql_ordini);

$num_carrello = 0;
if (isset($_SESSION['carrello'])) {
    if (is_array($_SESSION['carrello'])) {
        $num_carrello = count($_SESSION['carrello']);
    }
}

$oggi = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I miei abbonamenti - AIAIAI Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/storico.css">
</head>
<body>

<div class="bg-grid"></div>
<div class="bg-glow bg-glow-1"></div>
<div class="bg-glow bg-glow-2"></div>

<header>
    <div class="header-left">
        <span class="logo"><a href="../index.php">AIAIAI</a></span>
        <span class="logo-sub">STORE</span>
    </div>
    <nav class="nav-user">
        <span class="nav-nome">Ciao, <span><?php echo htmlspecialchars($utente['nome']); ?></span></span>
        <a href="storico_ordini.php" class="btn-ghost">I miei abbonamenti</a>
        <a href="carrello.php" class="carrello-btn">
            Carrello
            <?php if ($num_carrello > 0) { ?>
                <span class="carrello-badge"><?php echo $num_carrello; ?></span>
            <?php } ?>
        </a>
        <a href="logout.php" class="btn-ghost">Esci</a>
    </nav>
</header>

<main class="main-storico">

    <h2 class="sezionetitolo">I miei abbonamenti</h2>
    <p class="sezione-desc">Storico completo dei tuoi abbonamenti AI</p>

    <?php if (isset($_GET['annullato'])) { ?>
    <div class="msg-ok">Abbonamento annullato. Resterà attivo fino alla data di scadenza.</div>
    <?php } ?>

    <?php
    $ordini_lista = [];
    $tot_speso    = 0;
    $tot_attivi   = 0;

    while ($ord = mysqli_fetch_assoc($result_ordini)) {
        if ($ord['stato_abbonamento'] == 'annullato') {
            $ord['_stato_reale'] = 'annullato';
        } else if ($ord['data_rinnovo'] < $oggi) {
            $ord['_stato_reale'] = 'scaduto';
        } else {
            $ord['_stato_reale'] = 'attivo';
            $tot_attivi = $tot_attivi + 1;
        }
        $ordini_lista[] = $ord;
        $tot_speso = $tot_speso + $ord['totale_ordine'];
    }
    $num_ordini = count($ordini_lista);
    ?>

    <?php if ($num_ordini > 0) { ?>

    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-num"><?php echo $num_ordini; ?></div>
            <div class="stat-label">Abbonamenti totali</div>
        </div>
        <div class="stat-box">
            <div class="stat-num"><?php echo $tot_attivi; ?></div>
            <div class="stat-label">Abbonamenti attivi</div>
        </div>
        <div class="stat-box">
            <div class="stat-num">€<?php echo number_format($tot_speso, 2, ',', '.'); ?></div>
            <div class="stat-label">Totale speso</div>
        </div>
    </div>

    <div class="tabella-container">
        <table class="ordini-tabella">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Agente/i</th>
                    <th>Data acquisto</th>
                    <th>Rinnovo / Scadenza</th>
                    <th>Carta</th>
                    <th>Totale/mese</th>
                    <th>Stato</th>
                    <th>Azione</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                reset($ordini_lista);
                while ($ord = current($ordini_lista)) {
                    $sr = $ord['_stato_reale'];

                    if ($sr == 'attivo') {
                        $stato_class = 'stato-attivo';
                        $stato_label = 'Attivo';
                    } else if ($sr == 'scaduto') {
                        $stato_class = 'stato-scaduto';
                        $stato_label = 'Scaduto';
                    } else {
                        $stato_class = 'stato-annullato';
                        $stato_label = 'Annullato';
                    }

                    // Recupero i dati della carta in modo procedurale isolato senza JOIN
                    $id_carta_corrente = $ord['id_carta'];
                    $sql_carta = "SELECT numero_carta, circuito FROM carte_salvate WHERE id_carta = '$id_carta_corrente'";
                    $result_carta = mysqli_query($conn, $sql_carta);
                    $dati_carta = mysqli_fetch_assoc($result_carta);
                    
                    $num_carta_visualizzato = $dati_carta['numero_carta'];
                    $circuito_carta = $dati_carta['circuito'];

                    // Selezione dell icona senza costrutti a freccia
                    $icona = '💳';
                    if ($circuito_carta == 'visa') {
                        $icona = '💳';
                    } else if ($circuito_carta == 'mastercard') {
                        $icona = '🔴';
                    } else if ($circuito_carta == 'amex') {
                        $icona = '🔷';
                    }
                    $sql_prodotti = "SELECT p.nome_prodotto FROM dettagliordini d JOIN prodotti p ON d.id_prodotto = p.id_prodotto WHERE d.id_ordine = '" . $ord['id_ordine'] . "'";
                    $result_prodotti = mysqli_query($conn, $sql_prodotti);
                    $nomi_prodotti = [];
                    while ($rp = mysqli_fetch_assoc($result_prodotti)) {
                        $nomi_prodotti[] = htmlspecialchars($rp['nome_prodotto']);
                    }
                    $prodotti_label = !empty($nomi_prodotti) ? implode(', ', $nomi_prodotti) : '—';
                ?>
                <tr>
                    <td><strong>#<?php echo $ord['id_ordine']; ?></strong></td>
                    <td class="td-agenti"><?php echo $prodotti_label; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($ord['data_acquisto'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($ord['data_rinnovo'])); ?></td>
                    <td><?php echo $icona; ?> <?php echo htmlspecialchars($num_carta_visualizzato); ?></td>
                    <td><strong>€<?php echo number_format($ord['totale_ordine'], 2, ',', '.'); ?></strong></td>
                    <td><span class="stato-badge <?php echo $stato_class; ?>"><?php echo $stato_label; ?></span></td>
                    <td>
                        <div class="azioni-td">
                            <?php if ($sr == 'attivo') { ?>
                            <form action="annulla_abbonamento.php" method="POST" onsubmit="return confirm('Sei sicuro di voler annullare questo abbonamento?');">
                                <input type="hidden" name="id_ordine" value="<?php echo $ord['id_ordine']; ?>">
                                <button type="submit" class="btn-annulla">Annulla</button>
                            </form>
                            <?php } else { ?>
                            <span class="nessuna-azione">Nessuna</span>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <?php 
                    next($ordini_lista);
                } 
                ?>
            </tbody>
        </table>
    </div>

    <?php } else { ?>
    <div class="nessun-ordine">
        <p class="icona-vuota">📦</p>
        <p>Non hai ancora nessun abbonamento attivo. Scopri i nostri agenti AI!</p>
        <a href="../index.php" class="cta">Esplora lo store</a>
    </div>
    <?php } ?>

    <div class="torna-store-container">
        <a href="../index.php" class="link-ritorno">Torna allo store</a>
    </div>

</main>

<footer>
    <div class="footer-inner">
        <span class="footer-brand">© 2026 AIAIAI STORE - ENRICO LANZAROLO 5Bi</span>
        <span class="footer-note">Abbonamenti digitali - rinnovo automatico mensile</span>
    </div>
</footer>

<?php mysqli_close($conn); ?>
</body>
</html>