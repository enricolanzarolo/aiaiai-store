<?php
session_start();

//controllo se utente ha sessione attiva
$loggato = isset($_SESSION['id_utente']);

if ($loggato) {
    //connessione al database
    include 'php/connessione.php';
    
    //recupero id utente e controllo se e admin
    $id_utente = $_SESSION['id_utente'];
    $is_admin  = isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin';

    //query per prendere nome e cognome utente loggato
    $sql_utente = "SELECT nome, cognome FROM utenti WHERE id_utente = '$id_utente'";
    $result_utente = mysqli_query($conn, $sql_utente);
    $utente = mysqli_fetch_assoc($result_utente);

    //inizializzo il contatore del carrello a zero
	$num_carrello = 0;

	//se esiste il carrello nella sessione ed e un array valido conto gli elementi
	if (isset($_SESSION['carrello']) && is_array($_SESSION['carrello'])) {
    $num_carrello = count($_SESSION['carrello']);
}

    //chiusura connessione database
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIAIAI Store</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="bg-grid"></div>
    <div class="bg-particles"></div> 
    <div class="bg-glow bg-glow-1"></div>
    <div class="bg-glow bg-glow-2"></div>
    <div class="bg-glow bg-glow-3"></div>

    <header>
        <div class="header-left">
            <span class="logo"><a href="index.php">AIAIAI</a></span>
            <span class="logo-sub">STORE</span>
        </div>
        <nav <?php echo $loggato ? 'class="nav-user"' : ''; ?>>
            <?php if ($loggato) { ?>
                <span class="nav-nome">Ciao, <span><?php echo htmlspecialchars($utente['nome']); ?></span></span>
                <?php if ($is_admin) { ?><a href="php/index_admin.php" class="btn-admin-nav"> Pannello Admin</a><?php } ?>
                <a href="php/storico_ordini.php" class="btn-ghost">I miei abbonamenti</a>
                <a href="php/carrello.php" class="btn-ghost">
                    Carrello
                    <?php if ($num_carrello > 0) { ?><span class="carrello-badge"><?php echo $num_carrello; ?></span><?php } ?>
                </a>
                <a href="php/logout.php" class="btn-ghost">Esci</a>
            <?php } else { ?>
                <a href="php/login.php" class="btn-ghost">Accedi</a>
            <?php } ?>
        </nav>
    </header>

    <main>

        <section class="hero">
            <div class="hero-badge">
                <span class="badge-dot"></span>
                Agenti AI — Abbonamento mensile
            </div>
            <h1>Automatizza.<br>Scala.<br><span class="gradient-text">Domina.</span></h1>
            <p class="hero-desc">
                Agenti AI specializzati per marketing, analisi dati,
                automazione, assistenza clienti e sviluppo.
                Scegli la categoria. Attivi subito.
            </p>
            <?php if ($loggato) { ?>
                <a href="#agenti" class="cta">Scegli il tuo agente →</a>
            <?php } else { ?>
                <a href="php/login.php" class="cta">Inizia ora →</a>
            <?php } ?>
        </section>

        <section class="categorie-sezione" id="agenti">
            <h2 class="sezionetitolo">Scegli il tuo Agente AI</h2>
            <p class="sezione-desc">Ogni agente è progettato per ottimizzare il tuo workflow. Clicca per scoprire i piani.</p>

            <div class="agenti-grid">

                <div class="agente-card stagger-1">
                    <div class="agente-media">
                        <video autoplay muted loop playsinline>
                            <source src="img/agent-marketing.mp4" type="video/mp4">
                        </video>
                    </div>
                    <div class="agente-content">
                        <h3>AI Marketing Agent</h3>
                        <p class="agente-role">Campagne · Social · SEO</p>
                        <p class="agente-desc">Genera contenuti, pianifica campagne e analizza le performance dei tuoi social. Ottimizza le strategie SEO e crea copy persuasivo automatizzato.</p>
                        <?php if ($loggato) { ?>
                            <a href="php/catalogo.php?id_categoria=1" class="agente-btn">Scopri di più</a>
                        <?php } else { ?>
                            <a href="php/login.php?cat=1" class="agente-btn">Scopri di più</a>
                        <?php } ?>
                    </div>
                </div>

                <div class="agente-card stagger-2">
                    <div class="agente-media">
                        <video autoplay muted loop playsinline>
                            <source src="img/agent-analytics.mp4" type="video/mp4">
                        </video>
                    </div>
                    <div class="agente-content">
                        <h3>AI Data Analyst</h3>
                        <p class="agente-role">Dashboard · Report · Insights</p>
                        <p class="agente-desc">Trasforma i tuoi dati in insight azionabili. Crea dashboard interattive, report automatizzati e previsioni basate su machine learning.</p>
                        <?php if ($loggato) { ?>
                            <a href="php/catalogo.php?id_categoria=2" class="agente-btn">Scopri di più</a>
                        <?php } else { ?>
                            <a href="php/login.php?cat=2" class="agente-btn">Scopri di più</a>
                        <?php } ?>
                    </div>
                </div>

                <div class="agente-card stagger-3">
                    <div class="agente-media">
                        <video autoplay muted loop playsinline>
                            <source src="img/agent-automation.mp4" type="video/mp4">
                        </video>
                    </div>
                    <div class="agente-content">
                        <h3>AI Automation Agent</h3>
                        <p class="agente-role">Workflow · Integrazioni · Trigger</p>
                        <p class="agente-desc">Automatizza i processi ripetitivi. Collega i tuoi strumenti preferiti e crea flussi di lavoro intelligenti senza scrivere codice.</p>
                        <?php if ($loggato) { ?>
                            <a href="php/catalogo.php?id_categoria=3" class="agente-btn">Scopri di più</a>
                        <?php } else { ?>
                            <a href="php/login.php?cat=3" class="agente-btn">Scopri di più</a>
                        <?php } ?>
                    </div>
                </div>

                <div class="agente-card stagger-4">
                    <div class="agente-media">
                        <video autoplay muted loop playsinline>
                            <source src="img/agent-support.mp4" type="video/mp4">
                        </video>
                    </div>
                    <div class="agente-content">
                        <h3>AI Customer Support</h3>
                        <p class="agente-role">Chatbot · Ticket · FAQ</p>
                        <p class="agente-desc">Assistente clienti 24/7 con risposte intelligenti. Gestisce ticket, FAQ e scalare all'operatore umano quando necessario.</p>
                        <?php if ($loggato) { ?>
                            <a href="php/catalogo.php?id_categoria=4" class="agente-btn">Scopri di più</a>
                        <?php } else { ?>
                            <a href="php/login.php?cat=4" class="agente-btn">Scopri di più</a>
                        <?php } ?>
                    </div>
                </div>

                <div class="agente-card stagger-5">
                    <div class="agente-media">
                        <video autoplay muted loop playsinline>
                            <source src="img/agent-dev.mp4" type="video/mp4">
                        </video>
                    </div>
                    <div class="agente-content">
                        <h3>AI Developer Agent</h3>
                        <p class="agente-role">Code · Review · Debug</p>
                        <p class="agente-desc">Assistente di programmazione AI. Genera codice, effettua code review, identifica bug e suggerisce ottimizzazioni in tempo reale.</p>
                        <?php if ($loggato) { ?>
                            <a href="php/catalogo.php?id_categoria=5" class="agente-btn">Scopri di più</a>
                        <?php } else { ?>
                            <a href="php/login.php?cat=5" class="agente-btn">Scopri di più</a>
                        <?php } ?>
                    </div>
                </div>

            </div>
        </section>

        <?php if (!$loggato) { ?>
            <section class="bottom-cta">
                <div class="bottom-cta-box">
                    <p>Hai già un account?</p>
                    <a href="php/login.php">Accedi al tuo store →</a>
                </div>
            </section>
        <?php } ?>

    </main>

    <footer>
        <div class="footer-inner">
            <span class="footer-brand">© 2026 AIAIAI STORE - ENRICO LANZAROLO 5Bi</span>
            <span class="footer-note">Abbonamenti digitali — rinnovo automatico mensile</span>
        </div>
    </footer>

</body>
</html>