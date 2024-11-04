<?php
// Connexió a la base de dades
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gimnas";

// Crear connexió
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprovar la connexió
if ($conn->connect_error) {
    die("Connexió fallida: " . $conn->connect_error);
}

// Verificar si el campo 'data_modificacio' existe en la tabla 'socis'
$tableName = 'socis';
$columnName = 'data_modificacio';

// Consulta para verificar si el campo existe
$query = "SHOW COLUMNS FROM $tableName LIKE '$columnName'";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    // Si el campo no existe, añadirlo
    $alterQuery = "ALTER TABLE $tableName ADD $columnName DATE DEFAULT NULL";
    if ($conn->query($alterQuery) === TRUE) {
        echo "El campo '$columnName' se ha creado en la tabla '$tableName'.";
    } else {
        echo "Error al crear el campo: " . $conn->error;
    }
}

// Consultar les activitats per a la llista de verificació
$sql = "SELECT id, nom FROM activitats";
$result_activitats = $conn->query($sql);

// Inicialitzar variables per població i província
$poblacio = '';
$provincia = '';

// Comprovar si s'ha enviat una sol·licitud AJAX per carregar població i província
if (isset($_GET['cp'])) {
    $cp = $_GET['cp'];
    $sql_cp = "SELECT Poblacio, Provincia FROM codipostal WHERE CP = '$cp'";
    $result_cp = $conn->query($sql_cp);

    if ($result_cp->num_rows > 0) {
        $row_cp = $result_cp->fetch_assoc();
        echo json_encode($row_cp); // Retornar com a JSON
        exit; // Terminar l'execució
    } else {
        echo json_encode(['Poblacio' => '', 'Provincia' => '']); // Retornar buit si no es troba
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Rebre dades del formulari
    $DNI = $_POST['DNI'];
    $Nom = $_POST['Nom'];
    $Carrer = $_POST['Carrer'];
    $Codipostal = $_POST['Codipostal'];
    $Poblacio = $_POST['Poblacio'];
    $Provincia = $_POST['Provincia'];
    $email = $_POST['email'];
    $Data_naixement = $_POST['Data_naixement'];
    $Telefon1 = $_POST['Telefon1'];
    $Telefon2 = $_POST['Telefon2'];
    $Telefon3 = $_POST['Telefon3'];
    $Numero_Conta = $_POST['Numero_Conta'];
    $Sepa = isset($_POST['Sepa']) ? 1 : 0;
    
    // Obtenir noms d'activitats seleccionades
    $activitats_seleccionades = isset($_POST['Activitats']) ? $_POST['Activitats'] : [];
    $nombres_activitats = [];
    
    foreach ($activitats_seleccionades as $id_actividad) {
        // Consultar el nom de l'activitat per ID
        $sql_nombre = "SELECT nom FROM activitats WHERE id = '$id_actividad'";
        $result_nombre = $conn->query($sql_nombre);
        if ($result_nombre->num_rows > 0) {
            $row_nombre = $result_nombre->fetch_assoc();
            $nombres_activitats[] = $row_nombre['nom'];
        }
    }
    
    // Concatenar noms d'activitats
    $Activitats = implode(',', $nombres_activitats);
    
    $Quantitat = $_POST['Quantitat'];
    $Alta = $_POST['Alta'];
    $Baixa = $_POST['Baixa'];
    $Facial = isset($_POST['Facial']) ? 1 : 0;
    $Data_Inici_activitat = $_POST['Data_Inici_activitat'];
    $Usuari = $_POST['Usuari'];

    // Si es carrega una foto
    if (isset($_FILES['Foto']) && $_FILES['Foto']['error'] == 0) {
        // Verifica que el tipo de archivo sea una imagen válida
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['Foto']['tmp_name']);
        
        if (in_array($fileType, $allowedTypes)) {
            // Convertir la imagen a binario
            $Foto = addslashes(file_get_contents($_FILES['Foto']['tmp_name']));
        } else {
            // Si no es una imagen válida, establece a null
            $Foto = null;
            echo "Tipus de fitxer no vàlid.";
        }
    } else {
        $Foto = null; // No es carrega foto
    }
    
	 // Generar un número temporal si no se proporciona DNI
    if (empty($DNI)) {
        // Obtener el siguiente ID de la tabla 'socis' para usar como número temporal
        $sql_temp_id = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = 'socis'";
        $result_temp_id = $conn->query($sql_temp_id);
        if ($result_temp_id->num_rows > 0) {
            $row_temp_id = $result_temp_id->fetch_assoc();
            $DNI = $row_temp_id['AUTO_INCREMENT']; // Usar el próximo ID como DNI
        }
    }
	
    // SQL per inserir les dades
    $sql_insert = "INSERT INTO socis (DNI, Nom, Carrer, Codipostal, Poblacio, Provincia, email, Data_naixement, Telefon1, Telefon2, Telefon3, Numero_Conta, Sepa, Activitats, Quantitat, Alta, Baixa, Facial, Data_Inici_activitat, Usuari, Foto, data_modificacio) 
                   VALUES ('$DNI', '$Nom', '$Carrer', '$Codipostal', '$Poblacio', '$Provincia', '$email', '$Data_naixement', '$Telefon1', '$Telefon2', '$Telefon3', '$Numero_Conta', '$Sepa', '$Activitats', '$Quantitat', '$Alta', '$Baixa', '$Facial', '$Data_Inici_activitat', '$Usuari', '$Foto', CURDATE())";

    if ($conn->query($sql_insert) === TRUE) {
        echo "Registre afegit correctament.";
    } else {
        echo "Error: " . $sql_insert . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Afegir socis</title>
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f0f0f0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.form-container {
    width: 100vw; /* Usa el 90% del ancho de la ventana */
    height: auto; /* Altura automática */
    max-width: 210mm; /* Ancho máximo */
    max-height: 100vh; /* Altura máxima */
    background-color: white;
    padding: 20px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden; /* Evita el scroll en el contenedor */
    border-radius: 8px; /* Opcional: añade bordes redondeados */
    display: flex; /* Usa flexbox para organizar elementos internos */
    flex-direction: column; /* Coloca elementos en columna */
}

.form-container label {
    display: inline-block;
    width: 100%; /* Cambiado para que use todo el ancho disponible */
    margin-bottom: 5px; /* Espaciado entre etiquetas */
    text-align: left; /* Alinea a la izquierda */
}
}

.form-container input[type="text"],
.form-container input[type="email"],
.form-container input[type="date"],
.form-container input[type="file"] {
    width: 100%; /* Se adapta al 100% del contenedor */
    max-width: 300px; /* Ancho máximo */
    margin-bottom: 10px;
}

.form-container input[type="checkbox"] {
    margin-left: 10px;
    margin-bottom: 10px;
}

/* Estilos para la caja de actividades */
.activity-box {
    width: 100%; /* Se adapta al 100% del contenedor */
    max-width: 350px; /* Ancho máximo */
    height: 150px; /* Altura fija */
    overflow-y: auto; /* Permite el desplazamiento vertical */
    border: 1px solid #ccc;
    text-align: left;
    padding: 10px;
    margin: 10px 0; /* Espaciado vertical */
}

.activity-box label {
    display: block; /* Asegura que cada actividad esté en su propia línea */
}

.photo-box {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 105px; /* Ancho máximo */
    height: 135px; /* Altura máxima para mantener la relación 3.5:4.5 */
    border: 1px solid #ccc;
    text-align: center;
    background-color: #eaeaea;
    overflow: hidden; /* Oculta cualquier parte de la imagen que exceda el contenedor */
}

.photo-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
}

.buttons-container {
    margin-top: 20px;
    text-align: center;
}

/* Media Queries para pantallas pequeñas */
@media (max-width: 600px) {
    .form-container {
        width: 80vw; /* Aumenta el ancho en pantallas pequeñas */
        height: 80vh; /* Aumenta la altura en pantallas pequeñas */
    }

    .activity-box {
        max-width: 100%; /* Asegura que no exceda el ancho del contenedor */
    }
}


    </style>
    <script>
        function carregarPoblacioProvincia() {
            var cp = document.getElementById('Codipostal').value;

            if (cp) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '?cp=' + cp, true);
                xhr.onload = function() {
                    if (this.status == 200) {
                        var response = JSON.parse(this.responseText);
                        document.getElementById('Poblacio').value = response.Poblacio;
                        document.getElementById('Provincia').value = response.Provincia;
                    }
                };
                xhr.send();
            } else {
                document.getElementById('Poblacio').value = '';
                document.getElementById('Provincia').value = '';
            }
        }

        function mostrarPreviewFoto(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var fotoPreview = document.getElementById('foto-preview');
                    fotoPreview.src = e.target.result;
                    fotoPreview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</head>
<body>

<div class="form-container">
    <h1>Afegir socis</h1>
   <form method="POST" enctype="multipart/form-data">
    <div style="display: flex; flex-direction: column; gap: 10px; max-width: 600px;">

        <div style="display: flex; align-items: center;">
            <label for="DNI" style="width: 150px;">DNI:</label>
            <input type="text" name="DNI" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Nom" style="width: 150px;">Nom:</label>
            <input type="text" name="Nom" required style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Carrer" style="width: 150px;">Carrer:</label>
            <input type="text" name="Carrer" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Codipostal" style="width: 150px;">Codi Postal:</label>
            <input type="text" name="Codipostal" id="Codipostal" onblur="carregarPoblacioProvincia()" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Poblacio" style="width: 150px;">Població:</label>
            <input type="text" name="Poblacio" id="Poblacio" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Provincia" style="width: 150px;">Província:</label>
            <input type="text" name="Provincia" id="Provincia" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="email" style="width: 150px;">Email:</label>
            <input type="email" name="email" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Data_naixement" style="width: 150px;">Data de naixement:</label>
            <input type="date" name="Data_naixement" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Telefon1" style="width: 150px;">Telèfon 1:</label>
            <input type="text" name="Telefon1" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Telefon2" style="width: 150px;">Telèfon 2:</label>
            <input type="text" name="Telefon2" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Telefon3" style="width: 150px;">Telèfon 3:</label>
            <input type="text" name="Telefon3" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Numero_Conta" style="width: 150px;">Número de Compte:</label>
            <input type="text" name="Numero_Conta" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Sepa" style="width: 150px;">SEPA:</label>
            <input type="checkbox" name="Sepa">
        </div>

        <div style="display: flex; align-items: flex-start;">
            <label for="Activitats" style="width: 150px;">Activitats:</label>
            <div class="activity-box" style="flex: 1;">
                <?php if ($result_activitats->num_rows > 0): ?>
                    <?php while($row_activitat = $result_activitats->fetch_assoc()): ?>
                        <label style="display: inline-block; margin-right: 10px;">
                            <input type="checkbox" name="Activitats[]" value="<?php echo $row_activitat['id']; ?>"> 
                            <?php echo $row_activitat['nom']; ?>
                        </label>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Quantitat" style="width: 150px;">Quantitat:</label>
            <input type="text" name="Quantitat" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Alta" style="width: 150px;">Alta:</label>
            <input type="date" name="Alta" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Baixa" style="width: 150px;">Baixa:</label>
            <input type="date" name="Baixa" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Facial" style="width: 150px;">Facial:</label>
            <input type="checkbox" name="Facial">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Data_Inici_activitat" style="width: 150px;">Data d'inici activitat:</label>
            <input type="date" name="Data_Inici_activitat" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Usuari" style="width: 150px;">Usuari:</label>
            <input type="text" name="Usuari" style="flex: 1;">
        </div>

        <div style="display: flex; align-items: center;">
            <label for="Foto" style="width: 150px;">Foto (opcional):</label>
            <input type="file" name="Foto" accept="image/*" onchange="mostrarPreviewFoto(this)" style="flex: 1;">
        </div>

        <div class="photo-box" style="display: flex; align-items: center;">
            <img id="foto-preview" src="" alt="Foto de perfil" style="display: none;">
        </div>

    </div>
        <div class="buttons-container">
            <input type="submit" value="Afegir">
        </div>
    </form>
</div>

</body>
</html>