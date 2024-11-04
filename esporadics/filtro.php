<?php
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

// Comprovar si s'ha enviat una cerca
$nom_cerca = isset($_POST['nom']) ? $_POST['nom'] : '';
$soci_data = [];

// Si s'ha introduït un nom, cercar esporàdics
if ($nom_cerca != '') {
    $sql = "SELECT * FROM esporadics WHERE Nom LIKE '%$nom_cerca%' OR DNI LIKE '%$nom_cerca%'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $soci_data[] = $row;
        }
    }
} else {
    // Obtenir tots els esporàdics si no s'ha cercat res
    $sql = "SELECT * FROM esporadics";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $soci_data[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Filtrar Esporàdics</title>
    <style>
        /* Estil per a la taula */
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        a {
            color: blue;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Filtrar Esporàdics</h1>
    <form method="POST" action="filtro.php">
        <input type="text" name="nom" placeholder="Introduïu el nom o DNI" value="<?php echo htmlspecialchars($nom_cerca); ?>">
        <input type="submit" value="Cercar">
    </form>

    <table>
        <tr>
            <th>Nom</th>
            <th>DNI</th>
            <th>Carrer</th>
            <th>Codi Postal</th>
            <th>Població</th>
            <th>Provincia</th>
            <th>Correu Electrònic</th>
            <th>Data de Naixement</th>
            <th>Telèfon 1</th>
            <th>Telèfon 2</th>
            <th>Telèfon 3</th>
            <th>Número de Comptes</th>
            <th>SEPA</th>
            <th>Activitats</th>
            <th>Quantitat</th>
            <th>Data d'Alta</th>
            <th>Data de Baixa</th>
            <th>Facial</th>
            <th>Data Inici Activitat</th>
            <th>Usuari</th>
            <th>Descompte</th>
            <th>Total</th>
            <th>Temps Descompte</th>
            <th>Extres</th>
            <th>En mà</th>
        </tr>
        <?php if (count($soci_data) > 0): ?>
            <?php foreach ($soci_data as $soci): ?>
                <tr>
                    <td><a href="modificar1.php?DNI_seleccionat=<?php echo htmlspecialchars($soci['DNI']); ?>"><?php echo htmlspecialchars($soci['Nom']); ?></a></td>
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
                    <td><?php echo htmlspecialchars($soci['Activitats']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Quantitat']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Alta']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Baixa']); ?></td>
                    <td><?php echo $soci['Facial'] ? 'Sí' : 'No'; ?></td>
                    <td><?php echo htmlspecialchars($soci['Data_Inici_activitat']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Usuari']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Descompte']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Total']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Temps_descompte']); ?></td>
                    <td><?php echo htmlspecialchars($soci['Extres']); ?></td>
                    <td><?php echo $soci['En_ma'] ? 'Sí' : 'No'; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="23">No s'han trobat esporàdics.</td>
            </tr>
        <?php endif; ?>
    </table>

    <?php $conn->close(); ?>
</body>
</html>
