<?php require_once("auth.php"); ?>
<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=fournisseurs.xls");

$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "
SELECT
    f.Mat AS 'Matricule Fiscale',
    CONCAT(COALESCE(f.Nom,''), ' ', COALESCE(f.Prenom,'')) AS 'Nom Complet',
    f.NomEntreprise AS 'Entreprise',
    f.Email,
    f.NumTel AS 'Téléphone',
    COUNT(DISTINCT sm.idsm) AS 'Entrées Stock',
    COALESCE(SUM(sm.qte), 0) AS 'Stock Total (toutes matières)'
FROM fournisseur f
LEFT JOIN stock_matiere sm ON f.Mat = sm.Mat
GROUP BY f.Mat
ORDER BY f.NomEntreprise
";

$result = $conn->query($sql);

echo "Matricule Fiscale\tNom Complet\tEntreprise\tEmail\tTéléphone\tEntrées Stock\tStock Total\n";

while ($row = $result->fetch_assoc()) {
    echo $row['Matricule Fiscale'] . "\t" .
         trim($row['Nom Complet'])  . "\t" .
         $row['Entreprise']         . "\t" .
         $row['Email']              . "\t" .
         $row['Téléphone']          . "\t" .
         $row['Entrées Stock']      . "\t" .
         $row['Stock Total (toutes matières)'] . "\n";
}

$conn->close();
?>
