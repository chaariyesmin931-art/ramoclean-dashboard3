<?php require_once("auth.php"); ?>
<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=client_factures.xls");

require_once("connexion.php");

$pipeline = [
    ['$group' => ['_id' => '$MatFis', 'factures_count' => ['$sum' => 1]]],
    ['$lookup' => [
        'from' => 'client',
        'localField' => '_id',
        'foreignField' => 'MatFis',
        'as' => 'client_info'
    ]],
    ['$unwind' => '$client_info'],
    ['$sort' => ['factures_count' => -1]]
];

$cursor = $db->facture->aggregate($pipeline);

echo "Matricule\tClient\tEntreprise\tEmail\tTelephone\tNombre de Factures\n";

foreach ($cursor as $row) {
    $rowArray = (array) $row;
    $clientInfo = (array) $rowArray['client_info'];
    
    $client = trim(($clientInfo['Nom'] ?? '') . ' ' . ($clientInfo['Prenom'] ?? ''));
    if (!$client) $client = "Unknown";
    
    $Matricule = $clientInfo['MatFis'] ? $clientInfo['MatFis'] : "Unknown";
    $Entreprise = ($clientInfo['NomEntreprise'] ?? '') ? $clientInfo['NomEntreprise'] : "Unknown";
    $Email = ($clientInfo['Email'] ?? '') ? $clientInfo['Email'] : "Unknown";
    $Tel = ($clientInfo['NumTel'] ?? '') ? $clientInfo['NumTel'] : "Unknown";
    $Factures = $rowArray['factures_count'] ? $rowArray['factures_count'] : "0";
    
    echo $Matricule."\t".
         $client."\t".
         $Entreprise."\t".
         $Email."\t".
         $Tel."\t".
         $Factures."\n";
}

// Add clients with 0 invoices
$allClients = $db->client->find();
$facturedClientMats = [];
$cursor->rewind();
foreach ($cursor as $row) {
    $facturedClientMats[] = ((array)$row)['_id'];
}
foreach ($allClients as $clientDoc) {
    $c = (array) $clientDoc;
    if (!in_array($c['MatFis'], $facturedClientMats)) {
        $client = trim(($c['Nom'] ?? '') . ' ' . ($c['Prenom'] ?? ''));
        if (!$client) $client = "Unknown";
        $Matricule = $c['MatFis'] ? $c['MatFis'] : "Unknown";
        $Entreprise = ($c['NomEntreprise'] ?? '') ? $c['NomEntreprise'] : "Unknown";
        $Email = ($c['Email'] ?? '') ? $c['Email'] : "Unknown";
        $Tel = ($c['NumTel'] ?? '') ? $c['NumTel'] : "Unknown";
        echo $Matricule."\t".$client."\t".$Entreprise."\t".$Email."\t".$Tel."\t0\n";
    }
}
?>