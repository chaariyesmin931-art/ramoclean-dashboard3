<?php require("details_produit_logic.php"); ?>
<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramo Clean — Détails Produit</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="details_produit.css">
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
        <div class="page-title">📦 <?php echo htmlspecialchars($produit['NomProduit']); ?></div>
        <a href="produit.php" class="btn">← Retour aux produits</a>
    </div>

    <!-- ALERTS -->
    <?php if ($success): ?>
    <div class="alert alert-success">✓ <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error">✗ <?php echo $error; ?></div>
    <?php endif; ?>

    <!-- STOCK WARNING with force option -->
    <?php if ($warning): ?>
    <div class="alert alert-warning">
        <div class="warning-title">⚠ Stock insuffisant pour <?php echo intval($_POST['qte_produce']); ?> unité(s)</div>
        <div class="warning-list"><?php echo $warning; ?></div>
        <p style="font-size:13px;">Voulez-vous continuer quand même ? Les matières manquantes seront déduites jusqu'à 0.</p>
        <form method="POST" action="details_produit.php?id=<?php echo $id; ?>">
            <input type="hidden" name="qte_produce" value="<?php echo intval($_POST['qte_produce']); ?>">
            <input type="hidden" name="force_produce" value="1">
            <div class="warning-actions">
                <a href="details_produit.php?id=<?php echo $id; ?>" class="btn">✕ Annuler</a>
                <button type="submit" name="produce" class="btn-spark">⚠ Continuer quand même</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- PRODUCT INFO -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📋 Informations générales</span>
            <span class="pill pill-green"><?php echo htmlspecialchars($produit['NomFamille']); ?></span>
        </div>
        <div class="info-grid">
            <div class="info-box">
                <span class="info-box-label">ID Produit</span>
                <span class="info-box-value">#<?php echo $produit['IdProduit']; ?></span>
            </div>
            <div class="info-box">
                <span class="info-box-label">Nom</span>
                <span class="info-box-value"><?php echo htmlspecialchars($produit['NomProduit']); ?></span>
            </div>
            <div class="info-box">
                <span class="info-box-label">Poids</span>
                <span class="info-box-value"><?php echo $produit['poid']; ?> <?php echo htmlspecialchars($produit['typee']); ?></span>
            </div>
            <div class="info-box">
                <span class="info-box-label">Prix unitaire</span>
                <span class="info-box-value"><?php echo $produit['PrixUnit']; ?> DT</span>
            </div>
            <div class="info-box">
                <span class="info-box-label">TVA</span>
                <span class="info-box-value"><?php echo $produit['tva']; ?>%</span>
            </div>
            <div class="info-box">
                <span class="info-box-label">Arôme</span>
                <span class="info-box-value"><?php echo $produit['arome'] ? htmlspecialchars($produit['arome']) : '—'; ?></span>
            </div>
        </div>

        <!-- Stock summary -->
        <div style="display:flex; align-items:center; gap:14px; margin-top:14px; padding-top:14px; border-top:1.5px solid var(--border);">
            <div class="info-box" style="flex:1;">
                <span class="info-box-label">Stock actuel</span>
                <span class="info-box-value big"><?php echo $produit['stock_total']; ?> unités</span>
            </div>
            <div class="info-box" style="flex:1;">
                <span class="info-box-label">Productible maintenant</span>
                <span class="info-box-value big spark"><?php echo $maxProducible; ?> unités</span>
            </div>
            <div style="display:flex; flex-direction:column; gap:6px;">
                <?php
                $stock = $produit['stock_total'];
                if ($stock > 10) echo '<span class="stock-badge stock-ok">● Stock OK</span>';
                elseif ($stock > 0) echo '<span class="stock-badge stock-low">● Stock faible</span>';
                else echo '<span class="stock-badge stock-empty">● Rupture de stock</span>';
                ?>
            </div>
        </div>
    </div>

    <div class="two-col">

        <!-- RECIPE / MATERIALS -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">🧪 Recette — Matières nécessaires</span>
            </div>

            <?php if (empty($materiaux)): ?>
            <p class="empty-state" style="padding:20px 0;">Aucune matière ajoutée à la recette.</p>
            <?php else: ?>
            <table class="recipe-table">
                <thead>
                    <tr>
                        <th>Matière</th>
                        <th>Qté / unité</th>
                        <th>Stock dispo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($materiaux as $m):
                    $ratio = $m['needed'] > 0 ? $m['mat_stock'] / $m['needed'] : 999;
                    $cls = $ratio >= 10 ? 'mat-stock-ok' : ($ratio >= 1 ? 'mat-stock-low' : 'mat-stock-empty');
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($m['NomMat']); ?></strong><br>
                        <small style="color:var(--text-muted);"><?php echo $m['mat_type']; ?></small>
                    </td>
                    <td><?php echo $m['needed']; ?></td>
                    <td class="<?php echo $cls; ?>"><?php echo $m['mat_stock']; ?></td>
                    <td>
                        <a href="details_produit.php?id=<?php echo $id; ?>&remove_mat=<?php echo $m['IdMatiere']; ?>"
                           class="details-btn"
                           style="background:#fde8e8;border-color:#f5b8b8;color:#a32d2d;"
                           onclick="return confirm('Supprimer cette matière de la recette ?')">✕</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <!-- Add matiere to recipe -->
            <form method="POST" action="details_produit.php?id=<?php echo $id; ?>">
                <div class="add-mat-form">
                    <div class="form-group">
                        <label>Matière</label>
                        <select name="IdMatiere" required>
                            <option value="">— Choisir —</option>
                            <?php foreach ($allMatieres as $m): ?>
                            <option value="<?php echo $m['IdMatiere']; ?>">
                                <?php echo htmlspecialchars($m['NomMat']); ?> (<?php echo $m['typee']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Qté nécessaire / unité</label>
                        <input type="number" name="qte_needed" min="1" placeholder="ex: 200" required>
                    </div>
                    <button type="submit" name="add_matiere" class="btn-primary">+ Ajouter</button>
                </div>
            </form>
        </div>

        <!-- PRODUCE STOCK -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">🏭 Produire du stock</span>
            </div>

            <p style="font-size:13px; color:var(--text-soft); margin-bottom:16px; line-height:1.6;">
                Entrez le nombre d'unités à produire. Les matières nécessaires seront automatiquement
                déduites du stock selon la recette.
            </p>

            <?php if (empty($materiaux)): ?>
            <div class="alert alert-warning" style="font-size:13px;">
                ⚠ Ajoutez d'abord des matières à la recette avant de produire.
            </div>
            <?php else: ?>

            <!-- Materials needed preview -->
            <div style="background:var(--olive-bg); border:1.5px solid var(--border); border-radius:12px; padding:14px; margin-bottom:16px;">
                <div style="font-size:11px; text-transform:uppercase; letter-spacing:0.06em; color:var(--text-muted); margin-bottom:10px;">
                    Consommation par unité produite
                </div>
                <?php foreach ($materiaux as $m): ?>
                <div style="display:flex; justify-content:space-between; font-size:13px; padding:4px 0; border-bottom:1px solid var(--border);">
                    <span><?php echo htmlspecialchars($m['NomMat']); ?></span>
                    <span style="font-weight:600; color:var(--olive-dark);">
                        <?php echo $m['needed']; ?> <?php echo $m['mat_type']; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>

            <form method="POST" action="details_produit.php?id=<?php echo $id; ?>">
                <div class="produce-form">
                    <div class="form-group">
                        <label>Quantité à produire</label>
                        <input type="number" name="qte_produce" min="1" value="1" required>
                        <span class="max-hint">
                            Max sans avertissement : <strong><?php echo $maxProducible; ?></strong> unités
                        </span>
                    </div>
                    <button type="submit" name="produce" class="btn-primary" style="height:42px;">
                        ✓ Produire
                    </button>
                </div>
            </form>

            <?php endif; ?>
        </div>

    </div>

</div>
</body>
</html>
