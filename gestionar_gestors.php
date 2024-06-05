<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once(__DIR__ . '/tcpdf/tcpdf.php');

$usersFile = './USUARIS/users';

$dadesUsuaris = file($usersFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$dadesGestors = array_filter($dadesUsuaris, function($dadaUsuari) {
    return explode(':', $dadaUsuari)[2] === "gestor";
});

$selectedGestor = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["selected_gestor"])) {
        $selectedGestor = $_POST["selected_gestor"];
        list($nomUsuariEditat, $contrasenyaEditada, $rolEditat, $correuEditat, $idEditat, $nomEditat, $cognomEditat, $telefonEditat) = explode(':', obtenirDadesUsuari($selectedGestor, $dadesUsuaris));
    } elseif (isset($_POST["afegir_gestor"])) {
        $nouNomUsuari = $_POST["nous_usuari"];
        $novesDadesGestor = $nouNomUsuari . ":" . password_hash($_POST["nova_contra"], PASSWORD_DEFAULT) . ":gestor:" . $_POST["nou_gmail"]. ":" . $_POST["nou_id"] . ":" . $_POST["nou_nom"] . ":" . $_POST["nou_cognom"] . ":" . $_POST["nou_telefon"];
        $dadesUsuaris[] = $novesDadesGestor;
        file_put_contents($usersFile, implode("\n", $dadesUsuaris));
        header("Location: " . $_SERVER["PHP_SELF"]);
    } elseif (isset($_POST["update_gestor"])) {
        $nomUsuariEditat = $_POST["edit_usuari"];
        $contrasenyaEditada = $_POST["edit_contra"] ? password_hash($_POST["edit_contra"], PASSWORD_DEFAULT) : obtenirContrasenyaUsuari($nomUsuariEditat, $dadesUsuaris);
        $correuEditat = $_POST["edit_gmail"];
        $idEditat = $_POST["edit_id"];
        $nomEditat = $_POST["edit_nom"];
        $cognomEditat = $_POST["edit_cognom"];
        $telefonEditat = $_POST["edit_telefon"];
        
        foreach ($dadesUsuaris as &$dadaUsuari) {
            $nomUsuariGuardat = explode(':', $dadaUsuari)[0];
            if ($nomUsuariGuardat === $nomUsuariEditat) {
                $dadaUsuari = "$nomUsuariEditat:$contrasenyaEditada:gestor:$correuEditat:$idEditat:$nomEditat:$cognomEditat:$telefonEditat";
                break;
            }
        }
        file_put_contents($usersFile, implode("\n", $dadesUsuaris));
        header("Location: " . $_SERVER["PHP_SELF"]);
    } elseif (isset($_POST["delete_gestor"])) {
        $nomUsuariEsborrat = $_POST["borrar_gestor"];
        $dadesGestors = array_filter($dadesGestors, function ($dadaGestor) use ($nomUsuariEsborrat) {
            return explode(':', $dadaGestor)[0] !== $nomUsuariEsborrat;
        });
        $dadesUsuaris = array_filter($dadesUsuaris, function ($dadaUsuari) use ($nomUsuariEsborrat) {
            return explode(':', $dadaUsuari)[0] !== $nomUsuariEsborrat || explode(':', $dadaUsuari)[2] !== "gestor";
        });
        file_put_contents($usersFile, implode("\n", $dadesUsuaris));
        header("Location: " . $_SERVER["PHP_SELF"]);
    }
}

function obtenirDadesUsuari($nomUsuari, $dadesUsuaris)
{
    foreach ($dadesUsuaris as $dadaUsuari) {
        $nomUsuariGuardat = explode(':', $dadaUsuari)[0];
        if ($nomUsuariGuardat === $nomUsuari) {
            return $dadaUsuari;
        }
    }
    return null;
}

function obtenirContrasenyaUsuari($nomUsuari, $dadesUsuaris)
{
    foreach ($dadesUsuaris as $dadaUsuari) {
        $nomUsuariGuardat = explode(':', $dadaUsuari)[0];
        if ($nomUsuariGuardat === $nomUsuari) {
            return explode(':', $dadaUsuari)[1];
        }
    }
    return null;
}

if (isset($_POST['download_gestors_pdf'])) {
    generateGestorsPDF($dadesGestors);
}

function generateGestorsPDF($dadesGestors) {
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
    $html .= '<h1>Informació dels Gestors</h1>';
    $html .= '<table>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Telèfon</th>
                </tr>';
    foreach ($dadesGestors as $gestor) {
        list($username, , , $email, , $nom, $cognom, $telefon) = explode(':', $gestor);
        $html .= "<tr>
                    <td>$nom $cognom</td>
                    <td>$email</td>
                    <td>$telefon</td>
                  </tr>";
    }
    $html .= '</table>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('gestors.pdf', 'D');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestió de Gestors</title>
    <link rel="stylesheet" href="CSS/general.css">
</head>
<body>
    <header>
        <h2>Gestio de gestors</h2>
    </header>
    <div class="container">

        <form method="post">
            <h2>Agregar un Nou Gestor</h2>
            <label for="nou_id">ID:</label>
            <input type="text" id="nou_id" name="nou_id" required>
            <label for="nous_usuari">Usuari:</label>
            <input type="text" id="nous_usuari" name="nous_usuari" required>
            <label for="nova_contra">Contrasenya:</label>
            <input type="password" id="nova_contra" name="nova_contra" required>
            <label for="nou_gmail">Correu:</label>
            <input type="email" id="nou_gmail" name="nou_gmail" required>
            <label for="nou_nom">Nom:</label>
            <input type="text" id="nou_nom" name="nou_nom" required>
            <label for="nou_cognom">Cognom:</label>
            <input type="text" id="nou_cognom" name="nou_cognom" required>
            <label for="nou_telefon">Telefon:</label>
            <input type="text" id="nou_telefon" name="nou_telefon" required>
            <input type="submit" name="afegir_gestor" value="Afegir">
        </form>

        <form method="post">
            <h2>Modificar Gestor</h2>
            <label for="selected_gestor">Usuari:</label>
            <select id="selected_gestor" name="selected_gestor">
                <?php foreach ($dadesGestors as $gestorData): ?>
                    <?php list($username) = explode(':', $gestorData); ?>
                    <option value="<?php echo $username; ?>"><?php echo $username; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="select_gestor">Seleccionar</button>
        </form>

        <?php if ($selectedGestor): ?>
            <form method="post">
                <h2>Modificar Gestor</h2>
                <input type="hidden" name="edit_usuari" value="<?php echo $selectedGestor; ?>">
                <label for="edit_id">ID:</label>
                <input type="text" id="edit_id" name="edit_id" value="<?php echo $idEditat; ?>" required>
                <label for="edit_contra">Contrasenya (deixar en blanc per mantenir la mateixa):</label>
                <input type="password" id="edit_contra" name="edit_contra">
                <label for="edit_gmail">Correu:</label>
                <input type="email" id="edit_gmail" name="edit_gmail" value="<?php echo $correuEditat; ?>" required>
                <label for="edit_nom">Nom:</label>
                <input type="text" id="edit_nom" name="edit_nom" value="<?php echo $nomEditat; ?>" required>
                <label for="edit_cognom">Cognom:</label>
                <input type="text" id="edit_cognom" name="edit_cognom" value="<?php echo $cognomEditat; ?>" required>
                <label for="edit_telefon">Telefon:</label>
                <input type="text" id="edit_telefon" name="edit_telefon" value="<?php echo $telefonEditat; ?>" required>
                <input type="submit" name="update_gestor" value="Modificar Gestor">
            </form>
        <?php endif; ?>

        <form method="post">
            <h2>Eliminar Gestor</h2>
            <label for="borrar_gestor">Usuari:</label>
            <select id="borrar_gestor" name="borrar_gestor">
                <?php foreach ($dadesGestors as $gestorData): ?>
                    <?php list($username) = explode(':', $gestorData); ?>
                    <option value="<?php echo $username; ?>"><?php echo $username; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="delete_gestor" class="delete">Esborrar</button>
        </form>

        <h2>Gestors</h2>
        <?php foreach ($dadesGestors as $gestorData): ?>
            <?php list($username, $password, $role, $email, $id, $nom, $cognom, $telefon) = explode(':', $gestorData); ?>
            <div class="carta">
                <p><strong>ID:</strong> <?php echo $id; ?></p>
                <p><strong>Usuari:</strong> <?php echo $username; ?></p>
                <p><strong>Nom complet:</strong> <?php echo "$nom $cognom"; ?></p>
                <p><strong>Correu:</strong> <?php echo $email; ?></p>
                <p><strong>Telefon:</strong> <?php echo $telefon; ?></p>
            </div>
        <?php endforeach; ?>
        <form method="post">
            <button type="submit" name="download_gestors_pdf">Descarrega PDF dels Gestors</button>
        </form>
        <a href="inici.php" class="back-btn">Tornar</a>
    </div>
</body>
</html>








