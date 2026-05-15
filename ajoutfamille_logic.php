<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";

/* Allowed values for typee */
$allowed_types = ['kg', 'lit'];

$familleCollection = $db->famille;
$familleMatCollection = $db->famille_mat;
$matiereCollection = $db->matiere;

/* =============================================
   HANDLE NEW FAMILLE CREATION
   ============================================= */
if (isset($_POST['create_famille'])) {
    $idFam  = intval($_POST['IdFamille']);
    $nomFam = trim($_POST['NomFamille']);
    $typee  = trim($_POST['typee']);
    $arome  = trim($_POST['arome']);
    $tva    = intval($_POST['tva']);

    /* Collect ingredients */
    $mat_ids  = isset($_POST['mat_id'])  ? $_POST['mat_id']  : [];
    $mat_qtes = isset($_POST['mat_qte']) ? $_POST['mat_qte'] : [];

    $ingredients = [];
    for ($i = 0; $i < count($mat_ids); $i++) {
        $mid  = intval($mat_ids[$i]);
        $mqte = floatval($mat_qtes[$i]);
        if ($mid > 0 && $mqte > 0) {
            $ingredients[] = ['id' => $mid, 'qte' => $mqte];
        }
    }

    if ($idFam <= 0 || $nomFam === "") {
        $error = "L'ID et le nom de la catégorie sont obligatoires.";
    } elseif (!in_array($typee, $allowed_types)) {
        $error = "Type invalide. Valeurs acceptées : " . implode(', ', $allowed_types) . ".";
    } elseif (empty($ingredients)) {
        $error = "Ajoutez au moins une matière première à la recette de base.";
    } else {
        $check = $familleCollection->countDocuments(['IdFamille' => $idFam]);
        if ($check > 0) {
            $error = "Une catégorie avec cet ID existe déjà.";
        } else {
            try {
                /* Insert famille */
                $familleCollection->insertOne([
                    'IdFamille' => $idFam,
                    'NomFamille' => $nomFam,
                    'typee' => $typee,
                    'arome' => $arome,
                    'tva' => $tva
                ]);

                /* Insert base recipe into famille_mat */
                foreach ($ingredients as $ing) {
                    $familleMatCollection->insertOne([
                        'IdFamille' => $idFam,
                        'IdMatiere' => $ing['id'],
                        'qte_per_unit' => $ing['qte']
                    ]);
                }

                $success = "Catégorie « $nomFam » créée avec " . count($ingredients) . " ingrédient(s) de base !";

            } catch (Exception $e) {
                // To safely handle partial inserts, we should ideally use sessions, but we'll try a manual delete
                $familleCollection->deleteOne(['IdFamille' => $idFam]);
                $familleMatCollection->deleteMany(['IdFamille' => $idFam]);
                $error = "Erreur lors de la création : " . $e->getMessage();
            }
        }
    }
}

/* =============================================
   LOAD MATIERES FOR FORM
   ============================================= */
$allMatieres = [];
$resMat = $matiereCollection->find([], ['sort' => ['NomMat' => 1]]);
foreach ($resMat as $m) {
    $allMatieres[] = (array) $m;
}

?>
