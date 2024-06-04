<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$usersFile = './USUARIS/users';
$dadesClients = loadClients($usersFile);

function loadClients($filename) {
    if (!file_exists($filename)) {
        return [];
    }
    return file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

$authenticatedClient = null;
foreach ($dadesClients as $clientData) {
    $clientInfo = explode(':', $clientData);
    if ($clientInfo[0] === $_SESSION['username']) {
        $authenticatedClient = $clientInfo;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dades clients</title>
    <link rel="stylesheet" href="CSS/general.css">
</head>
<body>
    <header>
        <h2>Les meves Dades</h2>
    </header>
    <div class="container">
        <h2>Dades</h2>
        <?php if ($authenticatedClient !== null): ?>
            <div class="carta">
                <p><strong>ID:</strong> <?php echo $authenticatedClient[4] ?? ''; ?></p>
                <p><strong>Usuari:</strong> <?php echo $authenticatedClient[0] ?? ''; ?></p>
                <p><strong>Nom complet:</strong> <?php echo $authenticatedClient[5] ?? ''; ?> <?php echo $authenticatedClient[6] ?? ''; ?></p>
                <p><strong>Correu:</strong> <?php echo $authenticatedClient[3] ?? ''; ?></p>
                <p><strong>Telefon:</strong> <?php echo $authenticatedClient[7] ?? ''; ?></p>
                <p><strong>Adreça:</strong> <?php echo $authenticatedClient[8] ?? ''; ?></p>
                <p><strong>Tarjeta VISA:</strong> <?php echo $authenticatedClient[9] ?? ''; ?></p>
                <p><strong>Gestor:</strong> <?php echo $authenticatedClient[10] ?? ''; ?></p>
            </div>
        <?php else: ?>
            <p>No s'han trobat dades per al client autenticat.</p>
        <?php endif; ?>
        <a href="inici.php" class="back-btn">Tornar</a>
    </div>
</body>
</html>
