<?php  
$sql1 = "
SELECT client.Nom, client.Prenom, COUNT(facture.NumFact) AS total_factures
FROM client
LEFT JOIN facture ON client.MatFis = facture.MatFis
GROUP BY client.MatFis
ORDER BY total_factures DESC
LIMIT 5
";
$TopClient = $conn->query($sql1);

$sql2 = "
SELECT Nom, Prenom 
FROM client 
ORDER BY MatFis DESC
LIMIT 5
";
$ClientRecent = $conn->query($sql2);

$sql3 = "
SELECT NumFact
FROM facture
ORDER BY facture.datefact DESC
LIMIT 5
";
$FactureRecent = $conn->query($sql3);

$sql4 = "
SELECT produit.NomProduit, SUM(prodfact.qte) AS total_bought
FROM produit
JOIN prodfact ON produit.IdProduit = prodfact.IdProduit
GROUP BY produit.IdProduit
ORDER BY total_bought DESC
LIMIT 5
";
$TopProduit = $conn->query($sql4);

// --- EMPLOYEES ---
$sqlRecentEmployes = "
SELECT Nom, Prenom, Cin, NumTel
FROM employeur
ORDER BY Cin DESC
LIMIT 5
";
$RecentEmployes = $conn->query($sqlRecentEmployes);

$sqlTotalEmployes = "SELECT COUNT(*) as total FROM employeur";
$resTotalEmp = $conn->query($sqlTotalEmployes);
$totalEmployes = $resTotalEmp->fetch_assoc()['total'];

// ----------------

$searchf = isset($_GET['searchf']) ? trim($_GET['searchf']) : "";
$month   = isset($_GET['month']) ? $_GET['month'] : "";

$sqlFactures = "
SELECT facture.NumFact, facture.datefact, facture.payment, client.NomEntreprise
FROM facture
LEFT JOIN client ON facture.MatFis = client.MatFis
";

$conditions = [];

if($searchf !== ""){
    $searchf = mysqli_real_escape_string($conn, $searchf);
    $conditions[] = "(facture.NumFact LIKE '%$searchf%' 
                     OR client.NomEntreprise LIKE '%$searchf%')";
}

if($month !== ""){
    $month = intval($month);
    $conditions[] = "MONTH(facture.datefact) = $month";
}

if(!empty($conditions)){
    $sqlFactures .= " WHERE " . implode(" AND ", $conditions);
}

$sqlFactures .= " ORDER BY facture.datefact DESC";
$resultFactures = $conn->query($sqlFactures); 

$sqlTotal = "SELECT COUNT(*) as total FROM facture";
$resTotal = $conn->query($sqlTotal);
$totalFactures = $resTotal->fetch_assoc()['total'];

$sqlMonth = "
SELECT COUNT(*) as total 
FROM facture 
WHERE MONTH(datefact) = MONTH(CURRENT_DATE()) 
AND YEAR(datefact) = YEAR(CURRENT_DATE())
";
$resMonth = $conn->query($sqlMonth);
$facturesMonth = $resMonth->fetch_assoc()['total'];

$sqlLastMonth = "
SELECT COUNT(*) as total 
FROM facture 
WHERE MONTH(datefact) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
AND YEAR(datefact) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)
";
$resLastMonth = $conn->query($sqlLastMonth);
$lastMonth = $resLastMonth->fetch_assoc()['total'];

$difference = $facturesMonth - $lastMonth;

$searchc = "";
if(isset($_GET['searchc'])){
    $searchc = $_GET['searchc'];
    $sqlClients = "
    SELECT client.MatFis, client.NomEntreprise, client.NumTel
    FROM client
    WHERE client.NomEntreprise LIKE '%$searchc%'
    OR client.NumTel LIKE '%$searchc%'
    ORDER BY client.NomEntreprise
    ";
} else {
    $sqlClients = "
    SELECT client.MatFis, client.NomEntreprise, client.NumTel
    FROM client
    ORDER BY client.NomEntreprise
    ";
}
$resultClients = $conn->query($sqlClients);

$sqlTotal = "SELECT COUNT(*) as total FROM client";
$resTotal = $conn->query($sqlTotal);
$totalClients = $resTotal->fetch_assoc()['total'];

$clientsMonth = 0;
$lastMonth = 0;
$differencec = $clientsMonth - $lastMonth;

$sqlProd = "SELECT COUNT(*) as total FROM produit";
$resProd = $conn->query($sqlProd);
$totalProduits = $resProd->fetch_assoc()['total'];

$sqlFam = "SELECT COUNT(*) as total FROM famille";
$resFam = $conn->query($sqlFam);
$totalFamilles = $resFam->fetch_assoc()['total'];

$searchp = "";
if(isset($_GET['searchp']) && $_GET['searchp'] != ""){
    $searchp = mysqli_real_escape_string($conn, $_GET['searchp']);
    $sqlProduits = "
    SELECT 
        produit.IdProduit, produit.NomProduit, produit.PrixUnit, produit.Poid, 
        famille.NomFamille, famille.typee,
        COALESCE(SUM(stock_produit.qte),0) AS total_qte
    FROM produit
    LEFT JOIN famille ON produit.IdFamille = famille.IdFamille
    LEFT JOIN stock_produit ON produit.IdProduit = stock_produit.IdProduit
    WHERE produit.NomProduit LIKE '%$searchp%'
       OR famille.NomFamille LIKE '%$searchp%'
    GROUP BY produit.IdProduit, produit.NomProduit, produit.Poid, produit.PrixUnit, famille.NomFamille, famille.typee
    ORDER BY produit.NomProduit
    ";
} else {
    $sqlProduits = "
    SELECT 
        produit.IdProduit, produit.NomProduit, produit.PrixUnit, produit.Poid, 
        famille.NomFamille, famille.typee,
        COALESCE(SUM(stock_produit.qte),0) AS total_qte
    FROM produit
    LEFT JOIN famille ON produit.IdFamille = famille.IdFamille
    LEFT JOIN stock_produit ON produit.IdProduit = stock_produit.IdProduit
    GROUP BY produit.IdProduit, produit.NomProduit, produit.Poid, produit.PrixUnit, famille.NomFamille, famille.typee
    ORDER BY produit.NomProduit
    ";
}
$resultProduit = $conn->query($sqlProduits);

$searchm = "";
if(isset($_GET['searchm']) && $_GET['searchm'] != ""){
    $searchm = mysqli_real_escape_string($conn, $_GET['searchm']);
    $sqlMatiere = "
    SELECT matiere.IdMatiere, matiere.NomMat
    FROM matiere
    WHERE matiere.NomMat LIKE '%$searchm%'
    ORDER BY matiere.NomMat
    ";
} else {
    $sqlMatiere = "
    SELECT matiere.IdMatiere, matiere.NomMat
    FROM matiere
    ORDER BY matiere.NomMat
    ";
}
$resultMatiere = $conn->query($sqlMatiere);

$searchfn = "";
if(isset($_GET['searchfn']) && $_GET['searchfn'] != ""){
    $searchfn = mysqli_real_escape_string($conn, $_GET['searchfn']);
    $sqlFournisseur = "
    SELECT fournisseur.Mat, fournisseur.NomEntreprise
    FROM fournisseur
    WHERE fournisseur.NomEntreprise LIKE '%$searchfn%'
    ORDER BY fournisseur.NomEntreprise
    ";
} else {
    $sqlFournisseur = "
    SELECT fournisseur.Mat, fournisseur.NomEntreprise
    FROM fournisseur
    ORDER BY fournisseur.NomEntreprise
    ";
}
$resultFournisseur = $conn->query($sqlFournisseur);

$sqlTotal = "SELECT COUNT(*) as total FROM fournisseur";
$resTotal = $conn->query($sqlTotal);
$totalFournisseurs = $resTotal->fetch_assoc()['total'];
?>