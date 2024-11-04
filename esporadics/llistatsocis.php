<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Conectar a la base de dades
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gimnas";

// Crear la connexió
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la connexió
if ($conn->connect_error) {
    die("Connexió fallida: " . $conn->connect_error);
}

// Funció per generar Excel
function generarExcel($tipus, $esporadics) {
    // Crear un nou document d'Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Títol de la fulla d'Excel segons el tipus
    $titol = $tipus == 'actius' ? 'Esporàdics Actius' : 'Esporàdics Inactius';
    $sheet->setTitle($titol);

    // Col·locar el títol de la fulla
    $sheet->setCellValue('A1', $titol);

    // Títols de les columnes (sense incloure 'Foto')
    $encapçalaments = array('ID', 'DNI', 'Nom', 'Carrer', 'Codipostal', 'Poblacio', 'Provincia', 'email', 'Data_naixement', 'Telefon1', 'Telefon2', 'Telefon3', 'Numero_Conta', 'Sepa', 'Activitats', 'Quantitat', 'Alta', 'Baixa', 'Facial', 'Data_Inici_activitat', 'Usuari', 'Descompte', 'Total', 'Temps_descompte', 'Extres', 'En_ma');

    // Establir els encapçalaments a la fila 2
    $col = 'A';
    foreach ($encapçalaments as $encapçament) {
        $sheet->setCellValue($col . '2', $encapçament);
        $col++;
    }

    // Omplir les dades dels esporàdics
    $fila = 3;
    foreach ($esporadics as $esporadic) {
        $col = 'A';
        foreach ($encapçalaments as $camp) {
            // Excloure el camp 'Foto'
            if ($camp !== 'Foto') {
                $sheet->setCellValue($col . $fila, $esporadic[$camp]);
                $col++;
            }
        }
        $fila++;
    }

    // Ajustar l'ample de les columnes automàticament
    foreach (range('A', 'V') as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    // Aplicar estils a la fulla d'Excel (bordes i alineació)
    $sheet->getStyle("A2:V$fila")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => '000000'],
            ],
        ],
    ]);
    $sheet->getStyle("A2:V$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle("A2:V$fila")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    // Establir els encapçalaments per a la descàrrega de l'arxiu
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $titol . '.xlsx"');
    header('Cache-Control: max-age=0');

    // Crear l'arxiu d'Excel i enviar-ho al navegador
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// Obtenir la llista d'esporàdics "actius" (amb activitats)
if (isset($_POST['tipus'])) {
    $tipus = $_POST['tipus'];
    if ($tipus == 'actius') {
        $sql = "SELECT * FROM esporadics WHERE Activitats IS NOT NULL AND Activitats != ''";
    } elseif ($tipus == 'inactius') {
        $sql = "SELECT * FROM esporadics WHERE Activitats IS NULL OR Activitats = ''";
    }

    $result = $conn->query($sql);
    $esporadics = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $esporadics[] = $row;
        }
    }

    generarExcel($tipus, $esporadics);
}

// Tancar la connexió
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generar Llistes d'Esporàdics</title>
</head>
<body>
    <h1>Seleccioneu el Tipus de Llista</h1>
    
    <!-- Formulari per generar llistes -->
    <form method="post" action="">
        <label for="tipus">Seleccioneu el tipus de llista:</label>
        <select name="tipus" id="tipus" required>
            <option value="">Seleccioneu un tipus</option>
            <option value="actius">Esporàdics Actius</option>
            <option value="inactius">Esporàdics Inactius</option>
        </select>
        <br><br>
        <input type="submit" value="Generar Excel">
    </form>
</body>
</html>
