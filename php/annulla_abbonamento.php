<?php
session_start();
include 'connessione.php';

//controllo se utente e loggato
if (!isset($_SESSION['id_utente'])) {
    header('Location: login.php');
    exit;
}

//controllo che la richiesta sia inviata via post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: storico_ordini.php');
    exit;
}

$id_utente = $_SESSION['id_utente'];

//inizializzo id ordine a zero
$id_ordine = 0;

//recupero id ordine in modo umanizzato senza ternari meccanici
if (isset($_POST['id_ordine'])) {
    $id_ordine = (int)$_POST['id_ordine'];
}

//se id ordine non e valido torno allo storico
if ($id_ordine == 0) {
    header('Location: storico_ordini.php');
    exit;
}

//controllo che ordine esista sia di questo utente e sia attivo
$sql_check = "SELECT id_ordine, stato_abbonamento, data_rinnovo
              FROM ordini
              WHERE id_ordine = '$id_ordine'
              AND id_utente = '$id_utente'
              AND stato_abbonamento = 'attivo'
              AND data_rinnovo >= CURDATE()";
$result = mysqli_query($conn, $sql_check);

//se non trovo riscontro chiudo e torno indietro
if (mysqli_num_rows($result) == 0) {
    mysqli_close($conn);
    header('Location: storico_ordini.php');
    exit;
}

//eseguo aggiornamento per disattivare abbonamento
$sql_annulla = "UPDATE ordini SET stato_abbonamento = 'annullato' WHERE id_ordine = '$id_ordine' AND id_utente = '$id_utente'";
mysqli_query($conn, $sql_annulla);

//chiusura formale della connessione al database
mysqli_close($conn);

//reindirizzo con parametro di successo
header('Location: storico_ordini.php?annullato=1');
exit;
?>