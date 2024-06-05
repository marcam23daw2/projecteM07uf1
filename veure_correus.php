<?php
session_start();

if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "gestor") {
    header("Location: login.php");
    exit();
}

$correusFile = './correus';
$correus = file($correusFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Correus Rebuts</title>
    <link rel="stylesheet" href="CSS/general.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #004d40;
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        header h2 {
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }
        th {
            background-color: #004d40;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        td {
            vertical-align: top;
            border: 2px solid #ddd;
        }
        button,
    input[type="submit"] {
        background-color: #00796b;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    button:hover,
    input[type="submit"]:hover {
        background-color: #004d40;
    }
    </style>
</head>
<body>
    <header>
        <h2>Correus Rebuts</h2>
    </header>
    <div class="container">
        <h2>Llista de Correus Rebuts</h2>
        <table>
            <tr>
                <th>Usuari</th>
                <th>Assumpte</th>
                <th>Missatge</th>
            </tr>
            <?php foreach ($correus as $correu): ?>
                <?php list($usuari, $assumpte, $missatge) = explode(':', $correu); ?>
                <tr>
                    <td><?php echo htmlspecialchars($usuari); ?></td>
                    <td><?php echo htmlspecialchars($assumpte); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($missatge)); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <a href="inici.php" class="back-btn">Tornar</a>
</body>
</html>
