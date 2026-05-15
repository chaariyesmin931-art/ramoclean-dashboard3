<?php require_once("auth.php"); ?>
<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=employes.xls");

$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "
SELECT
    Cin,
    COALESCE(Nom, '') AS 'Nom',
    COALESCE(Prenom, '') AS 'Prénom',
    Email,
    NumTel AS 'Téléphone'
FROM employeur
ORDER BY Nom, Prenom
";

$result = $conn->query($sql);

echo "CIN\tNom\tPrénom\tEmail\tTéléphone\n";

while ($row = $result->fetch_assoc()) {
    echo $row['Cin']       . "\t" .
         $row['Nom']       . "\t" .
         $row['Prénom']    . "\t" .
         $row['Email']     . "\t" .
         $row['Téléphone'] . "\n";
}

$conn->close();
?>
