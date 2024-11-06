<?php

// Ejecutar el comando
exec($command);
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = ""; // Cambia esto si es diferente
$dbname = "gimnas";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar conexión
if ($conn->connect_error) {
    die("Conexió fallida: " . $conn->connect_error);
}

// Consulta para obtener los esporádics con fecha de baja
$sqlEsporadics = "SELECT Nom, Telefon1, Telefon2, Telefon3, email, DATEDIFF(Baixa, CURDATE()) AS Dies_Fins_Baixa
                  FROM esporadics
                  WHERE Baixa >= CURDATE()
                  ORDER BY Dies_Fins_Baixa ASC
                  LIMIT 10";

$resultEsporadics = $conn->query($sqlEsporadics);
$sociosEsporadics = [];
if ($resultEsporadics->num_rows > 0) {
    while($row = $resultEsporadics->fetch_assoc()) {
        $sociosEsporadics[] = $row;
    }
}

// Inicializa la variable
$sociosCumpleaños = [];

// Consulta para obtener los días faltantes para el próximo cumpleaños
$sqlCumpleaños = "SELECT Nom, Telefon1, Telefon2, Telefon3, email, Activitats,
                         DATEDIFF(
                             IF(
                                 DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(Data_naixement), '-', DAY(Data_naixement))) >= CURDATE(),
                                 DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(Data_naixement), '-', DAY(Data_naixement))),
                                 DATE(CONCAT(YEAR(CURDATE() + INTERVAL 1 YEAR), '-', MONTH(Data_naixement), '-', DAY(Data_naixement)))
                             ),
                             CURDATE()
                         ) AS Dies_Fins_Aniversari
                  FROM socis
                  HAVING Dies_Fins_Aniversari >= 0
                  ORDER BY Dies_Fins_Aniversari ASC";
				  
$resultCumpleaños = $conn->query($sqlCumpleaños);
if ($resultCumpleaños) {
    if ($resultCumpleaños->num_rows > 0) {
        while ($row = $resultCumpleaños->fetch_assoc()) {
            $sociosCumpleaños[] = $row;
        }
    } else {
        $noCumpleañosMessage = "No hi ha socis amb aniversaris pròxims.";
    }
} else {
    echo "Error en la consulta de cumpleaños: " . $conn->error;
}

// Consultar el total de socios
$sqlTotalSocis = "SELECT COUNT(*) AS total FROM socis";
$resultTotalSocis = $conn->query($sqlTotalSocis);
$totalSocis = $resultTotalSocis->fetch_assoc()['total'];

// Consultar los socios activos
$sqlSocisActius = "SELECT COUNT(*) AS actius FROM socis WHERE Activitats != ''";
$resultSocisActius = $conn->query($sqlSocisActius);
$socisActius = $resultSocisActius->fetch_assoc()['actius'];

// Consultar la suma total de ingresos de la columna Quantitat en socis
$sqlQuantitatTotalSocis = "SELECT SUM(Quantitat) AS totalQuantitat FROM socis WHERE Activitats != ''";
$resultQuantitatTotalSocis = $conn->query($sqlQuantitatTotalSocis);
$totalQuantitatSocis = $resultQuantitatTotalSocis->fetch_assoc()['totalQuantitat'] ?? 0;

// Consultar la suma total de ingresos de la columna Quantitat en esporadics
$sqlQuantitatTotalEsporadics = "SELECT SUM(Quantitat) AS totalQuantitat FROM esporadics WHERE Baixa >= CURDATE()";
$resultQuantitatTotalEsporadics = $conn->query($sqlQuantitatTotalEsporadics);
$totalQuantitatEsporadics = $resultQuantitatTotalEsporadics->fetch_assoc()['totalQuantitat'] ?? 0;

// Sumar ambos totales
$totalQuantitat = $totalQuantitatSocis + $totalQuantitatEsporadics;

// Cierre de conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestió Gimnas</title>
    <style>
    body {
        font-family: Arial, sans-serif;
    }

    .menu {
        background-color: #333;
        overflow: hidden;
    }
    .menu a {
        float: left;
        display: block;
        color: white;
        text-align: center;
        padding: 14px 16px;
        text-decoration: none;
    }
    .menu a:hover {
        background-color: #ddd;
        color: black;
    }
    .dropdown {
        float: left;
        overflow: hidden;
    }
    .dropdown .dropbtn {
        cursor: pointer;
        padding: 14px 16px;
        border: none;
        outline: none;
        color: white;
        background-color: inherit;
        font-family: inherit;
        font-size: 16px;
        margin: 0;
    }
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f9f9f9;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
    }
    .dropdown-content a {
        float: none;
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        text-align: left;
    }
    .dropdown-content a:hover {
        background-color: #ddd;
    }
    .dropdown:hover .dropdown-content {
        display: block;
    }

    /* Contenedor para las tablas */
    .table-container {
    display: flex; /* Utiliza flexbox para un mejor ajuste */
    flex-direction: column; /* Apila las tablas verticalmente */
    width: 100%; /* Asegúrate de que ocupe todo el ancho */
    height: 100%; /* Asegúrate de que ocupe toda la altura */
    overflow: auto; /* Añadir desplazamiento si el contenido excede */
    padding: 10px; /* Espacio alrededor del contenedor */
}
        }

    /* Estilo para las tablas */
    table {
        width: 50%; /* La tabla ocupa todo el ancho del contenedor */
    }

    th, td {
        border: 1px solid #ddd;
        padding: 5px;
        text-align: left;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    th:first-child, td:first-child {
        width: 7cm; /* Ancho fijo para la primera columna */
    }

    th:not(:first-child), td:not(:first-child) {
        width: 4cm; /* Ancho fijo para las demás columnas */
    }

    th {
        background-color: #f2f2f2;
    }

    .etiqueta {
        color: red;
        font-weight: bold;
        margin-bottom: 10px;
        display: inline-block;
        text-align: center;
    }
    .totals {
        color: red;
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: white;
        padding: 10px;
        border: 1px solid #ccc;
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }
    .logo {
        float: right;
        margin: 10px;
        width: 250px;
        height: auto;
    }
    .titulo {
        color: red;
        text-align: left;
        margin-left: 10px;
    }
    .scrollable {
        max-height: 400px;
        overflow-y: scroll;
        border: 1px solid #ccc;
        padding: 10px;
        margin-top: 20px;
    }
</style>

</head>
<body>

<div class="menu">
    <div class="dropdown">
        <button class="dropbtn">Activitats</button>
        <div class="dropdown-content">
            <a href="javascript:void(0);" onclick="openWindow('llistat/activitats.php')">Afegir/Eliminar</a>
            <a href="javascript:void(0);" onclick="openWindow('llistat/llistat.php')">Llistat PDF</a>
            <a href="javascript:void(0);" onclick="openWindow('llistat/llistat_excel.php')">Llistat Excel</a>
        </div>
    </div>
    <div class="dropdown">
        <button class="dropbtn">Socis</button>
        <div class="dropdown-content">
            <a href="javascript:void(0);" onclick="openWindow('socis/insertar.php')">Afegir</a>
            <a href="javascript:void(0);" onclick="openWindow('socis/filtro.php')">Modificar</a>
            <a href="javascript:void(0);" onclick="openWindow('socis/fitxa.php')">Fitxa</a>
            <a href="javascript:void(0);" onclick="openWindow('llistat/llistatsocis.php')">Llistat</a>
        </div>
    </div>
	<div class="dropdown">
        <button class="dropbtn">Esporadics</button>
        <div class="dropdown-content">
            <a href="javascript:void(0);" onclick="openWindow('esporadics/insertar.php')">Afegir</a>
            <a href="javascript:void(0);" onclick="openWindow('esporadics/filtro.php')">Modificar</a>
            <a href="javascript:void(0);" onclick="openWindow('esporadics/fitxa.php')">Fitxa</a>
            <a href="javascript:void(0);" onclick="openWindow('esporadics/llistatsocis.php')">Llistat</a>
			<a href="javascript:void(0);" onclick="openWindow('esporadics/importar_contabilitat.php')">Contabilitat</a>
         </div>
    </div>
</div>

<!-- Logo -->
<img src="logo/logo.jpg" alt="Logo" class="logo" />

<!-- Título debajo del logo -->
<h2 class="titulo">Esporàdics</h2>

<div class="scrollable">
    <?php if (!empty($sociosEsporadics)): ?>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Telefon 1</th>
                    <th>Díes Fins Baixa</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sociosEsporadics as $socio): ?>
                    <tr>
                        <td><?php echo $socio['Nom']; ?></td>
                        <td><?php echo $socio['Telefon1']; ?></td>
                        <td><?php echo $socio['Dies_Fins_Baixa']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hi ha esporàdics amb baixes pròximes.</p>
    <?php endif; ?>
</div>

<!-- Mostrar los próximos cumpleaños -->
<h2 class="titulo">Aniversaris</h2>
<div class="scrollable">
    <?php if (!empty($sociosCumpleaños)): ?>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Telefon 1</th>
                    <th>Activitats</th>
                    <th>Díes Fins Aniversari</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sociosCumpleaños as $socio): ?>
                    <tr>
                        <td><?php echo $socio['Nom']; ?></td>
                        <td><?php echo $socio['Telefon1']; ?></td>
                        <td><?php echo $socio['Activitats']; ?></td> <!-- Mostrar actividades -->
                        <td><?php echo $socio['Dies_Fins_Aniversari']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p><?php echo $noCumpleañosMessage ?? ''; ?></p>
    <?php endif; ?>
</div>

<!-- Mostrar los totales -->
<div class="totals">
    <div class="etiqueta">Total de Socis: <?php echo $totalSocis; ?></div>
    <div class="etiqueta">Socis actius: <?php echo $socisActius; ?></div>
    <div class="etiqueta">Quantitat Total (Socis): <?php echo $totalQuantitatSocis; ?></div>
    <div class="etiqueta">Quantitat Total (Esporàdics): <?php echo $totalQuantitatEsporadics; ?></div>
</div>

<script>
function openWindow(url) {
    window.open(url, '_blank');
}
</script>

</body>
</html>
