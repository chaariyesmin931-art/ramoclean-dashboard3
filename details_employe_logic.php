<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
if ($id === '') { header("Location: employe.php"); exit(); }

$collection = $db->employeur;

/* =============================================
   HANDLE UPDATE
   ============================================= */
if (isset($_POST['update_employe'])) {
    $nom    = trim($_POST['Nom']);
    $prenom = trim($_POST['Prenom']);
    $email  = trim($_POST['Email']);
    $tel    = trim($_POST['NumTel']);

    if ($email === "" || $tel === "") {
        $error = "L'email et le téléphone sont obligatoires.";
    } elseif (strlen($tel) !== 8 || !ctype_digit($tel)) {
        $error = "Le téléphone doit contenir exactement 8 chiffres.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        $checkTel = $collection->countDocuments(['NumTel' => $tel, 'Cin' => ['$ne' => $id]]);
        if ($checkTel > 0) {
            $error = "Ce numéro de téléphone est déjà utilisé par un autre employé.";
        } else {
            try {
                $collection->updateOne(
                    ['Cin' => $id],
                    ['$set' => [
                        'Nom' => $nom,
                        'Prenom' => $prenom,
                        'Email' => $email,
                        'NumTel' => $tel
                    ]]
                );
                $success = "Employé mis à jour avec succès.";
            } catch (Exception $e) {
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}

/* =============================================
   HANDLE DELETE
   ============================================= */
if (isset($_POST['delete_employe'])) {
    try {
        $collection->deleteOne(['Cin' => $id]);
        header("Location: employe.php?success=Employé+supprimé");
        exit();
    } catch (Exception $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

/* =============================================
   LOAD EMPLOYE DATA
   ============================================= */
$employe = $collection->findOne(['Cin' => $id]);
if (!$employe) { header("Location: employe.php"); exit(); }
$employe = (array) $employe;
?>
