# AIAIAI Store

Piattaforma e-commerce per la vendita di abbonamenti mensili ad agenti di Intelligenza Artificiale.

Progetto di fine anno, ITIS Informatica, a.s. 2025/2026. Il primo progetto in cui ho lavorato con una logica lato server, dopo quelli solo in HTML, CSS e JavaScript.

**Tecnologie:** PHP (procedurale), MySQL, HTML, CSS

---

## L'idea

La traccia chiedeva un negozio online classico. Ho scelto di vendere **prodotti digitali in abbonamento** e ho adattato ogni requisito a questa logica:

- **nessun magazzino:** un agente AI ha disponibilità illimitata, quindi niente quantità disponibili
- **nessuna spedizione:** al posto degli stati "in elaborazione / spedito / consegnato" c'è uno stato dell'abbonamento, `attivo` o `annullato`
- **rinnovo mensile:** ogni ordine ha una data di acquisto e una di rinnovo, e un abbonamento oltre la data di rinnovo viene mostrato come scaduto
- **tre fasce per categoria:** Base, Pro ed Enterprise, con logica di upgrade

## Funzionalità

**Utente**
- registrazione e login
- catalogo con 5 categorie (Marketing, Analisi Dati, Automazione, Assistenza Clienti, Sviluppo) e 15 piani
- pagina di dettaglio per ogni agente
- carrello gestito in sessione
- checkout con carte di pagamento salvate o nuove
- storico abbonamenti, con annullamento

**Amministratore**
- pannello riservato agli utenti con ruolo `admin`, con statistiche e ultimi iscritti

**Interfaccia**
- design responsive per smartphone, dark mode, video animati nella home

## Database

Sei tabelle relazionali:

| Tabella | Contenuto |
| --- | --- |
| `categorie` | le 5 macro-categorie di agenti |
| `prodotti` | i 15 piani, con prezzo e fascia (`base`, `pro`, `enterprise`) |
| `utenti` | clienti e amministratori, con password salvate come hash bcrypt |
| `carte_salvate` | metodi di pagamento riutilizzabili |
| `ordini` | abbonamenti, con stato, data di acquisto e data di rinnovo |
| `dettagliordini` | i prodotti contenuti in ogni ordine |

La progettazione completa, con tutti i campi e i vincoli spiegati, è nella [relazione tecnica](AIAIAI%20Store%20-%20Enrico%20Lanzarolo.pdf).

## Struttura del progetto

```
aiaiai-store/
├── index.php              home
├── aiaiai_store.sql       creazione e popolamento del database
├── php/
│   ├── connessione.php    connessione al database
│   ├── registrazione.php
│   ├── login.php / logout.php
│   ├── catalogo.php       prodotti per categoria
│   ├── prodotto.php       dettaglio del singolo agente
│   ├── carrello.php
│   ├── conferma_ordine.php
│   ├── storico_ordini.php
│   ├── annulla_abbonamento.php
│   └── index_admin.php    pannello amministratore
├── css/                   un foglio di stile per pagina
└── img/                   video di sfondo della home
```

## Come avviarlo in locale

1. Installa [XAMPP](https://www.apachefriends.org/) e avvia **Apache** e **MySQL**.
2. Copia la cartella del progetto in `htdocs`.
3. Apri phpMyAdmin (`http://localhost/phpmyadmin`) e importa `aiaiai_store.sql`.
4. Vai su `http://localhost/aiaiai-store`.

Gli account di prova sono nel file SQL: le password sono salvate come hash, e quelle in chiaro sono nei commenti del file.

## Note sullo sviluppo

L'idea, la progettazione della piattaforma e del database, i flussi utente e admin sono miei. Il codice PHP è stato sviluppato con il supporto dell'AI e l'ho rivisto; HTML, CSS e la logica di base li conosco.

Tutti i dati nel database, comprese le carte di pagamento, sono **fittizi**. Le carte sono salvate in chiaro solo perché si tratta di un progetto didattico: in un negozio reale i dati di pagamento non vanno mai salvati così, ma gestiti da un fornitore di pagamenti.

## Prossimi passi

Questo progetto sarà il bersaglio del mio primo laboratorio di sicurezza: un audit del codice con Burp Suite (SQL injection, XSS, CSRF, gestione delle sessioni, controllo degli accessi), seguito dalla correzione delle vulnerabilità trovate. I risultati verranno documentati in questo repository.
