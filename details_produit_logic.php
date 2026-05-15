<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";
$warning = "";

/* Get product ID from URL */
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header("Location: produit.php"); exit(); }

$produitCollection = $db->produit;
$familleCollection = $db->famille;
$prodmatCollection = $db->prodmat;
$matiereCollection = $db->matiere;
$stockMatiereCollection = $db->stock_matiere;
$stockProduitCollection = $db->stock_produit;

/* =============================================
   ADD MATIERE TO PRODUCT RECIPE (prodmat)
   ============================================= */
if (isset($_POST['add_matiere'])) {
    $idMat = intval($_POST['IdMatiere']);
    $qte   = intval($_POST['qte_needed']);

    if ($idMat <= 0 || $qte <= 0) {
        $error = "Veuillez sélectionner une matière et une quantité valide.";
    } else {
        try {
            $prodmatCollection->updateOne(
                ['IdProduit' => $id, 'IdMatiere' => $idMat],
                ['$set' => ['IdProduit' => $id, 'IdMatiere' => $idMat, 'qte' => $qte]],
                ['upsert' => true]
            );
            $success = "Matière ajoutée/mise à jour dans la recette.";
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

/* =============================================
   REMOVE MATIERE FROM RECIPE
   ============================================= */
if (isset($_GET['remove_mat'])) {
    $idMat = intval($_GET['remove_mat']);
    $prodmatCollection->deleteOne(['IdProduit' => $id, 'IdMatiere' => $idMat]);
    header("Location: details_produit.php?id=$id&success=Matière+supprimée+de+la+recette");
    exit();
}

/* =============================================
   PRODUCE STOCK
   ============================================= */
if (isset($_POST['produce'])) {
    $qte_prod  = intval($_POST['qte_produce']);
    $force     = isset($_POST['force_produce']); /* ignore stock warning */

    if ($qte_prod <= 0) {
        $error = "La quantité à produire doit être supérieure à 0.";
    } else {
        /* Load recipe */
        $recipe = [];
        $resRecipe = $prodmatCollection->find(['IdProduit' => $id]);
        foreach ($resRecipe as $r) {
            $rArray = (array) $r;
            $matInfo = $matiereCollection->findOne(['IdMatiere' => $rArray['IdMatiere']]);
            $rArray['NomMat'] = $matInfo ? $matInfo['NomMat'] : 'Inconnu';
            $rArray['needed'] = $rArray['qte'];
            
            // Get stock for this matiere
            $stockCursor = $stockMatiereCollection->find(['IdMatiere' => $rArray['IdMatiere']]);
            $totalStock = 0;
            foreach ($stockCursor as $sc) {
                $totalStock += $sc['qte'] ?? 0;
            }
            $rArray['stock'] = $totalStock;
            
            $recipe[] = $rArray;
        }

        /* Check if enough stock for each material */
        $shortages = [];
        foreach ($recipe as $r) {
            $required = $r['needed'] * $qte_prod;
            if ($r['stock'] < $required) {
                $shortages[] = "« {$r['NomMat']} » : besoin {$required}, disponible {$r['stock']}";
            }
        }

        if (!empty($shortages) && !$force) {
            /* Show warning — user can force */
            $warning = implode("<br>", $shortages);
        } else {
            /* Proceed with production */
            try {
                /* Subtract materials from stock */
                foreach ($recipe as $r) {
                    $required = $r['needed'] * $qte_prod;
                    $idMat = $r['IdMatiere'];

                    /* Get all stock rows for this material ordered oldest first */
                    $stockRows = $stockMatiereCollection->find(['IdMatiere' => $idMat], ['sort' => ['idsm' => 1]]);
                    $toSubtract = $required;

                    foreach ($stockRows as $row) {
                        if ($toSubtract <= 0) break;
                        
                        $rowArray = (array) $row;
                        if ($rowArray['qte'] <= $toSubtract) {
                            $toSubtract -= $rowArray['qte'];
                            $stockMatiereCollection->deleteOne(['idsm' => $rowArray['idsm']]);
                        } else {
                            $newQte = $rowArray['qte'] - $toSubtract;
                            $stockMatiereCollection->updateOne(['idsm' => $rowArray['idsm']], ['$set' => ['qte' => $newQte]]);
                            $toSubtract = 0;
                        }
                    }
                }

                /* Add to stock_produit */
                $existing = $stockProduitCollection->findOne(['IdProduit' => $id]);
                if ($existing) {
                    $existingArray = (array) $existing;
                    $newQte = ($existingArray['qte'] ?? 0) + $qte_prod;
                    // Provide a default empty query to ensure updateOne signature matches if idsp is missing
                    $query = isset($existingArray['idsp']) ? ['idsp' => $existingArray['idsp']] : ['IdProduit' => $id];
                    $stockProduitCollection->updateOne($query, ['$set' => ['qte' => $newQte]]);
                } else {
                    /* Get IdFamille for this product */
                    $prod = $produitCollection->findOne(['IdProduit' => $id]);
                    $famId = $prod ? $prod['IdFamille'] : 0;
                    $idsp = time() + rand(1, 1000); // Generate unique idsp
                    $stockProduitCollection->insertOne([
                        'idsp' => $idsp,
                        'IdProduit' => $id,
                        'IdFamille' => $famId,
                        'qte' => $qte_prod
                    ]);
                }

                $success = "$qte_prod unité(s) produite(s) et ajoutée(s) au stock avec succès.";

            } catch (Exception $e) {
                $error = "Erreur lors de la production : " . $e->getMessage();
            }
        }
    }
}

/* =============================================
   LOAD PRODUCT DATA
   ============================================= */
$produit = $produitCollection->findOne(['IdProduit' => $id]);
if (!$produit) { header("Location: produit.php"); exit(); }
$produit = (array) $produit;

// Get Famille
$famInfo = $familleCollection->findOne(['IdFamille' => $produit['IdFamille']]);
if ($famInfo) {
    $produit['NomFamille'] = $famInfo['NomFamille'];
    $produit['typee'] = $famInfo['typee'];
    $produit['arome'] = $famInfo['arome'];
    $produit['tva'] = $famInfo['tva'];
} else {
    $produit['NomFamille'] = 'Inconnue';
    $produit['typee'] = '';
    $produit['arome'] = '';
    $produit['tva'] = 0;
}

// Get Stock
$stockDocs = $stockProduitCollection->find(['IdProduit' => $id]);
$totalStock = 0;
foreach ($stockDocs as $sd) {
    $totalStock += $sd['qte'] ?? 0;
}
$produit['stock_total'] = $totalStock;

/* Load recipe (materials needed) */
$materiaux = [];
$resMatNeeded = $prodmatCollection->find(['IdProduit' => $id]);
foreach ($resMatNeeded as $pm) {
    $pmArray = (array) $pm;
    $matInfo = $matiereCollection->findOne(['IdMatiere' => $pmArray['IdMatiere']]);
    if ($matInfo) {
        $pmArray['NomMat'] = $matInfo['NomMat'];
        $pmArray['mat_type'] = $matInfo['typee'];
    } else {
        $pmArray['NomMat'] = 'Inconnu';
        $pmArray['mat_type'] = '';
    }
    
    // Get Stock for this material
    $scursor = $stockMatiereCollection->find(['IdMatiere' => $pmArray['IdMatiere']]);
    $mstock = 0;
    foreach ($scursor as $sc) {
        $mstock += $sc['qte'] ?? 0;
    }
    $pmArray['mat_stock'] = $mstock;
    $pmArray['needed'] = $pmArray['qte'];
    
    $materiaux[] = $pmArray;
}

/* Load all available matieres for the add form */
$allMatieres = [];
$resAllMat = $matiereCollection->find([], ['sort' => ['NomMat' => 1]]);
foreach ($resAllMat as $m) $allMatieres[] = (array) $m;

/* Max producible based on current stock */
$maxProducible = PHP_INT_MAX;
foreach ($materiaux as $m) {
    if (($m['needed'] ?? 0) > 0) {
        $maxProducible = min($maxProducible, floor(($m['mat_stock'] ?? 0) / $m['needed']));
    }
}
if ($maxProducible === PHP_INT_MAX) $maxProducible = 0;

/* Pass along success from redirect */
if (isset($_GET['success'])) $success = htmlspecialchars($_GET['success']);

?>
