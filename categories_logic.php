<?php require_once("auth.php"); ?>
<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = "";
$error   = "";
$allowed_types = ['kg', 'lit'];

/* =============================================
   HANDLE DELETE CATEGORY
   ============================================= */
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    if ($conn->query("DELETE FROM famille WHERE IdFamille=$delId")) {
        header("Location: categories.php?success=Catégorie+supprimée");
        exit();
    } else {
        $error = "Impossible de supprimer : des produits utilisent cette catégorie.";
    }
}

/* =============================================
   HANDLE UPDATE CATEGORY INFO
   ============================================= */
if (isset($_POST['update_famille'])) {
    $editId  = intval($_POST['IdFamille']);
    $nom     = mysqli_real_escape_string($conn, trim($_POST['NomFamille']));
    $typee   = mysqli_real_escape_string($conn, trim($_POST['typee']));
    $arome   = mysqli_real_escape_string($conn, trim($_POST['arome']));
    $tva     = intval($_POST['tva']);

    if ($nom === "") {
        $error = "Le nom de la catégorie est obligatoire.";
    } elseif (!in_array($typee, $allowed_types)) {
        $error = "Type invalide. Valeurs acceptées : " . implode(', ', $allowed_types) . ".";
    } else {
        $sql = "UPDATE famille SET NomFamille='$nom', typee='$typee', arome='$arome', tva=$tva
                WHERE IdFamille=$editId";
        if ($conn->query($sql)) $success = "Catégorie mise à jour avec succès.";
        else $error = "Erreur : " . $conn->error;
    }
}
 
/* =============================================
   HANDLE ADD MATIERE TO RECIPE (famille_mat)
   ============================================= */
if (isset($_POST['add_recipe_mat'])) {
    $famId  = intval($_POST['recipe_IdFamille']);
    $matId  = intval($_POST['recipe_IdMatiere']);
    $qte    = floatval($_POST['recipe_qte']);

    if ($matId <= 0 || $qte <= 0) {
        $error = "Veuillez choisir une matière et une quantité valide.";
    } else {
        /* Upsert */
        $check = $conn->query("SELECT * FROM famille_mat WHERE IdFamille=$famId AND IdMatiere=$matId");
        if ($check->num_rows > 0) {
            $conn->query("UPDATE famille_mat SET qte_per_unit=$qte WHERE IdFamille=$famId AND IdMatiere=$matId");
        } else {
            $conn->query("INSERT INTO famille_mat (IdFamille, IdMatiere, qte_per_unit) VALUES ($famId, $matId, $qte)");
        }
        $success = "Recette mise à jour.";
    }
}

/* =============================================
   HANDLE REMOVE MATIERE FROM RECIPE
   ============================================= */
if (isset($_GET['remove_mat'])) {
    $famId = intval($_GET['fam']);
    $matId = intval($_GET['remove_mat']);
    $conn->query("DELETE FROM famille_mat WHERE IdFamille=$famId AND IdMatiere=$matId");
    header("Location: categories.php?success=Ingrédient+supprimé+de+la+recette#cat-$famId");
    exit();
}

/* =============================================
   LOAD ALL CATEGORIES WITH RECIPE
   ============================================= */
$resCategories = $conn->query("
    SELECT famille.*,
           COUNT(DISTINCT produit.IdProduit) AS nb_produits
    FROM famille
    LEFT JOIN produit ON famille.IdFamille = produit.IdFamille
    GROUP BY famille.IdFamille
    ORDER BY famille.NomFamille
");
$categories = [];
while ($c = $resCategories->fetch_assoc()) $categories[] = $c;

/* Load recipes for all categories */
$resRecipes = $conn->query("
    SELECT famille_mat.IdFamille, famille_mat.IdMatiere,
           famille_mat.qte_per_unit, matiere.NomMat, matiere.typee AS mat_type
    FROM famille_mat
    JOIN matiere ON famille_mat.IdMatiere = matiere.IdMatiere
    ORDER BY famille_mat.IdFamille, matiere.NomMat
");
$recipes = [];
while ($r = $resRecipes->fetch_assoc()) {
    $recipes[$r['IdFamille']][] = $r;
}

/* Load all matieres for recipe add form */
$allMatieres = [];
$resMat = $conn->query("SELECT IdMatiere, NomMat, typee FROM matiere ORDER BY NomMat");
while ($m = $resMat->fetch_assoc()) $allMatieres[] = $m;

if (isset($_GET['success'])) $success = htmlspecialchars($_GET['success']);

$conn->close();
?>
