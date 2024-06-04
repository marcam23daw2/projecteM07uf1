<?php
session_start();

$usersFile = './USUARIS/users';

if(!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    $updated = false;

    $lines = file($usersFile);

    foreach ($lines as $key => &$line) {
        $data = explode(":", $line);

        if(trim($data[2]) === 'admin') {
            $line = $newUsername . ":" . password_hash($password, PASSWORD_DEFAULT) . ":" . $data[2] . ":" . $email . "\n";
            $updated = true;
            $_SESSION['username'] = $newUsername;
            break; 
        }
    }

    if ($updated) {
        file_put_contents($usersFile, implode('', $lines));

        header("Location: editar_administrador.php?success=1");
        exit;
    } else {
        $error_message = "No s'ha pogut trobar l'usuari administrador.";
    }
}
?>


<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuari Admin</title>
    <link rel="stylesheet" href="CSS/general.css">
</head>
<body>
    <header>
        <h2>Modificar Usuari Administrador</h2>
    </header>
    <div class="container">
        <?php if(isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <?php if(isset($_GET['success'])): ?>
            <p class="success">Les dades de l'usuari administrador s'han actualitzat amb èxit.</p>
        <?php endif; ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <label for="username">Nou Nom d'Usuari:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Nova Contrasenya:</label>
            <input type="password" id="password" name="password" required>
            <label for="email">Nou Correu Electrònic:</label>
            <input type="email" id="email" name="email" required>
            <input type="submit" value="Guardar Canvis">
        </form>
        <a href="inici.php" class="back-btn">Tornar Enrere</a>
    </div>
</body>
</html>
