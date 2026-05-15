<?php require_once("auth.php"); ?>
<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = "";
$error   = "";

/* Allowed values for typee — must match your DB CHECK constraint */
$allowed_types = ['kg', 'g', 'L', 'ml', 'u'];

/* =============================================
   HANDLE NEW MATIERE CREATION
   ============================================= */
if (isset($_POST['create_matiere'])) {
    $idMat = intval($_POST['IdMatiere']);
    $nom   = mysqli_real_escape_string($conn, trim($_POST['NomMat']));
    $typee = mysqli_real_escape_string($conn, trim($_POST['typee']));
    $desc  = mysqli_real_escape_string($conn, trim($_POST['descriptionn']));

    if ($idMat <= 0 || $nom === "") {
        $error = "L'ID et le nom de la matière sont obligatoires.";
    } elseif (!in_array($typee, $allowed_types)) {
        $error = "Type invalide. Valeurs acceptées : " . implode(', ', $allowed_types) . ".";
    } else {
        $check = $conn->query("SELECT IdMatiere FROM matiere WHERE IdMatiere=$idMat");
        if ($check->num_rows > 0) {
            $error = "Une matière avec cet ID existe déjà.";
        } else {
            $sql = "INSERT INTO matiere (IdMatiere, NomMat, typee, descriptionn)
                    VALUES ($idMat, '$nom', '$typee', '$desc')";
            if ($conn->query($sql)) {
                $success = "Matière « $nom » créée avec succès !";
            } else {
                /* Catch constraint violation specifically */
                if (strpos($conn->error, 'chk_type') !== false) {
                    $error = "Type invalide pour votre base de données. Valeurs acceptées : " . implode(', ', $allowed_types) . ".";
                } else {
                    $error = "Erreur lors de la création : " . $conn->error;
                }
            }
        }
    }
}

$conn->close();
?>