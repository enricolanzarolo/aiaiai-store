<?php
//avvio la sessione per controllare i privilegi dell utente
session_start();
include 'connessione.php';

//controllo accesso admin per sicurezza portale
if (!isset($_SESSION['id_utente']) || $_SESSION['ruolo'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$id_utente = (int)$_SESSION['id_utente']; 

//recupero dati dell admin corrente per la personalizzazione
$sql_admin = "SELECT nome, cognome FROM utenti WHERE id_utente = $id_utente";
$query_admin = mysqli_query($conn, $sql_admin);
$admin = mysqli_fetch_assoc($query_admin);

//statistiche essenziali 
$res_utenti = mysqli_query($conn, "SELECT COUNT(*) AS n FROM utenti WHERE ruolo = 'utente'");
$dati_utenti = mysqli_fetch_assoc($res_utenti);
$tot_utenti = 0;
if (isset($dati_utenti['n'])) {
    $tot_utenti = $dati_utenti['n'];
}

$res_ordini = mysqli_query($conn, "SELECT COUNT(*) AS n FROM ordini");
$dati_ordini = mysqli_fetch_assoc($res_ordini);
$tot_ordini = 0;
if (isset($dati_ordini['n'])) {
    $tot_ordini = $dati_ordini['n'];
}

$res_attivi = mysqli_query($conn, "SELECT COUNT(*) AS n FROM ordini WHERE stato_abbonamento = 'attivo' AND data_rinnovo >= CURDATE()");
$dati_attivi = mysqli_fetch_assoc($res_attivi);
$abb_attivi = 0;
if (isset($dati_attivi['n'])) {
    $abb_attivi = $dati_attivi['n'];
}

$res_ricavo_mese = mysqli_query($conn, "SELECT SUM(totale_ordine) AS tot FROM ordini WHERE stato_abbonamento = 'attivo' AND data_rinnovo >= CURDATE()");
$dati_ricavo_mese = mysqli_fetch_assoc($res_ricavo_mese);
$ricavo_mese = 0;
if (isset($dati_ricavo_mese['tot'])) {
    $ricavo_mese = $dati_ricavo_mese['tot'];
}

$res_ricavo_tot = mysqli_query($conn, "SELECT SUM(totale_ordine) AS tot FROM ordini");
$dati_ricavo_tot = mysqli_fetch_assoc($res_ricavo_tot);
$ricavo_tot = 0;
if (isset($dati_ricavo_tot['tot'])) {
    $ricavo_tot = $dati_ricavo_tot['tot'];
}

//distribuzione piani calcolata in php tramite array locale
$fasce = ['base' => 0, 'pro' => 0, 'enterprise' => 0];
$sql_piani = "SELECT p.fascia_abbonamento FROM ordini o, dettagliordini d, prodotti p WHERE o.id_ordine = d.id_ordine AND d.id_prodotto = p.id_prodotto AND o.stato_abbonamento = 'attivo' AND o.data_rinnovo >= CURDATE()";
$res_piani = mysqli_query($conn, $sql_piani);

if ($res_piani) {
    while ($row = mysqli_fetch_assoc($res_piani)) {
        $f_nome = strtolower($row['fascia_abbonamento']);
        if (array_key_exists($f_nome, $fasce)) {
            $fasce[$f_nome]++;
        }
    }
}
$tot_fasce = array_sum($fasce);
if ($tot_fasce == 0) {
    $tot_fasce = 1;
}

//recupero gli ultimi 6 utenti iscritti ordinati per data
$ultimi_utenti = mysqli_query($conn, "SELECT nome, cognome, email, data_registrazione FROM utenti WHERE ruolo = 'utente' ORDER BY data_registrazione DESC LIMIT 6");

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello di Controllo Admin — AIAIAI Store</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

    <header>
        <div class="logo-area">
            <h1>AIAIAI <span>STORE ADMIN</span></h1>
        </div>
        <div class="nav-links">
            <a href="../index.php" class="btn-ritorna">← Torna al Sito Pubblico</a>
            <a href="logout.php" class="btn-logout">Esci</a>
        </div>
    </header>

    <main>
        <div class="welcome-msg">
            <h2>Bentornato, <?php echo htmlspecialchars($admin['nome']); ?></h2>
            <p>Panoramica globale delle vendite e delle performance del portale.</p>
        </div>

        <div class="kpi-container">
            <div class="kpi-box">
                <div class="label">Utenti Registrati</div>
                <div class="number"><?php echo $tot_utenti; ?></div>
            </div>
            <div class="kpi-box kpi-evidenziato">
                <div class="label">Abbonamenti Attivi</div>
                <div class="number"><?php echo $abb_attivi; ?></div>
            </div>
            <div class="kpi-box">
                <div class="label">Fatturato Mensile Corrente</div>
                <div class="number kpi-positivo">€<?php echo number_format($ricavo_mese, 2, ',', '.'); ?></div>
            </div>
            <div class="kpi-box">
                <div class="label">Volume d'Affari Storico</div>
                <div class="number">€<?php echo number_format($ricavo_tot, 2, ',', '.'); ?></div>
            </div>
        </div>

        <div class="dashboard-layout">
            
            <div class="card-block">
                <h3>Abbonamenti Attivi per Fascia</h3>
                <?php 
                $lista_piani = ['base', 'pro', 'enterprise'];
                reset($lista_piani);
                while ($f = current($lista_piani)) {
                    $percentuale = round(($fasce[$f] / $tot_fasce) * 100);
                ?>
                <div class="progress-row">
                    <div class="progress-info">
                        <span class="progress-nome"><?php echo $f; ?></span>
                        <span><strong><?php echo $fasce[$f]; ?></strong> utenze</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?php echo $percentuale; ?>%;"></div>
                    </div>
                </div>
                <?php 
                    next($lista_piani);
                } 
                ?>
            </div>

            <div class="card-block">
                <h3>Ultimi Iscritti alla Piattaforma</h3>
                <ul class="simple-list">
                    <?php 
                    if ($ultimi_utenti) {
                        while ($u = mysqli_fetch_assoc($ultimi_utenti)) {
                    ?>
                    <li class="list-item">
                        <strong><?php echo htmlspecialchars($u['nome'] . ' ' . $u['cognome']); ?></strong>
                        <small><?php echo htmlspecialchars($u['email']); ?> · Registrato il <?php echo date('d/m/Y', strtotime($u['data_registrazione'])); ?></small>
                    </li>
                    <?php 
                        }
                    } 
                    ?>
                </ul>
            </div>

        </div>
    </main>

</body>
</html>