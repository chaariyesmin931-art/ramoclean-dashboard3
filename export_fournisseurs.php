<?php require_once("auth.php"); ?>
<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=fournisseurs.xls");

require_once("connexion.php");

$fournisseurCollection = $db->fournisseur;
$stockMatiereCollection = $db->stock_matiere;

$cursor = $fournisseurCollection->find([], ['sort' => ['NomEntreprise' => 1]]);

echo "Matricule Fiscale\tNom Complet\tEntreprise\tEmail\tTéléphone\tEntrées Stock\tStock Total\n";

foreach ($cursor as $row) {
    $rowArray = (array) $row;
    $mat = $rowArray['Mat'] ?? '';
    
    $stockDocs = $stockMatiereCollection->find(['Mat' => $mat]);
    $entrees = 0;
    $totalStock = 0;
    foreach ($stockDocs as $sd) {
        $entrees++;
        $totalStock += $sd['qte'] ?? 0;
    }
    
    $nomComplet = trim(($rowArray['Nom'] ?? '') . ' ' . ($rowArray['Prenom'] ?? ''));
    
    echo $mat . "\t" .
         $nomComplet  . "\t" .
         ($rowArray['NomEntreprise'] ?? '') . "\t" .
         ($rowArray['Email'] ?? '')         . "\t" .
         ($rowArray['NumTel'] ?? '')        . "\t" .
         $entrees                           . "\t" .
         $totalStock                        . "\n";
}
?>
