<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";

/* Allowed values for typee — must match your validation */
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
        if (mongoExists($matieres, 'IdMatiere', $idMat)) {
            $error = "Une matière avec cet ID existe déjà.";
        } else {
            try {
                $matiereDoc = [
                    'IdMatiere' => $idMat,
                    'NomMat' => $nom,
                    'typee' => $typee,
                    'descriptionn' => $desc,
                    'created_at' => new MongoDB\BSON\UTCDateTime()
                ];
                mongoInsert($matieres, $matiereDoc);
                $success = "Matière « $nom » créée avec succès !";
            } catch (Exception $e) {
                $error = "Erreur lors de la création : " . $e->getMessage();
            }
        }
    }
}
?>