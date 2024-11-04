<?php
// Incloure FPDF
require('fpdf/fpdf.php');

// Connexió a la base de dades
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gimnas";

// Crear connexió
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la connexió
if ($conn->connect_error) {
    die("Connexió fallida: " . $conn->connect_error);
}

$activitat_seleccionada = null;
$socis = [];

// Verificar si s'ha enviat el formulari
if (isset($_POST['actividad'])) {
    $activitat_seleccionada = $_POST['actividad'];
    // SQL per obtenir socis que participen en l'activitat seleccionada
    $sql = "SELECT Nom FROM socis WHERE FIND_IN_SET('$activitat_seleccionada', Activitats) ORDER BY Nom ASC"; // Ordenar alfabèticament
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $socis[] = $row['Nom']; // Desar noms dels socis
        }
    }

    // Generar PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, htmlspecialchars($activitat_seleccionada), 0, 1, 'C');

    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(10); // Salto de línia

    // Configurar mida de cel·la
    $cell_height = 6; // Alçada de la cel·la (0.6 cm)
    $first_cell_width = 50; // Amplada de la primera cel·la (5 cm)
    $subsequent_cell_width = 5; // Amplada de les cel·les següents (0.5 cm)
    $margin = 10; // Margen de la fulla

    // Establir el color per al fons de la quadrícula
    $pdf->SetFillColor(255, 255, 255);

    // Definir el total de files i columnes
    $total_rows = 40; // Total de files per omplir
    $total_columns = 26; // Total de columnes (1 cel·la ampla + 14 cel·les estretes)
    $row_index = 0; // Índex de fila

    // Dibujar quadrícula completa
    for ($row_index = 0; $row_index < $total_rows; $row_index++) {
        // Dibujar la primera cel·la amb amplada diferent
        $pdf->Rect($margin, 40 + $row_index * $cell_height, $first_cell_width, $cell_height, 'DF');
        
        // Dibujar cel·les següents sense espai entre elles
        for ($col = 1; $col < $total_columns; $col++) {
            $pdf->Rect($margin + $first_cell_width + ($col - 1) * $subsequent_cell_width, 40 + $row_index * $cell_height, $subsequent_cell_width, $cell_height, 'DF');
        }

        // Si hi ha noms de socis, inserir-los a la primera cel·la
        if ($row_index < count($socis)) {
            $pdf->Text($margin + 1, 40 + $row_index * $cell_height + 4, htmlspecialchars($socis[$row_index]));
        }
    }

    // Salvar el document PDF
    $pdf->Output('D', 'socis_activitat_' . $activitat_seleccionada . '.pdf'); // 'D' força la descàrrega
    exit; // Terminar l'script
}

// Obtenir llista d'activitats
$activitats = [];
$sql_activitats = "SELECT * FROM activitats";
$result_activitats = $conn->query($sql_activitats);

if ($result_activitats->num_rows > 0) {
    while ($row = $result_activitats->fetch_assoc()) {
        $activitats[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Seleccionar Activitat</title>
</head>
<body>
    <h1>Seleccionar Activitat</h1>
    <form action="llistat.php" method="POST" style="text-align: center;">
        <select name="actividad" required>
            <?php foreach ($activitats as $activitat): ?>
                <option value="<?php echo $activitat['nom']; ?>"><?php echo $activitat['nom']; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" value="Buscar">
    </form>
</body>
</html>
