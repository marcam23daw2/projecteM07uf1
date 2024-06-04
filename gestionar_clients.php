<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once(__DIR__ . '/tcpdf/tcpdf.php');

$usersFile = './USUARIS/users';
$dadesUsuaris = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$dadesClients = array_filter($dadesUsuaris, function($dadesUsuari) {
    return explode(':', $dadesUsuari)[2] === "client";
});

$dadesGestors = array_filter($dadesUsuaris, function($dadesUsuari) {
    return explode(':', $dadesUsuari)[2] === "gestor";
});

$selectedClient = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["select_client"])) {
        $selectedClient = $_POST["client_seleccionat"];
        list($usernameEditat, $contrasenyaEditada, $rolEditat, $emailEditat, $idEditat, $nomEditat, $cognomEditat, $telefonEditat, $adreçaEditat, $tarjetaEditada, $gestorAssignatEditat) = explode(':', obtenirDadesUsuari($selectedClient, $dadesUsuaris));
    } elseif (isset($_POST["add_client"])) {
        $nouNomUsuari = $_POST["nou_client"];
        $novaContrasenya = $_POST["nova_contra"];
        $nouClientData = $nouNomUsuari . ":" . password_hash($novaContrasenya, PASSWORD_DEFAULT) . ":client:" . $_POST["nou_correu"] . ":" . $_POST["nou_id"] . ":" . $_POST["nou_nom"] . ":" . $_POST["nou_cognom"] . ":" . $_POST["nou_telefon"] . ":" . $_POST["nova_adreça"] . ":" . $_POST["nova_tarjeta"] . ":" . $_POST["nou_gestor_assignat"];
        $dadesUsuaris[] = $nouClientData;
        file_put_contents($usersFile, implode("\n", $dadesUsuaris));
        $carpetaComandes = "./comandes/" . $nouNomUsuari;
        $carpetaCistelles = "./cistelles/" . $nouNomUsuari;
        if (!file_exists($carpetaComandes) || !file_exists($carpetaCistelles)) {
            mkdir($carpetaComandes);
            mkdir($carpetaCistelles);
        }
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit();
    } elseif (isset($_POST["edit_client"]) && $_POST["metode"] == "PUT") {
        $usernameEditat = $_POST["editar_usuari"];
        $contrasenyaEditada = password_hash($_POST["editar_contra"], PASSWORD_DEFAULT);
        $emailEditat = $_POST["editar_gmail"];
        $idEditat = $_POST["editar_id"];
        $nomEditat = $_POST["editar_nom"];
        $cognomEditat = $_POST["editar_cognom"];
        $telefonEditat = $_POST["editar_telefon"];
        $adreçaEditat = $_POST["editar_adreça"];
        $tarjetaEditada = $_POST["editar_tarjeta"];
        $gestorAssignatEditat = $_POST["editar_gestorclient"];

        foreach ($dadesUsuaris as $clau => $dadesUsuari) {
            $nomUsuariEmmagatzemat = explode(':', $dadesUsuari)[0];
            if ($nomUsuariEmmagatzemat === $usernameEditat) {
                $dadesUsuaris[$clau] = "$usernameEditat:$contrasenyaEditada:client:$emailEditat:$idEditat:$nomEditat:$cognomEditat:$telefonEditat:$adreçaEditat:$tarjetaEditada:$gestorAssignatEditat";
                break;
            }
        }
        file_put_contents($usersFile, implode("\n", $dadesUsuaris));

        header("Location: " . $_SERVER["PHP_SELF"]);
        exit();
    } elseif (isset($_POST["delete_client"]) && $_POST["metode"] == "DELETE") {
        $clientEliminat = $_POST["eliminat_client_nom_usuari"];
        $dadesClients = array_filter($dadesClients, function ($dadesClient) use ($clientEliminat) {
            return explode(':', $dadesClient)[0] !== $clientEliminat;
        });
        $dadesUsuaris = array_filter($dadesUsuaris, function ($dadesUsuari) use ($clientEliminat) {
            return explode(':', $dadesUsuari)[0] !== $clientEliminat || explode(':', $dadesUsuari)[2] !== "client";
        });
        file_put_contents($usersFile, implode("\n", $dadesUsuaris));
    
        // Eliminar les carpetes relacionades amb el client
        $carpetaComandes = "./comandes/$clientEliminat";
        $carpetaCistelles = "./cistelles/$clientEliminat";
    
        if (is_dir($carpetaComandes)) {
            $files = glob($carpetaComandes . '/*');
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($carpetaComandes);
        }
    
        if (is_dir($carpetaCistelles)) {
            $files = glob($carpetaCistelles . '/*');
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($carpetaCistelles);
        }
    
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit();
    }
    
}

function obtenirDadesUsuari($nomUsuari, $dadesUsuaris)
{
    foreach ($dadesUsuaris as $dadesUsuari) {
        $nomUsuariEmmagatzemat = explode(':', $dadesUsuari)[0];
        if ($nomUsuariEmmagatzemat === $nomUsuari) {
            return $dadesUsuari;
        }
    }
    return null;
}

if (isset($_POST['download_clients_pdf'])) {
    generateClientsPDF($dadesClients);
}

function generateClientsPDF($dadesClients) {
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    
    $html = '<style>
                h1 { text-align: center; color: #4CAF50; }
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #ddd; padding: 8px; }
                th { background-color: #4CAF50; color: white; }
                tr:nth-child(even) { background-color: #f2f2f2; }
                tr:hover { background-color: #ddd; }
             </style>';
    $html .= '<h1>Informació dels Clients</h1>';
    $html .= '<table>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Telèfon</th>
                    <th>Adreça</th>
                    <th>Targeta</th>
                    <th>Gestor Assignat</th>
                </tr>';
    foreach ($dadesClients as $client) {
        list($username, , , $email, , $nom, $cognom, $telefon, $adreça, $tarjeta, $gestorAssignat) = explode(':', $client);
        $html .= "<tr>
                    <td>$nom $cognom</td>
                    <td>$email</td>
                    <td>$telefon</td>
                    <td>$adreça</td>
                    <td>$tarjeta</td>
                    <td>$gestorAssignat</td>
                  </tr>";
    }
    $html .= '</table>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('clients.pdf', 'D');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestors</title>
    <link rel="stylesheet" href="CSS/general.css">
</head>
<body>
    <header>
        <h2>Gestio de clients</h2>
    </header>
    <div class="container">

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <h2>Crear usuari</h2>
            <label for="nou_id">ID:</label>
            <input type="text" id="nou_id" name="nou_id" required><br>
            <label for="nou_client">Nom d'Usuari:</label>
            <input type="text" id="nou_client" name="nou_client" required><br>
            <label for="nova_contra">Contrasenya:</label>
            <input type="password" id="nova_contra" name="nova_contra" required><br>
            <label for="nou_correu">Correu Electrònic:</label>
            <input type="email" id="nou_correu" name="nou_correu" required><br>
            <label for="nou_nom">Nom:</label>
            <input type="text" id="nou_nom" name="nou_nom" required><br>
            <label for="nou_cognom">Cognom:</label>
            <input type="text" id="nou_cognom" name="nou_cognom" required><br>            
            <label for="nou_telefon">Telèfon:</label>
            <input type="text" id="nou_telefon" name="nou_telefon" required><br>
            <label for="nova_adreça">Adreça:</label>
            <input type="text" id="nova_adreça" name="nova_adreça" required><br>
            <label for="nova_tarjeta">Tarjeta:</label>
            <input type="text" id="nova_tarjeta" name="nova_tarjeta" required><br>
            <label for="nou_gestor_assignat">Gestor Assignat:</label>
            <select id="nou_gestor_assignat" name="nou_gestor_assignat" required><br>
                <option value="" selected>Selecciona un gestor</option>
                <?php foreach ($dadesGestors as $gestorData): ?>
                    <?php $username = explode(':', $gestorData)[0]; ?>
                    <?php $selected = ($username === $selectedGestor) ? 'selected' : ''; ?>
                    <option value="<?php echo $username; ?>" <?php echo $selected; ?>><?php echo $username; ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" name="add_client">Afegir</button>
        </form>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <h2>Modificar un Client</h2>
            <label for="client_seleccionat">Client:</label>
            <select id="client_seleccionat" name="client_seleccionat" onchange="this.form.submit()">
                <option value="" selected>Selecciona un client</option>
                <?php foreach ($dadesClients as $clientData): ?>
                    <?php $username = explode(':', $clientData)[0]; ?>
                    <option value="<?php echo $username; ?>" <?php echo ($username === $selectedClient) ? 'selected' : ''; ?>><?php echo $username; ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="select_client" value="1">
        </form>

        <?php if ($selectedClient !== null): ?>
            <h2>Editar Client: <?php echo $selectedClient; ?></h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label for="editar_id">Nou ID:</label>
                <input type="text" id="editar_id" name="editar_id" required><br>
                <label for="editar_usuari">Nou nom d'Usuari:</label>
                <input type="text" name="editar_usuari" value="<?php echo $selectedClient; ?>"><br>
                <label for="editar_contra">Nova Contrasenya:</label>
                <input type="password" id="editar_contra" name="editar_contra" required><br>
                <label for="editar_gmail">Nou Correu Electrònic:</label>
                <input type="email" id="editar_gmail" name="editar_gmail" required><br>
                <label for="editar_nom">Nou Nom:</label>
                <input type="text" id="editar_nom" name="editar_nom" required><br>
                <label for="editar_cognom">Nou Cognom:</label>
                <input type="text" id="editar_cognom" name="editar_cognom" required><br>
                <label for="editar_telefon">Nou Telèfon:</label>
                <input type="text" id="editar_telefon" name="editar_telefon" required><br>       
                <label for="editar_adreça">Nova Adreça:</label>
                <input type="number" id="editar_adreça" name="editar_adreça" required><br>
                <label for="editar_tarjeta">Nova tarjeta:</label>
                <input type="text" id="editar_tarjeta" name="editar_tarjeta" required><br>
                <label for="editar_gestorclient">Nou Gestor:</label>
                <select id="editar_gestorclient" name="editar_gestorclient" required><br>
                    <option value="" selected>Selecciona un gestor</option>
                    <?php foreach ($dadesGestors as $gestorData): ?>
                        <?php $username = explode(':', $gestorData)[0]; ?>
                        <?php $selected = ($username === $selectedGestor) ? 'selected' : ''; ?>
                        <option value="<?php echo $username; ?>" <?php echo $selected; ?>><?php echo $username; ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="edit_client">Modificar</button>
                <input type="hidden" name="metode" value="PUT" />
            </form>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <h2>Eliminar Client</h2>
        <label for="esborrar_client">Usuari:</label>
        <select id="esborrar_client" name="esborrar_client">
            <?php foreach ($dadesClients as $clientData): ?>
                <?php list($username) = explode(':', $clientData); ?>
                <option value="<?php echo $username; ?>"><?php echo $username; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="delete_client" class="delete">Esborrar</button>
        <input type="hidden" name="metode" value="DELETE" />
    </form>


        <h2>Clients</h2>
        <?php foreach ($dadesClients as $clientData): ?>
            <?php list($username, $password, $role, $email, $id, $nom, $cognom, $telefon, $adreça, $tarjeta, $gestorAssignat) = explode(':', $clientData); ?>
                <div class="carta">
                    <p><strong>ID:</strong> <?php echo $id; ?></p>
                    <p><strong>Usuari:</strong> <?php echo $username; ?></p>
                    <p><strong>Nom complet:</strong> <?php echo "$nom $cognom"; ?></p>
                    <p><strong>Correu:</strong> <?php echo $email; ?></p>
                    <p><strong>Telefon:</strong> <?php echo $telefon; ?></p>
                    <p><strong>Adreça:</strong> <?php echo $adreça; ?></p>
                    <p><strong>Tarjeta VISA:</strong> <?php echo $tarjeta; ?></p>
                    <p><strong>Gestor:</strong> <?php echo $gestorAssignat; ?></p>
                </div>
            <?php endforeach; ?>
    <form method="post">
        <!-- Altres elements del formulari -->
        <button type="submit" name="download_clients_pdf">Descarrega PDF dels Clients</button>
    </form>
        <a href="inici.php" class="back-btn">Tornar</a>
    </div>
</body>
</html>
