<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gimnas";

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Connexió fallida: " . $conn->connect_error);
}

// Procesar solicitud AJAX
if (isset($_POST['activitat_id'])) {
    $activitat_id = $_POST['activitat_id'];
    $sql_activitat = "SELECT nom FROM activitats WHERE id = $activitat_id";
    $result_activitat = $conn->query($sql_activitat);
    $activitat = $result_activitat->fetch_assoc()['nom'];

    $sql = "SELECT Nom FROM socis WHERE Activitats LIKE '%$activitat%' ORDER BY Nom ASC";
    $result = $conn->query($sql);

    // Generar tabla HTML
    echo "<h2>Socis que participen en: $activitat</h2>";
    echo "<table border='1'>
            <tr>
                <th>Nom</th>
            </tr>";
    
    if ($result->num_rows > 0) {
        while ($socis = $result->fetch_assoc()) {
            echo "<tr><td>{$socis['Nom']}</td></tr>";
        }
    } else {
        echo "<tr><td>No s'han trobat socis per a l'activitat seleccionada.</td></tr>";
    }
    echo "</table>";
    exit;
}

// Generar Excel si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_excel'])) {
    $activitat_id = $_POST['activitat'];
    
    // Obtener el nombre de la actividad
    $sql_activitat = "SELECT nom FROM activitats WHERE id = $activitat_id";
    $result_activitat = $conn->query($sql_activitat);
    $activitat = $result_activitat->fetch_assoc()['nom'];

    // Consulta para obtener los socios
    $sql = "SELECT Nom FROM socis WHERE Activitats LIKE '%$activitat%' ORDER BY Nom ASC";
    $result = $conn->query($sql);

    // Crear un nuevo documento de Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Título de la hoja con el nombre de la actividad
    $sheet->setTitle($activitat);
    $sheet->setCellValue('A1', "Socis que participen en: $activitat");
    $sheet->mergeCells('A1:V1'); // Unir celdas para centrar el título
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Escribir los nombres de los socios
    $row = 3; // Comenzar a la fila 3
    while ($socis = $result->fetch_assoc()) {
        $sheet->setCellValue("A$row", $socis['Nom']);
        $row++;
    }

    // Si no hay resultados, insertar un mensaje
    if ($row == 3) {
        $sheet->setCellValue("A3", "No s'han trobat socis per a l'activitat seleccionada.");
    }

    // Configurar las dimensiones de las celdas
    $sheet->getColumnDimension('A')->setWidth(22);

    // Crear los bordes en todo el documento
    $styleArray = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => '000000'],
            ],
        ],
    ];

    $sheet->getStyle("A1:V$row")->applyFromArray($styleArray);

    // Establecer los encabezados para la descarga del archivo
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="socis_activitat_'.$activitat.'.xlsx"');
    header('Cache-Control: max-age=0');
    
    // Crear el archivo Excel y enviarlo al navegador
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Excel de Socis per Activitat</title>
    <style>
        /* Estilo de página A4 */
        .a4-page {
            width: 21cm;
            height: 29.7cm;
            margin: 0 auto;
            padding: 2cm;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            background-color: white;
            font-family: Arial, sans-serif;
        }
        h1 {
            text-align: center;
            font-size: 1.5em;
            margin-bottom: 1em;
        }
        label {
            display: inline-block;
            width: 100px;
            font-weight: bold;
            margin-bottom: 0.5em;
        }
        select, input[type="submit"] {
            margin-bottom: 1em;
            padding: 0.5em;
            width: calc(100% - 110px);
        }
        #result {
            margin-top: 2em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1em;
        }
        table, th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#activitat').change(function() {
                var activitat_id = $(this).val();
                if (activitat_id) {
                    $.ajax({
                        type: "POST",
                        url: "",
                        data: { activitat_id: activitat_id },
                        success: function(response) {
                            $('#result').html(response);
                        }
                    });
                } else {
                    $('#result').html("");
                }
            });
        });
    </script>
</head>
<body>
    <div class="a4-page">
        <h1>Seleccioneu una Activitat</h1>
        
        <!-- Formulario para seleccionar la actividad -->
        <form method="post" action="">
            <label for="activitat">Activitat:</label>
            <select name="activitat" id="activitat" required>
                <option value="">Seleccioneu una activitat</option>
                <?php
                // Conexión a la base de datos
                $conn = new mysqli($servername, $username, $password, $dbname);

                // Verificar la conexión
                if ($conn->connect_error) {
                    die("Connexió fallida: " . $conn->connect_error);
                }

                // Obtener todas las actividades para el select
                $sql = "SELECT id, nom FROM activitats";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<option value="'.$row['id'].'">'.$row['nom'].'</option>';
                    }
                }

                // Cerrar la conexión
                $conn->close();
                ?>
            </select>
            <br><br>
            <input type="submit" name="generate_excel" value="Generar Excel">
        </form>

        <!-- Contenedor para mostrar los resultados -->
        <div id="result"></div>
    </div>
</body>
</html>