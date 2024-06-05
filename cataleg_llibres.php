<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'gestor') {
    header("Location: login.php");
    exit;
}

$llibresFile = './LLIBRES/llibres';

function loadLlibres($filename) {
    if (!file_exists($filename)) {
        return [];
    }
    return file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

function saveLlibres($filename, $llibres) {
    file_put_contents($filename, implode("\n", $llibres));
}

$llibres = loadLlibres($llibresFile);

if (isset($_POST['add_llibre'])) {
    $nom = $_POST['nom'];
    $id = $_POST['id'];
    $preu = $_POST['preu'];
    $quantitat = $_POST['quantitat'];
    $disponibilitat = $_POST['disponibilitat'];
    $iva = $_POST['iva'];

    $llibre = "$id:$nom:$preu:$quantitat:$disponibilitat:$iva";
    $llibres[] = $llibre;
    saveLlibres($llibresFile, $llibres);
}

if (isset($_POST['modify_llibre'])) {
    $id = $_POST['id'];

    $nom = $_POST['nom'];
    $preu = $_POST['preu'];
    $quantitat = $_POST['quantitat'];
    $disponibilitat = $_POST['disponibilitat'];
    $iva = $_POST['iva'];

    // crear nova entrada
    $llibre = "$id:$nom:$preu:$quantitat:$disponibilitat:$iva";

    // modificar
    $index = -1;
    foreach ($llibres as $key => $value) {
        if (explode(':', $value)[0] === $id) {
            $index = $key;
            break;
        }
    }

    if ($index !== -1) {
        $llibres[$index] = $llibre;
        saveLlibres($llibresFile, $llibres);
    }
}

if (isset($_POST['delete_llibre'])) {
    $id = $_POST['id'];

    $llibres = array_filter($llibres, function($llibre) use ($id) {
        return explode(':', $llibre)[0] !== $id;
    });

    saveLlibres($llibresFile, $llibres);
}

if (isset($_POST['download_llibres_pdf'])) {
    generateLlibresPDF($llibres);
}

// generar pdf
function generateLlibresPDF($llibres) {
    require_once('tcpdf/tcpdf.php'); 

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
    $html .= '<h1>Informació dels Llibres</h1>';
    $html .= '<table>
                <tr>
                    <th>Nom</th>
                    <th>Preu</th>
                    <th>Quantitat</th>
                    <th>Disponibilitat</th>
                    <th>IVA</th>
                </tr>';
    foreach ($llibres as $llibre) {
        list($id, $nom, $preu, $quantitat, $disponibilitat, $iva) = explode(':', $llibre);
        $html .= "<tr>
                    <td>$nom</td>
                    <td>$preu €</td>
                    <td>$quantitat ud</td>
                    <td>$disponibilitat</td>
                    <td>$iva %</td>
                  </tr>";
    }
    $html .= '</table>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('llibres.pdf', 'D');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestió de Llibres</title>
    <link rel="stylesheet" href="CSS/general.css">
</head>

<body>
<header>
        <h2>Gestionar Llibres</h2>
    </header>
    <div class="container">
    
    <form method="post">
        <h2>Afegir Nou Llibre</h2>
        <label for="id">ID:</label>
        <input type="text" name="id" required><br>
        <label for="nom">Nom:</label>
        <input type="text" name="nom" required><br>
        <label for="preu">Preu:</label>
        <input type="text" name="preu" required><br>
        <label for="quantitat">Quantitat:</label>
        <input type="text" name="quantitat" required><br>
        <label for="disponibilitat">Disponibilitat:</label>
        <select name="disponibilitat">
            <option value="si">Sí</option>
            <option value="no">No</option>
        </select><br>
        <label for="iva">IVA:</label>
        <input type="text" name="iva" required><br> 
        <button type="submit" name="add_llibre">Afegir Llibre</button>
    </form>

    <form method="post">
        <h2>Modificar Llibre</h2>
        <label for="id_modify">Selecciona el llibre a modificar:</label>
        <select name="id" id="id_modify" required>
            <?php foreach ($llibres as $llibre) {
                $dades_llibre = explode(':', $llibre);
                $nom = $dades_llibre[1];
                $id = $dades_llibre[0];
                echo "<option value='$id'>$nom</option>";
            } ?>
        </select><br>
        <label for="nom_modify">Nom:</label>
        <input type="text" name="nom" id="nom_modify" required><br>
        <label for="preu_modify">Preu:</label>
        <input type="text" name="preu" id="preu_modify" required><br>
        <label for="quantitat_modify">Quantitat:</label>
        <input type="text" name="quantitat" id="quantitat_modify" required><br>
        <label for="disponibilitat_modify">Disponibilitat:</label>
        <select name="disponibilitat" id="disponibilitat_modify">
            <option value="si">Sí</option>
            <option value="no">No</option>
        </select><br>
        <label for="iva_modify">IVA:</label>
        <input type="text" name="iva" id="iva_modify" required><br>
        <button type="submit" name="modify_llibre">Modificar Llibre</button>
    </form>


    <form method="post">
        <h2>Esborrar Llibre</h2>
        <label for="id">Selecciona el llibre a esborrar:</label>
        <select name="id" required>
            <?php foreach ($llibres as $llibre) {
                $dades_llibre = explode(':', $llibre);
                $nom = $dades_llibre[1];
                $id = $dades_llibre[0];
                echo "<option value='$id'>$nom</option>";
            } ?>
        </select><br>
        <button type="submit" name="delete_llibre" class="delete">Esborrar</button>
    </form>


    <h2>Llista de Llibres</h2>
    <?php foreach ($llibres as $llibre): ?>
        <?php list($id, $nom, $preu, $quantitat, $disponibilitat, $iva) = explode(':', $llibre); ?>
        <div class="carta">
            <p><strong>ID:</strong> <?php echo $id; ?></p>
            <p><strong>Nom:</strong> <?php echo $nom; ?></p>
            <p><strong>Preu:</strong> <?php echo $preu; ?> €</p>
            <p><strong>Quantitat:</strong> <?php echo $quantitat; ?> ud</p>
            <p><strong>Disponibilitat:</strong> <?php echo $disponibilitat; ?></p>
            <p><strong>IVA:</strong> <?php echo $iva; ?> %</p>
        </div>
    <?php endforeach; ?>
    <form method="post">
        <button type="submit" name="download_llibres_pdf">Descarrega PDF dels Llibres</button>
    </form>
    <a href="inici.php" class="back-btn">Tornar</a>
</div>
</body>
</html>
