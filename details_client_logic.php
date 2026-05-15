<?php require_once("auth.php"); ?>
<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = "";
$error   = "";

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
if ($id === '') { header("Location: client.php"); exit(); }

/* =============================================
   HANDLE UPDATE
   ============================================= */
if (isset($_POST['update_client'])) {
    $nom        = mysqli_real_escape_string($conn, trim($_POST['Nom']));
    $prenom     = mysqli_real_escape_string($conn, trim($_POST['Prenom']));
    $entreprise = mysqli_real_escape_string($conn, trim($_POST['NomEntreprise']));
    $email      = mysqli_real_escape_string($conn, trim($_POST['Email']));
    $tel        = mysqli_real_escape_string($conn, trim($_POST['NumTel']));

    if ($entreprise === "" || $email === "" || $tel === "") {
        $error = "L'entreprise, l'email et le téléphone sont obligatoires.";
    } elseif (strlen($tel) !== 8 || !ctype_digit($tel)) {
        $error = "Le téléphone doit contenir exactement 8 chiffres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        $checkTel = $conn->query("SELECT NumTel FROM client WHERE NumTel='$tel' AND MatFis!='$id'");
        if ($checkTel->num_rows > 0) {
            $error = "Ce numéro de téléphone est déjà utilisé par un autre client.";
        } else {
            $sql = "UPDATE client SET Nom='$nom', Prenom='$prenom',
                    NomEntreprise='$entreprise', Email='$email', NumTel='$tel'
                    WHERE MatFis='$id'";
            if ($conn->query($sql)) $success = "Client mis à jour avec succès.";
            else $error = "Erreur : " . $conn->error;
        }
    }
}

/* =============================================
   HANDLE DELETE
   ============================================= */
if (isset($_POST['delete_client'])) {
    if ($conn->query("DELETE FROM client WHERE MatFis='$id'")) {
        header("Location: client.php?success=Client+supprimé");
        exit();
    } else {
        $error = "Erreur lors de la suppression : " . $conn->error;
    }
}

/* =============================================
   LOAD CLIENT DATA
   ============================================= */
$res = $conn->query("SELECT * FROM client WHERE MatFis='$id'");
if ($res->num_rows === 0) { header("Location: client.php"); exit(); }
$client = $res->fetch_assoc();

/* Load factures for this client */
$resFactures = $conn->query("
    SELECT NumFact, datefact, payment
    FROM facture
    WHERE MatFis='$id'
    ORDER BY datefact DESC
");
$factures = [];
while ($f = $resFactures->fetch_assoc()) $factures[] = $f;

$conn->close();
?>
