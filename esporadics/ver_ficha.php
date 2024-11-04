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

// Inicialitzar variable per al DNI seleccionat
$dni_seleccionat = '';

// Verificar si s'ha enviat el formulari de cerca o si s'ha passat el DNI per URL
if (isset($_POST['ver_ficha']) && isset($_POST['DNI_seleccionado'])) {
    $dni_seleccionat = $_POST['DNI_seleccionado'];
} elseif (isset($_GET['DNI'])) {
    $dni_seleccionat = $_GET['DNI'];
}

// Obtenir la informació del soci seleccionat
if (!empty($dni_seleccionat)) {
    $sql = "SELECT * FROM esporadics WHERE DNI = '$dni_seleccionat'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $socio = $result->fetch_assoc();
    } else {
        echo "No s'ha trobat el soci.";
    }
} else {
    echo "No s'ha seleccionat cap soci.";
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Fitxa del Soci</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin: 20px;
        }
        .info-socio {
            flex: 1;
            margin-right: 20px;
        }
        .foto-socio {
            max-width: 200px;
            max-height: 200px;
        }
    </style>
</head>
<body>
    <div>
        <h1>Fitxa del Soci</h1>

        <div class="info-socio">
            <?php if (isset($socio)): ?>
                <p><strong>DNI:</strong> <?php echo $socio['DNI']; ?></p>
                <p><strong>Nom:</strong> <?php echo $socio['Nom']; ?></p>
                <p><strong>Carrer:</strong> <?php echo $socio['Carrer']; ?></p>
                <p><strong>Codi Postal:</strong> <?php echo $socio['Codipostal']; ?></p>
                <p><strong>Població:</strong> <?php echo $socio['Poblacio']; ?></p>
                <p><strong>Província:</strong> <?php echo $socio['Provincia']; ?></p>
                <p><strong>Email:</strong> <?php echo $socio['email']; ?></p>
                <p><strong>Telèfon 1:</strong> <?php echo $socio['Telefon1']; ?></p>
                <p><strong>Telèfon 2:</strong> <?php echo $socio['Telefon2']; ?></p>
                <p><strong>Telèfon 3:</strong> <?php echo $socio['Telefon3']; ?></p>
                <p><strong>Número de Compte:</strong> <?php echo $socio['Numero_Conta']; ?></p>
                <p><strong>SEPA:</strong> <?php echo $socio['Sepa']; ?></p>
                <p><strong>Activitats:</strong> <?php echo $socio['Activitats']; ?></p>
                <p><strong>Quantitat:</strong> <?php echo $socio['Quantitat']; ?></p>
                <p><strong>Alta:</strong> <?php echo $socio['Alta']; ?></p>
                <p><strong>Baixa:</strong> <?php echo $socio['Baixa']; ?></p>
                <p><strong>Facial:</strong> <?php echo $socio['Facial']; ?></p>
                <p><strong>Data Inici Activitat:</strong> <?php echo $socio['Data_Inici_activitat']; ?></p>
            <?php else: ?>
                <p>No s'han trobat dades del soci.</p>
            <?php endif; ?>
            
            <a href="fitxa.php">Tornar a buscar</a>
        </div>
    </div>

    <div>
        <!-- Mostrar foto a la part superior dreta -->
        <?php if (isset($socio) && !empty($socio['Foto'])): ?>
            <h3>Foto del Soci:</h3>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($socio['Foto']); ?>" alt="Foto de <?php echo $socio['Nom']; ?>" class="foto-socio"/>
        <?php else: ?>
            <p>No hi ha foto disponible.</p>
        <?php endif; ?>
    </div>
</body>
</html>
