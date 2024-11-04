<?php
// Conexión a la base de datos
$servername = "localhost"; // Cambia esto si es necesario
$username = "root"; // Cambia esto si es necesario
$password = ""; // Cambia esto si es necesario
$dbname = "gimnas"; // Cambia esto si es necesario

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Inicializa las variables
$nom_cerca = '';
$order_by = 'data_modificacio'; // Ordenar primero por data_modificacio
$order = 'DESC'; // Descendente para que los más recientes aparezcan primero

// Captura los valores de búsqueda y orden
if (isset($_GET['cerca'])) {
    $nom_cerca = $_GET['cerca'];
}
if (isset($_GET['order_by'])) {
    $order_by = $_GET['order_by'];
}
if (isset($_GET['order'])) {
    $order = $_GET['order'];
}

// Construcción de la consulta
$sql = "SELECT * FROM socis WHERE Nom LIKE ? OR Activitats LIKE ? ORDER BY data_modificacio DESC, $order_by $order";
$stmt = $conn->prepare($sql);
$search_param = "%" . $conn->real_escape_string($nom_cerca) . "%";
$stmt->bind_param("ss", $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Búsqueda de Socios</title>
</head>
<body>
    <h1>Búsqueda de Socios</h1>
    <form method="GET" action="">
        <label for="cerca">Buscar por Nombre o Actividad:</label>
        <input type="text" name="cerca" value="<?php echo htmlspecialchars($nom_cerca); ?>">
        <input type="submit" value="Buscar">
    </form>

    <table border="1">
        <tr>
            <th><a href="?order_by=Nom&order=<?php echo ($order_by == 'Nom' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">Nom</a></th>
            <th><a href="?order_by=Activitats&order=<?php echo ($order_by == 'Activitats' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">Activitats</a></th>
            <th><a href="?order_by=data_modificacio&order=<?php echo ($order_by == 'data_modificacio' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">Data Modificació</a></th>
            <th><a href="?order_by=DNI&order=<?php echo ($order_by == 'DNI' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">DNI</a></th>
			<th><a href="?order_by=Carrer&order=<?php echo ($order_by == 'Carrer' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">Carrer</a></th>
			<th><a href="?order_by=Codi Postal&order=<?php echo ($order_by == 'Codi Postal' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">Codi Postal</a></th>
			<th><a href="?order_by=Poblacio&order=<?php echo ($order_by == 'Poblacio' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">Poblacio</a></th>
			<th><a href="?order_by=Provincia&order=<?php echo ($order_by == 'Provincia' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">Provincia</a></th>
			<th><a href="?order_by=email&order=<?php echo ($order_by == 'email' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">email</a></th>
			<th><a href="?order_by=Data_naixement&order=<?php echo ($order_by == 'Data_naixement' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">Data_naixement</a></th>
			<th><a href="?order_by=Telefon1&order=<?php echo ($order_by == 'Telefon1' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">Telefon1</a></th>
			<th><a href="?order_by=Telefon2&order=<?php echo ($order_by == 'Telefon2' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">Telefon2</a></th>
			<th><a href="?order_by=Telefon3&order=<?php echo ($order_by == 'Telefon3' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">Telefon3</a></th>
			<th><a href="?order_by=Numero_Conta&order=<?php echo ($order_by == 'Numero_Conta' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">Numero_Conta</a></th>
			<th><a href="?order_by=Sepa&order=<?php echo ($order_by == 'Sepa' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">Sepa</a></th>
			<th><a href="?order_by=Quantitat&order=<?php echo ($order_by == 'Quantitat' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&cerca=<?php echo urlencode($nom_cerca); ?>">Quantitat</a></th>
        </tr>

        <?php if ($result->num_rows > 0) : ?>
            <?php while ($soci = $result->fetch_assoc()) : ?>
                <tr>
                    <td><a href="modificar1.php?DNI_seleccionat=<?php echo urlencode($soci['DNI']); ?>"><?php echo htmlspecialchars($soci['Nom']); ?></a></td>
                    <td><?php echo htmlspecialchars($soci['Activitats']); ?></td>
                    <td><?php echo htmlspecialchars($soci['data_modificacio']); ?></td>
                    <td><?php echo htmlspecialchars($soci['DNI']); ?></td>
					<td><?php echo htmlspecialchars($soci['Carrer']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Codipostal']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Poblacio']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Provincia']); ?></td>
                    <td><?php echo htmlspecialchars($soci['email']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Data_naixement']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Telefon1']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Telefon2']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Telefon3']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Numero_Conta']); ?></td>
                    <td><?php echo $soci['Sepa'] ? 'Sí' : 'No'; ?></td>
                    <td><?php echo htmlspecialchars($soci['Quantitat']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="15">No s'han trobat socis.</td>
            </tr>
        <?php endif; ?>
    </table>

    <?php
    // Cierra la conexión
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>
