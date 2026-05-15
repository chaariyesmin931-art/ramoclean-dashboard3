<?php require_once("auth.php"); ?>
<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = "";
$error   = "";

/* =============================================
   HANDLE NEW FOURNISSEUR CREATION
   ============================================= */
if (isset($_POST['create_fournisseur'])) {
    $mat        = mysqli_real_escape_string($conn, trim($_POST['Mat']));
    $nom        = mysqli_real_escape_string($conn, trim($_POST['Nom']));
    $prenom     = mysqli_real_escape_string($conn, trim($_POST['Prenom']));
    $entreprise = mysqli_real_escape_string($conn, trim($_POST['NomEntreprise']));
    $email      = mysqli_real_escape_string($conn, trim($_POST['Email']));
    $tel        = mysqli_real_escape_string($conn, trim($_POST['NumTel']));

    /* Validation */
    if ($mat === "" || $entreprise === "" || $email === "" || $tel === "") {
        $error = "Le matricule, l'entreprise, l'email et le téléphone sont obligatoires.";
    } elseif (strlen($tel) !== 8 || !ctype_digit($tel)) {
        $error = "Le numéro de téléphone doit contenir exactement 8 chiffres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        $checkMat = $conn->query("SELECT Mat FROM fournisseur WHERE Mat='$mat'");
        if ($checkMat->num_rows > 0) {
            $error = "Un fournisseur avec ce matricule existe déjà.";
        } else {
            $checkTel = $conn->query("SELECT NumTel FROM fournisseur WHERE NumTel='$tel'");
            if ($checkTel->num_rows > 0) {
                $error = "Ce numéro de téléphone est déjà utilisé par un autre fournisseur.";
            } else {
                $sql = "INSERT INTO fournisseur (Mat, Nom, Prenom, NomEntreprise, Email, NumTel)
                        VALUES ('$mat', '$nom', '$prenom', '$entreprise', '$email', '$tel')";
                if ($conn->query($sql)) {
                    $success = "Fournisseur « $entreprise » créé avec succès !";
                } else {
                    $error = "Erreur lors de la création : " . $conn->error;
                }
            }
        }
    }
}

$conn->close();
?>
