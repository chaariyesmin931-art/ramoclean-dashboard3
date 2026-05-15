<?php require_once("auth.php"); ?>
<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=client_factures.xls");
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "
SELECT 
    c.MatFis AS 'Matricule Fiscale',
    CONCAT(c.Nom, ' ', c.Prenom) AS 'Nom Complet',
    c.NomEntreprise AS 'Entreprise',
    c.Email,
    c.NumTel AS 'Téléphone',
    COUNT(f.NumFact) AS 'Nombre de Factures'
FROM client c
LEFT JOIN facture f ON c.MatFis = f.MatFis
GROUP BY c.MatFis
ORDER BY COUNT(f.NumFact) DESC;
";
$result = $conn->query($sql);
echo "Matricule\tClient\tEntreprise\tEmail\tTelephone\tNombre de Factures\n";
while($row = $result->fetch_assoc()){
    $client = $row['Nom Complet'] ? $row['Nom Complet'] : "Unknown";
    $Matricule = $row['Matricule Fiscale']  ? $row['Matricule Fiscale'] : "Unknown";
    $Entreprise = $row['Entreprise'] ? $row['Entreprise'] : "Unknown";
    $Email = $row['Email'] ? $row['Email'] : "Unknown";
    $Tel = $row['Téléphone'] ? $row['Téléphone'] : "Unknown";
    $Factures = $row['Nombre de Factures'] ? $row['Nombre de Factures'] : "0";
    echo $Matricule."\t".
         $client."\t".
         $Entreprise."\t".
         $Email."\t".
         $Tel."\t".
         $Factures."\n";
}
$conn->close();
?>