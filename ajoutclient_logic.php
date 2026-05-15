<?php require_once("auth.php"); ?>
<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = "";
$error   = "";
 
/* =============================================
   HANDLE NEW CLIENT CREATION
   ============================================= */
if (isset($_POST['create_client'])) {
    $matfis    = mysqli_real_escape_string($conn, trim($_POST['MatFis']));
    $nom       = mysqli_real_escape_string($conn, trim($_POST['Nom']));
    $prenom    = mysqli_real_escape_string($conn, trim($_POST['Prenom']));
    $entreprise = mysqli_real_escape_string($conn, trim($_POST['NomEntreprise']));
    $email     = mysqli_real_escape_string($conn, trim($_POST['Email']));
    $tel       = mysqli_real_escape_string($conn, trim($_POST['NumTel']));

    /* Validation */
    if ($matfis === "" || $entreprise === "" || $email === "" || $tel === "") {
        $error = "Le matricule fiscale, l'entreprise, l'email et le téléphone sont obligatoires.";
    } elseif (strlen($tel) !== 8 || !ctype_digit($tel)) {
        $error = "Le numéro de téléphone doit contenir exactement 8 chiffres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        /* Check MatFis uniqueness */
        $check = $conn->query("SELECT MatFis FROM client WHERE MatFis='$matfis'");
        if ($check->num_rows > 0) {
            $error = "Un client avec ce matricule fiscale existe déjà.";
        } else {
            /* Check phone uniqueness */
            $checkTel = $conn->query("SELECT NumTel FROM client WHERE NumTel='$tel'");
            if ($checkTel->num_rows > 0) {
                $error = "Ce numéro de téléphone est déjà utilisé par un autre client.";
            } else {
                $sql = "INSERT INTO client (MatFis, Nom, Prenom, NomEntreprise, Email, NumTel)
                        VALUES ('$matfis', '$nom', '$prenom', '$entreprise', '$email', '$tel')";
                if ($conn->query($sql)) {
                    $success = "Client « $entreprise » créé avec succès !";
                } else {
                    $error = "Erreur lors de la création : " . $conn->error;
                }
            }
        }
    }
}

$conn->close();
?>
