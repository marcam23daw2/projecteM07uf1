<?php
session_start();

if (!isset($_SESSION["username"]) || $_SESSION["role"] !== "gestor") {
    header("Location: login.php");
    exit();
}

$comandesDir = './COMANDES/';
$deleteRequestsFile = './delete_requests.txt';

if (!is_readable($comandesDir)) {
    die("No es pot llegir el directori de comandes.");
}

$comandesFiles = array_diff(scandir($comandesDir), array('..', '.'));
$deleteRequests = file_exists($deleteRequestsFile) ? file($deleteRequestsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
$comandes = [];

foreach ($comandesFiles as $file) {
    $username = str_replace('_comanda', '', $file);
    if (in_array($username, $deleteRequests)) {
        $orderFilePath = $comandesDir . $file;
        if (!is_readable($orderFilePath)) {
            error_log("Error: No es pot llegir el fitxer $orderFilePath");
            continue;
        }
        $comandaData = file($orderFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($comandaData === false) {
            error_log("Error: No es pot llegir el fitxer $orderFilePath");
            continue;
        }
        $orderItems = [];
        foreach ($comandaData as $orderLine) {
            $orderItems[] = explode(':', $orderLine);
        }
        $comandes[$username] = $orderItems;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    $username = $_POST['username'];
    $orderFile = $comandesDir . $username . '_comanda';
    if (file_exists($orderFile)) {
        unlink($orderFile);
    }
    $deleteRequests = array_diff($deleteRequests, [$username]);
    file_put_contents($deleteRequestsFile, implode("\n", $deleteRequests));
    header('Location: comandes.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comandes Rebudes</title>
    <link rel="stylesheet" href="CSS/general.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            max-width: 1200px;
        }
        header {
            background-color: #004d40;
            color: #ffffff;
            padding: 20px;
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
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #004d40;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .delete-btn {
            background-color: #d9534f;
            color: white;
            border: none;
            padding: 10px 15px;
            text-align: center;
            cursor: pointer;
        }
        .delete-btn:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>
        <header>
            <h2>Comandes Rebudes</h2>
        </header>
        <div class="container">
        <?php if (empty($comandes)): ?>
            <p>No hi ha comandes pendents de revisió.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Usuari</th>
                        <th>Productes</th>
                        <th>Quantitat Total</th>
                        <th>Preu Total Sense IVA</th>
                        <th>Valor IVA Total</th>
                        <th>Preu Total Amb IVA</th>
                        <th>Acció</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comandes as $username => $orderItems): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($username); ?></td>
                            <td>
                                <?php foreach ($orderItems as $item): ?>
                                    <?php echo htmlspecialchars($item[1]) . " (" . htmlspecialchars($item[3]) . "), "; ?>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php
                                $totalQuantitat = 0;
                                foreach ($orderItems as $item) {
                                    $totalQuantitat += $item[3];
                                }
                                echo $totalQuantitat;
                                ?>
                            </td>
                            <td>
                                <?php
                                $totalPreuSenseIVA = 0;
                                foreach ($orderItems as $item) {
                                    $totalPreuSenseIVA += $item[2] * $item[3];
                                }
                                echo number_format($totalPreuSenseIVA, 2) . "€";
                                ?>
                            </td>
                            <td>
                                <?php
                                $totalIVA = 0;
                                foreach ($orderItems as $item) {
                                    $totalIVA += $item[2] * ($item[5] / 100) * $item[3];
                                }
                                echo number_format($totalIVA, 2) . "€";
                                ?>
                            </td>
                            <td>
                                <?php echo number_format($totalPreuSenseIVA + $totalIVA, 2); ?>€
                            </td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                                    <button type="submit" name="delete_order" class="delete-btn">Eliminar Comanda</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <a href="inici.php" class="back-btn">Tornar</a>

</body>
</html>
