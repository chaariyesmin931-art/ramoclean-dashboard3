<?php
require_once("connexion.php");

$success  = "";
$error    = "";
$warning  = "";
$shortages = [];

$allowed_types = ['fact', 'bdl', 'devis'];

/* =============================================
   HELPER: Get next facture number
   ============================================= */
function getNextFactureNumber($collection) {
    $lastFact = mongoFindOne($collection, [], ['sort' => ['NumFact' => -1]]);
    return $lastFact ? ($lastFact['NumFact'] + 1) : 1;
}

/* =============================================
   HANDLE CREATION
   ============================================= */
if (isset($_POST['create_facture'])) {
    $matfis  = trim($_POST['MatFis']);
    $date    = trim($_POST['datefact']);
    $type    = in_array($_POST['TypeFact'], $allowed_types) ? $_POST['TypeFact'] : 'fact';
    $force   = isset($_POST['force_create']);
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
        $checkClient = mongoFindOne($clients, ['MatFis' => $matfis]);
        if (!$checkClient) {
            $error = "Client introuvable.";
        } else {

            /* ---- Stock check (only for facture) ---- */
            if ($type === 'fact' && !$force) {
                foreach ($lignes as $l) {
                    $pid = $l['id'];
                    $qteNeeded = $l['qte'];

                    $prodDoc = mongoFindOne($produits, ['IdProduit' => $pid]);
                    if (!$prodDoc) {
                        $error = "Produit #$pid introuvable.";
                        break;
                    }

                    $stockDoc = mongoFindOne($stock, ['IdProduit' => $pid]);
                    $stockDispo = $stockDoc ? ($stockDoc['qte'] ?? 0) : 0;
                    $nomProduit = $prodDoc['NomProduit'] ?? "Produit #$pid";

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
                    goto end_of_logic;
                }
            }

            /* ---- Proceed with creation ---- */
            try {
                $numFact = getNextFactureNumber($factures);

                /* 1. Create facture document with embedded product lines */
                $factureDoc = [
                    'NumFact' => $numFact,
                    'MatFis' => $matfis,
                    'TypeFact' => $type,
                    'datefact' => $date,
                    'payment' => $payment,
                    'lignes' => $lignes,
                    'created_at' => new MongoDB\BSON\UTCDateTime()
                ];
                mongoInsert($factures, $factureDoc);

                /* 2. Deduct from stock (fact only) */
                if ($type === 'fact') {
                    foreach ($lignes as $l) {
                        $pid = $l['id'];
                        $qteToRemove = $l['qte'];

                        /* Decrement stock */
                        $stockDoc = mongoFindOne($stock, ['IdProduit' => $pid]);
                        if ($stockDoc) {
                            $newQte = max(0, ($stockDoc['qte'] ?? 0) - $qteToRemove);
                            mongoUpdate($stock, ['IdProduit' => $pid], ['qte' => $newQte]);
                        }
                    }
                }

                header("Location: print_facture.php?id=$numFact");
                exit();

            } catch (Exception $e) {
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}

end_of_logic:

/* Load clients */
$allClients = [];
try {
    $clientsResult = mongoFindAll($clients, []);
    foreach ($clientsResult as $c) {
        $allClients[] = $c;
    }
} catch (Exception $e) {
    $error = "Erreur lors du chargement des clients : " . $e->getMessage();
}

/* Load produits with current stock */
$allProduits = [];
try {
    $produitsResult = mongoFindAll($produits, []);
    foreach ($produitsResult as $p) {
        /* Get stock info */
        $stockDoc = mongoFindOne($stock, ['IdProduit' => $p['IdProduit']]);
        $stockQte = $stockDoc ? ($stockDoc['qte'] ?? 0) : 0;

        /* Get famille info */
        $familleDoc = mongoFindOne($familles, ['IdFamille' => $p['IdFamille']]);

        $p['stock'] = $stockQte;
        if ($familleDoc) {
            $p['NomFamille'] = $familleDoc['NomFamille'] ?? '';
            $p['typee'] = $familleDoc['typee'] ?? '';
            $p['tva'] = $familleDoc['tva'] ?? 0;
        }

        $allProduits[] = $p;
    }
} catch (Exception $e) {
    $error = "Erreur lors du chargement des produits : " . $e->getMessage();
}

/* Next facture number */
try {
    $nextNum = getNextFactureNumber($factures);
} catch (Exception $e) {
    $nextNum = '—';
}

?>
