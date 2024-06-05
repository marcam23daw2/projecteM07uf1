<?php
session_start();

// carregar els productes
$productsFile = './LLIBRES/llibres';
$products = loadProducts($productsFile);

function loadProducts($filename) {
    if (!file_exists($filename)) {
        return [];
    }
    $products = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    usort($products, function($a, $b) {
        $aId = explode(':', $a)[0];
        $bId = explode(':', $b)[0];
        return $aId - $bId;
    });
    return $products;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['cistella'] = [];
    foreach ($_POST['quantitats'] as $productId => $quantitat) {
        if ($quantitat > 0) {
            $_SESSION['cistella'][$productId] = $quantitat;
        }
    }
    header('Location: cistella_clients.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catàleg de Compra</title>
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
        <h2>Catàleg de Productes</h2>
    </header>
    <div class="container">
        <form method="post" action="cataleg_clients.php">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Preu</th>
                        <th>Disponible</th>
                        <th>Quantitat</th>
                        <th>IVA (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $productData): ?>
                        <?php list($id, $nom, $preu, $quantitat, $disponibilitat, $iva) = explode(':', $productData); ?>
                        <tr>
                            <td><?php echo htmlspecialchars($id); ?></td>
                            <td><?php echo htmlspecialchars($nom); ?></td>
                            <td><?php echo number_format($preu, 2); ?>€</td>
                            <td><?php echo $disponibilitat === 'si' ? 'Sí' : 'No'; ?></td>
                            <td>
                                <?php if ($disponibilitat === 'si'): ?>
                                    <input type="number" name="quantitats[<?php echo htmlspecialchars($id); ?>]" min="0" max="<?php echo htmlspecialchars($quantitat); ?>" value="0">
                                <?php else: ?>
                                    No disponible
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($iva); ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit">Finalitzar selecció</button>
        </form>
        <a href="inici.php" class="back-btn">Tornar</a>
    </div>
</body>
</html>
