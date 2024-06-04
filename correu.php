<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$correusFile = './correus.txt'; // Archivo donde se guardarán los correos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assumpte = $_POST["assumpte"];
    $descripcio = $_POST["descripcio"];
    $username = $_SESSION['username'];

    // Formato del correo: usuari:assumpte:descripcio
    $correu = "$username:$assumpte:$descripcio\n";

    // Guardar el correo en el archivo
    file_put_contents($correusFile, $correu, FILE_APPEND);

    echo "El correu ha estat enviat correctament.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contacta amb el suport</title>
    <link rel="stylesheet" href="CSS/general.css">
</head>
<body>
        <header>
            <h2>Enviar correu al suport</h2>
        </header>
        <div class="container">
        <form method="post" action="">
            <label for="assumpte">Assumpte:</label>
            <input type="text" id="assumpte" name="assumpte" required><br><br>
            <label for="descripcio">Descripció:</label>
            <textarea id="descripcio" name="descripcio" required></textarea><br><br>
            <input type="submit" value="Enviar">
        </form>
        <a class="back-btn" href="inici.php">Tornar</a>
    </div>
</body>
</html>
