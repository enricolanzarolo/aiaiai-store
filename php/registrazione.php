<?php
session_start();
include 'connessione.php';

$cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

if (isset($_SESSION['id_utente'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//protegge da sql injection
    $nome    = mysqli_real_escape_string($conn, trim($_POST['nome']));
    $cognome    = mysqli_real_escape_string($conn, trim($_POST['cognome']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $cat_post = isset($_POST['cat']) ? (int)$_POST['cat'] : 0;

    // Controllo email duplicata
    $check = mysqli_query($conn, "SELECT id_utente FROM utenti WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $errore = "Email già registrata.";
    } else {
        $sql = "INSERT INTO utenti (nome, cognome, email, password, data_registrazione, ruolo)
                VALUES ('$nome', '$cognome', '$email', '$password', NOW(), 'utente')";
        if (mysqli_query($conn, $sql)) {
            // Auto-login dopo registrazione
            $new_id = mysqli_insert_id($conn);
            $_SESSION['id_utente'] = $new_id;
            $_SESSION['ruolo']     = 'utente';
            mysqli_close($conn);
            if ($cat_post > 0) {
                header("Location: catalogo.php?id_categoria=$cat_post");
            } else {
                header("Location: ../index.php");
            }
            exit;
        } else {
            $errore = "Errore durante la registrazione.";
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
    <title>Registrazione - AIAIAI Store</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <div class="login-logo">
                <span class="logo-main">AIAIAI</span>
                <span class="logo-sub">STORE</span>
            </div>

            <h1>Crea il tuo account</h1>
            <p class="login-subtitle">Registrati per accedere allo store</p>

            <?php if (!empty($errore)) echo "<p class='errore-msg'>$errore</p>"; ?>

            <form class="login-form" action="" method="POST">
                <input type="hidden" name="cat" value="<?php echo $cat; ?>">

                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" placeholder="Mario" required>
                </div>

                <div class="form-group">
                    <label for="cognome">Cognome</label>
                    <input type="text" id="cognome" name="cognome" placeholder="Rossi" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="mario@esempio.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="login-btn">Registrati →</button>
            </form>

            <a href="login.php<?php echo $cat > 0 ? '?cat='.$cat : ''; ?>" class="back-link">Hai già un account? Accedi!</a>
            <a href="../index.php" class="back-link">← Torna allo store</a>
        </div>

        <p class="login-footer">© 2026 AIAIAI STORE - ENRICO LANZAROLO 5Bi</p>
    </div>
</body>
</html>
