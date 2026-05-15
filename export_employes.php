<?php require_once("auth.php"); ?>
<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=employes.xls");

require_once("connexion.php");

$employeurCollection = $db->employeur;

$cursor = $employeurCollection->find([], ['sort' => ['Nom' => 1, 'Prenom' => 1]]);

echo "CIN\tNom\tPrénom\tEmail\tTéléphone\n";

foreach ($cursor as $row) {
    $rowArray = (array) $row;
    $nom = isset($rowArray['Nom']) ? $rowArray['Nom'] : '';
    $prenom = isset($rowArray['Prenom']) ? $rowArray['Prenom'] : '';
    
    echo ($rowArray['Cin'] ?? '')       . "\t" .
         $nom                           . "\t" .
         $prenom                        . "\t" .
         ($rowArray['Email'] ?? '')     . "\t" .
         ($rowArray['NumTel'] ?? '')    . "\n";
}
?>
