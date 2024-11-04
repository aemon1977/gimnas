<?php
// Connexió a la base de dades
$servername = "localhost";
$username = "root";
$database = "gimnas";

// Crear la connexió
$conn = new mysqli($servername, $username, "", $database);

// Verificar la connexió
if ($conn->connect_error) {
    die("Connexió fallida: " . $conn->connect_error);
}

// Eliminar activitat si s'ha rebut la sol·licitud d'eliminació
if (isset($_GET['eliminar_id'])) {
    $eliminar_id = $_GET['eliminar_id'];
    $sql = "DELETE FROM activitats WHERE id = $eliminar_id";
    if ($conn->query($sql) === TRUE) {
        echo "Activitat eliminada amb èxit.";
    } else {
        echo "Error a l'eliminar l'activitat: " . $conn->error;
    }
}

// Si s'ha enviat el formulari per afegir
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];

    // Afegir nova activitat
    $sql = "INSERT INTO activitats (nom) VALUES ('$nom')";
    if ($conn->query($sql) === TRUE) {
        echo "Nova activitat afegida amb èxit.";
    } else {
        echo "Error a l'afegir: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Activitats</title>
</head>
<body>
    <h2>Afegeix Activitat</h2>

    <!-- Formulari per afegir nova activitat -->
    <form action="activitats.php" method="POST">
        <label for="nom">Nom de l'activitat:</label>
        <input type="text" name="nom" required>
        
        <br><br>
        <button type="submit">Desa</button>
    </form>

    <h2>Llista d'Activitats</h2>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Accions</th>
        </tr>
        
        <?php
        // Mostrar la llista d'activitats
        $sql = "SELECT * FROM activitats";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['nom'] . "</td>";
                echo "<td>
                        <a href='activitats.php?eliminar_id=" . $row['id'] . "' onclick=\"return confirm('Estàs segur que vols eliminar aquesta activitat?');\">Eliminar</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No hi ha activitats disponibles</td></tr>";
        }
        ?>
    </table>
</body>
</html>

<?php
// Tancar la connexió
$conn->close();
?>
