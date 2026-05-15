<?php require_once("auth.php"); ?>
<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = "";
$error   = "";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header("Location: facture.php"); exit(); }

/* =============================================
   TOGGLE PAYMENT (fact only)
   ============================================= */
if (isset($_POST['toggle_payment'])) {
    $newPayment = intval($_POST['new_payment']);
    $conn->query("UPDATE facture SET payment=$newPayment WHERE NumFact=$id");
    $success = $newPayment ? "Facture marquée comme payée." : "Facture marquée comme non payée.";
}

/* =============================================
   DELETE FACTURE
   ============================================= */
if (isset($_POST['delete_facture'])) {
    if ($conn->query("DELETE FROM facture WHERE NumFact=$id")) {
        header("Location: facture.php?success=Facture+supprimée");
        exit();
    } else {
        $error = "Erreur lors de la suppression : " . $conn->error;
    }
}

/* =============================================
   LOAD FACTURE DATA
   ============================================= */
$res = $conn->query("
    SELECT facture.*, client.NomEntreprise, client.Nom, client.Prenom,
           client.Email, client.NumTel, client.MatFis AS ClientMat
    FROM facture
    LEFT JOIN client ON facture.MatFis = client.MatFis
    WHERE facture.NumFact = $id
");
if ($res->num_rows === 0) { header("Location: facture.php"); exit(); }
$fact = $res->fetch_assoc();

/* Load product lines */
$resLines = $conn->query("
    SELECT prodfact.qte,
           produit.IdProduit, produit.NomProduit, produit.PrixUnit, produit.poid,
           famille.typee, famille.tva, famille.NomFamille
    FROM prodfact
    JOIN produit ON prodfact.IdProduit = produit.IdProduit
    LEFT JOIN famille ON produit.IdFamille = famille.IdFamille
    WHERE prodfact.NumFact = $id
");
$lines = [];
while ($l = $resLines->fetch_assoc()) $lines[] = $l;

/* Totals */
$totalHT = 0; $totalTVA = 0;
foreach ($lines as $l) {
    $ht = $l['PrixUnit'] * $l['qte'];
    $totalHT  += $ht;
    $totalTVA += $ht * (($l['tva'] ?? 0) / 100);
}
$totalTTC   = $totalHT + $totalTVA;
$timbreFisc = 1.000;
$totalFinal = $totalTTC + $timbreFisc;

if (isset($_GET['success'])) $success = htmlspecialchars($_GET['success']);

$conn->close();
?>
