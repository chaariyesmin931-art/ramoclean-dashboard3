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
        /* Check MatFis uniqueness */
        if (mongoExists($clients, 'MatFis', $matfis)) {
            $error = "Un client avec ce matricule fiscale existe déjà.";
        } else {
            /* Check phone uniqueness */
            if (mongoExists($clients, 'NumTel', $tel)) {
                $error = "Ce numéro de téléphone est déjà utilisé par un autre client.";
            } else {
                try {
                    $clientDoc = [
                        'MatFis' => $matfis,
                        'Nom' => $nom,
                        'Prenom' => $prenom,
                        'NomEntreprise' => $entreprise,
                        'Email' => $email,
                        'NumTel' => $tel,
                        'created_at' => new MongoDB\BSON\UTCDateTime()
                    ];
                    mongoInsert($clients, $clientDoc);
                    $success = "Client « $entreprise » créé avec succès !";
                } catch (Exception $e) {
                    $error = "Erreur lors de la création : " . $e->getMessage();
                }
            }
        }
    }
}
?>
