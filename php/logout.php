<?php
session_start();
session_destroy();
header('Location: ../index.php');
exit;
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - AIAIAI Store</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <div class="login-logo">
                <span class="logo-main">AIAIAI</span>
                <span class="logo-sub">STORE</span>
            </div>

            <h1>Sei uscito</h1>
            <p class="login-subtitle">La tua sessione è stata terminata</p>

            <form method="post" action="">
                <input type="submit" name="logout" value="Logout" class="login-btn">
            </form>

            <a href="../index.php" class="back-link">← Torna allo store</a>
        </div>

        <p class="login-footer">© 2026 AIAIAI STORE - ENRICO LANZAROLO 5Bi</p>
    </div>
</body>
</html>