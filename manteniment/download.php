<?php
// Ruta del archivo a descargar
$file = $_GET['file']; // Obtener el nombre del archivo desde la URL

// Directorio donde se guardan las copias de seguridad
$backupDir = __DIR__ . '/backups/';

// Ruta completa del archivo
$filePath = $backupDir . basename($file); // Usa basename para evitar problemas de seguridad

// Verificar si el archivo existe
if (file_exists($filePath)) {
    // Establecer los encabezados para forzar la descarga
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    
    // Leer el archivo y enviarlo al navegador
    readfile($filePath);
    exit; // Terminar el script después de enviar el archivo
} else {
    echo "Error: El archivo no existe.";
}
?>
