<?php
//avvio la sessione per recuperare i dati dell utente loggato
session_start();

//includo il file per la connessione al database
include 'connessione.php';

//controllo se non e presente l id utente in sessione
if (!isset($_SESSION['id_utente'])) {
    header('Location: login.php');
    exit;
}

//salvo l id utente in una variabile locale
$id_utente = $_SESSION['id_utente'];

//controllo che il metodo di richiesta sia post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: carrello.php');
    exit;
}

//verifica che ci sia qualcosa nel carrello della sessione
$carrello_valido = false;
if (!empty($_SESSION['carrello'])) {
    if (is_array($_SESSION['carrello'])) {
        $carrello_valido = true;
    }
}

if (!$carrello_valido) {
    header('Location: carrello.php');
    exit;
}

//recupero i dati inviati dal modulo di pagamento
$metodo = $_POST['metodo_pagamento'];
$id_carta_selezionata = 0;
$intestatario = '';
$numero_carta = '';
$scadenza = '';
$circuito = '';
$salva_carta = 0;
$errore = '';

if ($metodo == 'carta_salvata') {
    if (isset($_POST['id_carta_selezionata'])) {
        $id_carta_selezionata = (int)$_POST['id_carta_selezionata'];
    }

    if ($id_carta_selezionata == 0) {
        $errore = 'Seleziona una carta valida.';
    } else {
        //recupero i dati della carta salvata per mostrarli
        $sql_check = "SELECT * FROM carte_salvate WHERE id_carta = '$id_carta_selezionata' AND id_utente = '$id_utente'";
        $res_check = mysqli_query($conn, $sql_check);
        if (mysqli_num_rows($res_check) == 0) {
            $errore = 'Carta non valida.';
        } else {
            $carta_dati = mysqli_fetch_assoc($res_check);
            $numero_carta = $carta_dati['numero_carta'];
            $circuito = $carta_dati['circuito'];
        }
    }

} elseif ($metodo == 'nuova_carta') {
    $intestatario = mysqli_real_escape_string($conn, trim($_POST['intestatario']));
    $numero_carta = mysqli_real_escape_string($conn, trim($_POST['numero_carta']));
    $scadenza = mysqli_real_escape_string($conn, trim($_POST['scadenza']));
    $circuito = mysqli_real_escape_string($conn, $_POST['circuito']);
    if (isset($_POST['salva_carta'])) {
        $salva_carta = 1;
    }

    if (empty($intestatario) || empty($numero_carta) || empty($scadenza)) {
        $errore = 'Compila tutti i dati della carta.';
    }
} else {
    $errore = 'Metodo di pagamento non valido.';
}

if (!empty($errore)) {
    mysqli_close($conn);
    header('Location: carrello.php?errore=' . urlencode($errore));
    exit;
}

//recupero l elenco dei prodotti per calcolare il totale
$array_id_puliti = [];
reset($_SESSION['carrello']);
while ($id_singolo = current($_SESSION['carrello'])) {
    $array_id_puliti[] = (int)$id_singolo;
    next($_SESSION['carrello']);
}
$ids = implode(',', $array_id_puliti);

$sql_totale = "SELECT id_prodotto, prezzo, nome_prodotto FROM prodotti WHERE id_prodotto IN ($ids)";
$res_totale = mysqli_query($conn, $sql_totale);
$totale_ordine = 0;
$prodotti_mostra = [];

while ($p = mysqli_fetch_assoc($res_totale)) {
    $prodotti_mostra[] = $p;
    $totale_ordine += $p['prezzo'];
}

//recupero i dati dell utente corrente
$sql_utente = "SELECT nome, cognome FROM utenti WHERE id_utente = '$id_utente'";
$res_utente = mysqli_query($conn, $sql_utente);
$utente = mysqli_fetch_assoc($res_utente);


//fase uno mostra la pagina di richiesta conferma
if (!isset($_POST['conferma_definitiva'])) {
    mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riepilogo Ordine - AIAIAI Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/conferma.css">
</head>
<body>
<div class="bg-grid"></div>

<header>
    <div class="header-left">
        <span class="logo"><a href="../index.php">AIAIAI</a></span>
        <span class="logo-sub">STORE</span>
    </div>
</header>

<main class="conferma-main">
    <div class="conferma-box">
        <h2 class="conferma-titolo">Fase 1 - Conferma il tuo ordine</h2>
        <p class="conferma-sub">Controlla i dati prima di rendere attivo l abbonamento sul database.</p>

        <div class="dettaglio-box">
            <div class="det-titolo">Prodotti nel carrello</div>
            <?php 
            reset($prodotti_mostra);
            while ($p = current($prodotti_mostra)) { 
            ?>
            <div class="det-row">
                <span class="det-label"><?php echo htmlspecialchars($p['nome_prodotto']); ?></span>
                <span class="det-val">€<?php echo number_format($p['prezzo'], 2, ',', '.'); ?>/mese</span>
            </div>
            <?php 
                next($prodotti_mostra);
            } 
            ?>
            <div class="det-row">
                <span class="det-label">Totale mensile da addebitare</span>
                <span class="det-totale">€<?php echo number_format($totale_ordine, 2, ',', '.'); ?></span>
            </div>
        </div>

        <div class="dettaglio-box">
            <div class="det-titolo">Metodo pagamento scelto</div>
            <div class="det-row">
                <span class="det-label">Tipo pagamento</span>
                <span class="det-val"><?php echo htmlspecialchars($metodo); ?></span>
            </div>
            <div class="det-row">
                <span class="det-label">Carta di credito</span>
                <span class="det-val"><?php echo htmlspecialchars($numero_carta); ?> (<?php echo strtoupper($circuito); ?>)</span>
            </div>
        </div>

        <form action="" method="POST">
            <input type="hidden" name="metodo_pagamento" value="<?php echo htmlspecialchars($metodo); ?>">
            <input type="hidden" name="id_carta_selezionata" value="<?php echo $id_carta_selezionata; ?>">
            <input type="hidden" name="intestatario" value="<?php echo htmlspecialchars($intestatario); ?>">
            <input type="hidden" name="numero_carta" value="<?php echo htmlspecialchars($numero_carta); ?>">
            <input type="hidden" name="scadenza" value="<?php echo htmlspecialchars($scadenza); ?>">
            <input type="hidden" name="circuito" value="<?php echo htmlspecialchars($circuito); ?>">
            <input type="hidden" name="salva_carta" value="<?php echo $salva_carta; ?>">
            <input type="hidden" name="conferma_definitiva" value="1">

            <div class="azioni">
                <a href="carrello.php" class="btn-outline">Rifiuta e torna al carrello</a>
                <button type="submit" class="cta">Conferma ed effettua acquisto</button>
            </div>
        </form>
    </div>
</main>
</body>
</html>
<?php
    exit;
}


//fase due esecuzione delle query nel database dopo il secondo invio
$id_carta = 0;
if ($metodo == 'carta_salvata') {
    $id_carta = $id_carta_selezionata;
} elseif ($metodo == 'nuova_carta') {
    $num_pulito = preg_replace('/\s/', '', $numero_carta);
    $num_mascherato = '****' . substr($num_pulito, -4);
    
    $sql_carta = "INSERT INTO carte_salvate (id_utente, intestatario, numero_carta, scadenza, circuito, predefinita) VALUES ('$id_utente', '$intestatario', '$num_mascherato', '$scadenza', '$circuito', '$salva_carta')";
    mysqli_query($conn, $sql_carta);
    $id_carta = mysqli_insert_id($conn);
}

//generazione delle date per la scrittura record
$data_acquisto = date('Y-m-d');
$data_rinnovo = date('Y-m-d', strtotime('+1 month'));

//inserisco l ordine principale
$sql_ordine = "INSERT INTO ordini (id_utente, id_carta, data_acquisto, data_rinnovo, stato_abbonamento, totale_ordine) VALUES ('$id_utente', '$id_carta', '$data_acquisto', '$data_rinnovo', 'attivo', '$totale_ordine')";
mysqli_query($conn, $sql_ordine);
$id_ordine = mysqli_insert_id($conn);

//inserisco i dettagli legati all ordine corrente
reset($prodotti_mostra);
while ($prod = current($prodotti_mostra)) {
    $id_prod = $prod['id_prodotto'];
    $prezzo_u = $prod['prezzo'];
    $sql_det = "INSERT INTO dettagliordini (id_ordine, id_prodotto, prezzo_unitario) VALUES ('$id_ordine', '$id_prod', '$prezzo_u')";
    mysqli_query($conn, $sql_det);
    next($prodotti_mostra);
}

//svuota il carrello dopo il salvataggio dei dati
unset($_SESSION['carrello']);
$_SESSION['carrello'] = [];

//recupero la carta addebitata finale per la ricevuta grafica
$sql_carta_conf = "SELECT * FROM carte_salvate WHERE id_carta = '$id_carta'";
$res_carta_conf = mysqli_query($conn, $sql_carta_conf);
$carta_conf = mysqli_fetch_assoc($res_carta_conf);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordine Confermato - AIAIAI Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/conferma.css">
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
    <nav>
        <a href="../index.php" class="btn-ghost">Store</a>
    </nav>
</header>

<main class="conferma-main">
    <div class="conferma-box">
        <div class="conferma-icona">✅</div>
        <h2 class="conferma-titolo">Fase 2 - Ordine salvato e confermato!</h2>
        <p class="conferma-sub">
            Grazie <?php echo htmlspecialchars($utente['nome']); ?>! I tuoi abbonamenti sono ora registrati e attivi.
        </p>

        <div class="dettaglio-box">
            <div class="det-titolo">Riepilogo finale ordine #<?php echo $id_ordine; ?></div>
            <?php 
            reset($prodotti_mostra);
            while ($p = current($prodotti_mostra)) { 
            ?>
            <div class="det-row">
                <span class="det-label"><?php echo htmlspecialchars($p['nome_prodotto']); ?></span>
                <span class="det-val">€<?php echo number_format($p['prezzo'], 2, ',', '.'); ?>/mese</span>
            </div>
            <?php 
                next($prodotti_mostra);
            } 
            ?>
            <div class="det-row">
                <span class="det-label">Totale addebitato</span>
                <span class="det-totale">€<?php echo number_format($totale_ordine, 2, ',', '.'); ?></span>
            </div>
        </div>

        <div class="dettaglio-box">
            <div class="det-titolo">Dettagli temporali abbonamento</div>
            <div class="det-row">
                <span class="det-label">Data acquisto</span>
                <span class="det-val"><?php echo date('d/m/Y'); ?></span>
            </div>
            <div class="det-row">
                <span class="det-label">Prossimo rinnovo automatico</span>
                <span class="det-val"><?php echo date('d/m/Y', strtotime('+1 month')); ?></span>
            </div>
            <div class="det-row">
                <span class="det-label">Stato sistema</span>
                <span class="det-val" style="color:var(--cyan);">● Attivo</span>
            </div>
            <?php if ($carta_conf) { ?>
            <div class="det-row">
                <span class="det-label">Metodo di addebito</span>
                <span class="det-val"><?php echo htmlspecialchars($carta_conf['numero_carta']); ?> (<?php echo strtoupper($carta_conf['circuito']); ?>)</span>
            </div>
            <?php } ?>
        </div>

        <div class="azioni">
            <a href="storico_ordini.php" class="btn-outline">I miei abbonamenti</a>
            <a href="../index.php" class="btn-outline">Torna allo store</a>
        </div>
    </div>
</main>

<footer>
    <div class="footer-inner">
        <span class="footer-brand">© 2026 AIAIAI STORE - ENRICO LANZAROLO 5Bi</span>
        <span class="footer-note">Abbonamenti digitali — rinnovo automatico mensile</span>
    </div>
</footer>
</body>
</html>