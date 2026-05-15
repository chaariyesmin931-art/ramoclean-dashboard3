<?php require_once("auth.php"); ?>
<?php
$conn = new mysqli("localhost", "root", "", "ramoclean");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = "";
$error   = "";

$allowed_types = ['kg', 'lit'];

/* =============================================
   HANDLE NEW PRODUCT CREATION
   ============================================= */
if (isset($_POST['create_produit'])) {
    $idProd = intval($_POST['IdProduit']);
    $idFam  = intval($_POST['IdFamille']);
    $nom    = mysqli_real_escape_string($conn, trim($_POST['NomProduit']));
    $poid   = floatval($_POST['poid']);
    $prix   = floatval($_POST['PrixUnit']);

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

    if ($idProd <= 0 || $idFam <= 0 || $nom === "" || $poid <= 0 || $prix <= 0) {
        $error = "Tous les champs du produit sont obligatoires.";
    } elseif (empty($ingredients)) {
        $error = "La recette ne peut pas être vide.";
    } else {
        $check = $conn->query("SELECT IdProduit FROM produit WHERE IdProduit=$idProd");
        if ($check->num_rows > 0) {
            $error = "Un produit avec cet ID existe déjà.";
        } else {
            $conn->begin_transaction();
            try {
                $conn->query("INSERT INTO produit (IdProduit, IdFamille, NomProduit, poid, PrixUnit)
                              VALUES ($idProd, $idFam, '$nom', $poid, $prix)");
                foreach ($ingredients as $ing) {
                    $conn->query("INSERT INTO prodmat (IdProduit, IdMatiere, qte)
                                  VALUES ($idProd, {$ing['id']}, {$ing['qte']})");
                }
                $conn->commit();
                $success = "Produit « $nom » créé avec succès avec " . count($ingredients) . " ingrédient(s) !";
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Erreur lors de la création : " . $e->getMessage();
            }
        }
    }
}

/* =============================================
   HANDLE INLINE CATEGORY CREATION
   ============================================= */
if (isset($_POST['create_famille'])) {
    $idFam  = intval($_POST['new_IdFamille']);
    $nomFam = mysqli_real_escape_string($conn, trim($_POST['new_NomFamille']));
    $typee  = mysqli_real_escape_string($conn, trim($_POST['new_typee']));
    $arome  = mysqli_real_escape_string($conn, trim($_POST['new_arome']));
    $tva    = intval($_POST['new_tva']);

    if ($idFam <= 0 || $nomFam === "") {
        $error = "L'ID et le nom de la catégorie sont obligatoires.";
    } elseif (!in_array($typee, $allowed_types)) {
        $error = "Type invalide. Valeurs acceptées : " . implode(', ', $allowed_types) . ".";
    } else {
        $check = $conn->query("SELECT IdFamille FROM famille WHERE IdFamille=$idFam");
        if ($check->num_rows > 0) {
            $error = "Une catégorie avec cet ID existe déjà.";
        } else {
            $sql = "INSERT INTO famille (IdFamille, NomFamille, typee, arome, tva)
                    VALUES ($idFam, '$nomFam', '$typee', '$arome', $tva)";
            if ($conn->query($sql)) {
                $success = "Catégorie « $nomFam » créée ! Vous pouvez maintenant la sélectionner.";
            } else {
                $error = strpos($conn->error, 'chk_type') !== false
                    ? "Type refusé. Valeurs autorisées : " . implode(', ', $allowed_types) . "."
                    : "Erreur : " . $conn->error;
            }
        }
    }
}

/* =============================================
   LOAD DATA FOR FORM
   ============================================= */
$familles = [];
$resFam = $conn->query("SELECT * FROM famille ORDER BY NomFamille");
while ($f = $resFam->fetch_assoc()) $familles[] = $f;

$allMatieres = [];
$resMat = $conn->query("SELECT IdMatiere, NomMat, typee FROM matiere ORDER BY NomMat");
while ($m = $resMat->fetch_assoc()) $allMatieres[] = $m;

/* Load famille_mat base recipes for JS auto-calculation */
$familleRecipes = [];
$resRec = $conn->query("
    SELECT famille_mat.IdFamille, famille_mat.IdMatiere,
           famille_mat.qte_per_unit, matiere.NomMat, matiere.typee AS mat_type
    FROM famille_mat
    JOIN matiere ON famille_mat.IdMatiere = matiere.IdMatiere
    ORDER BY famille_mat.IdFamille
");
if ($resRec) {
    while ($r = $resRec->fetch_assoc()) {
        $familleRecipes[$r['IdFamille']][] = $r;
    }
}

$conn->close();
?>
