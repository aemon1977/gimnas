<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gimnas";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($_GET['cp'])) {
    $cp = $_GET['cp'];

    // Consulta para obtener la población y provincia
    $sql = "SELECT Poblacio, Provincia FROM codipostal WHERE CP = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row); // Retorna la población y provincia en formato JSON
    } else {
        echo json_encode(null); // No se encontró el código postal
    }

    $stmt->close();
}

$conn->close();
?>
