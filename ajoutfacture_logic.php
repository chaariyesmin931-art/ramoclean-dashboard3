<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success  = "";
$error    = "";
$warning  = "";           /* stock shortage warning */
$shortages = [];          /* list of shortage details */

$allowed_types = ['fact', 'bdl', 'devis'];

/* =============================================
   HANDLE CREATION
   ============================================= */
if (isset($_POST['create_facture'])) {
    $matfis  = mysqli_real_escape_string($conn, trim($_POST['MatFis']));
    $date    = mysqli_real_escape_string($conn, trim($_POST['datefact']));
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
        $checkClient = $conn->query("SELECT MatFis FROM client WHERE MatFis='$matfis'");
        if ($checkClient->num_rows === 0) {
            $error = "Client introuvable.";
        } else {

            /* ---- Stock check (only for facture) ---- */
            if ($type === 'fact' && !$force) {
                foreach ($lignes as $l) {
                    $pid = $l['id'];
                    $qteNeeded = $l['qte'];

                    $resStock = $conn->query("
                        SELECT produit.NomProduit,
                               COALESCE(SUM(stock_produit.qte), 0) AS stock_dispo
                        FROM produit
                        LEFT JOIN stock_produit ON produit.IdProduit = stock_produit.IdProduit
                        WHERE produit.IdProduit = $pid
                        GROUP BY produit.IdProduit
                    ");
                    $stockRow = $resStock->fetch_assoc();
                    $stockDispo = intval($stockRow['stock_dispo'] ?? 0);
                    $nomProduit = $stockRow['NomProduit'] ?? "Produit #$pid";

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
            $conn->begin_transaction();
            try {
                /* 1. Insert facture */
                $conn->query("INSERT INTO facture (MatFis, TypeFact, datefact, payment)
                              VALUES ('$matfis', '$type', '$date', $payment)");
                $numFact = $conn->insert_id;

                /* 2. Insert product lines */
                foreach ($lignes as $l) {
                    $conn->query("INSERT INTO prodfact (NumFact, IdProduit, qte)
                                  VALUES ($numFact, {$l['id']}, {$l['qte']})");
                }

                /* 3. Deduct from stock_produit (fact only) */
                if ($type === 'fact') {
                    foreach ($lignes as $l) {
                        $pid         = $l['id'];
                        $qteToRemove = $l['qte'];

                        $stockRows = $conn->query(
                            "SELECT idsp, qte FROM stock_produit
                             WHERE IdProduit = $pid ORDER BY idsp ASC"
                        );
                        while ($qteToRemove > 0 && $row = $stockRows->fetch_assoc()) {
                            if ($row['qte'] <= $qteToRemove) {
                                $conn->query("DELETE FROM stock_produit WHERE idsp = {$row['idsp']}");
                                $qteToRemove -= $row['qte'];
                            } else {
                                $newQte = $row['qte'] - $qteToRemove;
                                $conn->query("UPDATE stock_produit SET qte = $newQte WHERE idsp = {$row['idsp']}");
                                $qteToRemove = 0;
                            }
                        }
                    }
                }

                $conn->commit();
                header("Location: print_facture.php?id=$numFact");
                exit();

            } catch (Exception $e) {
                $conn->rollback();
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}

end_of_logic:

/* Load clients */
$allClients = [];
$res = $conn->query("SELECT MatFis, NomEntreprise, Nom, Prenom, NumTel FROM client ORDER BY NomEntreprise");
while ($c = $res->fetch_assoc()) $allClients[] = $c;

/* Load produits with current stock */
$allProduits = [];
$res = $conn->query("
    SELECT produit.IdProduit, produit.NomProduit, produit.PrixUnit,
           produit.poid, famille.typee, famille.NomFamille, famille.tva,
           COALESCE(SUM(stock_produit.qte), 0) AS stock
    FROM produit
    LEFT JOIN famille ON produit.IdFamille = famille.IdFamille
    LEFT JOIN stock_produit ON produit.IdProduit = stock_produit.IdProduit
    GROUP BY produit.IdProduit
    ORDER BY produit.NomProduit
");
while ($p = $res->fetch_assoc()) $allProduits[] = $p;

/* Next facture number */
$resNext = $conn->query("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA='ramoclean' AND TABLE_NAME='facture'");
$nextNum = $resNext ? ($resNext->fetch_assoc()['AUTO_INCREMENT'] ?? '—') : '—';

$conn->close();
?>