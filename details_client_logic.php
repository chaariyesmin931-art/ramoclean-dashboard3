<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
if ($id === '') { header("Location: client.php"); exit(); }

$collection = $db->client;

/* =============================================
   HANDLE UPDATE
   ============================================= */
if (isset($_POST['update_client'])) {
    $nom        = trim($_POST['Nom']);
    $prenom     = trim($_POST['Prenom']);
    $entreprise = trim($_POST['NomEntreprise']);
    $email      = trim($_POST['Email']);
    $tel        = trim($_POST['NumTel']);

    if ($entreprise === "" || $email === "" || $tel === "") {
        $error = "L'entreprise, l'email et le téléphone sont obligatoires.";
    } elseif (strlen($tel) !== 8 || !ctype_digit($tel)) {
        $error = "Le téléphone doit contenir exactement 8 chiffres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        $checkTel = $collection->countDocuments(['NumTel' => $tel, 'MatFis' => ['$ne' => $id]]);
        if ($checkTel > 0) {
            $error = "Ce numéro de téléphone est déjà utilisé par un autre client.";
        } else {
            try {
                $collection->updateOne(
                    ['MatFis' => $id],
                    ['$set' => [
                        'Nom' => $nom,
                        'Prenom' => $prenom,
                        'NomEntreprise' => $entreprise,
                        'Email' => $email,
                        'NumTel' => $tel
                    ]]
                );
                $success = "Client mis à jour avec succès.";
            } catch (Exception $e) {
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}

/* =============================================
   HANDLE DELETE
   ============================================= */
if (isset($_POST['delete_client'])) {
    try {
        $collection->deleteOne(['MatFis' => $id]);
        header("Location: client.php?success=Client+supprimé");
        exit();
    } catch (Exception $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

/* =============================================
   LOAD CLIENT DATA
   ============================================= */
$client = $collection->findOne(['MatFis' => $id]);
if (!$client) { header("Location: client.php"); exit(); }
// Convert BSONDocument to array to be compatible with existing frontend code
$client = (array) $client;

/* Load factures for this client */
$facturesCollection = $db->facture;
$resFactures = $facturesCollection->find(
    ['MatFis' => $id],
    ['sort' => ['datefact' => -1]]
);
$factures = [];
foreach ($resFactures as $f) {
    $factures[] = (array) $f;
}
?>
