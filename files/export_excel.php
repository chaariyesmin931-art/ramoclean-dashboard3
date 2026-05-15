<?php require_once("auth.php"); ?>
<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=factures.xls");
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "
SELECT 
    facture.NumFact, 
    facture.MatFis, 
    facture.datefact, 
    facture.payment, 
    client.NomEntreprise
FROM facture
LEFT JOIN client ON facture.MatFis = client.MatFis
ORDER BY facture.datefact DESC
";
$result = $conn->query($sql);
echo "Numero Facture\tClient\tMatricule Fiscale\tDate Facture\tStatut Paiement\n";
while($row = $result->fetch_assoc()){
    $client = $row['NomEntreprise'] ? $row['NomEntreprise'] : "Unknown";
    $status = ($row['payment'] == 1) ? "Payee " : "Non Payee";
    $date = date("d/m/Y", strtotime($row['datefact']));
    echo $row['NumFact']."\t".
         $client."\t".
         $row['MatFis']."\t".
         $date."\t".
         $status."\n";
}
$conn->close();
?>