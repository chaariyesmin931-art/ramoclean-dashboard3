<?php require("categories_logic.php"); ?>
<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramo Clean — Catégories</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="categories.css">
</head>
<body>

<nav>
    <div class="nav-logo-area">
        <img src="logonobg.png" alt="Ramo Clean">
        <span class="nav-sub">100% Naturel</span>
    </div>
    <p class="nav-section-label">Menu</p>
    <ul>
        <li><a href="main.php">⬡ Dashboard</a></li>
        <li><a href="facture.php">⬡ Facture</a></li>
        <li><a href="produit.php" class="active">⬡ Produit</a></li>
        <li><a href="matiere.php">⬡ Matiere</a></li>
    </ul>
    <p class="nav-section-label" style="margin-top:10px;">Personnes</p>
    <ul>
        <li><a href="client.php">⬡ Client</a></li>
        <li><a href="fournisseur.php">⬡ Fournisseur</a></li>
        <li><a href="employe.php">⬡ Employe</a></li>
    </ul>
    <div class="nav-logout">
    <a href="logout.php">⬡ Déconnexion</a>
</div>
</nav>

<div class="dashboard">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="page-title">🏷️ Catégories</div>
        <div style="display:flex; gap:10px;">
            <a href="produit.php" class="btn">← Retour aux produits</a>
            <a href="ajoutfamille.php" class="btn-primary">+ Nouvelle catégorie</a>
        </div>
    </div>

    <!-- ALERTS -->
    <?php if ($success): ?>
    <div class="alert alert-success">✓ <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error">✗ <?php echo $error; ?></div>
    <?php endif; ?>

    <!-- SUMMARY -->
    <div style="display:flex; gap:14px; flex-wrap:wrap; margin-bottom:4px;">
        <div class="card" style="flex:1; min-width:150px;">
            <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.06em; color:var(--text-muted);">Total catégories</div>
            <div style="font-size:32px; font-weight:600; color:var(--olive-dark); margin-top:6px;"><?php echo count($categories); ?></div>
        </div>
        <div class="card" style="flex:1; min-width:150px;">
            <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.06em; color:var(--text-muted);">Avec recette</div>
            <div style="font-size:32px; font-weight:600; color:var(--olive-dark); margin-top:6px;">
                <?php echo count(array_filter($categories, fn($c) => isset($recipes[$c['IdFamille']]))); ?>
            </div>
        </div>
        <div class="card" style="flex:1; min-width:150px;">
            <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.06em; color:var(--text-muted);">Sans recette</div>
            <div style="font-size:32px; font-weight:600; color:#854f0b; margin-top:6px;">
                <?php echo count(array_filter($categories, fn($c) => !isset($recipes[$c['IdFamille']]))); ?>
            </div>
        </div>
    </div>

    <?php if (empty($categories)): ?>
    <div class="empty-state card" style="padding:40px; text-align:center;">
        Aucune catégorie trouvée.
        <a href="ajoutfamille.php" style="color:var(--olive-mid); font-weight:600; margin-left:8px;">Créer une catégorie →</a>
    </div>
    <?php endif; ?>

    <!-- CATEGORY CARDS -->
    <?php foreach ($categories as $cat):
        $catId     = $cat['IdFamille'];
        $catRecipe = $recipes[$catId] ?? [];
        $hasRecipe = !empty($catRecipe);
    ?>
    <div class="cat-card" id="cat-<?php echo $catId; ?>">

        <!-- HEADER (clickable to expand) -->
        <div class="cat-card-header" onclick="toggleCard(<?php echo $catId; ?>)">
            <div class="cat-header-left">
                <span class="cat-name"><?php echo htmlspecialchars($cat['NomFamille']); ?></span>
                <div class="cat-meta">
                    <span>📏 <?php echo htmlspecialchars($cat['typee']); ?></span>
                    <?php if ($cat['arome']): ?>
                    <span>🌿 <?php echo htmlspecialchars($cat['arome']); ?></span>
                    <?php endif; ?>
                    <span>🏷 TVA <?php echo $cat['tva']; ?>%</span>
                    <span>📦 <?php echo $cat['nb_produits']; ?> produit(s)</span>
                    <?php if ($hasRecipe): ?>
                    <span style="color:#2e5c1e;">🧪 <?php echo count($catRecipe); ?> ingrédient(s)</span>
                    <?php else: ?>
                    <span style="color:#854f0b;">⚠ Pas de recette</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="cat-header-right">
                <span class="pill <?php echo $hasRecipe ? 'pill-green' : 'pill-amber'; ?>">
                    <?php echo $hasRecipe ? 'Recette OK' : 'Sans recette'; ?>
                </span>
                <span class="chevron" id="chevron-<?php echo $catId; ?>">›</span>
            </div>
        </div>

        <!-- BODY (collapsed by default) -->
        <div class="cat-card-body" id="body-<?php echo $catId; ?>">
            <div class="cat-two-col">

                <!-- EDIT FORM -->
                <div>
                    <div style="font-size:13px; font-weight:600; color:var(--olive-dark); margin-bottom:12px;">
                        ✏️ Modifier les informations
                    </div>
                    <form method="POST" action="categories.php#cat-<?php echo $catId; ?>">
                        <input type="hidden" name="IdFamille" value="<?php echo $catId; ?>">
                        <div class="cat-edit-grid">

                            <div class="form-group full">
                                <label>Nom de la catégorie *</label>
                                <input type="text" name="NomFamille" maxlength="24" required
                                    value="<?php echo htmlspecialchars($cat['NomFamille']); ?>">
                            </div>

                            <div class="form-group full">
                                <label>Type / Unité *</label>
                                <div class="type-options">
                                    <?php
                                    $types = ['kg' => 'kg', 'lit' => 'lit'];
                                    foreach ($types as $val => $label):
                                    ?>
                                    <label class="type-option">
                                        <input type="radio" name="typee" value="<?php echo $val; ?>"
                                            <?php echo ($cat['typee'] === $val) ? 'checked' : ''; ?>>
                                        <span><?php echo $label; ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Arôme</label>
                                <input type="text" name="arome" maxlength="24" placeholder="ex: Lavande"
                                    value="<?php echo htmlspecialchars($cat['arome'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label>TVA (%)</label>
                                <input type="number" name="tva" min="0" max="100"
                                    value="<?php echo $cat['tva']; ?>">
                            </div>

                        </div>
                        <div class="form-actions" style="margin-top:12px;">
                            <button type="submit" name="update_famille" class="btn-primary">💾 Enregistrer</button>
                        </div>
                    </form>
                </div>

                <!-- RECIPE -->
                <div>
                    <div style="font-size:13px; font-weight:600; color:var(--olive-dark); margin-bottom:12px;">
                        🧪 Recette de base
                        <span style="font-size:11px; color:var(--text-muted); font-weight:400;">
                            (par 1 <?php echo htmlspecialchars($cat['typee']); ?>)
                        </span>
                    </div>

                    <?php if (empty($catRecipe)): ?>
                    <p style="font-size:13px; color:var(--text-muted); margin-bottom:12px;">
                        Aucun ingrédient. Ajoutez-en ci-dessous.
                    </p>
                    <?php else: ?>
                    <table class="recipe-table">
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Qté / <?php echo htmlspecialchars($cat['typee']); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($catRecipe as $r): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($r['NomMat']); ?></strong><br>
                                <small style="color:var(--text-muted);"><?php echo $r['mat_type']; ?></small>
                            </td>
                            <td><?php echo $r['qte_per_unit']; ?></td>
                            <td>
                                <a href="categories.php?fam=<?php echo $catId; ?>&remove_mat=<?php echo $r['IdMatiere']; ?>"
                                   style="color:#a32d2d; font-size:12px; font-weight:600;"
                                   onclick="return confirm('Supprimer cet ingrédient de la recette ?')">✕</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>

                    <!-- Add ingredient to recipe -->
                    <?php if (!empty($allMatieres)): ?>
                    <form method="POST" action="categories.php#cat-<?php echo $catId; ?>">
                        <input type="hidden" name="recipe_IdFamille" value="<?php echo $catId; ?>">
                        <div class="add-recipe-row">
                            <div class="form-group">
                                <label>Matière</label>
                                <select name="recipe_IdMatiere" required>
                                    <option value="">— Choisir —</option>
                                    <?php foreach ($allMatieres as $m): ?>
                                    <option value="<?php echo $m['IdMatiere']; ?>">
                                        <?php echo htmlspecialchars($m['NomMat']); ?> (<?php echo $m['typee']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Qté / <?php echo htmlspecialchars($cat['typee']); ?></label>
                                <input type="number" name="recipe_qte" step="0.001" min="0.001"
                                    placeholder="ex: 0.800" required>
                            </div>
                            <button type="submit" name="add_recipe_mat" class="btn-primary"
                                style="height:38px; font-size:13px; white-space:nowrap;">
                                + Ajouter
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                    <p style="font-size:12px; color:#854f0b; margin-top:10px;">
                        ⚠ <a href="ajoutmatiere.php" style="color:#854f0b; font-weight:600;">Créer une matière</a>
                        avant d'ajouter une recette.
                    </p>
                    <?php endif; ?>
                </div>

            </div>

            <!-- DANGER ZONE -->
            <div class="cat-danger">
                <p>⚠ Supprimer cette catégorie — impossible si des produits y sont rattachés.</p>
                <a href="categories.php?delete=<?php echo $catId; ?>"
                   class="btn-danger"
                   onclick="return confirm('Supprimer définitivement la catégorie « <?php echo htmlspecialchars($cat['NomFamille']); ?> » ?')">
                    🗑 Supprimer
                </a>
            </div>

        </div>
    </div>
    <?php endforeach; ?>

</div>

<script>
function toggleCard(id) {
    const body    = document.getElementById('body-' + id);
    const chevron = document.getElementById('chevron-' + id);
    const isOpen  = body.classList.toggle('open');
    chevron.classList.toggle('open', isOpen);
}

/* Auto-open the card that was just edited / had an error */
<?php
$anchorId = null;
if (isset($_POST['IdFamille']))       $anchorId = intval($_POST['IdFamille']);
elseif (isset($_POST['recipe_IdFamille'])) $anchorId = intval($_POST['recipe_IdFamille']);
if ($anchorId):
?>
document.addEventListener('DOMContentLoaded', () => {
    const body    = document.getElementById('body-<?php echo $anchorId; ?>');
    const chevron = document.getElementById('chevron-<?php echo $anchorId; ?>');
    if (body)    body.classList.add('open');
    if (chevron) chevron.classList.add('open');
    document.getElementById('cat-<?php echo $anchorId; ?>')
        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
});
<?php endif; ?>
</script>

</body>
</html>
