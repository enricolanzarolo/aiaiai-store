create database if not exists aiaiaistore;

use aiaiaistore;

create table categorie (
    id_categoria int auto_increment primary key,
    nome_categoria varchar(100) not null unique
);

create table utenti (
    id_utente int auto_increment primary key,
    nome varchar(100),
    cognome varchar(100),
    email varchar(150) unique,
    password varchar(255),
    ruolo enum('utente', 'admin') not null default 'utente',
    data_registrazione date
);

-- fascia_abbonamento rappresenta le tre fasce di prezzo: base, pro, enterprise
create table prodotti (
    id_prodotto int auto_increment primary key,
    nome_prodotto varchar(150) not null unique,
    descrizione varchar(500),
    prezzo decimal(10, 2) not null,
    fascia_abbonamento enum('base', 'pro', 'enterprise') not null,
    id_categoria int,
    foreign key (id_categoria) references categorie(id_categoria)
);

create table carte_salvate (
    id_carta int auto_increment primary key,
    id_utente int not null,
    intestatario varchar(100) not null,
    numero_carta varchar(20) not null,
    scadenza varchar(5) not null,
    circuito enum('visa', 'mastercard', 'amex') not null,
    predefinita tinyint(1) not null default 0,
    foreign key (id_utente) references utenti(id_utente)
);

-- data_rinnovo viene aggiornata ogni mese se stato_abbonamento = 'attivo'
create table ordini (
    id_ordine int auto_increment primary key,
    id_utente int,
    id_carta int,
    data_acquisto date not null,
    data_rinnovo date not null,
    stato_abbonamento enum('attivo', 'annullato') not null default 'attivo',
    totale_ordine decimal(10, 2),
    foreign key (id_utente) references utenti(id_utente),
    foreign key (id_carta) references carte_salvate(id_carta)
);

create table dettagliordini (
    id_dettaglio int auto_increment primary key,
    id_ordine int,
    id_prodotto int,
    prezzo_unitario decimal(10, 2) not null,
    foreign key (id_ordine) references ordini(id_ordine),
    foreign key (id_prodotto) references prodotti(id_prodotto)
);

use aiaiaistore;

-- categorie (5)
insert into categorie (nome_categoria) values
('Agenti di Marketing'),
('Agenti di Analisi Dati'),
('Agenti di Automazione'),
('Agenti di Assistenza Clienti'),
('Agenti di Sviluppo');

-- utenti (10)
-- password in chiaro: pass_enrico, pass_alberto, pass_giovanni, pass_sara, pass_davide,
--                     pass_giulia, pass_andrea, pass_chiara, pass_matteo, pass_elena
insert into utenti (nome, cognome, email, password, ruolo, data_registrazione) values
('Enrico',   'Lanzarolo',  'enrico.lanzarolo@aiaiaistore.it',   '$2y$10$ZuP050RkhILHnRiF7xtuv.7mdbjSAp9c/77I8mU2.9kdrK46D5Z/O', 'admin',  '2024-01-10'),
('Alberto',  'Cappellari', 'alberto.cappellari@aiaiaistore.it', '$2y$10$xi5wE34XNMLiAi2WcgOmWOjhIj6sMGI8iijW6gt7SkoDSQOjKhNXK', 'admin',  '2024-01-12'),
('Giovanni', 'Centin',     'giovanni.centin@gmail.com',         '$2y$10$7g.LBOdFGBrqwarHtnxFSO9.SsdiVCRhlV0D9a4hYHtZizMLStuEK', 'utente', '2024-02-05'),
('Sara',     'Salenti',    'sara.salenti@gmail.com',            '$2y$10$7u9zBSjtBG50TKN7Pe4Dvuox.L54PNARroabKRlcjKRM0PiHpOuj6', 'utente', '2024-02-18'),
('Davide',   'Bissoli',    'davide.bissoli@gmail.com',          '$2y$10$oRhXuadVcdODPdm3tKYYNu3NNYKiTuC/kaVkBCHVQERlmntJpfg.O', 'utente', '2024-03-01'),
('Giulia',   'Mansardi',   'giulia.mansardi@gmail.com',         '$2y$10$Hb9J.8YtgjPQE/UlSYiLi.HasL.0i113J6XkOY3bsnM527.gCv.US', 'utente', '2024-03-14'),
('Andrea',   'Giacomello', 'andrea.giacomello@gmail.com',       '$2y$10$msGuJqQWP.Z/FcEt3T7XsuauFsEYy4nUzNo1ECTfVLRxP6WgBEABO', 'utente', '2024-04-02'),
('Chiara',   'Schettino',  'chiara.schettino@gmail.com',        '$2y$10$hEAvm4VO7l8W2w82aBbQY.Pa4W3EZmQlTT1lfJJP35IU6SotQfaUG', 'utente', '2024-04-20'),
('Matteo',   'Damante',    'matteo.damante@gmail.com',          '$2y$10$BIDTYXwqLu6bsMocP8ysU..AXAN1oEgicAdiAxdQDkjLLO62Tgg0e', 'utente', '2024-05-08'),
('Elena',    'Justremo',   'elena.justremo@gmail.com',          '$2y$10$gTSW4ZIcPeBDBkmMAEd6cuwKFMs76LOiWp8SVn4qB.pe7LlWG6UXy', 'utente', '2024-05-22');

-- prodotti (15) - 3 per categoria, distribuiti sulle tre fasce
insert into prodotti (nome_prodotto, descrizione, prezzo, fascia_abbonamento, id_categoria) values

-- id_categoria = 1 
('AI Marketing Base',           'Agente AI per campagne email e post social di base',              19.99, 'base',       1),
('AI Marketing Pro',            'Agente AI per strategie multicanale e analisi audience',          59.99, 'pro',        1),
('AI Marketing Enterprise',     'Suite completa per grandi team marketing con reportistica AI',   199.99, 'enterprise', 1),

-- id_categoria = 2
('AI Analytics Base',           'Agente AI per dashboard e report semplici',                       24.99, 'base',       2),
('AI Analytics Pro',            'Agente AI per analisi predittiva e visualizzazioni avanzate',     89.99, 'pro',        2),
('AI Analytics Enterprise',     'Piattaforma enterprise di analisi dati in tempo reale con AI',  299.99, 'enterprise', 2),

-- id_categoria = 3
('AI Automazione Base',         'Agente AI per automazione di task ripetitivi di base',            14.99, 'base',       3),
('AI Automazione Pro',          'Agente AI per workflow complessi e integrazioni API',             49.99, 'pro',        3),
('AI Automazione Enterprise',   'Automazione enterprise multi-processo con monitoraggio AI',     249.99, 'enterprise', 3),

-- id_categoria = 4
('AI Chatbot Base',             'Chatbot AI per risposte automatiche alle FAQ',                     9.99, 'base',       4),
('AI Chatbot Pro',              'Agente AI per assistenza clienti multilingua e sentiment analysis',44.99, 'pro',       4),
('AI Chatbot Enterprise',       'Soluzione enterprise con AI conversazionale personalizzabile',  179.99, 'enterprise', 4),

-- id_categoria = 5
('AI Code Assistant Base',      'Agente AI per completamento e revisione del codice di base',      29.99, 'base',       5),
('AI Code Assistant Pro',       'Agente AI per code review avanzato, debug e refactoring',         99.99, 'pro',        5),
('AI Code Assistant Enterprise','Suite AI completa per team di sviluppo enterprise',             399.99, 'enterprise', 5);

-- carte salvate (una per ciascun utente con ordini)
insert into carte_salvate (id_utente, intestatario, numero_carta, scadenza, circuito, predefinita) values
(3, 'Giovanni Centin',   '****3842', '09/26', 'visa',       1),
(4, 'Sara Salenti',      '****7291', '11/25', 'mastercard', 1),
(5, 'Davide Bissoli',    '****5104', '03/27', 'visa',       1),
(6, 'Giulia Mansardi',   '****6637', '07/26', 'amex',       1),
(7, 'Andrea Giacomello', '****9083', '01/27', 'mastercard', 1);

-- ordini (5)
-- data_rinnovo = data_acquisto + 1 mese
insert into ordini (id_utente, id_carta, data_acquisto, data_rinnovo, stato_abbonamento, totale_ordine) values
(3, 1, '2026-04-24', '2026-06-24', 'attivo',    44.98),
(4, 2, '2026-04-10', '2026-06-10', 'attivo',    89.99),
(5, 3, '2026-03-15', '2026-06-15', 'attivo',   289.97),
(6, 4, '2025-11-18', '2025-12-18', 'annullato', 39.98),
(7, 5, '2026-01-01', '2026-02-01', 'annullato', 399.99);

-- dettagliordini
-- ordine 1 (giovanni centin): ai marketing base + ai analytics base
insert into dettagliordini (id_ordine, id_prodotto, prezzo_unitario) values
(1,  1,  19.99),
(1,  4,  24.99);

-- ordine 2 (sara salenti): ai analytics pro
insert into dettagliordini (id_ordine, id_prodotto, prezzo_unitario) values
(2,  5,  89.99);

-- ordine 3 (davide bissoli): ai automazione enterprise + ai chatbot base + ai code assistant base
insert into dettagliordini (id_ordine, id_prodotto, prezzo_unitario) values
(3,  9, 249.99),
(3, 10,   9.99),
(3, 13,  29.99);

-- ordine 4 (giulia mansardi): ai chatbot base + ai code assistant base
insert into dettagliordini (id_ordine, id_prodotto, prezzo_unitario) values
(4, 10,   9.99),
(4, 13,  29.99);

-- ordine 5 (andrea giacomello): ai code assistant enterprise
insert into dettagliordini (id_ordine, id_prodotto, prezzo_unitario) values
(5, 15, 399.99);