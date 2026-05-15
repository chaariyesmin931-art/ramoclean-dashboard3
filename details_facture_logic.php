<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header("Location: facture.php"); exit(); }

$factureCollection = $db->facture;
$clientCollection = $db->client;
$prodfactCollection = $db->prodfact;
$produitCollection = $db->produit;
$familleCollection = $db->famille;

/* =============================================
   TOGGLE PAYMENT (fact only)
   ============================================= */
if (isset($_POST['toggle_payment'])) {
    $newPayment = intval($_POST['new_payment']);
    $factureCollection->updateOne(['NumFact' => $id], ['$set' => ['payment' => $newPayment]]);
    $success = $newPayment ? "Facture marquée comme payée." : "Facture marquée comme non payée.";
}

/* =============================================
   DELETE FACTURE
   ============================================= */
if (isset($_POST['delete_facture'])) {
    try {
        $factureCollection->deleteOne(['NumFact' => $id]);
        $prodfactCollection->deleteMany(['NumFact' => $id]);
        header("Location: facture.php?success=Facture+supprimée");
        exit();
    } catch (Exception $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

/* =============================================
   LOAD FACTURE DATA
   ============================================= */
$fact = $factureCollection->findOne(['NumFact' => $id]);
if (!$fact) { header("Location: facture.php"); exit(); }
$fact = (array) $fact;

$clientInfo = $clientCollection->findOne(['MatFis' => $fact['MatFis']]);
if ($clientInfo) {
    $fact['NomEntreprise'] = $clientInfo['NomEntreprise'];
    $fact['Nom'] = $clientInfo['Nom'];
    $fact['Prenom'] = $clientInfo['Prenom'];
    $fact['Email'] = $clientInfo['Email'];
    $fact['NumTel'] = $clientInfo['NumTel'];
    $fact['ClientMat'] = $clientInfo['MatFis'];
} else {
    $fact['NomEntreprise'] = 'Inconnu';
    $fact['Nom'] = '';
    $fact['Prenom'] = '';
    $fact['Email'] = '';
    $fact['NumTel'] = '';
    $fact['ClientMat'] = '';
}

/* Load product lines */
$lines = [];
$resLines = $prodfactCollection->find(['NumFact' => $id]);
foreach ($resLines as $l) {
    $lArray = (array) $l;
    $prodInfo = $produitCollection->findOne(['IdProduit' => $lArray['IdProduit']]);
    if ($prodInfo) {
        $lArray['NomProduit'] = $prodInfo['NomProduit'];
        $lArray['PrixUnit'] = $prodInfo['PrixUnit'];
        $lArray['poid'] = $prodInfo['poid'];
        
        $famInfo = $familleCollection->findOne(['IdFamille' => $prodInfo['IdFamille']]);
        if ($famInfo) {
            $lArray['typee'] = $famInfo['typee'];
            $lArray['tva'] = $famInfo['tva'];
            $lArray['NomFamille'] = $famInfo['NomFamille'];
        }
    }
    $lines[] = $lArray;
}

/* Totals */
$totalHT = 0; $totalTVA = 0;
foreach ($lines as $l) {
    $prixUnit = $l['PrixUnit'] ?? 0;
    $ht = $prixUnit * $l['qte'];
    $totalHT  += $ht;
    $totalTVA += $ht * (($l['tva'] ?? 0) / 100);
}
$totalTTC   = $totalHT + $totalTVA;
$timbreFisc = 1.000;
$totalFinal = $totalTTC + $timbreFisc;

if (isset($_GET['success'])) $success = htmlspecialchars($_GET['success']);
?>
