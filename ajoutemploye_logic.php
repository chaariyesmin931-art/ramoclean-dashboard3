<?php require_once("auth.php"); ?>
<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = "";
$error   = "";

/* =============================================
   HANDLE NEW EMPLOYE CREATION
   ============================================= */
if (isset($_POST['create_employe'])) {
    $cin    = mysqli_real_escape_string($conn, trim($_POST['Cin']));
    $nom    = mysqli_real_escape_string($conn, trim($_POST['Nom']));
    $prenom = mysqli_real_escape_string($conn, trim($_POST['Prenom']));
    $email  = mysqli_real_escape_string($conn, trim($_POST['Email']));
    $tel    = mysqli_real_escape_string($conn, trim($_POST['NumTel']));

    /* Validation */
    if ($cin === "" || $email === "" || $tel === "") {
        $error = "Le CIN, l'email et le téléphone sont obligatoires.";
    } elseif (strlen($tel) !== 8 || !ctype_digit($tel)) {
        $error = "Le numéro de téléphone doit contenir exactement 8 chiffres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        $checkCin = $conn->query("SELECT Cin FROM employeur WHERE Cin='$cin'");
        if ($checkCin->num_rows > 0) {
            $error = "Un employé avec ce CIN existe déjà.";
        } else {
            $checkTel = $conn->query("SELECT NumTel FROM employeur WHERE NumTel='$tel'");
            if ($checkTel->num_rows > 0) {
                $error = "Ce numéro de téléphone est déjà utilisé par un autre employé.";
            } else {
                $sql = "INSERT INTO employeur (Cin, Nom, Prenom, Email, NumTel)
                        VALUES ('$cin', '$nom', '$prenom', '$email', '$tel')";
                if ($conn->query($sql)) {
                    $success = "Employé « $nom $prenom » créé avec succès !";
                } else {
                    $error = "Erreur lors de la création : " . $conn->error;
                }
            }
        }
    }
}

$conn->close();
?>
