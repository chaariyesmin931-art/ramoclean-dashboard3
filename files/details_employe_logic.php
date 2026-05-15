<?php require_once("auth.php"); ?>
<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = "";
$error   = "";

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
if ($id === '') { header("Location: employe.php"); exit(); }

/* =============================================
   HANDLE UPDATE
   ============================================= */
if (isset($_POST['update_employe'])) {
    $nom    = mysqli_real_escape_string($conn, trim($_POST['Nom']));
    $prenom = mysqli_real_escape_string($conn, trim($_POST['Prenom']));
    $email  = mysqli_real_escape_string($conn, trim($_POST['Email']));
    $tel    = mysqli_real_escape_string($conn, trim($_POST['NumTel']));

    if ($email === "" || $tel === "") {
        $error = "L'email et le téléphone sont obligatoires.";
    } elseif (strlen($tel) !== 8 || !ctype_digit($tel)) {
        $error = "Le téléphone doit contenir exactement 8 chiffres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        $checkTel = $conn->query("SELECT NumTel FROM employeur WHERE NumTel='$tel' AND Cin!='$id'");
        if ($checkTel->num_rows > 0) {
            $error = "Ce numéro de téléphone est déjà utilisé par un autre employé.";
        } else {
            $sql = "UPDATE employeur SET Nom='$nom', Prenom='$prenom',
                    Email='$email', NumTel='$tel'
                    WHERE Cin='$id'";
            if ($conn->query($sql)) $success = "Employé mis à jour avec succès.";
            else $error = "Erreur : " . $conn->error;
        }
    }
}

/* =============================================
   HANDLE DELETE
   ============================================= */
if (isset($_POST['delete_employe'])) {
    if ($conn->query("DELETE FROM employeur WHERE Cin='$id'")) {
        header("Location: employe.php?success=Employé+supprimé");
        exit();
    } else {
        $error = "Erreur lors de la suppression : " . $conn->error;
    }
}

/* =============================================
   LOAD EMPLOYE DATA
   ============================================= */
$res = $conn->query("SELECT * FROM employeur WHERE Cin='$id'");
if ($res->num_rows === 0) { header("Location: employe.php"); exit(); }
$employe = $res->fetch_assoc();

$conn->close();
?>
