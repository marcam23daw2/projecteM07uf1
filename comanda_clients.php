<?php
session_start();

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

function loadOrder($username) {
    $orderFile = './COMANDES/' . $username . '_comanda';
    if (!file_exists($orderFile)) {
        return [];
    }
    $orderData = file($orderFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $orderItems = [];
    foreach ($orderData as $orderLine) {
        $orderItems[] = explode(':', $orderLine);
    }
    return $orderItems;
}

function createOrder($username, $cistella, $products) {
    $orderFile = './COMANDES/' . $username . '_comanda';
    $orderData = [];
    foreach ($cistella as $productId => $quantitat) {
        $product = $products[$productId];
        $orderData[] = implode(':', array_merge($product, [$quantitat]));
    }
    file_put_contents($orderFile, implode("\n", $orderData));
}

function requestOrderDeletion($username) {
    $deleteRequestsFile = './delete_requests.txt';
    file_put_contents($deleteRequestsFile, $username . "\n", FILE_APPEND);
}

$productsFile = './LLIBRES/llibres';
$products = loadProducts($productsFile);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_order'])) {
        createOrder($_SESSION['username'], $_SESSION['cistella'], $products);
        unset($_SESSION['cistella']);
        header('Location: cataleg_clients.php');
        exit;
    } elseif (isset($_POST['delete_order'])) {
        requestOrderDeletion($_SESSION['username']);
        $message = 'Petició de eliminació de comanda enviada correctament.';
    }
}

$order = loadOrder($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Les meves Comandes</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #ffffff;
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

        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        button,
        .back-btn {
            background-color: #00796b;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        button:hover,
        .back-btn:hover {
            background-color: #004d40;
        }

        button.delete {
            background-color: #d32f2f;
        }

        button.delete:hover {
            background-color: #b71c1c;
        }

        .no-orders {
            background-color: #ffcc80;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }

        .message {
            margin: 20px 0;
            padding: 15px;
            background-color: #e0f7fa;
            border: 1px solid #00796b;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <h2>La meva Comanda</h2>
    </header>
    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if (empty($order)): ?>
            <p class="no-orders">No tens cap comanda.</p>
        <?php else: ?>
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
                    <?php foreach ($order as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item[1]); ?></td>
                            <td><?php echo htmlspecialchars($item[3]); ?></td>
                            <td><?php echo number_format($item[2], 2); ?>€</td>
                            <td><?php echo number_format($item[2] * ($item[5] / 100), 2); ?>€</td>
                            <td><?php echo number_format($item[2] * (1 + $item[5] / 100), 2); ?>€</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="actions">
                <form method="post" style="display: inline;">
                    <button type="submit" name="delete_order" class="delete">Solicitar Esborrar Comanda</button>
                </form>
                <form method="post" style="display: inline;">
                    <button type="submit" name="create_order">Crear Comanda</button>
                </form>
            </div>
        <?php endif; ?>
        <a href="inici.php" class="back-btn">Tornar</a>
    </div>
</body>
</html>
