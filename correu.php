<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$correusFile = './correus'; 
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assumpte = "Petició de modificació/esborrament del compte de client"; // Assumpte 
    $descripcio = $_POST["descripcio"];
    $username = $_SESSION['username'];

    $correu = "$username:$assumpte:$descripcio\n";

    file_put_contents($correusFile, $correu, FILE_APPEND);

    $message = "El correu ha estat enviat correctament.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contacta amb el suport</title>
    <link rel="stylesheet" href="CSS/general.css">
    <style>
    .container {
        width: 80%;
        margin: 20px auto;
        padding: 20px;
        background-color: white;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    form {
        display: flex;
        flex-direction: column;
    }

    label {
        font-weight: bold;
        margin-bottom: 5px;
    }

    input[type="text"],
    textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    input[type="submit"] {
        background-color: #00796b;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background-color: #004d40;
    }

    .back-btn {
        display: inline-block;
        margin-top: 20px;
        color: white;
        text-decoration: none;
        font-weight: bold;
    }

    #assumpte {
        background-color: #f9f9f9;
        border: 1px solid #ccc;
        padding: 10px;
        border-radius: 4px;
    }

    .message {
        background-color: #dff0d8;
        color: #3c763d;
        padding: 10px;
        border: 1px solid #d6e9c6;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    </style>
</head>
<body>
    <header>
        <h2>Enviar correu al suport</h2>
    </header>
    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <label for="assumpte">Assumpte:</label>
            <p id="assumpte">Petició de modificació/esborrament del compte de client</p><br><br>
            <label for="descripcio">Descripció:</label>
            <textarea id="descripcio" name="descripcio" required></textarea><br><br>
            <input type="submit" value="Enviar">
        </form>
        <a class="back-btn" href="inici.php">Tornar</a>
    </div>
</body>
</html>
