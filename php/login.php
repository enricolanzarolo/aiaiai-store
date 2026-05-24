<?php
session_start();
include 'connessione.php';

//Leggo il parametro cat (categoria su cui ha cliccato "Scopri di più")
$cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

//Se l'utente è già loggato, lo reindirizzo subito
if (isset($_SESSION['id_utente'])) {
    if ($cat > 0) {
        header("Location: catalogo.php?id_categoria=$cat");
    } else {
        header("Location: ../index.php");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']); //evita sql injection
    $password = $_POST['password'];
    $cat_post = isset($_POST['cat']) ? (int)$_POST['cat'] : 0;

    $sql    = "SELECT * FROM utenti WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
            $_SESSION['id_utente'] = $row['id_utente'];
            $_SESSION['ruolo']     = $row['ruolo'];

            mysqli_close($conn);

            if ($row['ruolo'] == 'admin') {
                header("Location: ../index.php");
            } elseif ($cat_post > 0) {
                header("Location: catalogo.php?id_categoria=$cat_post");
            } else {
                header("Location: ../index.php");
            }
            exit;
        }
    }

    $errore = "Email o password non corretti.";
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AIAIAI Store</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <div class="login-logo">
                <span class="logo-main">AIAIAI</span>
                <span class="logo-sub">STORE</span>
            </div>

            <h1>Accedi al tuo account</h1>
            <?php if ($cat > 0): ?>
                <p class="login-subtitle">Accedi per vedere i piani della categoria selezionata</p>
            <?php else: ?>
                <p class="login-subtitle">Inserisci le tue credenziali per continuare</p>
            <?php endif; ?>

            <?php if (!empty($errore)) echo "<p class='errore-msg'>$errore</p>"; ?>

            <form class="login-form" action="" method="POST">
                <!-- Passo il cat come campo nascosto per mantenerlo nel POST -->
                <input type="hidden" name="cat" value="<?php echo $cat; ?>">

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="nome@esempio.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="login-btn">Accedi →</button>
            </form>

            <a href="registrazione.php<?php echo $cat > 0 ? '?cat='.$cat : ''; ?>" class="back-link">Non hai un account? Registrati!</a>
            <a href="../index.php" class="back-link">← Torna allo store</a>
        </div>

        <p class="login-footer">© 2026 AIAIAI STORE - ENRICO LANZAROLO 5Bi</p>
    </div>
</body>
</html>
