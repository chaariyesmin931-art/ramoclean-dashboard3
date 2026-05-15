<?php require_once("auth.php"); ?>
<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = "";
$error   = "";
$warning = "";

/* Get product ID from URL */
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header("Location: produit.php"); exit(); }

/* =============================================
   ADD MATIERE TO PRODUCT RECIPE (prodmat)
   ============================================= */
if (isset($_POST['add_matiere'])) {
    $idMat = intval($_POST['IdMatiere']);
    $qte   = intval($_POST['qte_needed']);

    if ($idMat <= 0 || $qte <= 0) {
        $error = "Veuillez sélectionner une matière et une quantité valide.";
    } else {
        /* Check if already linked */
        $check = $conn->query("SELECT * FROM prodmat WHERE IdProduit=$id AND IdMatiere=$idMat");
        if ($check->num_rows > 0) {
            /* Update existing */
            $conn->query("UPDATE prodmat SET qte=$qte WHERE IdProduit=$id AND IdMatiere=$idMat");
            $success = "Quantité de matière mise à jour.";
        } else {
            $conn->query("INSERT INTO prodmat (IdProduit, IdMatiere, qte) VALUES ($id, $idMat, $qte)");
            $success = "Matière ajoutée à la recette du produit.";
        }
    }
}

/* =============================================
   REMOVE MATIERE FROM RECIPE
   ============================================= */
if (isset($_GET['remove_mat'])) {
    $idMat = intval($_GET['remove_mat']);
    $conn->query("DELETE FROM prodmat WHERE IdProduit=$id AND IdMatiere=$idMat");
    header("Location: details_produit.php?id=$id&success=Matière+supprimée+de+la+recette");
    exit();
}

/* =============================================
   PRODUCE STOCK — add qty to stock_produit,
   subtract from stock_matiere
   ============================================= */
if (isset($_POST['produce'])) {
    $qte_prod  = intval($_POST['qte_produce']);
    $force     = isset($_POST['force_produce']); /* ignore stock warning */

    if ($qte_prod <= 0) {
        $error = "La quantité à produire doit être supérieure à 0.";
    } else {
        /* Load recipe */
        $recipe = [];
        $resRecipe = $conn->query("
            SELECT prodmat.IdMatiere, prodmat.qte as needed,
                   matiere.NomMat,
                   COALESCE(SUM(stock_matiere.qte), 0) as stock
            FROM prodmat
            JOIN matiere ON prodmat.IdMatiere = matiere.IdMatiere
            LEFT JOIN stock_matiere ON stock_matiere.IdMatiere = prodmat.IdMatiere
            WHERE prodmat.IdProduit = $id
            GROUP BY prodmat.IdMatiere, prodmat.qte, matiere.NomMat
        ");
        while ($r = $resRecipe->fetch_assoc()) $recipe[] = $r;

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
            $conn->begin_transaction();
            try {
                /* Subtract materials from stock */
                foreach ($recipe as $r) {
                    $required = $r['needed'] * $qte_prod;
                    $idMat = $r['IdMatiere'];

                    /* Get all stock rows for this material ordered oldest first */
                    $stockRows = $conn->query("SELECT idsm, qte FROM stock_matiere WHERE IdMatiere=$idMat ORDER BY idsm ASC");
                    $toSubtract = $required;

                    while ($toSubtract > 0 && $row = $stockRows->fetch_assoc()) {
                        if ($row['qte'] <= $toSubtract) {
                            $toSubtract -= $row['qte'];
                            $conn->query("DELETE FROM stock_matiere WHERE idsm={$row['idsm']}");
                        } else {
                            $newQte = $row['qte'] - $toSubtract;
                            $conn->query("UPDATE stock_matiere SET qte=$newQte WHERE idsm={$row['idsm']}");
                            $toSubtract = 0;
                        }
                    }
                    /* If forced and not enough stock, we just go to 0 — already handled above */
                }

                /* Add to stock_produit */
                $existing = $conn->query("SELECT idsp, qte FROM stock_produit WHERE IdProduit=$id LIMIT 1");
                if ($existing->num_rows > 0) {
                    $row = $existing->fetch_assoc();
                    $newQte = $row['qte'] + $qte_prod;
                    $conn->query("UPDATE stock_produit SET qte=$newQte WHERE idsp={$row['idsp']}");
                } else {
                    /* Get IdFamille for this product */
                    $fam = $conn->query("SELECT IdFamille FROM produit WHERE IdProduit=$id")->fetch_assoc();
                    $conn->query("INSERT INTO stock_produit (IdProduit, IdFamille, qte) VALUES ($id, {$fam['IdFamille']}, $qte_prod)");
                }

                $conn->commit();
                $success = "$qte_prod unité(s) produite(s) et ajoutée(s) au stock avec succès.";

            } catch (Exception $e) {
                $conn->rollback();
                $error = "Erreur lors de la production : " . $e->getMessage();
            }
        }
    }
}

/* =============================================
   LOAD PRODUCT DATA
   ============================================= */
$res = $conn->query("
    SELECT produit.*, famille.NomFamille, famille.typee, famille.arome, famille.tva,
           COALESCE(SUM(stock_produit.qte), 0) AS stock_total
    FROM produit
    LEFT JOIN famille ON produit.IdFamille = famille.IdFamille
    LEFT JOIN stock_produit ON produit.IdProduit = stock_produit.IdProduit
    WHERE produit.IdProduit = $id
    GROUP BY produit.IdProduit
");

if ($res->num_rows === 0) { header("Location: produit.php"); exit(); }
$produit = $res->fetch_assoc();

/* Load recipe (materials needed) */
$resMatNeeded = $conn->query("
    SELECT prodmat.IdMatiere, prodmat.qte AS needed,
           matiere.NomMat, matiere.typee AS mat_type,
           COALESCE(SUM(stock_matiere.qte), 0) AS mat_stock
    FROM prodmat
    JOIN matiere ON prodmat.IdMatiere = matiere.IdMatiere
    LEFT JOIN stock_matiere ON stock_matiere.IdMatiere = prodmat.IdMatiere
    WHERE prodmat.IdProduit = $id
    GROUP BY prodmat.IdMatiere, prodmat.qte, matiere.NomMat, matiere.typee
");
$materiaux = [];
while ($r = $resMatNeeded->fetch_assoc()) $materiaux[] = $r;

/* Load all available matieres for the add form */
$resAllMat = $conn->query("SELECT IdMatiere, NomMat, typee FROM matiere ORDER BY NomMat");
$allMatieres = [];
while ($r = $resAllMat->fetch_assoc()) $allMatieres[] = $r;

/* Max producible based on current stock */
$maxProducible = PHP_INT_MAX;
foreach ($materiaux as $m) {
    if ($m['needed'] > 0) {
        $maxProducible = min($maxProducible, floor($m['mat_stock'] / $m['needed']));
    }
}
if ($maxProducible === PHP_INT_MAX) $maxProducible = 0;

/* Pass along success from redirect */
if (isset($_GET['success'])) $success = htmlspecialchars($_GET['success']);

$conn->close();
?>
