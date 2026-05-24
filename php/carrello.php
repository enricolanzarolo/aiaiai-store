<?php
//avvio la sessione per gestire i dati dell utente
session_start();

//includo il file per connettermi al database
include 'connessione.php';

//controllo se l utente e loggato correttamente
if (!isset($_SESSION['id_utente'])) {
    //se non e loggato lo rimando alla pagina di login
    header('Location: login.php');
    exit;
}

//salvo l id dell utente in una variabile comoda
$id_utente = $_SESSION['id_utente'];

//controllo se il carrello esiste altrimenti lo creo vuoto
if (!isset($_SESSION['carrello']) || !is_array($_SESSION['carrello'])) {
    $_SESSION['carrello'] = [];
}

//controllo se e stato chiesto di aggiungere un prodotto
if (isset($_GET['azione']) && $_GET['azione'] == 'aggiungi' && isset($_GET['id_prodotto'])) {
    //trasformo l id in un numero intero per sicurezza
    $id_da_aggiungere = (int)$_GET['id_prodotto'];

    //se il prodotto non e gia nel carrello lo aggiungo
    if (!in_array($id_da_aggiungere, $_SESSION['carrello'])) {
        //inserisco l id dentro l array del carrello
        $_SESSION['carrello'][] = $id_da_aggiungere;
    }

    //imposto la pagina di ritorno predefinita
    $ritorno = 'carrello.php';
    
    //se arrivo dalla pagina del catalogo generale
    if (isset($_GET['da'])) {
        $ritorno = 'catalogo.php?id_categoria=' . (int)$_GET['da'];
    }
    //se arrivo dalla pagina del singolo prodotto
    if (isset($_GET['da_prodotto'])) {
        $ritorno = 'prodotto.php?id_prodotto=' . $id_da_aggiungere;
    }
    
    //eseguo il reindirizzamento alla pagina stabilita
    header('Location: ' . $ritorno);
    exit;
}

//controllo se e stato chiesto di rimuovere un prodotto
if (isset($_GET['azione']) && $_GET['azione'] == 'rimuovi' && isset($_GET['id_prodotto'])) {
    //trasformo l id in un numero intero per sicurezza
    $id_da_rimuovere = (int)$_GET['id_prodotto'];
    
    //cerco la posizione del prodotto dentro l array
    $chiave = array_search($id_da_rimuovere, $_SESSION['carrello']);
    
    //se il prodotto viene trovato dentro l array lo cancello
    if ($chiave !== false) {
        //elimino l elemento usando la sua chiave numerica
        unset($_SESSION['carrello'][$chiave]);
        //riordino gli indici dell array dal primo all ultimo
        $_SESSION['carrello'] = array_values($_SESSION['carrello']);
    }
    
    //rimando l utente alla pagina del carrello pulita
    header('Location: carrello.php');
    exit;
}

//query per recuperare il nome dell utente loggato
$sql_utente = "SELECT nome FROM utenti WHERE id_utente = '$id_utente'";
//eseguo la query di selezione sul database
$result_utente = mysqli_query($conn, $sql_utente);
//salvo i dati dell utente in un array associativo
$utente = mysqli_fetch_assoc($result_utente);

//creo un array vuoto per i prodotti e metto il totale a zero
$prodotti_carrello = [];
$totale = 0;

//se ci sono elementi dentro il carrello della sessione
if (!empty($_SESSION['carrello'])) {
    //faccio un ciclo per ogni id prodotto salvato
    foreach ($_SESSION['carrello'] as $id_prod_singolo) {
        //converto l id corrente in un numero intero
        $id_pulito = (int)$id_prod_singolo;
        
        //query per prendere i dati del singolo prodotto
        $sql_p = "SELECT * FROM prodotti WHERE id_prodotto = '$id_pulito'";
        //eseguo la query per trovare il prodotto corrispondente
        $res_p = mysqli_query($conn, $sql_p);
        //salvo i dati del prodotto estratti dal database
        $prod = mysqli_fetch_assoc($res_p);
        
        //se il prodotto esiste veramente dentro il database
        if ($prod) {
            //prendo l id della categoria del prodotto corrente
            $id_cat = $prod['id_categoria'];
            //query per prendere il nome testuale della categoria
            $sql_c = "SELECT nome_categoria FROM categorie WHERE id_categoria = '$id_cat'";
            //eseguo la query sulla tabella delle categorie
            $res_c = mysqli_query($conn, $sql_c);
            //salvo i dati della categoria in un array
            $cat = mysqli_fetch_assoc($res_c);
            
            //se esiste la categoria metto il nome altrimenti scrivo generica
            $prod['nome_categoria'] = $cat ? $cat['nome_categoria'] : 'Generica';
            
            //aggiungo il prodotto completo dentro l array del carrello
            $prodotti_carrello[] = $prod;
            //sommo il prezzo del prodotto al totale complessivo
            $totale = $totale + $prod['prezzo'];
        }
    }
}

//query per recuperare le carte di credito dell utente
$sql_carte = "SELECT * FROM carte_salvate WHERE id_utente = '$id_utente'";
//eseguo la query di selezione delle carte salvate
$result_carte = mysqli_query($conn, $sql_carte);
//inizializzo l array vuoto per contenere le carte
$carte = [];
//faccio un ciclo per estrarre tutte le carte trovate
while ($carta = mysqli_fetch_assoc($result_carte)) {
    //inserisco la carta corrente dentro l elenco delle carte
    $carte[] = $carta;
}

//conto quanti elementi ci sono dentro il carrello
$num_carrello = count($_SESSION['carrello']);
//chiudo la connessione al database aperta all inizio
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrello - AIAIAI Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/carrello.css">
</head>
<body>

<div class="bg-grid"></div>
<div class="bg-glow bg-glow-1"></div>
<div class="bg-glow bg-glow-2"></div>

<header>
    <div class="header-left">
        <span class="logo">AIAIAI</span>
        <span class="logo-sub">STORE</span>
    </div>
    <nav class="nav-user">
        <span class="nav-nome">Ciao, <span><?php echo $utente['nome']; ?></span></span>
        <a href="storico_ordini.php" class="btn-ghost">I miei abbonamenti</a>
        <a href="carrello.php" class="carrello-btn">
            🛒 Carrello
            <?php if ($num_carrello > 0) { ?><span class="carrello-badge"><?php echo $num_carrello; ?></span><?php } ?>
        </a>
        <a href="logout.php" class="btn-ghost">Esci</a>
    </nav>
</header>

<main class="carrello-main">

    <h2 class="sezionetitolo">🛒 Il tuo carrello</h2>
    <p class="sezione-desc">
        <?php echo $num_carrello; ?> elementi nel carrello
    </p>

    <?php if (empty($prodotti_carrello)) { ?>
        <div class="carrello-vuoto">
            <p class="icona-vuoto">🛒</p>
            <p>Il carrello è vuoto. Esplora le categorie per trovare il tuo agente AI.</p>
            <a href="../index.php" class="cta">Torna allo store →</a>
        </div>
    <?php } else { ?>
    <div class="carrello-layout">

        <div class="lista-prodotti">
            <?php 
            foreach ($prodotti_carrello as $item) { 
                $f = $item['fascia_abbonamento'];
                $fclass = 'f-' . $f;
                if ($f == 'enterprise') { $fclass = 'f-ent'; }
                ?>
                <div class="prodotto-row">
                    <div class="prodotto-info">
                        <div class="prodotto-nome">
                            <?php echo $item['nome_prodotto']; ?>
                            <span class="prodotto-fascia <?php echo $fclass; ?>"><?php echo $f; ?></span>
                        </div>
                        <div class="prodotto-cat"><?php echo $item['nome_categoria']; ?> · Rinnovo mensile</div>
                    </div>
                    <div class="prodotto-prezzo">€<?php echo $item['prezzo']; ?></div>
                    <a href="carrello.php?azione=rimuovi&id_prodotto=<?php echo $item['id_prodotto']; ?>" class="btn-rimuovi">✕ Rimuovi</a>
                </div>
            <?php } ?>

            <a href="../index.php" class="continua-shopping">← Continua lo shopping</a>
        </div>

        <div class="riepilogo-box">
            <h3>Riepilogo ordine</h3>

            <?php foreach ($prodotti_carrello as $item) { ?>
                <div class="riepilogo-row">
                    <span class="riepilogo-label"><?php echo $item['nome_prodotto']; ?></span>
                    <span>€<?php echo $item['prezzo']; ?></span>
                </div>
            <?php } ?>

            <div class="riepilogo-totale">
                <span>Totale/mese</span>
                <span>€<?php echo $totale; ?></span>
            </div>

            <form action="conferma_ordine.php" method="POST" id="formPagamento">
                <div class="sezione-pag">
                    <h4>Metodo di pagamento</h4>

                    <?php 
                    if (!empty($carte)) { 
                        foreach ($carte as $carta) {
                            $icona = '💳';
                            if ($carta['circuito'] == 'mastercard') { $icona = '🔴'; }
                            if ($carta['circuito'] == 'amex') { $icona = '🔷'; }
                            ?>
                            <label class="carta-option">
                                <input type="radio" name="metodo_pagamento" value="carta_salvata" <?php if ($carta['predefinita'] == 1) { echo 'checked'; } ?> onchange="selezionaCarta(<?php echo $carta['id_carta']; ?>)">
                                <input type="hidden" name="id_carta_nascosta_<?php echo $carta['id_carta']; ?>" value="<?php echo $carta['id_carta']; ?>">
                                <span class="carta-icon"><?php echo $icona; ?></span>
                                <div>
                                    <div class="carta-num"><?php echo $carta['numero_carta']; ?> · <?php echo $carta['circuito']; ?></div>
                                    <div class="carta-scad">Scad. <?php echo $carta['scadenza']; ?> · <?php echo $carta['intestatario']; ?></div>
                                </div>
                            </label>
                        <?php 
                        } 
                    } 
                    ?>

                    <label class="nuova-carta-toggle">
                        <input type="radio" name="metodo_pagamento" value="nuova_carta" <?php if (empty($carte)) { echo 'checked'; } ?> onchange="mostraNuovaCarta()">
                        <span>+ Usa una nuova carta</span>
                    </label>

                    <input type="hidden" name="id_carta_selezionata" id="id_carta_selezionata" value="<?php if (!empty($carte)) { echo $carte[0]['id_carta']; } ?>">

                    <div class="form-nuova <?php if (empty($carte)) { echo 'visibile'; } ?>" id="formNuovaCarta">
                        <div class="form-group">
                            <label>Intestatario</label>
                            <input type="text" name="intestatario" id="inputIntestario" placeholder="Nome Cognome">
                        </div>
                        <div class="form-group">
                            <label>Numero carta</label>
                            <input type="text" name="numero_carta" id="inputNumeroCarta" placeholder="**** **** **** 1234" maxlength="19">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Scadenza</label>
                                <input type="text" name="scadenza" id="inputScadenza" placeholder="MM/AA" maxlength="5">
                            </div>
                            <div class="form-group">
                                <label>Circuito</label>
                                <select name="circuito">
                                    <option value="visa">Visa</option>
                                    <option value="mastercard">Mastercard</option>
                                    <option value="amex">Amex</option>
                                </select>
                            </div>
                        </div>
                        <label class="salva-label">
                            <input type="checkbox" name="salva_carta" value="1">
                            Salva questa carta per i prossimi acquisti
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-paga">
                    Paga €<?php echo $totale; ?>/mese →
                </button>
            </form>

        </div>
    </div>
    <?php } ?>

</main>

<footer>
    <div class="footer-inner">
        <span class="footer-brand">© 2026 AIAIAI STORE - ENRICO LANZAROLO 5Bi</span>
        <span class="footer-note">Abbonamenti digitali — rinnovo automatico mensile</span>
    </div>
</footer>

<script>
//funzione per mostrare il modulo della nuova carta di credito
function mostraNuovaCarta() {
    document.getElementById('formNuovaCarta').classList.add('visibile');
    document.getElementById('id_carta_selezionata').value = '';
}
//funzione per selezionare una delle carte di credito salvate
function selezionaCarta(idCarta) {
    document.getElementById('formNuovaCarta').classList.remove('visibile');
    document.getElementById('id_carta_selezionata').value = idCarta;
}
//impostazione automatica dell id della prima carta se presente
<?php if (!empty($carte)) { ?>
document.getElementById('id_carta_selezionata').value = <?php echo $carte[0]['id_carta']; ?>;
<?php } ?>

// validazione JS dati carta
document.getElementById('formPagamento').addEventListener('submit', function(e) {
    const metodo = document.querySelector('input[name="metodo_pagamento"]:checked');
    if (!metodo || metodo.value !== 'nuova_carta') return; // carta salvata: nessun controllo

    const numero = document.getElementById('inputNumeroCarta').value.replace(/\s/g, '');
    const scadenza = document.getElementById('inputScadenza').value.trim();
    const intestatario = document.getElementById('inputIntestario').value.trim();

    if (!intestatario) {
        e.preventDefault();
        alert('Inserisci il nome dell\'intestatario.');
        return;
    }
    if (!/^\d{16}$/.test(numero)) {
        e.preventDefault();
        alert('Il numero carta deve essere di 16 cifre.');
        return;
    }
    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(scadenza)) {
        e.preventDefault();
        alert('La scadenza deve essere nel formato MM/AA.');
        return;
    }
    // controllo carta scaduta
    const [mm, yy] = scadenza.split('/');
    const scadenzaDate = new Date(2000 + parseInt(yy), parseInt(mm) - 1, 1);
    scadenzaDate.setMonth(scadenzaDate.getMonth() + 1); // scade a fine mese
    if (scadenzaDate <= new Date()) {
        e.preventDefault();
        alert('La carta è scaduta.');
        return;
    }
});
</script>

</body>
</html>