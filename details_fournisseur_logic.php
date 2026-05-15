<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
if ($id === '') { header("Location: fournisseur.php"); exit(); }

$collection = $db->fournisseur;

/* =============================================
   HANDLE UPDATE
   ============================================= */
if (isset($_POST['update_fournisseur'])) {
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
        $checkTel = $collection->countDocuments(['NumTel' => $tel, 'Mat' => ['$ne' => $id]]);
        if ($checkTel > 0) {
            $error = "Ce numéro de téléphone est déjà utilisé par un autre fournisseur.";
        } else {
            try {
                $collection->updateOne(
                    ['Mat' => $id],
                    ['$set' => [
                        'Nom' => $nom,
                        'Prenom' => $prenom,
                        'NomEntreprise' => $entreprise,
                        'Email' => $email,
                        'NumTel' => $tel
                    ]]
                );
                $success = "Fournisseur mis à jour avec succès.";
            } catch (Exception $e) {
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}

/* =============================================
   HANDLE DELETE
   ============================================= */
if (isset($_POST['delete_fournisseur'])) {
    try {
        $collection->deleteOne(['Mat' => $id]);
        header("Location: fournisseur.php?success=Fournisseur+supprimé");
        exit();
    } catch (Exception $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

/* =============================================
   LOAD FOURNISSEUR + STOCK MATIERES
   ============================================= */
$fournisseur = $collection->findOne(['Mat' => $id]);
if (!$fournisseur) { header("Location: fournisseur.php"); exit(); }
$fournisseur = (array) $fournisseur;

/* Matieres supplied by this fournisseur (via stock_matiere) */
$resMatieres = $db->stock_matiere->find(['Mat' => $id]);
$matieres = [];
foreach ($resMatieres as $sm) {
    $smArray = (array) $sm;
    $matiereInfo = $db->matiere->findOne(['IdMatiere' => $smArray['IdMatiere'] ?? null]);
    if ($matiereInfo) {
        $smArray['NomMat'] = $matiereInfo['NomMat'];
        $smArray['typee'] = $matiereInfo['typee'];
    } else {
        $smArray['NomMat'] = 'Inconnu';
        $smArray['typee'] = '';
    }
    $matieres[] = $smArray;
}
?>
