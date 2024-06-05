<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION["role"];
$username = $_SESSION["username"];
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interfície</title>
    <link rel="stylesheet" href="CSS/llibreria.css">
    <style>
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: #004d40;
            color: white;
        }

        .logout-btn {
            background-color: #00796b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #004d40;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background-color: #f2f2f2; 
        }

        th, td {
            border: 1px solid #ddd; 
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:hover {background-color: #ddd;}
    </style>
</head>
<body>
    <header>
        <h1> <?php echo strtoupper($username); ?>: <?php echo($role); ?></h1>
        <form action="logout.php" method="post">
            <input class="logout-btn" type="submit" value="Logout">
        </form>
    </header>

    <section>
        <?php
        if ($role === "admin") {
            echo '
            <table>
                <tr><td><a href="editar_administrador.php">Modificar el usuari Administrador</a></td></tr>
                <tr><td><a href="gestionar_gestors.php">Gestionar els Gestors</a></td></tr>
                <tr><td><a href="gestionar_clients.php">Gestio d&apos;usuaris Clients</a></td></tr>
            </table>';
        } elseif ($role === "gestor") {
            echo '
            <table>
                <tr><td><a href="cataleg_llibres.php">Gestionar Cataleg de Llibres</a></td></tr>
                <tr><td><a href="veure_correus.php">Veure Correus Rebuts</a></td></tr>
                <tr><td><a href="comandes.php">Veure Comandes per Eliminar</a></td></tr>
            </table>';
        } elseif ($role === "client") {
            echo '
            <table>
                <tr><td><a href="dades_client.php">Les meves dades</a></td></tr>
                <tr><td><a href="cataleg_clients.php">Catàleg de Compra</a></td></tr>
                <tr><td><a href="cistella_clients.php">La meva Cistella</a></td></tr>
                <tr><td><a href="comanda_clients.php">Les meves Comandes</a></td></tr>
                <tr><td><a href="correu.php">Correu al meu Gestor</a></td></tr>
            </table>';
        }
        ?>
    </section>

</body>
</html>

