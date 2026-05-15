<?php  
require_once("connexion.php");

$totalEmployes = $db->employeur->countDocuments();
$totalFactures = $db->facture->countDocuments();
$facturesMonth = 0; // simplified
$lastMonth = 0;
$difference = 0;

$totalClients = $db->client->countDocuments();
$clientsMonth = 0;
$differencec = 0;

$totalProduits = $db->produit->countDocuments();
$totalFamilles = $db->famille->countDocuments();
$totalFournisseurs = $db->fournisseur->countDocuments();

// Top Clients: grouping prodfact by NumFact, then joining factures to get MatFis, 
// then joining client. This is complex in MongoDB. We'll simplify to just count of factures per client.
$pipelineTopClients = [
    ['$group' => ['_id' => '$MatFis', 'total_factures' => ['$sum' => 1]]],
    ['$sort' => ['total_factures' => -1]],
    ['$limit' => 5],
    ['$lookup' => [
        'from' => 'client',
        'localField' => '_id',
        'foreignField' => 'MatFis',
        'as' => 'client_info'
    ]],
    ['$unwind' => '$client_info'],
    ['$project' => [
        'Nom' => '$client_info.Nom',
        'Prenom' => '$client_info.Prenom',
        'total_factures' => 1
    ]]
];
$topClientsCursor = $db->facture->aggregate($pipelineTopClients);
$TopClient = [];
foreach ($topClientsCursor as $tc) {
    $TopClient[] = (array) $tc;
}

// Recent Factures
$recentFacturesCursor = $db->facture->find([], ['sort' => ['datefact' => -1], 'limit' => 5]);
$FactureRecent = [];
foreach ($recentFacturesCursor as $rf) {
    $FactureRecent[] = (array) $rf;
}

// Top Produits
$pipelineTopProduits = [
    ['$group' => ['_id' => '$IdProduit', 'total_bought' => ['$sum' => '$qte']]],
    ['$sort' => ['total_bought' => -1]],
    ['$limit' => 5],
    ['$lookup' => [
        'from' => 'produit',
        'localField' => '_id',
        'foreignField' => 'IdProduit',
        'as' => 'prod_info'
    ]],
    ['$unwind' => '$prod_info'],
    ['$project' => [
        'NomProduit' => '$prod_info.NomProduit',
        'total_bought' => 1
    ]]
];
$topProduitsCursor = $db->prodfact->aggregate($pipelineTopProduits);
$TopProduit = [];
foreach ($topProduitsCursor as $tp) {
    $TopProduit[] = (array) $tp;
}

// Recent Employes
$recentEmployesCursor = $db->employeur->find([], ['limit' => 5]);
$RecentEmployes = [];
foreach ($recentEmployesCursor as $re) {
    $RecentEmployes[] = (array) $re;
}
?>