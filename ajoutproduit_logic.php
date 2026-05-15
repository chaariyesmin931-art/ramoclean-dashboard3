<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";

$allowed_types = ['kg', 'lit'];

$produitCollection = $db->produit;
$familleCollection = $db->famille;
$prodmatCollection = $db->prodmat;
$matiereCollection = $db->matiere;
$familleMatCollection = $db->famille_mat;

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
        $check = $produitCollection->countDocuments(['IdProduit' => $idProd]);
        if ($check > 0) {
            $error = "Un produit avec cet ID existe déjà.";
        } else {
            try {
                $produitCollection->insertOne([
                    'IdProduit' => $idProd,
                    'IdFamille' => $idFam,
                    'NomProduit' => $nom,
                    'poid' => $poid,
                    'PrixUnit' => $prix
                ]);
                foreach ($ingredients as $ing) {
                    $prodmatCollection->insertOne([
                        'IdProduit' => $idProd,
                        'IdMatiere' => $ing['id'],
                        'qte' => $ing['qte']
                    ]);
                }
                $success = "Produit « $nom » créé avec succès avec " . count($ingredients) . " ingrédient(s) !";
            } catch (Exception $e) {
                // simple manual rollback
                $produitCollection->deleteOne(['IdProduit' => $idProd]);
                $prodmatCollection->deleteMany(['IdProduit' => $idProd]);
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
        $check = $familleCollection->countDocuments(['IdFamille' => $idFam]);
        if ($check > 0) {
            $error = "Une catégorie avec cet ID existe déjà.";
        } else {
            try {
                $familleCollection->insertOne([
                    'IdFamille' => $idFam,
                    'NomFamille' => $nomFam,
                    'typee' => $typee,
                    'arome' => $arome,
                    'tva' => $tva
                ]);
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
$familles = [];
$resFam = $familleCollection->find([], ['sort' => ['NomFamille' => 1]]);
foreach ($resFam as $f) $familles[] = (array) $f;

$allMatieres = [];
$resMat = $matiereCollection->find([], ['sort' => ['NomMat' => 1]]);
foreach ($resMat as $m) $allMatieres[] = (array) $m;

/* Load famille_mat base recipes for JS auto-calculation */
$familleRecipes = [];
$resRec = $familleMatCollection->find([], ['sort' => ['IdFamille' => 1]]);
foreach ($resRec as $r) {
    $rArray = (array) $r;
    $mat = $matiereCollection->findOne(['IdMatiere' => $rArray['IdMatiere']]);
    if ($mat) {
        $rArray['NomMat'] = $mat['NomMat'];
        $rArray['mat_type'] = $mat['typee'];
    }
    $familleRecipes[$rArray['IdFamille']][] = $rArray;
}
?>
