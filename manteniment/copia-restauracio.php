<?php
// Configuración
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // Sin contraseña para el usuario root
$dbName = 'gimnas';
$backupDir = 'backups/'; // Directorio donde se guardan las copias de seguridad

// Comprobar si el directorio de copias de seguridad existe, si no, crearlo
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Función para hacer la copia de seguridad
function backupDatabase($host, $user, $pass, $name, $backupDir) {
    $date = date('Y-m-d_H-i-s');
    $backupFile = $backupDir . "gimnas_backup_{$date}.sql";
    $command = "\"C:\\xampp\\mysql\\bin\\mysqldump.exe\" --opt -h {$host} -u {$user} -p{$pass} {$name} > \"{$backupFile}\"";

    // Ejecutar el comando
    system($command, $output);
    
    if ($output === 0 && file_exists($backupFile)) {
        return "Backup realizado con éxito: $backupFile";
    } else {
        return "Error al realizar el backup.";
    }
}

// Función para restaurar la base de datos
function restoreDatabase($host, $user, $pass, $name, $filePath) {
    $command = "\"C:\\xampp\\mysql\\bin\\mysql.exe\" -h {$host} -u {$user} -p{$pass} {$name} < \"{$filePath}\"";
    system($command, $output);
    
    if ($output === 0) {
        return "Restauración realizada con éxito desde: $filePath";
    } else {
        return "Error al restaurar la base de datos.";
    }
}

// Función para formatear el tamaño del archivo
function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

// Función para comprobar y ajustar los límites de PHP
function checkAndAdjustPHPSettings($fileSize) {
    // Obtener el tamaño actual de upload_max_filesize y post_max_size
    $currentUploadLimit = ini_get('upload_max_filesize');
    $currentPostLimit = ini_get('post_max_size');
    
    // Calcular nuevo límite basado en el tamaño del archivo
    $newLimitMB = ceil($fileSize / (1024 * 1024)); // Convertir a MB
    $newLimit = $newLimitMB + 10; // Agregar un margen de 10 MB

    // Convertir a formato adecuado
    $newLimitStr = $newLimit . 'M';

    // Ajustar los límites si el nuevo límite es mayor que el actual
    if ($newLimitMB > (int)filter_var($currentUploadLimit, FILTER_SANITIZE_NUMBER_INT)) {
        ini_set('upload_max_filesize', $newLimitStr);
    }
    if ($newLimitMB > (int)filter_var($currentPostLimit, FILTER_SANITIZE_NUMBER_INT)) {
        ini_set('post_max_size', $newLimitStr);
    }
}

// Procesar las solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Backup de la base de datos
    if (isset($_POST['backup'])) {
        $message = backupDatabase($dbHost, $dbUser, $dbPass, $dbName, $backupDir);
        echo $message . "<br>";
    }

    // Restaurar desde un archivo subido
    if (isset($_FILES['fileToRestore']) && $_FILES['fileToRestore']['error'] == UPLOAD_ERR_OK) {
        $fileSize = $_FILES['fileToRestore']['size'];
        checkAndAdjustPHPSettings($fileSize); // Ajustar límites antes de procesar

        $uploadedFile = $_FILES['fileToRestore']['tmp_name'];
        $restoreMessage = restoreDatabase($dbHost, $dbUser, $dbPass, $dbName, $uploadedFile);
        echo $restoreMessage . "<br>";
    }

    // Restaurar desde un archivo seleccionado
    if (isset($_POST['fileToRestoreSelect']) && $_POST['fileToRestoreSelect'] != '') {
        $fileToRestore = $backupDir . $_POST['fileToRestoreSelect']; // Archivo a restaurar
        if (file_exists($fileToRestore)) {
            $restoreMessage = restoreDatabase($dbHost, $dbUser, $dbPass, $dbName, $fileToRestore);
            echo $restoreMessage . "<br>";
        } else {
            echo "Error: El archivo de copia de seguridad no existe.<br>";
        }
    }
}

// Mostrar copias de seguridad existentes
$backupFiles = array_diff(scandir($backupDir), ['.', '..']);
echo "<h2>Copias de Seguridad</h2>";
echo "<table border='1'><tr><th>Archivo</th><th>Tamaño</th><th>Acciones</th></tr>";
foreach ($backupFiles as $file) {
    $filePath = $backupDir . $file;
    echo "<tr>";
    echo "<td>$file</td>";
    echo "<td>" . formatSize(filesize($filePath)) . "</td>";
    echo "<td><a href='$filePath' download>Descargar</a> | <a href='?delete=$file'>Eliminar</a></td>";
    echo "</tr>";
}
echo "</table>";

// Eliminar una copia de seguridad
if (isset($_GET['delete'])) {
    $fileToDelete = $backupDir . $_GET['delete'];
    if (file_exists($fileToDelete)) {
        unlink($fileToDelete);
        echo "Copia de seguridad eliminada: " . $_GET['delete'] . "<br>";
    } else {
        echo "Error: El archivo no existe.<br>";
    }
}
?>

<!-- Formulario para realizar el backup y restaurar -->
<h2>Crear Backup</h2>
<form method="POST">
    <input type="submit" name="backup" value="Hacer Copia de Seguridad">
</form>

<h2>Restaurar Base de Datos</h2>
<form method="POST" enctype="multipart/form-data">
    <label for="fileToRestore">Selecciona un archivo SQL para restaurar:</label>
    <input type="file" name="fileToRestore" id="fileToRestore" accept=".sql">
    <input type="submit" value="Restaurar desde Archivo">
</form>

<h2>Restaurar desde Copia Existente</h2>
<form method="POST">
    <label for="fileToRestoreSelect">Selecciona una copia de seguridad:</label>
    <select name="fileToRestoreSelect" id="fileToRestoreSelect">
        <option value="">Seleccione una copia</option>
        <?php foreach ($backupFiles as $file): ?>
            <option value="<?php echo $file; ?>"><?php echo $file; ?></option>
        <?php endforeach; ?>
    </select>
    <input type="submit" value="Restaurar desde Copia Existente">
</form>
