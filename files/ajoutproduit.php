<?php require("ajoutproduit_logic.php"); ?>
<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramo Clean — Ajouter Produit</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="ajoutproduit.css">
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

    <div class="topbar">
        <div class="page-title">➕ Ajouter un produit</div>
        <a href="produit.php" class="btn">← Retour aux produits</a>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success">✓ <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error">✗ <?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="ajoutproduit.php">

        <!-- PRODUCT INFO -->
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header">
                <span class="card-title">📦 Informations du produit</span>
            </div>
            <div class="form-grid">

                <div class="form-group">
                    <label>ID Produit *</label>
                    <input type="number" name="IdProduit" placeholder="ex: 114" min="1" required
                        value="<?php echo (isset($_POST['IdProduit']) && !$success) ? htmlspecialchars($_POST['IdProduit']) : ''; ?>">
                    <span class="form-hint">Identifiant unique du produit</span>
                </div>

                <div class="form-group">
                    <label>Nom du produit *</label>
                    <input type="text" name="NomProduit" placeholder="ex: Savon Lavande 200g" maxlength="24" required
                        value="<?php echo (isset($_POST['NomProduit']) && !$success) ? htmlspecialchars($_POST['NomProduit']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Poids *</label>
                    <input type="number" name="poid" id="poid-input" step="0.001" min="0.001" required
                        placeholder="ex: 0.200"
                        value="<?php echo (isset($_POST['poid']) && !$success) ? htmlspecialchars($_POST['poid']) : ''; ?>"
                        oninput="autoCalcRecipe()">
                    <span class="form-hint" id="poid-hint">Sélectionnez d'abord une catégorie</span>
                </div>

                <div class="form-group">
                    <label>Prix unitaire (DT) *</label>
                    <input type="number" name="PrixUnit" placeholder="ex: 2.500" step="0.001" min="0.001" required
                        value="<?php echo (isset($_POST['PrixUnit']) && !$success) ? htmlspecialchars($_POST['PrixUnit']) : ''; ?>">
                </div>

                <div class="form-group full">
                    <label>Catégorie *</label>
                    <select name="IdFamille" id="famille-select" required onchange="onCategoryChange(this.value)">
                        <option value="">— Choisir une catégorie —</option>
                        <?php foreach ($familles as $f): ?>
                        <option value="<?php echo $f['IdFamille']; ?>"
                            <?php echo (isset($_POST['IdFamille']) && $_POST['IdFamille'] == $f['IdFamille']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($f['NomFamille']); ?>
                            — <?php echo htmlspecialchars($f['typee']); ?>
                            <?php echo $f['arome'] ? '· ' . htmlspecialchars($f['arome']) : ''; ?>
                            (TVA <?php echo $f['tva']; ?>%)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group full">
                    <div class="cat-preview" id="cat-preview">
                        <div class="cat-field"><span>Nom</span><span id="prev-nom">—</span></div>
                        <div class="cat-field"><span>Type / Unité</span><span id="prev-type">—</span></div>
                        <div class="cat-field"><span>Arôme</span><span id="prev-arome">—</span></div>
                        <div class="cat-field"><span>TVA</span><span id="prev-tva">—</span></div>
                    </div>
                </div>

                <div class="form-group full">
                    <button type="button" class="toggle-new-cat" onclick="toggleNewCat()">
                        <span id="toggle-icon">✦</span>
                        <span id="toggle-label">Catégorie introuvable ? En créer une rapide</span>
                    </button>
                    <span class="form-hint" style="margin-top:6px;">
                        Pour une catégorie avec recette complète :
                        <a href="ajoutfamille.php" style="color:var(--blue-spark);font-weight:600;">Créer une catégorie →</a>
                    </span>
                </div>

            </div>
        </div>

        <!-- RECIPE -->
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header">
                <span class="card-title">🧪 Recette — Ingrédients par unité produite *</span>
                <button type="button" class="btn" onclick="addIngredientRow()">+ Ajouter matière</button>
            </div>

            <div id="auto-calc-notice" style="display:none; margin-bottom:14px;">
                <div class="alert alert-success" style="font-size:13px; padding:10px 14px;">
                    ✦ <span>Recette chargée depuis la catégorie. Entrez le poids pour adapter les quantités.</span>
                </div>
            </div>

            <div id="no-recipe-notice" style="display:none; margin-bottom:14px;">
                <div class="alert alert-warning" style="font-size:13px; padding:10px 14px;">
                    ⚠ Cette catégorie n'a pas de recette de base. Ajoutez les ingrédients manuellement
                    ou <a href="categories.php" style="color:#854f0b; font-weight:600;">configurez la recette de la catégorie →</a>
                </div>
            </div>

            <p style="font-size:13px; color:var(--text-soft); margin-bottom:14px; line-height:1.6;">
                Sélectionnez une catégorie pour charger automatiquement sa recette de base.
                Entrez ensuite le poids pour adapter les quantités. Vous pouvez tout modifier librement.
            </p>

            <div id="ingredients-container">
                <?php
                $prev_ids  = isset($_POST['mat_id'])  ? $_POST['mat_id']  : [];
                $prev_qtes = isset($_POST['mat_qte']) ? $_POST['mat_qte'] : [];
                if (!empty($prev_ids) && !$success):
                    for ($i = 0; $i < count($prev_ids); $i++):
                        $pid  = intval($prev_ids[$i]);
                        $pqte = floatval($prev_qtes[$i]);
                        if ($pid <= 0) continue;
                ?>
                <div class="ingredient-row" id="row-<?php echo $i; ?>">
                    <div class="form-group" style="flex:2;">
                        <label>Matière première</label>
                        <select name="mat_id[]" required>
                            <option value="">— Choisir —</option>
                            <?php foreach ($allMatieres as $m): ?>
                            <option value="<?php echo $m['IdMatiere']; ?>"
                                <?php echo ($m['IdMatiere'] == $pid) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($m['NomMat']); ?> (<?php echo $m['typee']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Quantité</label>
                        <input type="number" name="mat_qte[]" step="0.001" min="0.001" required
                            value="<?php echo $pqte; ?>">
                    </div>
                    <button type="button" class="remove-row-btn" onclick="removeRow('row-<?php echo $i; ?>')">✕</button>
                </div>
                <?php endfor; else: ?>
                <div class="ingredient-row" id="row-0">
                    <div class="form-group" style="flex:2;">
                        <label>Matière première</label>
                        <select name="mat_id[]" required>
                            <option value="">— Choisir —</option>
                            <?php foreach ($allMatieres as $m): ?>
                            <option value="<?php echo $m['IdMatiere']; ?>">
                                <?php echo htmlspecialchars($m['NomMat']); ?> (<?php echo $m['typee']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Quantité</label>
                        <input type="number" name="mat_qte[]" step="0.001" min="0.001" required placeholder="ex: 0.200">
                    </div>
                    <button type="button" class="remove-row-btn" onclick="removeRow('row-0')" style="visibility:hidden;">✕</button>
                </div>
                <?php endif; ?>
            </div>

            <?php if (empty($allMatieres)): ?>
            <div class="alert alert-warning" style="margin-top:12px; font-size:13px;">
                ⚠ Aucune matière première trouvée.
                <a href="ajoutmatiere.php" style="color:#854f0b;font-weight:600;">Créer une matière →</a>
            </div>
            <?php endif; ?>
        </div>

        <div style="display:flex; gap:12px; justify-content:flex-end;">
            <a href="produit.php" class="btn">Annuler</a>
            <button type="submit" name="create_produit" class="btn-primary">✓ Créer le produit</button>
        </div>

    </form>

    <!-- INLINE QUICK CATEGORY -->
    <div class="new-cat-section" id="new-cat-section" style="margin-top:18px;">
        <div class="new-cat-title">✦ Créer une catégorie rapide (sans recette de base)</div>
        <p style="font-size:12px; color:#1060a0; margin-bottom:14px;">
            Pour une catégorie complète avec recette :
            <a href="ajoutfamille.php" style="color:#1060a0;font-weight:700;">Créer une catégorie →</a>
        </p>
        <form method="POST" action="ajoutproduit.php">
            <div class="form-grid">
                <div class="form-group">
                    <label>ID Catégorie *</label>
                    <input type="number" name="new_IdFamille" placeholder="ex: 1112" min="1" required
                        value="<?php echo (isset($_POST['new_IdFamille']) && !$success) ? htmlspecialchars($_POST['new_IdFamille']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="new_NomFamille" placeholder="ex: Liquide vaisselle" maxlength="24" required
                        value="<?php echo (isset($_POST['new_NomFamille']) && !$success) ? htmlspecialchars($_POST['new_NomFamille']) : ''; ?>">
                </div>
                <div class="form-group full">
                    <label>Type / Unité *</label>
                    <div class="type-options">
                        <?php
                        $types = ['kg' => 'Kilogramme (kg)', 'lit' => 'Litre (lit)'];
                        $sel_type = isset($_POST['new_typee']) ? $_POST['new_typee'] : 'kg';
                        foreach ($types as $val => $label):
                        ?>
                        <label class="type-option">
                            <input type="radio" name="new_typee" value="<?php echo $val; ?>"
                                <?php echo ($sel_type === $val) ? 'checked' : ''; ?>>
                            <span><?php echo $label; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label>Arôme</label>
                    <input type="text" name="new_arome" placeholder="ex: Lavande" maxlength="24"
                        value="<?php echo isset($_POST['new_arome']) ? htmlspecialchars($_POST['new_arome']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>TVA (%)</label>
                    <input type="number" name="new_tva" min="0" max="100"
                        value="<?php echo isset($_POST['new_tva']) ? htmlspecialchars($_POST['new_tva']) : '19'; ?>">
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="button" class="btn" onclick="toggleNewCat()">Annuler</button>
                <button type="submit" name="create_famille" class="btn-spark">✦ Créer</button>
            </div>
        </form>
    </div>

</div>

<div id="cat-data" style="display:none;"><?php echo json_encode($familles); ?></div>
<div id="matieres-data" style="display:none;"><?php echo json_encode($allMatieres); ?></div>
<div id="recipes-data" style="display:none;"><?php echo json_encode($familleRecipes); ?></div>

<script>
const familles       = JSON.parse(document.getElementById('cat-data').textContent);
const matieres       = JSON.parse(document.getElementById('matieres-data').textContent);
const familleRecipes = JSON.parse(document.getElementById('recipes-data').textContent);
let rowCount         = <?php echo max(1, count($prev_ids ?? [])); ?>;

function matOptions(selectedId) {
    let html = '<option value="">— Choisir —</option>';
    matieres.forEach(m => {
        const sel = m.IdMatiere == selectedId ? 'selected' : '';
        html += `<option value="${m.IdMatiere}" ${sel}>${m.NomMat} (${m.typee})</option>`;
    });
    return html;
}

function addIngredientRow(matId, qte) {
    const container = document.getElementById('ingredients-container');
    const id = 'row-' + rowCount++;
    const div = document.createElement('div');
    div.className = 'ingredient-row';
    div.id = id;
    div.innerHTML = `
        <div class="form-group" style="flex:2;">
            <label>Matière première</label>
            <select name="mat_id[]" required>${matOptions(matId || null)}</select>
        </div>
        <div class="form-group" style="flex:1;">
            <label>Quantité</label>
            <input type="number" name="mat_qte[]" step="0.001" min="0.001" required
                placeholder="ex: 0.200" value="${qte !== undefined ? qte : ''}">
        </div>
        <button type="button" class="remove-row-btn" onclick="removeRow('${id}')">✕</button>
    `;
    container.appendChild(div);
    updateRemoveButtons();
}

function removeRow(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.ingredient-row');
    rows.forEach(row => {
        const btn = row.querySelector('.remove-row-btn');
        if (btn) btn.style.visibility = rows.length > 1 ? 'visible' : 'hidden';
    });
}

function onCategoryChange(id) {
    const preview = document.getElementById('cat-preview');
    if (!id) { preview.classList.remove('visible'); return; }
    const f = familles.find(x => x.IdFamille == id);
    if (!f)  { preview.classList.remove('visible'); return; }

    document.getElementById('prev-nom').textContent   = f.NomFamille;
    document.getElementById('prev-type').textContent  = f.typee;
    document.getElementById('prev-arome').textContent = f.arome || '—';
    document.getElementById('prev-tva').textContent   = f.tva + '%';
    document.getElementById('poid-hint').textContent  = `Entrez le poids en ${f.typee}`;
    preview.classList.add('visible');

    const recipe = familleRecipes[id];
    if (!recipe || recipe.length === 0) {
        /* No base recipe — clear rows and show empty state */
        document.getElementById('ingredients-container').innerHTML = '';
        rowCount = 0;
        addIngredientRow(); /* one blank row */
        document.getElementById('auto-calc-notice').style.display = 'none';
        document.getElementById('no-recipe-notice').style.display = 'block';
        return;
    }

    document.getElementById('no-recipe-notice').style.display = 'none';

    /* Load base recipe quantities immediately (poid = 1 means show raw per-unit values) */
    const poid = parseFloat(document.getElementById('poid-input').value);
    const multiplier = (poid && poid > 0) ? poid : 1;

    const container = document.getElementById('ingredients-container');
    container.innerHTML = '';
    rowCount = 0;

    recipe.forEach(r => {
        const calcQte = (parseFloat(r.qte_per_unit) * multiplier).toFixed(3);
        addIngredientRow(r.IdMatiere, calcQte);
    });

    const notice = document.getElementById('auto-calc-notice');
    notice.style.display = 'block';
    notice.querySelector('span').textContent = (poid && poid > 0)
        ? `Recette calculée automatiquement (catégorie × ${poid}). Modifiez si nécessaire.`
        : `Recette chargée depuis la catégorie. Entrez le poids pour adapter les quantités.`;

    updateRemoveButtons();
}

function autoCalcRecipe() {
    const famId = document.getElementById('famille-select').value;
    const poid  = parseFloat(document.getElementById('poid-input').value);
    if (!famId) return;

    const recipe = familleRecipes[famId];
    if (!recipe || recipe.length === 0) return;

    /* Update quantities in existing rows without rebuilding them */
    const rows = document.querySelectorAll('.ingredient-row');
    const multiplier = (poid && poid > 0) ? poid : 1;

    recipe.forEach((r, i) => {
        const row = rows[i];
        if (!row) return;
        const qteInput = row.querySelector('input[name="mat_qte[]"]');
        if (qteInput) {
            qteInput.value = (parseFloat(r.qte_per_unit) * multiplier).toFixed(3);
        }
    });

    const notice = document.getElementById('auto-calc-notice');
    notice.style.display = 'block';
    notice.querySelector('span').textContent = (poid && poid > 0)
        ? `Recette calculée automatiquement (catégorie × ${poid}). Modifiez si nécessaire.`
        : `Recette chargée depuis la catégorie. Entrez le poids pour adapter les quantités.`;
}

function toggleNewCat() {
    const section = document.getElementById('new-cat-section');
    const icon    = document.getElementById('toggle-icon');
    const label   = document.getElementById('toggle-label');
    const visible = section.classList.toggle('visible');
    icon.textContent  = visible ? '✕' : '✦';
    label.textContent = visible ? 'Masquer' : 'Catégorie introuvable ? En créer une rapide';
    if (visible) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

<?php if ($error && isset($_POST['create_famille'])): ?>
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('new-cat-section').classList.add('visible');
    document.getElementById('toggle-icon').textContent  = '✕';
    document.getElementById('toggle-label').textContent = 'Masquer';
});
<?php endif; ?>

document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('famille-select');
    if (sel.value) onCategoryChange(sel.value);
    updateRemoveButtons();
});
</script>

</body>
</html>
