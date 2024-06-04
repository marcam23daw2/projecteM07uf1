<?php
// Funció per generar el hash bcrypt per a una contrasenya específica
function generateBcryptHash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Dades de l'usuari a afegir
$username = "admin";
// Genera el hash bcrypt per a la contrasenya "fjeclot"
$password_hash = generateBcryptHash("fjeclot");
$email = "admin@fjeclot.net";

// Ruta al fitxer d'usuaris
$usersFile = './USUARIS/users';

// Crea una línia amb les dades de l'usuari
$userData = $username . ":" . $password_hash . ":" . $username . ":" . $email . "\n";

// Afegeix les dades de l'usuari al fitxer d'usuaris
$file = fopen($usersFile, "a");
fwrite($file, $userData);
fclose($file);

echo "Les credencials de l'usuari s'han afegit correctament al fitxer d'usuaris.";
?>

