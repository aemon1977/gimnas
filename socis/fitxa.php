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

$socios_encontrados = [];

// Buscar socio si se envía el formulario de búsqueda
if (isset($_POST['buscar'])) {
    $criterio_busqueda = $_POST['criterio_busqueda'];

    // SQL para buscar por DNI o nombre
    $sql_buscar = "SELECT DNI, Nom FROM socis WHERE DNI LIKE '%$criterio_busqueda%' OR Nom LIKE '%$criterio_busqueda%'";
    $result_buscar = $conn->query($sql_buscar);
    
    if ($result_buscar->num_rows > 0) {
        while ($row = $result_buscar->fetch_assoc()) {
            $socios_encontrados[] = $row; // Guardar resultados
        }
    } else {
        echo "No se encontraron socios.";
    }
}

// Obtener todos los socios para mostrar en la tabla
$sql_todos_socios = "SELECT DNI, Nom FROM socis";
$result_todos_socios = $conn->query($sql_todos_socios);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Socios</title>
    <style>
        /* Estilos para el formulario y la tabla */
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .nombre-socio {
            color: blue;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Buscar Socio</h1>
    
    <!-- Formulario de búsqueda -->
    <form action="fitxa.php" method="POST">
        Buscar por DNI o Nombre: <input type="text" name="criterio_busqueda" required>
        <input type="submit" name="buscar" value="Buscar">
    </form>

    <!-- Mostrar resultados si hay más de un socio encontrado -->
    <?php if (!empty($socios_encontrados)): ?>
        <h2>Resultados de la Búsqueda:</h2>
        <form action="ver_ficha.php" method="POST">
            <?php foreach ($socios_encontrados as $socio): ?>
                <div>
                    <input type="radio" name="DNI_seleccionado" value="<?php echo $socio['DNI']; ?>" required>
                    <label><?php echo $socio['DNI'] . " - " . $socio['Nom']; ?></label>
                </div>
            <?php endforeach; ?>
            <input type="submit" name="ver_ficha" value="Ver Ficha">
        </form>
    <?php endif; ?>

    <!-- Tabla con todos los socios -->
    <h2>Todos los Socios:</h2>
    <table>
        <thead>
            <tr>
                <th>DNI</th>
                <th>Nombre</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_todos_socios->num_rows > 0): ?>
                <?php while ($socio = $result_todos_socios->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $socio['DNI']; ?></td>
                        <td>
                            <a href="ver_ficha.php?DNI=<?php echo $socio['DNI']; ?>" class="nombre-socio">
                                <?php echo $socio['Nom']; ?>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">No hay socios disponibles.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
