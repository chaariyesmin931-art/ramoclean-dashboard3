<?php require_once("auth.php"); ?>
<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=factures.xls");

require_once("connexion.php");

$factureCollection = $db->facture;
$clientCollection = $db->client;

$cursor = $factureCollection->find([], ['sort' => ['datefact' => -1]]);

echo "Numero Facture\tClient\tMatricule Fiscale\tDate Facture\tStatut Paiement\n";

foreach ($cursor as $row) {
    $rowArray = (array) $row;
    
    $clientInfo = $clientCollection->findOne(['MatFis' => $rowArray['MatFis']]);
    $clientName = ($clientInfo && isset($clientInfo['NomEntreprise']) && $clientInfo['NomEntreprise']) ? $clientInfo['NomEntreprise'] : "Unknown";
    
    $status = (isset($rowArray['payment']) && $rowArray['payment'] == 1) ? "Payee " : "Non Payee";
    $date = date("d/m/Y", strtotime($rowArray['datefact'] ?? ''));
    
    echo ($rowArray['NumFact'] ?? '') . "\t" .
         $clientName . "\t" .
         ($rowArray['MatFis'] ?? '') . "\t" .
         $date . "\t" .
         $status . "\n";
}
?>