<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";

/* Allowed values for typee */
$allowed_types = ['kg', 'g', 'L', 'ml', 'u'];

/* =============================================
   HANDLE NEW MATIERE CREATION
   ============================================= */
if (isset($_POST['create_matiere'])) {
    $idMat = intval($_POST['IdMatiere']);
    $nom   = trim($_POST['NomMat']);
    $typee = trim($_POST['typee']);
    $desc  = trim($_POST['descriptionn']);

    if ($idMat <= 0 || $nom === "") {
        $error = "L'ID et le nom de la matière sont obligatoires.";
    } elseif (!in_array($typee, $allowed_types)) {
        $error = "Type invalide. Valeurs acceptées : " . implode(', ', $allowed_types) . ".";
    } else {
        $collection = $db->matiere;
        $check = $collection->countDocuments(['IdMatiere' => $idMat]);
        if ($check > 0) {
            $error = "Une matière avec cet ID existe déjà.";
        } else {
            try {
                $insertResult = $collection->insertOne([
                    'IdMatiere' => $idMat,
                    'NomMat' => $nom,
                    'typee' => $typee,
                    'descriptionn' => $desc
                ]);
                if ($insertResult->getInsertedCount() === 1) {
                    $success = "Matière « $nom » créée avec succès !";
                } else {
                    $error = "Erreur lors de la création.";
                }
            } catch (Exception $e) {
                $error = "Erreur lors de la création : " . $e->getMessage();
            }
        }
    }
}
?>