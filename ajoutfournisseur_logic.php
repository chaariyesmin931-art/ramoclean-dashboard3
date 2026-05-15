<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";

/* =============================================
   HANDLE NEW FOURNISSEUR CREATION
   ============================================= */
if (isset($_POST['create_fournisseur'])) {
    $mat        = trim($_POST['Mat']);
    $nom        = trim($_POST['Nom']);
    $prenom     = trim($_POST['Prenom']);
    $entreprise = trim($_POST['NomEntreprise']);
    $email      = trim($_POST['Email']);
    $tel        = trim($_POST['NumTel']);

    /* Validation */
    if ($mat === "" || $entreprise === "" || $email === "" || $tel === "") {
        $error = "Le matricule, l'entreprise, l'email et le téléphone sont obligatoires.";
    } elseif (strlen($tel) !== 8 || !ctype_digit($tel)) {
        $error = "Le numéro de téléphone doit contenir exactement 8 chiffres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        $collection = $db->fournisseur;

        $checkMat = $collection->countDocuments(['Mat' => $mat]);
        if ($checkMat > 0) {
            $error = "Un fournisseur avec ce matricule existe déjà.";
        } else {
            $checkTel = $collection->countDocuments(['NumTel' => $tel]);
            if ($checkTel > 0) {
                $error = "Ce numéro de téléphone est déjà utilisé par un autre fournisseur.";
            } else {
                try {
                    $insertResult = $collection->insertOne([
                        'Mat' => $mat,
                        'Nom' => $nom,
                        'Prenom' => $prenom,
                        'NomEntreprise' => $entreprise,
                        'Email' => $email,
                        'NumTel' => $tel
                    ]);
                    
                    if ($insertResult->getInsertedCount() === 1) {
                        $success = "Fournisseur « $entreprise » créé avec succès !";
                    } else {
                        $error = "Erreur lors de la création.";
                    }
                } catch (Exception $e) {
                    $error = "Erreur lors de la création : " . $e->getMessage();
                }
            }
        }
    }
}
?>
