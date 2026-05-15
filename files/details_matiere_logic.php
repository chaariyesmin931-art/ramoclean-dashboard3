<?php require_once("auth.php"); ?>
<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = "";
$error   = "";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header("Location: matiere.php"); exit(); }

$allowed_types = ['kg', 'lit'];

/* =============================================
   HANDLE UPDATE MATIERE
   ============================================= */
if (isset($_POST['update_matiere'])) {
    $nom   = mysqli_real_escape_string($conn, trim($_POST['NomMat']));
    $typee = mysqli_real_escape_string($conn, trim($_POST['typee']));
    $desc  = mysqli_real_escape_string($conn, trim($_POST['descriptionn']));

    if ($nom === "") {
        $error = "Le nom de la matière est obligatoire.";
    } elseif (!in_array($typee, $allowed_types)) {
        $error = "Type invalide. Valeurs acceptées : " . implode(', ', $allowed_types) . ".";
    } else {
        $sql = "UPDATE matiere SET NomMat='$nom', typee='$typee', descriptionn='$desc'
                WHERE IdMatiere=$id";
        if ($conn->query($sql)) $success = "Matière mise à jour avec succès.";
        else $error = "Erreur : " . $conn->error;
    }
}

/* =============================================
   HANDLE DELETE MATIERE
   ============================================= */
if (isset($_POST['delete_matiere'])) {
    if ($conn->query("DELETE FROM matiere WHERE IdMatiere=$id")) {
        header("Location: matiere.php?success=Matière+supprimée");
        exit();
    } else {
        $error = "Erreur lors de la suppression (vérifiez qu'aucun produit n'utilise cette matière) : " . $conn->error;
    }
}

/* =============================================
   HANDLE CREATE FOURNISSEUR (inline)
   ============================================= */
if (isset($_POST['create_fournisseur'])) {
    $mat        = mysqli_real_escape_string($conn, trim($_POST['new_Mat']));
    $nom        = mysqli_real_escape_string($conn, trim($_POST['new_Nom']));
    $prenom     = mysqli_real_escape_string($conn, trim($_POST['new_Prenom']));
    $entreprise = mysqli_real_escape_string($conn, trim($_POST['new_NomEntreprise']));
    $email      = mysqli_real_escape_string($conn, trim($_POST['new_Email']));
    $tel        = mysqli_real_escape_string($conn, trim($_POST['new_NumTel']));

    if ($mat === "" || $entreprise === "" || $email === "" || $tel === "") {
        $error = "Matricule, entreprise, email et téléphone sont obligatoires.";
    } elseif (strlen($tel) !== 8 || !ctype_digit($tel)) {
        $error = "Le téléphone doit contenir exactement 8 chiffres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        $checkMat = $conn->query("SELECT Mat FROM fournisseur WHERE Mat='$mat'");
        $checkTel = $conn->query("SELECT NumTel FROM fournisseur WHERE NumTel='$tel'");
        if ($checkMat->num_rows > 0) {
            $error = "Un fournisseur avec ce matricule existe déjà.";
        } elseif ($checkTel->num_rows > 0) {
            $error = "Ce numéro de téléphone est déjà utilisé.";
        } else {
            $sql = "INSERT INTO fournisseur (Mat, Nom, Prenom, NomEntreprise, Email, NumTel)
                    VALUES ('$mat', '$nom', '$prenom', '$entreprise', '$email', '$tel')";
            if ($conn->query($sql)) {
                $success = "Fournisseur « $entreprise » créé. Vous pouvez maintenant le sélectionner.";
            } else {
                $error = "Erreur : " . $conn->error;
            }
        }
    }
}

/* =============================================
   HANDLE ADD STOCK
   ============================================= */
if (isset($_POST['add_stock'])) {
    $mat = mysqli_real_escape_string($conn, trim($_POST['Mat']));
    $qte = intval($_POST['qte_add']);

    if ($mat === "") {
        $error = "Veuillez sélectionner un fournisseur.";
    } elseif ($qte <= 0) {
        $error = "La quantité doit être supérieure à 0.";
    } else {
        /* Check fournisseur exists */
        $checkFn = $conn->query("SELECT Mat FROM fournisseur WHERE Mat='$mat'");
        if ($checkFn->num_rows === 0) {
            $error = "Fournisseur introuvable.";
        } else {
            $sql = "INSERT INTO stock_matiere (IdMatiere, Mat, qte)
                    VALUES ($id, '$mat', $qte)";
            if ($conn->query($sql)) {
                $success = "$qte unité(s) ajoutée(s) au stock avec succès.";
            } else {
                $error = "Erreur lors de l'ajout : " . $conn->error;
            }
        }
    }
}

/* =============================================
   HANDLE DELETE STOCK ROW
   ============================================= */
if (isset($_GET['delete_stock'])) {
    $idsm = intval($_GET['delete_stock']);
    $conn->query("DELETE FROM stock_matiere WHERE idsm=$idsm AND IdMatiere=$id");
    header("Location: details_matiere.php?id=$id&success=Entrée+de+stock+supprimée");
    exit();
}

/* =============================================
   LOAD ALL DATA
   ============================================= */
$res = $conn->query("SELECT * FROM matiere WHERE IdMatiere=$id");
if ($res->num_rows === 0) { header("Location: matiere.php"); exit(); }
$matiere = $res->fetch_assoc();

$resStock = $conn->query("SELECT SUM(qte) as total_stock FROM stock_matiere WHERE IdMatiere=$id");
$totalStock = $resStock->fetch_assoc()['total_stock'] ?? 0;

$resProduits = $conn->query("
    SELECT produit.IdProduit, produit.NomProduit, prodmat.qte AS qte_needed
    FROM prodmat
    JOIN produit ON prodmat.IdProduit = produit.IdProduit
    WHERE prodmat.IdMatiere = $id
    ORDER BY produit.NomProduit
");
$produits = [];
while ($p = $resProduits->fetch_assoc()) $produits[] = $p;

$resFamilles = $conn->query("
    SELECT famille.IdFamille, famille.NomFamille, famille_mat.qte_per_unit
    FROM famille_mat
    JOIN famille ON famille_mat.IdFamille = famille.IdFamille
    WHERE famille_mat.IdMatiere = $id
    ORDER BY famille.NomFamille
");
$familles = [];
while ($f = $resFamilles->fetch_assoc()) $familles[] = $f;

$resStockRows = $conn->query("
    SELECT stock_matiere.idsm, stock_matiere.qte,
           fournisseur.NomEntreprise, fournisseur.Mat
    FROM stock_matiere
    LEFT JOIN fournisseur ON stock_matiere.Mat = fournisseur.Mat
    WHERE stock_matiere.IdMatiere = $id
    ORDER BY stock_matiere.idsm DESC
");
$stockRows = [];
while ($s = $resStockRows->fetch_assoc()) $stockRows[] = $s;

/* All fournisseurs for select */
$resFournisseurs = $conn->query("SELECT Mat, NomEntreprise FROM fournisseur ORDER BY NomEntreprise");
$allFournisseurs = [];
while ($f = $resFournisseurs->fetch_assoc()) $allFournisseurs[] = $f;

/* Pass along redirect success */
if (isset($_GET['success'])) $success = htmlspecialchars($_GET['success']);

$conn->close();
?>