<?php
require_once("connexion.php");

$success  = "";
$error    = "";
$warning  = "";           /* stock shortage warning */
$shortages = [];          /* list of shortage details */

$allowed_types = ['fact', 'bdl', 'devis'];

$clientCollection = $db->client;
$factureCollection = $db->facture;
$prodfactCollection = $db->prodfact;
$produitCollection = $db->produit;
$stockProduitCollection = $db->stock_produit;
$familleCollection = $db->famille;
$countersCollection = $db->counters; // to simulate auto_increment for NumFact

/* =============================================
   HANDLE CREATION
   ============================================= */
if (isset($_POST['create_facture'])) {
    $matfis  = trim($_POST['MatFis']);
    $date    = trim($_POST['datefact']);
    $type    = in_array($_POST['TypeFact'], $allowed_types) ? $_POST['TypeFact'] : 'fact';
    $force   = isset($_POST['force_create']); /* user confirmed despite shortage */
    $payment = 0;
  
    $prod_ids  = isset($_POST['prod_id'])  ? $_POST['prod_id']  : [];
    $prod_qtes = isset($_POST['prod_qte']) ? $_POST['prod_qte'] : [];

    /* Merge duplicate products */
    $lignesMap = [];
    for ($i = 0; $i < count($prod_ids); $i++) {
        $pid = intval($prod_ids[$i]);
        $qte = intval($prod_qtes[$i]);
        if ($pid > 0 && $qte > 0)
            $lignesMap[$pid] = isset($lignesMap[$pid]) ? $lignesMap[$pid] + $qte : $qte;
    }
    $lignes = [];
    foreach ($lignesMap as $pid => $qte) $lignes[] = ['id' => $pid, 'qte' => $qte];

    if ($matfis === "") {
        $error = "Veuillez sélectionner un client.";
    } elseif ($date === "") {
        $error = "La date est obligatoire.";
    } elseif (empty($lignes)) {
        $error = "Ajoutez au moins un produit.";
    } else {
        $checkClient = $clientCollection->countDocuments(['MatFis' => $matfis]);
        if ($checkClient === 0) {
            $error = "Client introuvable.";
        } else {

            /* ---- Stock check (only for facture) ---- */
            if ($type === 'fact' && !$force) {
                foreach ($lignes as $l) {
                    $pid = $l['id'];
                    $qteNeeded = $l['qte'];

                    $prodInfo = $produitCollection->findOne(['IdProduit' => $pid]);
                    $nomProduit = $prodInfo ? $prodInfo['NomProduit'] : "Produit #$pid";
                    
                    $stockDocs = $stockProduitCollection->find(['IdProduit' => $pid]);
                    $stockDispo = 0;
                    foreach ($stockDocs as $sd) {
                        $stockDispo += $sd['qte'] ?? 0;
                    }

                    if ($stockDispo < $qteNeeded) {
                        $shortages[] = [
                            'nom'      => $nomProduit,
                            'needed'   => $qteNeeded,
                            'dispo'    => $stockDispo,
                            'manque'   => $qteNeeded - $stockDispo,
                        ];
                    }
                }

                /* If shortages found — stop and show warning */
                if (!empty($shortages)) {
                    $warning = "Stock insuffisant pour certains produits.";
                    /* Don't proceed — let the view show the warning */
                    goto end_of_logic;
                }
            }

            /* ---- Proceed with creation ---- */
            try {
                // Generate next sequence for NumFact
                $counter = $countersCollection->findOneAndUpdate(
                    ['_id' => 'factureId'],
                    ['$inc' => ['seq' => 1]],
                    ['upsert' => true, 'returnDocument' => MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER]
                );
                $numFact = $counter->seq;

                /* 1. Insert facture */
                $factureCollection->insertOne([
                    'NumFact' => $numFact,
                    'MatFis' => $matfis,
                    'TypeFact' => $type,
                    'datefact' => $date,
                    'payment' => $payment
                ]);

                /* 2. Insert product lines */
                foreach ($lignes as $l) {
                    $prodfactCollection->insertOne([
                        'NumFact' => $numFact,
                        'IdProduit' => $l['id'],
                        'qte' => $l['qte']
                    ]);
                }

                /* 3. Deduct from stock_produit (fact only) */
                if ($type === 'fact') {
                    foreach ($lignes as $l) {
                        $pid         = $l['id'];
                        $qteToRemove = $l['qte'];

                        $stockRows = $stockProduitCollection->find(['IdProduit' => $pid], ['sort' => ['idsp' => 1]]);
                        foreach ($stockRows as $row) {
                            if ($qteToRemove <= 0) break;
                            $rowArray = (array) $row;
                            if ($rowArray['qte'] <= $qteToRemove) {
                                $stockProduitCollection->deleteOne(['idsp' => $rowArray['idsp']]);
                                $qteToRemove -= $rowArray['qte'];
                            } else {
                                $newQte = $rowArray['qte'] - $qteToRemove;
                                $stockProduitCollection->updateOne(['idsp' => $rowArray['idsp']], ['$set' => ['qte' => $newQte]]);
                                $qteToRemove = 0;
                            }
                        }
                    }
                }

                header("Location: print_facture.php?id=$numFact");
                exit();

            } catch (Exception $e) {
                // Manual rollback if needed
                if (isset($numFact)) {
                    $factureCollection->deleteOne(['NumFact' => $numFact]);
                    $prodfactCollection->deleteMany(['NumFact' => $numFact]);
                }
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}

end_of_logic:

/* Load clients */
$allClients = [];
$res = $clientCollection->find([], ['sort' => ['NomEntreprise' => 1]]);
foreach ($res as $c) $allClients[] = (array) $c;

/* Load produits with current stock */
$allProduits = [];
$res = $produitCollection->find([], ['sort' => ['NomProduit' => 1]]);
foreach ($res as $p) {
    $pArray = (array) $p;
    
    // Get Famille
    $famInfo = $familleCollection->findOne(['IdFamille' => $pArray['IdFamille']]);
    if ($famInfo) {
        $pArray['typee'] = $famInfo['typee'];
        $pArray['NomFamille'] = $famInfo['NomFamille'];
        $pArray['tva'] = $famInfo['tva'];
    } else {
        $pArray['typee'] = '';
        $pArray['NomFamille'] = 'Inconnue';
        $pArray['tva'] = 0;
    }
    
    // Get Stock
    $stockDocs = $stockProduitCollection->find(['IdProduit' => $pArray['IdProduit']]);
    $stockDispo = 0;
    foreach ($stockDocs as $sd) {
        $stockDispo += $sd['qte'] ?? 0;
    }
    $pArray['stock'] = $stockDispo;
    
    $allProduits[] = $pArray;
}

/* Next facture number */
$nextCounter = $countersCollection->findOne(['_id' => 'factureId']);
$nextNum = $nextCounter ? ($nextCounter->seq + 1) : 1;

?>