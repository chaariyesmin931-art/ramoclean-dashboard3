<?php require_once("auth.php"); ?>
<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = "";
$error   = "";

/* Allowed values for typee — matches DB CHECK constraint */
$allowed_types = ['kg', 'lit'];

/* =============================================
   HANDLE NEW FAMILLE CREATION
   ============================================= */
if (isset($_POST['create_famille'])) {
    $idFam  = intval($_POST['IdFamille']);
    $nomFam = mysqli_real_escape_string($conn, trim($_POST['NomFamille']));
    $typee  = mysqli_real_escape_string($conn, trim($_POST['typee']));
    $arome  = mysqli_real_escape_string($conn, trim($_POST['arome']));
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
        $check = $conn->query("SELECT IdFamille FROM famille WHERE IdFamille=$idFam");
        if ($check->num_rows > 0) {
            $error = "Une catégorie avec cet ID existe déjà.";
        } else {
            $conn->begin_transaction();
            try {
                /* Insert famille */
                $conn->query("INSERT INTO famille (IdFamille, NomFamille, typee, arome, tva)
                              VALUES ($idFam, '$nomFam', '$typee', '$arome', $tva)");

                /* Insert base recipe into famille_mat */
                foreach ($ingredients as $ing) {
                    $conn->query("INSERT INTO famille_mat (IdFamille, IdMatiere, qte_per_unit)
                                  VALUES ($idFam, {$ing['id']}, {$ing['qte']})");
                }

                $conn->commit();
                $success = "Catégorie « $nomFam » créée avec " . count($ingredients) . " ingrédient(s) de base !";

            } catch (Exception $e) {
                $conn->rollback();
                $error = "Erreur lors de la création : " . $e->getMessage();
            }
        }
    }
}

/* =============================================
   LOAD MATIERES FOR FORM
   ============================================= */
$allMatieres = [];
$resMat = $conn->query("SELECT IdMatiere, NomMat, typee FROM matiere ORDER BY NomMat");
while ($m = $resMat->fetch_assoc()) $allMatieres[] = $m;

$conn->close();
?>
