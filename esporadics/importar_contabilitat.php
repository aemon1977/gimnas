<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gimnas";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener los años únicos para el filtro
$sql_years = "SELECT DISTINCT YEAR(data) AS año FROM contabilitat_esporadics ORDER BY año DESC";
$result_years = $conn->query($sql_years);

// Recoger los años para el formulario
$years = [];
while ($row = $result_years->fetch_assoc()) {
    $years[] = $row['año'];
}

// Obtener mes y año seleccionados desde el formulario
$selected_month = isset($_POST['month']) ? $_POST['month'] : null;
$selected_year = isset($_POST['year']) ? $_POST['year'] : null;

// Mostrar formulario para seleccionar mes y año
echo '<!DOCTYPE html>';
echo '<html lang="es">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }';
echo '.container { max-width: 800px; margin: 0 auto; background: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); border-radius: 5px; }';
echo 'h2 { text-align: center; color: #333; }';
echo 'form { margin-bottom: 20px; text-align: center; }';
echo 'select, input[type="submit"] { margin: 5px; padding: 10px; font-size: 16px; }';
echo 'table { width: 100%; border-collapse: collapse; margin-top: 20px; }';
echo 'th, td { padding: 10px; text-align: left; border: 1px solid #ccc; }';
echo 'th { background-color: #f2f2f2; }';
echo 'h3 { text-align: center; color: #333; }';
echo '</style>';
echo '</head>';
echo '<body>';
echo '<div class="container">';
echo '<h2>Contabilitat</h2>';
echo '<form method="post">';
echo 'Mes: <select name="month">';
echo '<option value="">Tots</option>'; // Opción para mostrar todos los meses
for ($i = 1; $i <= 12; $i++) {
    $selected = ($i == $selected_month) ? 'selected' : '';
    echo "<option value='$i' $selected>$i</option>";
}
echo '</select>';

echo 'Año: <select name="year">';
echo '<option value="">Tots</option>'; // Opción para mostrar todos los años
foreach ($years as $year) {
    $selected = ($year == $selected_year) ? 'selected' : '';
    echo "<option value='$year' $selected>$year</option>";
}
echo '</select>';
echo '<input type="submit" value="Filtrar">';
echo '</form>';

// Preparar la consulta para mostrar los datos
$sql_display = "
    SELECT nom_soci, MONTH(data) AS mes, YEAR(data) AS año, SUM(quantitat) AS total
    FROM contabilitat_esporadics
";

// Condiciones de filtrado
$conditions = [];
$params = [];
$param_types = '';

if ($selected_month) {
    $conditions[] = "MONTH(data) = ?";
    $params[] = $selected_month;
    $param_types .= 'i'; // Tipo de dato entero
}

if ($selected_year) {
    $conditions[] = "YEAR(data) = ?";
    $params[] = $selected_year;
    $param_types .= 'i'; // Tipo de dato entero
}

if ($conditions) {
    $sql_display .= " WHERE " . implode(' AND ', $conditions);
}

$sql_display .= " GROUP BY nom_soci, mes, año";

$stmt_display = $conn->prepare($sql_display);

// Asignar parámetros si existen
if ($params) {
    $stmt_display->bind_param($param_types, ...$params);
}

// Ejecutar la consulta
$stmt_display->execute();
$result_display = $stmt_display->get_result();

// Mostrar los datos en formato de tabla
if ($result_display->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Nom</th><th>Mes</th><th>Any</th><th>Total</th></tr>";
    
    $total_global = 0; // Variable para sumar los totales
    while ($row = $result_display->fetch_assoc()) {
        echo "<tr><td>" . htmlspecialchars($row['nom_soci']) . "</td><td>" . htmlspecialchars($row['mes']) . "</td><td>" . htmlspecialchars($row['año']) . "</td><td>" . htmlspecialchars($row['total']) . "</td></tr>";
        $total_global += $row['total']; // Sumar total
    }
    echo "</table>";

    // Mostrar el total global
    echo "<h3>Total: $total_global</h3>";
} else {
    echo "<p>No hay datos para mostrar.</p>";
}

echo '</div>'; // Cierre de .container
echo '</body>';
echo '</html>';

// Cerrar conexión
$conn->close();
?>
