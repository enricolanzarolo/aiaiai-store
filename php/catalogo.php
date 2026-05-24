<?php
//avvio la sessione per recuperare i dati dell utente loggato
session_start();

//includo il file per la connessione al database
include 'connessione.php';

//controllo se non e presente l id utente in sessione
if (!isset($_SESSION['id_utente'])) {
    //rimando alla pagina di login per autenticarsi
    header('Location: login.php');
    exit;
}

//salvo l id utente in una variabile locale
$id_utente = $_SESSION['id_utente'];

//recupero l id della categoria dal parametro get
$id_categoria = 0;
if (isset($_GET['id_categoria'])) {
    $id_categoria = (int)$_GET['id_categoria'];
}

//se l id categoria e zero torno alla pagina principale
if ($id_categoria == 0) {
    header('Location: ../index.php');
    exit;
}

//seleziono il nome della categoria corrente
$sql_nomeCat = "SELECT nome_categoria FROM categorie WHERE id_categoria = '$id_categoria'";
$result_nomeCat = mysqli_query($conn, $sql_nomeCat);
$cat = mysqli_fetch_assoc($result_nomeCat);

//se la categoria non esiste nel database esco
if (!$cat) {
    header('Location: ../index.php');
    exit;
}

//recupero il nome dell utente loggato dal database
$sql_utente = "SELECT nome FROM utenti WHERE id_utente = '$id_utente'";
$result_utente = mysqli_query($conn, $sql_utente);
$utente = mysqli_fetch_assoc($result_utente);

//seleziono i prodotti appartenenti alla categoria indicata
$sql_prod = "SELECT * FROM prodotti WHERE id_categoria = '$id_categoria' ORDER BY prezzo ASC";
$result_prod = mysqli_query($conn, $sql_prod);

//recupero gli ordini attivi di questo utente
$sql_ordini = "SELECT id_ordine FROM ordini WHERE id_utente = '$id_utente' AND stato_abbonamento = 'attivo' AND data_rinnovo >= CURDATE()";
$res_ordini = mysqli_query($conn, $sql_ordini);

//imposto la variabile iniziale per la fascia posseduta
$fascia_posseduta = null;

//se ci sono ordini attivi controllo i prodotti acquistati
if ($res_ordini) {
    if (mysqli_num_rows($res_ordini) > 0) {
        //creo un array vuoto per salvare i codici ordine
        $lista_ordini = [];
        while ($ord = mysqli_fetch_assoc($res_ordini)) {
            $lista_ordini[] = $ord['id_ordine'];
        }
        
        //converto la lista degli ordini in stringa separata da virgole
        $stringa_ordini = implode(',', $lista_ordini);
        
        //recupero i codici prodotto associati a questi ordini
        $sql_dettagli = "SELECT id_prodotto FROM dettagliordini WHERE id_ordine IN ($stringa_ordini)";
        $res_dettagli = mysqli_query($conn, $sql_dettagli);
        
        //se trovo dettagli controllo le fasce dei prodotti
        if ($res_dettagli) {
            if (mysqli_num_rows($res_dettagli) > 0) {
                //creo un array vuoto per contenere i codici prodotto
                $lista_prodotti = [];
                while ($det = mysqli_fetch_assoc($res_dettagli)) {
                    $lista_prodotti[] = $det['id_prodotto'];
                }
                
                //converto la lista dei prodotti in stringa per la query
                $stringa_prodotti = implode(',', $lista_prodotti);
                
                //seleziono la fascia abbonamento piu alta per la categoria
                $sql_fascia = "SELECT fascia_abbonamento FROM prodotti WHERE id_prodotto IN ($stringa_prodotti) AND id_categoria = '$id_categoria' ORDER BY FIELD(fascia_abbonamento, 'enterprise', 'pro', 'base') LIMIT 1";
                $res_fascia = mysqli_query($conn, $sql_fascia);
                
                //se trovo il prodotto salvo la fascia trovata
                if ($res_fascia) {
                    if (mysqli_num_rows($res_fascia) > 0) {
                        $row_f = mysqli_fetch_assoc($res_fascia);
                        $fascia_posseduta = $row_f['fascia_abbonamento'];
                    }
                }
            }
        }
    }
}

//conto quanti elementi ci sono nel carrello della sessione
$num_carrello = 0;
if (isset($_SESSION['carrello'])) {
    if (is_array($_SESSION['carrello'])) {
        $num_carrello = count($_SESSION['carrello']);
    }
}

//chiudo la connessione attiva con il database mysql
mysqli_close($conn);

//funzione per calcolare lo stato dei bottoni
function stato_piano($fascia_prodotto, $fascia_posseduta) {
    if ($fascia_posseduta === null) {
        return 'disponibile';
    }

    $lv_prod = 0;
    if ($fascia_prodotto == 'base') {
        $lv_prod = 1;
    }
    if ($fascia_prodotto == 'pro') {
        $lv_prod = 2;
    }
    if ($fascia_prodotto == 'enterprise') {
        $lv_prod = 3;
    }

    $lv_poss = 0;
    if ($fascia_posseduta == 'base') {
        $lv_poss = 1;
    }
    if ($fascia_posseduta == 'pro') {
        $lv_poss = 2;
    }
    if ($fascia_posseduta == 'enterprise') {
        $lv_poss = 3;
    }

    if ($lv_prod == $lv_poss) {
        return 'gia_attivo';
    }
    if ($lv_prod > $lv_poss) {
        return 'upgrade';
    }
    
    return 'bloccato';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($cat['nome_categoria']); ?> - AIAIAI Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/catalogo.css">
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
            🛒 Carrello
            <?php if ($num_carrello > 0) { ?>
                <span class="carrello-badge"><?php echo $num_carrello; ?></span>
            <?php } ?>
        </a>
        <a href="logout.php" class="btn-ghost">Esci</a>
    </nav>
</header>

<main class="catalogo-container">

    <div class="breadcrumb">
        <a href="../index.php">Store</a>
        <span>/</span>
        <span><?php echo htmlspecialchars($cat['nome_categoria']); ?></span>
    </div>

    <?php if (isset($_GET['aggiunto'])) { ?>
    <div class="msg-successo">Prodotto aggiunto al carrello</div>
    <?php } ?>

    <?php if ($fascia_posseduta !== null) { ?>
    <div class="banner-abbonamento">
        Hai già un abbonamento <strong><?php echo strtoupper($fascia_posseduta); ?></strong> attivo per questa categoria.
        <?php if ($fascia_posseduta !== 'enterprise') { ?>
            Puoi fare upgrade al piano superiore.
        <?php } else { ?>
            Hai il piano massimo disponibile.
        <?php } ?>
    </div>
    <?php } ?>

    <h2 class="sezionetitolo"><?php echo htmlspecialchars($cat['nome_categoria']); ?></h2>
    <p class="sezione-desc">Scegli il piano più adatto. Tutti i piani includono rinnovo automatico mensile.</p>

    <div class="piani-grid">
        <?php while ($prod = mysqli_fetch_assoc($result_prod)) {
            $fascia = $prod['fascia_abbonamento'];
            $sp = stato_piano($fascia, $fascia_posseduta);
            
            $gia_in_carrello = false;
            if (isset($_SESSION['carrello'])) {
                if (is_array($_SESSION['carrello'])) {
                    if (in_array($prod['id_prodotto'], $_SESSION['carrello'])) {
                        $gia_in_carrello = true;
                    }
                }
            }
            
            $card_extra_class = "";
            if ($sp == 'bloccato') {
                $card_extra_class = "bloccato";
            }
        ?>
        <div class="piano-card <?php echo $fascia; ?> <?php echo $card_extra_class; ?>">

            <span class="piano-badge badge-<?php echo ($fascia == 'enterprise') ? 'ent' : $fascia; ?>">
                <?php echo strtoupper($fascia); ?>
            </span>

            <div class="piano-nome"><?php echo htmlspecialchars($prod['nome_prodotto']); ?></div>
            <div class="piano-desc"><?php echo htmlspecialchars($prod['descrizione']); ?></div>

            <div class="piano-prezzo <?php echo $fascia; ?>">
                €<?php echo number_format($prod['prezzo'], 2, ',', '.'); ?>
            </div>
            <div class="piano-mese">al mese · rinnovo automatico</div>

            <div class="piano-actions">
                <a href="prodotto.php?id_prodotto=<?php echo $prod['id_prodotto']; ?>" class="btn-dettagli">Dettagli</a>

                <?php if ($sp == 'bloccato') { ?>
                    <span class="btn-bloccato">Non disponibile</span>

                <?php } else if ($sp == 'gia_attivo') { ?>
                    <span class="btn-gia-attivo">Piano attivo</span>

                <?php } else if ($gia_in_carrello) { ?>
                    <span class="btn-gia-in-carrello">Nel carrello</span>

                <?php } else if ($sp == 'upgrade') { ?>
                    <a href="carrello.php?azione=aggiungi&id_prodotto=<?php echo $prod['id_prodotto']; ?>&da=<?php echo $id_categoria; ?>"
                       class="btn-upgrade">
                        Fai upgrade
                    </a>

                <?php } else { ?>
                    <a href="carrello.php?azione=aggiungi&id_prodotto=<?php echo $prod['id_prodotto']; ?>&da=<?php echo $id_categoria; ?>"
                       class="btn-aggiungi btn-<?php echo ($fascia == 'enterprise') ? 'ent' : $fascia; ?>">
                        Aggiungi al carrello
                    </a>
                <?php } ?>
            </div>

        </div>
        <?php } ?>
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