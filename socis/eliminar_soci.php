<?php
// Conexión a la base de datos
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

// Verificar si se ha enviado el DNI
if (isset($_POST['DNI'])) {
    $DNI = $conn->real_escape_string($_POST['DNI']);

    // Eliminar el socio
    $sql_delete = "DELETE FROM socis WHERE DNI='$DNI'";
    if ($conn->query($sql_delete) === TRUE) {
        echo "Socio eliminado correctamente!";
        // Redirigir a filtro.php después de la eliminación
        header("Location: filtro.php");
        exit;
    } else {
        echo "Error al eliminar el socio: " . $conn->error;
    }
}
$conn->close();
?>
