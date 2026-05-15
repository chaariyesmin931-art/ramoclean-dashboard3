<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header("Location: matiere.php"); exit(); }

$allowed_types = ['kg', 'g', 'L', 'ml', 'u'];

$collection = $db->matiere;
$stockCollection = $db->stock_matiere;
$fournisseurCollection = $db->fournisseur;

/* =============================================
   HANDLE UPDATE MATIERE
   ============================================= */
if (isset($_POST['update_matiere'])) {
    $nom   = trim($_POST['NomMat']);
    $typee = trim($_POST['typee']);
    $desc  = trim($_POST['descriptionn']);

    if ($nom === "") {
        $error = "Le nom de la matière est obligatoire.";
    } elseif (!in_array($typee, $allowed_types)) {
        $error = "Type invalide. Valeurs acceptées : " . implode(', ', $allowed_types) . ".";
    } else {
        try {
            $collection->updateOne(
                ['IdMatiere' => $id],
                ['$set' => ['NomMat' => $nom, 'typee' => $typee, 'descriptionn' => $desc]]
            );
            $success = "Matière mise à jour avec succès.";
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

/* =============================================
   HANDLE DELETE MATIERE
   ============================================= */
if (isset($_POST['delete_matiere'])) {
    try {
        $collection->deleteOne(['IdMatiere' => $id]);
        header("Location: matiere.php?success=Matière+supprimée");
        exit();
    } catch (Exception $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

/* =============================================
   HANDLE CREATE FOURNISSEUR (inline)
   ============================================= */
if (isset($_POST['create_fournisseur'])) {
    $mat        = trim($_POST['new_Mat']);
    $nom        = trim($_POST['new_Nom']);
    $prenom     = trim($_POST['new_Prenom']);
    $entreprise = trim($_POST['new_NomEntreprise']);
    $email      = trim($_POST['new_Email']);
    $tel        = trim($_POST['new_NumTel']);

    if ($mat === "" || $entreprise === "" || $email === "" || $tel === "") {
        $error = "Matricule, entreprise, email et téléphone sont obligatoires.";
    } elseif (strlen($tel) !== 8 || !ctype_digit($tel)) {
        $error = "Le téléphone doit contenir exactement 8 chiffres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        $checkMat = $fournisseurCollection->countDocuments(['Mat' => $mat]);
        $checkTel = $fournisseurCollection->countDocuments(['NumTel' => $tel]);
        if ($checkMat > 0) {
            $error = "Un fournisseur avec ce matricule existe déjà.";
        } elseif ($checkTel > 0) {
            $error = "Ce numéro de téléphone est déjà utilisé.";
        } else {
            try {
                $fournisseurCollection->insertOne([
                    'Mat' => $mat, 'Nom' => $nom, 'Prenom' => $prenom,
                    'NomEntreprise' => $entreprise, 'Email' => $email, 'NumTel' => $tel
                ]);
                $success = "Fournisseur « $entreprise » créé. Vous pouvez maintenant le sélectionner.";
            } catch (Exception $e) {
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}

/* =============================================
   HANDLE ADD STOCK
   ============================================= */
if (isset($_POST['add_stock'])) {
    $mat = trim($_POST['Mat']);
    $qte = intval($_POST['qte_add']);

    if ($mat === "") {
        $error = "Veuillez sélectionner un fournisseur.";
    } elseif ($qte <= 0) {
        $error = "La quantité doit être supérieure à 0.";
    } else {
        /* Check fournisseur exists */
        $checkFn = $fournisseurCollection->countDocuments(['Mat' => $mat]);
        if ($checkFn === 0) {
            $error = "Fournisseur introuvable.";
        } else {
            try {
                // Generate a unique numeric ID for idsm using timestamp
                $idsm = time() + rand(1, 1000);
                $stockCollection->insertOne([
                    'idsm' => $idsm,
                    'IdMatiere' => $id,
                    'Mat' => $mat,
                    'qte' => $qte
                ]);
                $success = "$qte unité(s) ajoutée(s) au stock avec succès.";
            } catch (Exception $e) {
                $error = "Erreur lors de l'ajout : " . $e->getMessage();
            }
        }
    }
}

/* =============================================
   HANDLE DELETE STOCK ROW
   ============================================= */
if (isset($_GET['delete_stock'])) {
    $idsm = intval($_GET['delete_stock']);
    $stockCollection->deleteOne(['idsm' => $idsm, 'IdMatiere' => $id]);
    header("Location: details_matiere.php?id=$id&success=Entrée+de+stock+supprimée");
    exit();
}

/* =============================================
   LOAD ALL DATA
   ============================================= */
$matiere = $collection->findOne(['IdMatiere' => $id]);
if (!$matiere) { header("Location: matiere.php"); exit(); }
$matiere = (array) $matiere;

// Total Stock
$stockCursor = $stockCollection->find(['IdMatiere' => $id]);
$totalStock = 0;
foreach ($stockCursor as $doc) {
    $totalStock += $doc['qte'] ?? 0;
}

// Prodmat (Produits requiring this matiere)
$resProduits = $db->prodmat->find(['IdMatiere' => $id]);
$produits = [];
foreach ($resProduits as $pm) {
    $pmArray = (array) $pm;
    $produitInfo = $db->produit->findOne(['IdProduit' => $pmArray['IdProduit'] ?? null]);
    if ($produitInfo) {
        $pmArray['NomProduit'] = $produitInfo['NomProduit'];
    } else {
        $pmArray['NomProduit'] = 'Inconnu';
    }
    $pmArray['qte_needed'] = $pmArray['qte'] ?? 0;
    $produits[] = $pmArray;
}

// Famille_mat (Familles requiring this matiere)
$resFamilles = $db->famille_mat->find(['IdMatiere' => $id]);
$familles = [];
foreach ($resFamilles as $fm) {
    $fmArray = (array) $fm;
    $familleInfo = $db->famille->findOne(['IdFamille' => $fmArray['IdFamille'] ?? null]);
    if ($familleInfo) {
        $fmArray['NomFamille'] = $familleInfo['NomFamille'];
    } else {
        $fmArray['NomFamille'] = 'Inconnue';
    }
    $familles[] = $fmArray;
}

// Stock Rows
$resStockRows = $stockCollection->find(['IdMatiere' => $id], ['sort' => ['idsm' => -1]]);
$stockRows = [];
foreach ($resStockRows as $sr) {
    $srArray = (array) $sr;
    $fournisseurInfo = $fournisseurCollection->findOne(['Mat' => $srArray['Mat'] ?? null]);
    if ($fournisseurInfo) {
        $srArray['NomEntreprise'] = $fournisseurInfo['NomEntreprise'];
    } else {
        $srArray['NomEntreprise'] = 'Inconnu';
    }
    $stockRows[] = $srArray;
}

/* All fournisseurs for select */
$resFournisseurs = $fournisseurCollection->find([], ['sort' => ['NomEntreprise' => 1]]);
$allFournisseurs = [];
foreach ($resFournisseurs as $f) {
    $allFournisseurs[] = (array) $f;
}

/* Pass along redirect success */
if (isset($_GET['success'])) $success = htmlspecialchars($_GET['success']);
?>