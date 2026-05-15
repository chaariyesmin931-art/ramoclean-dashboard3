<?php require_once("auth.php"); ?>
<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = "";
$error   = "";

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';
if ($id === '') { header("Location: fournisseur.php"); exit(); }

/* =============================================
   HANDLE UPDATE
   ============================================= */
if (isset($_POST['update_fournisseur'])) {
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
        $checkTel = $conn->query("SELECT NumTel FROM fournisseur WHERE NumTel='$tel' AND Mat!='$id'");
        if ($checkTel->num_rows > 0) {
            $error = "Ce numéro de téléphone est déjà utilisé par un autre fournisseur.";
        } else {
            $sql = "UPDATE fournisseur SET Nom='$nom', Prenom='$prenom',
                    NomEntreprise='$entreprise', Email='$email', NumTel='$tel'
                    WHERE Mat='$id'";
            if ($conn->query($sql)) $success = "Fournisseur mis à jour avec succès.";
            else $error = "Erreur : " . $conn->error;
        }
    }
}

/* =============================================
   HANDLE DELETE
   ============================================= */
if (isset($_POST['delete_fournisseur'])) {
    if ($conn->query("DELETE FROM fournisseur WHERE Mat='$id'")) {
        header("Location: fournisseur.php?success=Fournisseur+supprimé");
        exit();
    } else {
        $error = "Erreur lors de la suppression : " . $conn->error;
    }
}

/* =============================================
   LOAD FOURNISSEUR + STOCK MATIERES
   ============================================= */
$res = $conn->query("SELECT * FROM fournisseur WHERE Mat='$id'");
if ($res->num_rows === 0) { header("Location: fournisseur.php"); exit(); }
$fournisseur = $res->fetch_assoc();

/* Matieres supplied by this fournisseur (via stock_matiere) */
$resMatieres = $conn->query("
    SELECT stock_matiere.idsm, stock_matiere.qte,
           matiere.NomMat, matiere.typee
    FROM stock_matiere
    JOIN matiere ON stock_matiere.IdMatiere = matiere.IdMatiere
    WHERE stock_matiere.Mat = '$id'
    ORDER BY matiere.NomMat
");
$matieres = [];
while ($m = $resMatieres->fetch_assoc()) $matieres[] = $m;

$conn->close();
?>
