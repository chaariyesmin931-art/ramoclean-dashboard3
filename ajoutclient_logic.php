<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";

/* =============================================
   HANDLE NEW CLIENT CREATION
   ============================================= */
if (isset($_POST['create_client'])) {
    $matfis    = trim($_POST['MatFis']);
    $nom       = trim($_POST['Nom']);
    $prenom    = trim($_POST['Prenom']);
    $entreprise = trim($_POST['NomEntreprise']);
    $email     = trim($_POST['Email']);
    $tel       = trim($_POST['NumTel']);

    /* Validation */
    if ($matfis === "" || $entreprise === "" || $email === "" || $tel === "") {
        $error = "Le matricule fiscale, l'entreprise, l'email et le téléphone sont obligatoires.";
    } elseif (strlen($tel) !== 8 || !ctype_digit($tel)) {
        $error = "Le numéro de téléphone doit contenir exactement 8 chiffres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        $collection = $db->client;
        
        /* Check MatFis uniqueness */
        $check = $collection->countDocuments(['MatFis' => $matfis]);
        if ($check > 0) {
            $error = "Un client avec ce matricule fiscale existe déjà.";
        } else {
            /* Check phone uniqueness */
            $checkTel = $collection->countDocuments(['NumTel' => $tel]);
            if ($checkTel > 0) {
                $error = "Ce numéro de téléphone est déjà utilisé par un autre client.";
            } else {
                try {
                    $insertResult = $collection->insertOne([
                        'MatFis' => $matfis,
                        'Nom' => $nom,
                        'Prenom' => $prenom,
                        'NomEntreprise' => $entreprise,
                        'Email' => $email,
                        'NumTel' => $tel
                    ]);
                    
                    if ($insertResult->getInsertedCount() === 1) {
                        $success = "Client « $entreprise » créé avec succès !";
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
