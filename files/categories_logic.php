<?php require_once("auth.php"); ?>
<?php
require_once("connexion.php");

$success = "";
$error   = "";
$allowed_types = ['kg', 'lit'];

/* =============================================
   HANDLE DELETE CATEGORY
   ============================================= */
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    try {
        /* Check if any products use this category */
        $check = $produits->countDocuments(['IdFamille' => $delId]);
        if ($check > 0) {
            $error = "Impossible de supprimer : des produits utilisent cette catégorie.";
        } else {
            mongoDelete($familles, ['IdFamille' => $delId]);
            header("Location: categories.php?success=Catégorie+supprimée");
            exit();
        }
    } catch (Exception $e) {
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

/* =============================================
   HANDLE UPDATE CATEGORY INFO
   ============================================= */
if (isset($_POST['update_famille'])) {
    $editId  = intval($_POST['IdFamille']);
    $nom     = trim($_POST['NomFamille']);
    $typee   = trim($_POST['typee']);
    $arome   = trim($_POST['arome']);
    $tva     = intval($_POST['tva']);

    if ($nom === "") {
        $error = "Le nom de la catégorie est obligatoire.";
    } elseif (!in_array($typee, $allowed_types)) {
        $error = "Type invalide. Valeurs acceptées : " . implode(', ', $allowed_types) . ".";
    } else {
        try {
            $updateData = [
                'NomFamille' => $nom,
                'typee' => $typee,
                'arome' => $arome,
                'tva' => $tva
            ];
            mongoUpdate($familles, ['IdFamille' => $editId], $updateData);
            $success = "Catégorie mise à jour avec succès.";
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

/* =============================================
   HANDLE ADD MATIERE TO RECIPE (base_recipes)
   ============================================= */
if (isset($_POST['add_recipe_mat'])) {
    $famId  = intval($_POST['recipe_IdFamille']);
    $matId  = intval($_POST['recipe_IdMatiere']);
    $qte    = floatval($_POST['recipe_qte']);

    if ($matId <= 0 || $qte <= 0) {
        $error = "Veuillez choisir une matière et une quantité valide.";
    } else {
        try {
            $famille = mongoFindOne($familles, ['IdFamille' => $famId]);
            if (!$famille) {
                $error = "Catégorie non trouvée.";
            } else {
                /* Check if matière already in recipe */
                $recipes = $famille['base_recipes'] ?? [];
                $found = false;
                foreach ($recipes as &$recipe) {
                    if ($recipe['IdMatiere'] == $matId) {
                        $recipe['qte_per_unit'] = $qte;
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $recipes[] = [
                        'IdMatiere' => $matId,
                        'qte_per_unit' => $qte
                    ];
                }
                
                mongoUpdate($familles, ['IdFamille' => $famId], ['base_recipes' => $recipes]);
                $success = "Recette mise à jour.";
            }
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

/* =============================================
   HANDLE REMOVE MATIERE FROM RECIPE
   ============================================= */
if (isset($_GET['remove_mat'])) {
    $famId = intval($_GET['fam']);
    $matId = intval($_GET['remove_mat']);
    try {
        $famille = mongoFindOne($familles, ['IdFamille' => $famId]);
        if ($famille && isset($famille['base_recipes'])) {
            $recipes = array_filter($famille['base_recipes'], function($r) use ($matId) {
                return $r['IdMatiere'] != $matId;
            });
            mongoUpdate($familles, ['IdFamille' => $famId], ['base_recipes' => array_values($recipes)]);
        }
        header("Location: categories.php?success=Ingrédient+supprimé+de+la+recette#cat-$famId");
        exit();
    } catch (Exception $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

/* =============================================
   LOAD ALL CATEGORIES WITH RECIPE
   ============================================= */
$categories = [];
try {
    /* Use aggregation to get product count */
    $pipeline = [
        [
            '$lookup' => [
                'from' => 'produits',
                'localField' => 'IdFamille',
                'foreignField' => 'IdFamille',
                'as' => 'products'
            ]
        ],
        [
            '$project' => [
                'IdFamille' => 1,
                'NomFamille' => 1,
                'typee' => 1,
                'arome' => 1,
                'tva' => 1,
                'base_recipes' => 1,
                'nb_produits' => ['$size' => '$products']
            ]
        ],
        ['$sort' => ['NomFamille' => 1]]
    ];
    
    $result = mongoAggregate($familles, $pipeline);
    foreach ($result as $c) {
        $categories[] = $c;
    }
} catch (Exception $e) {
    $error = "Erreur lors du chargement des catégories : " . $e->getMessage();
}

/* Load recipes for all categories */
$recipes = [];
try {
    $famillesWithRecipes = mongoFindAll($familles, ['base_recipes' => ['$exists' => true]]);
    foreach ($famillesWithRecipes as $fam) {
        if (isset($fam['base_recipes'])) {
            $recipes[$fam['IdFamille']] = $fam['base_recipes'];
        }
    }
} catch (Exception $e) {
    // Silently handle
}

/* Load all matieres for recipe add form */
$allMatieres = [];
try {
    $matieresResult = mongoFindAll($matieres, []);
    foreach ($matieresResult as $m) {
        $allMatieres[] = $m;
    }
} catch (Exception $e) {
    $error = "Erreur lors du chargement des matières : " . $e->getMessage();
}

if (isset($_GET['success'])) $success = htmlspecialchars($_GET['success']);
?>

