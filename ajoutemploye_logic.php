<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";

/* =============================================
   HANDLE NEW EMPLOYE CREATION
   ============================================= */
if (isset($_POST['create_employe'])) {
    $cin    = trim($_POST['Cin']);
    $nom    = trim($_POST['Nom']);
    $prenom = trim($_POST['Prenom']);
    $email  = trim($_POST['Email']);
    $tel    = trim($_POST['NumTel']);

    /* Validation */
    if ($cin === "" || $email === "" || $tel === "") {
        $error = "Le CIN, l'email et le téléphone sont obligatoires.";
    } elseif (strlen($tel) !== 8 || !ctype_digit($tel)) {
        $error = "Le numéro de téléphone doit contenir exactement 8 chiffres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        $collection = $db->employeur;
        
        $checkCin = $collection->countDocuments(['Cin' => $cin]);
        if ($checkCin > 0) {
            $error = "Un employé avec ce CIN existe déjà.";
        } else {
            $checkTel = $collection->countDocuments(['NumTel' => $tel]);
            if ($checkTel > 0) {
                $error = "Ce numéro de téléphone est déjà utilisé par un autre employé.";
            } else {
                try {
                    $insertResult = $collection->insertOne([
                        'Cin' => $cin,
                        'Nom' => $nom,
                        'Prenom' => $prenom,
                        'Email' => $email,
                        'NumTel' => $tel
                    ]);
                    
                    if ($insertResult->getInsertedCount() === 1) {
                        $success = "Employé « $nom $prenom » créé avec succès !";
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
