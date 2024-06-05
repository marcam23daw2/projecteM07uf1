<?php
session_start();

$usersFile = './USUARIS/users';

if(isset($_SESSION['username'])) {
    header("Location: inici.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $file = fopen($usersFile, "r");

    $username = $_POST['username'];
    $password = $_POST['password'];

    while(!feof($file)) {
        $line = fgets($file);
        $data = explode(":", $line);

        if(trim($data[0]) == $username && password_verify($password, trim($data[1]))) {
            $_SESSION['username'] = $username;
            $_SESSION['email'] = trim($data[3]); 
            $_SESSION['role'] = trim($data[2]); 
            fclose($file);
            header("Location: inici.php");
            exit;
        }
    }

    fclose($file);

    $error_message = "Nom d'usuari o contrasenya incorrectes. Si us plau, intenta-ho de nou.";
}
?>


<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inici de sessió</title>
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

        header h1 {
            margin: 0;
        }

        nav {
            background-color: #00796b;
            margin-top: -10px; 
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: center;
        }

        nav ul li {
            display: inline-block;
            margin-right: 10px;
        }

        nav ul li a {
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            display: inline-block;
        }

        nav ul li a:hover {
            background-color: #004d40;
        }

        main {
            background-color: #f9f9f9;
            padding: 40px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        main h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        main p {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
        }

        main ul {
            list-style-type: none;
            padding: 0;
            margin-top: 20px;
        }

        main li {
            color: #333;
            font-size: 18px;
            margin-bottom: 10px;
        }

        main li:before {
            content: '\2022';
            color: #00796b;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
        }

        footer {
            background-color: #004d40;
            color: #ffffff;
            text-align: center;
            padding: 10px 0;
            margin-top: 20px;
            position: fixed;
            width: 100%;
            bottom: 0;
        }

        footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>Inici de sessió</h1>
    </header>
    <nav>
        <div class="container">
            <ul>
                <li><a href="index.php">Tornar</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <main>
            <?php 
            if(isset($error_message)) {
                echo '<p style="color:red;">' . $error_message . '</p>';
            }
            ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <label for="username">Nom d'usuari:</label><br>
                <input type="text" id="username" name="username" required><br><br>
                <label for="password">Contrasenya:</label><br>
                <input type="password" id="password" name="password" required><br><br>
                <input type="submit" value="Iniciar sessió">
            </form>
        </main>
    </div>
    <footer>
        <p>&copy; 2024 - Tots els drets reservats</p>
    </footer>
</body>
</html>
