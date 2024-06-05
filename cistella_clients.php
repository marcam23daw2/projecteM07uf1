<?php
session_start();

$productsFile = './LLIBRES/llibres';
$products = loadProducts($productsFile);

function loadProducts($filename) {
    if (!file_exists($filename)) {
        return [];
    }
    $products = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $productData = [];
    foreach ($products as $product) {
        $productInfo = explode(':', $product);
        $productData[$productInfo[0]] = $productInfo;
    }
    return $productData;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acceptar'])) {
        // guardar la comanda
        saveOrder($_SESSION['cistella'], $products);
        // borrar la cistella
        unset($_SESSION['cistella']);
        header('Location: comanda_clients.php');
        exit;
    } elseif (isset($_POST['modificar'])) {
        header('Location: cataleg_clients.php');
        exit;
    }
}

function saveOrder($basket, $products) {
    $orderDirectory = './COMANDES/';
    if (!file_exists($orderDirectory)) {
        mkdir($orderDirectory, 0777, true); 
    }
    $orderFile = $orderDirectory . $_SESSION['username'] . '_comanda';
    $orderData = [];
    foreach ($basket as $productId => $quantity) {
        $product = $products[$productId];
        $orderData[] = implode(':', array_merge($product, [$quantity]));
    }
    file_put_contents($orderFile, implode("\n", $orderData));
    // eliminar el fitxer de la cistella
    $basketFile = './CISTELLES/' . $_SESSION['username'] . '_cesta';
    if (file_exists($basketFile)) {
        unlink($basketFile);
    }
}

// calcular el resum de la cistella
$cistella = $_SESSION['cistella'] ?? [];
$resum = [];
$totalSenseIVA = 0;
$totalIVA = 0;

foreach ($cistella as $productId => $quantitat) {
    $product = $products[$productId];
    $preuSenseIVA = $product[2];
    $iva = $product[5];
    $preuAmbIVA = $preuSenseIVA * (1 + $iva / 100);
    $totalSenseIVA += $preuSenseIVA * $quantitat;
    $totalIVA += $preuSenseIVA * ($iva / 100) * $quantitat;
    $resum[] = [
        'nom' => $product[1],
        'quantitat' => $quantitat,
        'preuSenseIVA' => $preuSenseIVA,
        'valorIVA' => $preuSenseIVA * ($iva / 100),
        'preuAmbIVA' => $preuAmbIVA * $quantitat
    ];
}

$totalAmbIVA = $totalSenseIVA + $totalIVA;
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resum de la Cistella</title>
    <link rel="stylesheet" href="CSS/general.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
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
            padding: 20px 0;
            text-align: center;
        }

        header h2 {
            margin: 0;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            background-color: #00796b;
            color: #ffffff;
            padding: 10px;
            border-radius: 50%;
            width: 120px;
            text-align: center;
            box-sizing: border-box;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
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

        button.delete {
            background-color: #d32f2f;
        }

        button.delete:hover {
            background-color: #b71c1c;
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        .success {
            color: green;
            margin-top: 10px;
        }

        .back-btn {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #00796b;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #004d40;
        }

        .carta {
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .carta p {
            margin: 5px 0;
        }

        .carta p strong {
            color: #00796b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th,
        table td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        table th {
            background-color: #00796b;
            color: #fff;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <header>
        <h2>Resum de la Cistella</h2>
    </header>
    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Quantitat</th>
                    <th>Preu Sense IVA</th>
                    <th>Valor IVA</th>
                    <th>Preu Amb IVA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resum as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['nom']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantitat']); ?></td>
                        <td><?php echo number_format($item['preuSenseIVA'], 2); ?>€</td>
                        <td><?php echo number_format($item['valorIVA'], 2); ?>€</td>
                        <td><?php echo number_format($item['preuAmbIVA'], 2); ?>€</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><strong>Total Sense IVA:</strong> <?php echo number_format($totalSenseIVA, 2); ?> €</p>
        <p><strong>Total IVA:</strong> <?php echo number_format($totalIVA, 2); ?> €</p>
        <p><strong>Total Amb IVA:</strong> <?php echo number_format($totalAmbIVA, 2); ?> €</p>
        <p><strong>Data i Hora:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>

        <form method="post" action="cistella_clients.php">
            <button type="submit" name="acceptar">Acceptar Compra</button>
            <button type="submit" name="modificar">Modificar Cistella</button>
        </form>
        <a href="inici.php" class="back-btn">Tornar</a>
    </div>
</body>
</html>
