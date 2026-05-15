<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";

$allowed_types = ['kg', 'lit'];

/* =============================================
   HANDLE NEW PRODUCT CREATION
   ============================================= */
if (isset($_POST['create_produit'])) {
    $idProd = intval($_POST['IdProduit']);
    $idFam  = intval($_POST['IdFamille']);
    $nom    = trim($_POST['NomProduit']);
    $poid   = floatval($_POST['poid']);
    $prix   = floatval($_POST['PrixUnit']);

    $mat_ids  = isset($_POST['mat_id'])  ? $_POST['mat_id']  : [];
    $mat_qtes = isset($_POST['mat_qte']) ? $_POST['mat_qte'] : [];

    $ingredients = [];
    for ($i = 0; $i < count($mat_ids); $i++) {
        $mid  = intval($mat_ids[$i]);
        $mqte = floatval($mat_qtes[$i]);
        if ($mid > 0 && $mqte > 0) {
            $ingredients[] = ['id' => $mid, 'qte' => $mqte];
        }
    }

    if ($idProd <= 0 || $idFam <= 0 || $nom === "" || $poid <= 0 || $prix <= 0) {
        $error = "Tous les champs du produit sont obligatoires.";
    } elseif (empty($ingredients)) {
        $error = "La recette ne peut pas être vide.";
    } else {
        if (mongoExists($produits, 'IdProduit', $idProd)) {
            $error = "Un produit avec cet ID existe déjà.";
        } else {
            try {
                $produitDoc = [
                    'IdProduit' => $idProd,
                    'IdFamille' => $idFam,
                    'NomProduit' => $nom,
                    'poid' => $poid,
                    'PrixUnit' => $prix,
                    'ingredients' => $ingredients,
                    'created_at' => new MongoDB\BSON\UTCDateTime()
                ];
                mongoInsert($produits, $produitDoc);
                $success = "Produit « $nom » créé avec succès avec " . count($ingredients) . " ingrédient(s) !";
            } catch (Exception $e) {
                $error = "Erreur lors de la création : " . $e->getMessage();
            }
        }
    }
}

/* =============================================
   HANDLE INLINE CATEGORY CREATION
   ============================================= */
if (isset($_POST['create_famille'])) {
    $idFam  = intval($_POST['new_IdFamille']);
    $nomFam = trim($_POST['new_NomFamille']);
    $typee  = trim($_POST['new_typee']);
    $arome  = trim($_POST['new_arome']);
    $tva    = intval($_POST['new_tva']);

    if ($idFam <= 0 || $nomFam === "") {
        $error = "L'ID et le nom de la catégorie sont obligatoires.";
    } elseif (!in_array($typee, $allowed_types)) {
        $error = "Type invalide. Valeurs acceptées : " . implode(', ', $allowed_types) . ".";
    } else {
        if (mongoExists($familles, 'IdFamille', $idFam)) {
            $error = "Une catégorie avec cet ID existe déjà.";
        } else {
            try {
                $familleDoc = [
                    'IdFamille' => $idFam,
                    'NomFamille' => $nomFam,
                    'typee' => $typee,
                    'arome' => $arome,
                    'tva' => $tva,
                    'created_at' => new MongoDB\BSON\UTCDateTime()
                ];
                mongoInsert($familles, $familleDoc);
                $success = "Catégorie « $nomFam » créée ! Vous pouvez maintenant la sélectionner.";
            } catch (Exception $e) {
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}

/* =============================================
   LOAD DATA FOR FORM
   ============================================= */
$famillesList = [];
try {
    $famillesResult = mongoFindAll($familles, []);
    foreach ($famillesResult as $f) {
        $famillesList[] = $f;
    }
} catch (Exception $e) {
    $error = "Erreur lors du chargement des catégories : " . $e->getMessage();
}

$allMatieres = [];
try {
    $matieresResult = mongoFindAll($matieres, []);
    foreach ($matieresResult as $m) {
        $allMatieres[] = $m;
    }
} catch (Exception $e) {
    $error = "Erreur lors du chargement des matières : " . $e->getMessage();
}

/* Load famille_mat base recipes for JS auto-calculation */
$familleRecipes = [];
try {
    $famillesRecipesResult = mongoFindAll($familles, ['base_recipes' => ['$exists' => true]]);
    foreach ($famillesRecipesResult as $f) {
        if (isset($f['base_recipes'])) {
            $familleRecipes[$f['IdFamille']] = $f['base_recipes'];
        }
    }
} catch (Exception $e) {
    // Silently handle if recipes don't exist
}

// Use the loaded familles list for compatibility
$familles = $famillesList;
?>

