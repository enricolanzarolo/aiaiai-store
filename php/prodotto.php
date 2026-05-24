<?php
//avvio la sessione per tracciare lo stato dell utente loggato
session_start();
include 'connessione.php';

//controllo che l utente sia autenticato prima di mostrare il prodotto
if (!isset($_SESSION['id_utente'])) {
    header('Location: login.php');
    exit;
}

$id_utente = $_SESSION['id_utente'];

//controllo del parametro id prodotto passato in query string
$id_prodotto = 0;
if (isset($_GET['id_prodotto'])) {
    $id_prodotto = (int)$_GET['id_prodotto'];
}

if ($id_prodotto == 0) {
    header('Location: ../index.php');
    exit;
}

//recupero le informazioni del prodotto comprensive di categoria
$sql_prod = "SELECT p.*, c.nome_categoria, c.id_categoria FROM prodotti p JOIN categorie c ON p.id_categoria = c.id_categoria WHERE p.id_prodotto = '$id_prodotto'";
$result_prod = mysqli_query($conn, $sql_prod);

if (!$result_prod) {
    header('Location: ../index.php');
    exit;
}
if (mysqli_num_rows($result_prod) == 0) {
    header('Location: ../index.php');
    exit;
}

$prod = mysqli_fetch_assoc($result_prod);

//recupero il nome dell utente corrente per l intestazione
$sql_utente = "SELECT nome FROM utenti WHERE id_utente = '$id_utente'";
$result_utente = mysqli_query($conn, $sql_utente);
$utente = mysqli_fetch_assoc($result_utente);

//recupero altri prodotti alternativi appartenenti alla stessa categoria
$id_cat_prod = $prod['id_categoria'];
$sql_altri = "SELECT * FROM prodotti WHERE id_categoria = '$id_cat_prod' AND id_prodotto != '$id_prodotto' ORDER BY prezzo ASC";
$result_altri = mysqli_query($conn, $sql_altri);

//controllo della fascia gia posseduta dall utente per questa categoria
$id_categoria = $prod['id_categoria'];
$sql_fascia = "SELECT p2.fascia_abbonamento FROM ordini o JOIN dettagliordini d ON o.id_ordine = d.id_ordine JOIN prodotti p2 ON d.id_prodotto = p2.id_prodotto WHERE o.id_utente = '$id_utente' AND p2.id_categoria = '$id_categoria' AND o.stato_abbonamento = 'attivo' AND o.data_rinnovo >= CURDATE() ORDER BY FIELD(p2.fascia_abbonamento, 'enterprise', 'pro', 'base') LIMIT 1";
$res_fascia = mysqli_query($conn, $sql_fascia);
$fascia_posseduta = null;

if ($res_fascia) {
    if (mysqli_num_rows($res_fascia) > 0) {
        $row_f = mysqli_fetch_assoc($res_fascia);
        $fascia_posseduta = $row_f['fascia_abbonamento'];
    }
}

//conteggio degli elementi presenti nel carrello della sessione
$num_carrello = 0;
if (isset($_SESSION['carrello'])) {
    if (is_array($_SESSION['carrello'])) {
        $num_carrello = count($_SESSION['carrello']);
    }
}

//verifica se il prodotto corrente e gia presente nel carrello
$gia_in_carrello = false;
if (isset($_SESSION['carrello'])) {
    if (is_array($_SESSION['carrello'])) {
        if (in_array($id_prodotto, $_SESSION['carrello'])) {
            $gia_in_carrello = true;
        }
    }
}

//matrice statica delle caratteristiche dei vari abbonamenti
$features = [
    'base' => [
        '1 utente incluso',
        'Fino a 500 operazioni al mese',
        'Supporto via email',
        'Dashboard base',
        'Aggiornamenti standard'
    ],
    'pro' => [
        'Fino a 5 utenti',
        'Fino a 5000 operazioni al mese',
        'Supporto prioritario',
        'Dashboard avanzata e api access',
        'Integrazioni premium',
        'Aggiornamenti anticipati'
    ],
    'enterprise' => [
        'Utenti illimitati',
        'Operazioni illimitate',
        'Supporto dedicato 24 su 7',
        'Sla garantito 99.9 %',
        'Deployment on-premise disponibile',
        'Account manager dedicato',
        'Personalizzazione white-label'
    ]
];

$fascia = $prod['fascia_abbonamento'];
$lista_feat = [];
if (isset($features[$fascia])) {
    $lista_feat = $features[$fascia];
}

//definizione dei livelli numerici per calcolare upgrade o blocchi
$livelli = ['base' => 1, 'pro' => 2, 'enterprise' => 3];
$lv_prod = 0;
if (isset($livelli[$fascia])) {
    $lv_prod = $livelli[$fascia];
}

$lv_poss = 0;
if ($fascia_posseduta) {
    if (isset($livelli[$fascia_posseduta])) {
        $lv_poss = $livelli[$fascia_posseduta];
    }
}

//calcolo dello stato del piano d acquisto per l utente corrente
if ($fascia_posseduta === null) {
    $stato_piano = 'disponibile';
} else {
    if ($lv_prod == $lv_poss) {
        $stato_piano = 'gia_attivo';
    } else {
        if ($lv_prod > $lv_poss) {
            $stato_piano = 'upgrade';
        } else {
            $stato_piano = 'bloccato';
        }
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($prod['nome_prodotto']); ?> - AIAIAI Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dettaglio.css">
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

<main class="main-prodotto">

    <div class="breadcrumb">
        <a href="../index.php">Torna allo Store</a>
        <span>/</span>
        <a href="catalogo.php?id_categoria=<?php echo $prod['id_categoria']; ?>"><?php echo htmlspecialchars($prod['nome_categoria']); ?></a>
        <span>/</span>
        <span><?php echo htmlspecialchars($prod['nome_prodotto']); ?></span>
    </div>

    <div class="prodotto-layout">

        <div class="prodotto-info">
            <?php
            $classe_badge = "badge-" . $fascia;
            if ($fascia == "enterprise") {
                $classe_badge = "badge-ent";
            }
            ?>
            <span class="fascia-badge <?php echo $classe_badge; ?>">
                Piano <?php echo strtoupper($fascia); ?>
            </span>

            <h1 class="prodotto-nome"><?php echo htmlspecialchars($prod['nome_prodotto']); ?></h1>
            <p class="prodotto-cat">Categoria: <?php echo htmlspecialchars($prod['nome_categoria']); ?></p>
            <p class="prodotto-desc"><?php echo htmlspecialchars($prod['descrizione']); ?></p>

            <?php if (!empty($lista_feat)) { ?>
                <div class="features-titolo">Cosa include</div>
                <ul class="features-lista <?php echo $fascia; ?>">
                    <?php
                    reset($lista_feat);
                    while ($feat = current($lista_feat)) {
                    ?>
                        <li><?php echo htmlspecialchars($feat); ?></li>
                    <?php
                        next($lista_feat);
                    }
                    ?>
                </ul>
            <?php } ?>
        </div>

        <div class="acquisto-box">
            <div class="acquisto-prezzo <?php echo $fascia; ?>">
                €<?php echo number_format($prod['prezzo'], 2, ',', '.'); ?>
            </div>
            <div class="acquisto-mese">al mese - fatturazione mensile</div>

            <div class="info-row">
                <span class="info-label">Piano</span>
                <span class="info-val"><?php echo ucfirst($fascia); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Categoria</span>
                <span class="info-val"><?php echo htmlspecialchars($prod['nome_categoria']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Rinnovo</span>
                <span class="info-val">Mensile automatico</span>
            </div>
            <div class="info-row">
                <span class="info-label">Disdetta</span>
                <span class="info-val">In qualsiasi momento</span>
            </div>

            <?php if ($stato_piano == 'bloccato') { ?>
                <span class="btn-cta bloccato">Non disponibile</span>
                <div class="lock-info">
                    Hai gia un piano <?php echo strtoupper($fascia_posseduta); ?> attivo.
                    Non puoi passare a un piano inferiore.
                </div>

            <?php } else if ($stato_piano == 'gia_attivo') { ?>
                <span class="btn-cta gia-attivo">Piano gia attivo</span>
                <a href="storico_ordini.php" class="btn-carrello-link">Gestisci i tuoi abbonamenti</a>

            <?php } else if ($gia_in_carrello) { ?>
                <span class="btn-cta gia-aggiunto">Gia nel carrello</span>
                <a href="carrello.php" class="btn-carrello-link">Vai al carrello</a>

            <?php } else if ($stato_piano == 'upgrade') { ?>
                <a href="carrello.php?azione=aggiungi&id_prodotto=<?php echo $id_prodotto; ?>&da_prodotto=1" class="btn-cta upgrade">
                    Fai upgrade a <?php echo strtoupper($fascia); ?>
                </a>
                <a href="carrello.php" class="btn-carrello-link">Vedi il carrello</a>

            <?php } else { ?>
                <a href="carrello.php?azione=aggiungi&id_prodotto=<?php echo $id_prodotto; ?>&da_prodotto=1" class="btn-cta <?php echo $fascia; ?>">
                    Aggiungi al carrello
                </a>
                <a href="carrello.php" class="btn-carrello-link">Vedi il carrello</a>
            <?php } ?>
        </div>

    </div>

</main>

<footer>
    <div class="footer-inner">
        <span class="footer-brand">© 2026 AIAIAI STORE - ENRICO LANZAROLO 5Bi</span>
        <span class="footer-note">Abbonamenti digitali - rinnovo automatico mensile</span>
    </div>
</footer>

</body>
</html>