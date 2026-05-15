<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";
$allowed_types = ['kg', 'lit'];

$familleCollection = $db->famille;
$familleMatCollection = $db->famille_mat;
$produitCollection = $db->produit;
$matiereCollection = $db->matiere;

/* =============================================
   HANDLE DELETE CATEGORY
   ============================================= */
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    $countProduits = $produitCollection->countDocuments(['IdFamille' => $delId]);
    if ($countProduits > 0) {
        $error = "Impossible de supprimer : des produits utilisent cette catégorie.";
    } else {
        try {
            $familleCollection->deleteOne(['IdFamille' => $delId]);
            $familleMatCollection->deleteMany(['IdFamille' => $delId]);
            header("Location: categories.php?success=Catégorie+supprimée");
            exit();
        } catch (Exception $e) {
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    }
}

/* =============================================
   HANDLE UPDATE CATEGORY INFO
   ============================================= */
if (isset($_POST['update_famille'])) {
    $editId  = intval($_POST['IdFamille']);
    $nom     = trim($_POST['NomFamille']);
    $typee   = trim($_POST['typee']);
    $arome   = trim($_POST['arome']);
    $tva     = intval($_POST['tva']);

    if ($nom === "") {
        $error = "Le nom de la catégorie est obligatoire.";
    } elseif (!in_array($typee, $allowed_types)) {
        $error = "Type invalide. Valeurs acceptées : " . implode(', ', $allowed_types) . ".";
    } else {
        try {
            $familleCollection->updateOne(
                ['IdFamille' => $editId],
                ['$set' => [
                    'NomFamille' => $nom,
                    'typee' => $typee,
                    'arome' => $arome,
                    'tva' => $tva
                ]]
            );
            $success = "Catégorie mise à jour avec succès.";
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
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
        try {
            /* Upsert */
            $familleMatCollection->updateOne(
                ['IdFamille' => $famId, 'IdMatiere' => $matId],
                ['$set' => ['IdFamille' => $famId, 'IdMatiere' => $matId, 'qte_per_unit' => $qte]],
                ['upsert' => true]
            );
            $success = "Recette mise à jour.";
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

/* =============================================
   HANDLE REMOVE MATIERE FROM RECIPE
   ============================================= */
if (isset($_GET['remove_mat'])) {
    $famId = intval($_GET['fam']);
    $matId = intval($_GET['remove_mat']);
    $familleMatCollection->deleteOne(['IdFamille' => $famId, 'IdMatiere' => $matId]);
    header("Location: categories.php?success=Ingrédient+supprimé+de+la+recette#cat-$famId");
    exit();
}

/* =============================================
   LOAD ALL CATEGORIES WITH RECIPE
   ============================================= */
$resCategories = $familleCollection->find([], ['sort' => ['NomFamille' => 1]]);
$categories = [];
foreach ($resCategories as $c) {
    $cArray = (array) $c;
    $cArray['nb_produits'] = $produitCollection->countDocuments(['IdFamille' => $cArray['IdFamille']]);
    $categories[] = $cArray;
}

/* Load recipes for all categories */
$resRecipes = $familleMatCollection->find([], ['sort' => ['IdFamille' => 1]]);
$recipes = [];
foreach ($resRecipes as $r) {
    $rArray = (array) $r;
    $matInfo = $matiereCollection->findOne(['IdMatiere' => $rArray['IdMatiere'] ?? null]);
    if ($matInfo) {
        $rArray['NomMat'] = $matInfo['NomMat'];
        $rArray['mat_type'] = $matInfo['typee'];
    } else {
        $rArray['NomMat'] = 'Inconnu';
        $rArray['mat_type'] = '';
    }
    $recipes[$rArray['IdFamille']][] = $rArray;
}

/* Load all matieres for recipe add form */
$resMat = $matiereCollection->find([], ['sort' => ['NomMat' => 1]]);
$allMatieres = [];
foreach ($resMat as $m) {
    $allMatieres[] = (array) $m;
}

if (isset($_GET['success'])) $success = htmlspecialchars($_GET['success']);
?>
