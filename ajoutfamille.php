<?php require("ajoutfamille_logic.php"); ?>
<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramo Clean — Ajouter Catégorie</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="ajoutfamille.css">
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
        <div class="page-title">➕ Ajouter une catégorie</div>
        <a href="produit.php" class="btn">← Retour aux produits</a>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success">✓ <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error">✗ <?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="ajoutfamille.php">

        <!-- CATEGORY INFO -->
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header">
                <span class="card-title">🏷️ Informations de la catégorie</span>
            </div>
            <div class="form-grid">

                <div class="form-group">
                    <label>ID Catégorie *</label>
                    <input type="number" name="IdFamille" placeholder="ex: 1112" min="1" required
                        value="<?php echo (isset($_POST['IdFamille']) && !$success) ? htmlspecialchars($_POST['IdFamille']) : ''; ?>">
                    <span class="form-hint">Identifiant unique</span>
                </div>

                <div class="form-group">
                    <label>Nom de la catégorie *</label>
                    <input type="text" name="NomFamille" placeholder="ex: Savon solide" maxlength="24" required
                        value="<?php echo (isset($_POST['NomFamille']) && !$success) ? htmlspecialchars($_POST['NomFamille']) : ''; ?>">
                </div>

                <div class="form-group full">
                    <label>Type / Unité de base *</label>
                    <div class="type-options">
                        <?php
                        $types    = ['kg' => 'Kilogramme (kg)', 'lit' => 'Litre (lit)'];
                        $sel_type = isset($_POST['typee']) ? $_POST['typee'] : 'kg';
                        foreach ($types as $val => $label):
                        ?>
                        <label class="type-option" onclick="updateUnitLabel('<?php echo $val; ?>')">
                            <input type="radio" name="typee" value="<?php echo $val; ?>"
                                id="type-<?php echo $val; ?>"
                                <?php echo ($sel_type === $val) ? 'checked' : ''; ?>>
                            <span><?php echo $label; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <span class="form-hint">Les quantités de la recette seront par 1 unité de ce type</span>
                </div>

                <div class="form-group">
                    <label>Arôme</label>
                    <input type="text" name="arome" placeholder="ex: Lavande, Citron…" maxlength="24"
                        value="<?php echo (isset($_POST['arome']) && !$success) ? htmlspecialchars($_POST['arome']) : ''; ?>">
                    <span class="form-hint">Optionnel</span>
                </div>

                <div class="form-group">
                    <label>TVA (%)</label>
                    <input type="number" name="tva" placeholder="ex: 19" min="0" max="100"
                        value="<?php echo (isset($_POST['tva']) && !$success) ? htmlspecialchars($_POST['tva']) : '19'; ?>">
                </div>

            </div>
        </div>

        <!-- BASE RECIPE -->
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header">
                <span class="card-title">🧪 Recette de base</span>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="unit-label" id="unit-label">par 1 <?php echo $sel_type; ?></span>
                    <button type="button" class="btn" onclick="addIngredientRow()">+ Ajouter matière</button>
                </div>
            </div>

            <p style="font-size:13px; color:var(--text-soft); margin-bottom:14px; line-height:1.6;">
                Indiquez les matières nécessaires pour produire <strong>1 <span id="unit-word"><?php echo $sel_type; ?></span></strong>
                de cette catégorie. Les produits hériteront automatiquement de cette recette
                proportionnellement à leur poids.
            </p>

            <?php if (empty($allMatieres)): ?>
            <div class="alert alert-warning" style="font-size:13px;">
                ⚠ Aucune matière première trouvée.
                <a href="ajoutmatiere.php" style="color:#854f0b; font-weight:600;">Créer une matière →</a>
            </div>
            <?php else: ?>

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
                        <label>Qté par 1 <span class="row-unit"><?php echo $sel_type; ?></span></label>
                        <input type="number" name="mat_qte[]" step="0.001" min="0.001" required
                            placeholder="ex: 0.800" value="<?php echo $pqte; ?>">
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
                        <label>Qté par 1 <span class="row-unit"><?php echo $sel_type; ?></span></label>
                        <input type="number" name="mat_qte[]" step="0.001" min="0.001" required placeholder="ex: 0.800">
                    </div>
                    <button type="button" class="remove-row-btn" onclick="removeRow('row-0')" style="visibility:hidden;">✕</button>
                </div>
                <?php endif; ?>
            </div>

            <?php endif; ?>
        </div>

        <!-- ACTIONS -->
        <div class="form-actions">
            <a href="produit.php" class="btn">Annuler</a>
            <button type="submit" name="create_famille" class="btn-primary">✓ Créer la catégorie</button>
        </div>

    </form>

</div>

<div id="matieres-data" style="display:none;"><?php echo json_encode($allMatieres); ?></div>

<script>
const matieres = JSON.parse(document.getElementById('matieres-data').textContent);
let rowCount   = <?php echo max(1, count($prev_ids ?? [])); ?>;

function matOptions(selectedId) {
    let html = '<option value="">— Choisir —</option>';
    matieres.forEach(m => {
        const sel = (m.IdMatiere == selectedId) ? 'selected' : '';
        html += `<option value="${m.IdMatiere}" ${sel}>${m.NomMat} (${m.typee})</option>`;
    });
    return html;
}

function getCurrentUnit() {
    const checked = document.querySelector('input[name="typee"]:checked');
    return checked ? checked.value : 'kg';
}

function updateUnitLabel(val) {
    document.getElementById('unit-label').textContent = 'par 1 ' + val;
    document.getElementById('unit-word').textContent  = val;
    document.querySelectorAll('.row-unit').forEach(el => el.textContent = val);
}

function addIngredientRow() {
    const container = document.getElementById('ingredients-container');
    const unit      = getCurrentUnit();
    const id        = 'row-' + rowCount++;

    const div = document.createElement('div');
    div.className = 'ingredient-row';
    div.id = id;
    div.innerHTML = `
        <div class="form-group" style="flex:2;">
            <label>Matière première</label>
            <select name="mat_id[]" required>${matOptions(null)}</select>
        </div>
        <div class="form-group" style="flex:1;">
            <label>Qté par 1 <span class="row-unit">${unit}</span></label>
            <input type="number" name="mat_qte[]" step="0.001" min="0.001" required placeholder="ex: 0.800">
        </div>
        <button type="button" class="remove-row-btn" onclick="removeRow('${id}')">✕</button>
    `;
    container.appendChild(div);
    updateRemoveButtons();
}

function removeRow(id) {
    const row = document.getElementById(id);
    if (row) row.remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.ingredient-row');
    rows.forEach(row => {
        const btn = row.querySelector('.remove-row-btn');
        if (btn) btn.style.visibility = rows.length > 1 ? 'visible' : 'hidden';
    });
}

document.addEventListener('DOMContentLoaded', updateRemoveButtons);
</script>

</body>
</html>
