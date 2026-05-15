<?php require("details_matiere_logic.php"); ?>
<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramo Clean — Détails Matière</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="details_shared.css">
    <style>
        /* ---- Add stock form ---- */
        .add-stock-form {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            flex-wrap: wrap;
            padding: 16px;
            background: var(--olive-bg);
            border: 1.5px solid var(--border);
            border-radius: 14px;
            margin-bottom: 14px;
        }
        .add-stock-form .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            flex: 1;
            min-width: 160px;
        }
        .add-stock-form label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-soft);
        }
        .add-stock-form select,
        .add-stock-form input[type="number"] {
            padding: 9px 12px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            color: var(--text-dark);
            background: white;
            outline: none;
            font-family: inherit;
            transition: border-color 0.15s;
            width: 100%;
        }
        .add-stock-form select:focus,
        .add-stock-form input:focus { border-color: var(--olive-bright); }

        /* ---- Inline fournisseur section ---- */
        .new-fn-section {
            background: var(--blue-light);
            border: 1.5px solid #8fd0f0;
            border-radius: 14px;
            padding: 18px 20px;
            display: none;
            flex-direction: column;
            gap: 14px;
            margin-bottom: 14px;
        }
        .new-fn-section.visible { display: flex; }
        .new-fn-title {
            font-size: 14px;
            font-weight: 600;
            color: #1060a0;
        }
        .new-fn-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .new-fn-grid .form-group { display: flex; flex-direction: column; gap: 5px; }
        .new-fn-grid .form-group.full { grid-column: 1 / -1; }
        .new-fn-grid label {
            font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.05em;
            color: #1060a0;
        }
        .new-fn-grid input {
            padding: 9px 12px;
            border: 1.5px solid #8fd0f0;
            border-radius: 10px;
            font-size: 14px;
            color: var(--text-dark);
            background: white;
            outline: none;
            font-family: inherit;
            transition: border-color 0.15s;
            width: 100%;
        }
        .new-fn-grid input:focus { border-color: var(--blue-spark); }
        .new-fn-grid input::placeholder { color: var(--text-muted); }

        .toggle-fn-btn {
            background: none;
            border: none;
            color: var(--blue-spark);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            gap: 5px;
            font-family: inherit;
            transition: color 0.15s;
        }
        .toggle-fn-btn:hover { color: #1e8cbf; }
    </style>
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
        <li><a href="produit.php">⬡ Produit</a></li>
        <li><a href="matiere.php" class="active">⬡ Matiere</a></li>
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
        <div class="page-title">🧪 <?php echo htmlspecialchars($matiere['NomMat']); ?></div>
        <a href="matiere.php" class="btn">← Retour aux matières</a>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success">✓ <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error">✗ <?php echo $error; ?></div>
    <?php endif; ?>

    <!-- STOCK SUMMARY CARDS -->
    <div style="display:flex; gap:14px; flex-wrap:wrap;">
        <div class="card" style="flex:1; min-width:180px;">
            <div class="info-box-label">Stock total</div>
            <div class="info-box-value" style="font-size:32px; margin-top:6px;">
                <?php echo $totalStock ?? 0; ?>
                <span style="font-size:14px; color:var(--text-muted);"><?php echo $matiere['typee']; ?></span>
            </div>
            <?php
            $s = $totalStock ?? 0;
            if ($s > 50)    echo '<span class="stock-badge stock-ok" style="margin-top:8px;">● Stock OK</span>';
            elseif ($s > 0) echo '<span class="stock-badge stock-low" style="margin-top:8px;">● Stock faible</span>';
            else            echo '<span class="stock-badge stock-empty" style="margin-top:8px;">● Rupture</span>';
            ?>
        </div>
        <div class="card" style="flex:1; min-width:180px;">
            <div class="info-box-label">Utilisée dans</div>
            <div class="info-box-value" style="font-size:32px; margin-top:6px;"><?php echo count($produits); ?></div>
            <div style="font-size:13px; color:var(--text-muted); margin-top:4px;">produit(s)</div>
        </div>
        <div class="card" style="flex:1; min-width:180px;">
            <div class="info-box-label">Recette de base</div>
            <div class="info-box-value" style="font-size:32px; margin-top:6px;"><?php echo count($familles); ?></div>
            <div style="font-size:13px; color:var(--text-muted); margin-top:4px;">catégorie(s)</div>
        </div>
    </div>

    <!-- ADD STOCK SECTION -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">➕ Ajouter du stock</span>
        </div>

        <!-- ADD STOCK FORM -->
        <form method="POST" action="details_matiere.php?id=<?php echo $id; ?>">
            <div class="add-stock-form">
                <div class="form-group">
                    <label>Fournisseur *</label>
                    <select name="Mat" required>
                        <option value="">— Choisir un fournisseur —</option>
                        <?php foreach ($allFournisseurs as $f): ?>
                        <option value="<?php echo htmlspecialchars($f['Mat']); ?>"
                            <?php echo (isset($_POST['Mat']) && $_POST['Mat'] === $f['Mat']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($f['NomEntreprise']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantité à ajouter *</label>
                    <input type="number" name="qte_add" min="1" placeholder="ex: 100" required
                        value="<?php echo (isset($_POST['qte_add']) && !$success) ? htmlspecialchars($_POST['qte_add']) : ''; ?>">
                </div>
                <button type="submit" name="add_stock" class="btn-primary" style="height:42px; white-space:nowrap;">
                    ✓ Ajouter au stock
                </button>
            </div>
        </form>

        <!-- TOGGLE NEW FOURNISSEUR -->
        <button type="button" class="toggle-fn-btn" onclick="toggleNewFn()" style="margin-bottom:10px;">
            <span id="fn-icon">✦</span>
            <span id="fn-label">Fournisseur introuvable ? En créer un</span>
        </button>

        <!-- INLINE CREATE FOURNISSEUR -->
        <div class="new-fn-section" id="new-fn-section">
            <div class="new-fn-title">✦ Créer un nouveau fournisseur</div>
            <form method="POST" action="details_matiere.php?id=<?php echo $id; ?>">
                <div class="new-fn-grid">

                    <div class="form-group full">
                        <label>Matricule Fiscale *</label>
                        <input type="text" name="new_Mat" placeholder="ex: 1234567/M/A/E/014" maxlength="17" required
                            value="<?php echo (isset($_POST['new_Mat']) && !$success) ? htmlspecialchars($_POST['new_Mat']) : ''; ?>">
                    </div>

                    <div class="form-group full">
                        <label>Nom de l'entreprise *</label>
                        <input type="text" name="new_NomEntreprise" placeholder="ex: Fournisseur Chimique Tunis" maxlength="50" required
                            value="<?php echo (isset($_POST['new_NomEntreprise']) && !$success) ? htmlspecialchars($_POST['new_NomEntreprise']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" name="new_Nom" placeholder="ex: Ben Salah" maxlength="30"
                            value="<?php echo (isset($_POST['new_Nom']) && !$success) ? htmlspecialchars($_POST['new_Nom']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" name="new_Prenom" placeholder="ex: Karim" maxlength="30"
                            value="<?php echo (isset($_POST['new_Prenom']) && !$success) ? htmlspecialchars($_POST['new_Prenom']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="new_Email" placeholder="ex: contact@fournisseur.tn" maxlength="50" required
                            value="<?php echo (isset($_POST['new_Email']) && !$success) ? htmlspecialchars($_POST['new_Email']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Téléphone *</label>
                        <input type="text" name="new_NumTel" placeholder="ex: 71234567" maxlength="8" required
                            value="<?php echo (isset($_POST['new_NumTel']) && !$success) ? htmlspecialchars($_POST['new_NumTel']) : ''; ?>">
                    </div>

                </div>
                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:14px;">
                    <button type="button" class="btn" onclick="toggleNewFn()">Annuler</button>
                    <button type="submit" name="create_fournisseur" class="btn-spark">✦ Créer le fournisseur</button>
                </div>
            </form>
        </div>

        <!-- STOCK HISTORY TABLE -->
        <div style="margin-top:6px;">
            <div style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em;
                        color:var(--text-muted); margin-bottom:10px;">Historique des entrées</div>
            <?php if (empty($stockRows)): ?>
            <p class="empty-state" style="padding:16px 0;">Aucune entrée de stock.</p>
            <?php else: ?>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Fournisseur</th>
                        <th>Quantité</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($stockRows as $s): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($s['NomEntreprise'] ?? 'Inconnu'); ?></strong></td>
                    <td>
                        <span class="stock-badge <?php echo $s['qte'] > 0 ? 'stock-ok' : 'stock-empty'; ?>">
                            <?php echo $s['qte']; ?> <?php echo $matiere['typee']; ?>
                        </span>
                    </td>
                    <td>
                        <a href="details_matiere.php?id=<?php echo $id; ?>&delete_stock=<?php echo $s['idsm']; ?>"
                           style="color:#a32d2d; font-size:13px; font-weight:600;"
                           onclick="return confirm('Supprimer cette entrée de stock ?')">✕ Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="two-col">

        <!-- INFO + EDIT -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">📋 Informations & Modification</span>
            </div>

            <div class="info-grid" style="margin-bottom:20px;">
                <div class="info-box">
                    <span class="info-box-label">ID Matière</span>
                    <span class="info-box-value"><?php echo $matiere['IdMatiere']; ?></span>
                </div>
                <div class="info-box">
                    <span class="info-box-label">Type / Unité</span>
                    <span class="info-box-value"><?php echo htmlspecialchars($matiere['typee']); ?></span>
                </div>
                <div class="info-box full">
                    <span class="info-box-label">Description</span>
                    <span class="info-box-value" style="font-size:14px; font-weight:400;">
                        <?php echo $matiere['descriptionn'] ? htmlspecialchars($matiere['descriptionn']) : '—'; ?>
                    </span>
                </div>
            </div>

            <hr style="border:none;border-top:1.5px solid var(--border);margin:0 0 16px;">

            <form method="POST" action="details_matiere.php?id=<?php echo $id; ?>">
                <div class="edit-form-grid">
                    <div class="form-group full">
                        <label>Nom de la matière *</label>
                        <input type="text" name="NomMat" maxlength="24" required
                            value="<?php echo htmlspecialchars($matiere['NomMat']); ?>">
                    </div>
                    <div class="form-group full">
                        <label>Type / Unité *</label>
                        <div class="type-options">
                            <?php
                            $types = ['kg' => 'Kilogramme (kg)', 'lit' => 'Litre (lit)'];
                            foreach ($types as $val => $label):
                            ?>
                            <label class="type-option">
                                <input type="radio" name="typee" value="<?php echo $val; ?>"
                                    <?php echo ($matiere['typee'] === $val) ? 'checked' : ''; ?>>
                                <span><?php echo $label; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group full">
                        <label>Description</label>
                        <textarea name="descriptionn"><?php echo htmlspecialchars($matiere['descriptionn'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="form-actions" style="margin-top:16px;">
                    <button type="submit" name="update_matiere" class="btn-primary">💾 Enregistrer</button>
                </div>
            </form>
        </div>

        <!-- PRODUITS + CATEGORIES -->
        <div style="display:flex; flex-direction:column; gap:18px;">

            <div class="card">
                <div class="card-header">
                    <span class="card-title">📦 Produits utilisant cette matière</span>
                </div>
                <?php if (empty($produits)): ?>
                <p class="empty-state">Aucun produit.</p>
                <?php else: ?>
                <table class="detail-table">
                    <thead><tr><th>Produit</th><th>Qté / unité</th></tr></thead>
                    <tbody>
                    <?php foreach ($produits as $p): ?>
                    <tr>
                        <td>
                            <a href="details_produit.php?id=<?php echo $p['IdProduit']; ?>"
                               style="color:var(--olive-mid); font-weight:600;">
                                <?php echo htmlspecialchars($p['NomProduit']); ?>
                            </a>
                        </td>
                        <td><?php echo $p['qte_needed']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <span class="card-title">🏷️ Catégories (recette de base)</span>
                </div>
                <?php if (empty($familles)): ?>
                <p class="empty-state">Aucune catégorie.</p>
                <?php else: ?>
                <table class="detail-table">
                    <thead><tr><th>Catégorie</th><th>Qté / unité base</th></tr></thead>
                    <tbody>
                    <?php foreach ($familles as $f): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($f['NomFamille']); ?></strong></td>
                        <td><?php echo $f['qte_per_unit']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- DANGER ZONE -->
    <div class="danger-zone">
        <div class="danger-zone-text">
            <h4>⚠ Supprimer cette matière</h4>
            <p>Impossible si elle est utilisée dans des recettes de produits ou des catégories.</p>
        </div>
        <form method="POST" action="details_matiere.php?id=<?php echo $id; ?>"
              onsubmit="return confirm('Supprimer définitivement cette matière ?')">
            <button type="submit" name="delete_matiere" class="btn-danger">🗑 Supprimer</button>
        </form>
    </div>

</div>

<script>
function toggleNewFn() {
    const section = document.getElementById('new-fn-section');
    const icon    = document.getElementById('fn-icon');
    const label   = document.getElementById('fn-label');
    const visible = section.classList.toggle('visible');
    icon.textContent  = visible ? '✕' : '✦';
    label.textContent = visible ? 'Masquer' : 'Fournisseur introuvable ? En créer un';
    if (visible) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/* Auto-open fournisseur form on create error */
<?php if ($error && isset($_POST['create_fournisseur'])): ?>
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('new-fn-section').classList.add('visible');
    document.getElementById('fn-icon').textContent  = '✕';
    document.getElementById('fn-label').textContent = 'Masquer';
});
<?php endif; ?>
</script>

</body>
</html>