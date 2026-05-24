<?php

    $host = 'localhost';
    $username = 'root';
    $password = '';
    $dbase = 'aiaiaistore';

    $conn = mysqli_connect($host, $username, $password, $dbase);

    if ($conn) {
       
    }
    else {
        die ("Connessione fallita" . mysqli_connect_error());
    }

?>
