<?php require("details_fournisseur_logic.php"); ?>
<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramo Clean — Détails Fournisseur</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="details_shared.css">
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
        <li><a href="matiere.php">⬡ Matiere</a></li>
    </ul>
    <p class="nav-section-label" style="margin-top:10px;">Personnes</p>
    <ul>
        <li><a href="client.php">⬡ Client</a></li>
        <li><a href="fournisseur.php" class="active">⬡ Fournisseur</a></li>
        <li><a href="employe.php">⬡ Employe</a></li>
    </ul>
    <div class="nav-logout">
    <a href="logout.php">⬡ Déconnexion</a>
</div>
</nav>

<div class="dashboard">

    <div class="topbar">
        <div class="page-title">🏭 <?php echo htmlspecialchars($fournisseur['NomEntreprise']); ?></div>
        <a href="fournisseur.php" class="btn">← Retour aux fournisseurs</a>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success">✓ <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error">✗ <?php echo $error; ?></div>
    <?php endif; ?>

    <div class="avatar-row">
        <div class="avatar-circle">
            <?php
            $initials = strtoupper(
                substr($fournisseur['Nom'] ?? '', 0, 1) .
                substr($fournisseur['Prenom'] ?? '', 0, 1)
            );
            echo $initials ?: '🏭';
            ?>
        </div>
        <div class="avatar-info">
            <h2><?php echo htmlspecialchars($fournisseur['NomEntreprise']); ?></h2>
            <span><?php echo htmlspecialchars(($fournisseur['Nom'] ?? '') . ' ' . ($fournisseur['Prenom'] ?? '')); ?>
            — <?php echo count($matieres); ?> matière(s) en stock</span>
        </div>
    </div>

    <div class="two-col">

        <!-- INFO + EDIT -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">📋 Informations</span>
            </div>

            <div class="info-grid" style="margin-bottom:20px;">
                <div class="info-box full">
                    <span class="info-box-label">Matricule</span>
                    <span class="info-box-value"><?php echo htmlspecialchars($fournisseur['Mat']); ?></span>
                </div>
                <div class="info-box half">
                    <span class="info-box-label">Email</span>
                    <span class="info-box-value"><?php echo htmlspecialchars($fournisseur['Email']); ?></span>
                </div>
                <div class="info-box">
                    <span class="info-box-label">Téléphone</span>
                    <span class="info-box-value"><?php echo htmlspecialchars($fournisseur['NumTel']); ?></span>
                </div>
            </div>

            <hr style="border:none;border-top:1.5px solid var(--border);margin:0 0 16px;">

            <form method="POST" action="details_fournisseur.php?id=<?php echo urlencode($id); ?>">
                <div class="edit-form-grid">
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" name="Nom" maxlength="30"
                            value="<?php echo htmlspecialchars($fournisseur['Nom'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" name="Prenom" maxlength="30"
                            value="<?php echo htmlspecialchars($fournisseur['Prenom'] ?? ''); ?>">
                    </div>
                    <div class="form-group full">
                        <label>Nom de l'entreprise *</label>
                        <input type="text" name="NomEntreprise" maxlength="50" required
                            value="<?php echo htmlspecialchars($fournisseur['NomEntreprise']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="Email" maxlength="50" required
                            value="<?php echo htmlspecialchars($fournisseur['Email']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Téléphone *</label>
                        <input type="text" name="NumTel" maxlength="8" required
                            value="<?php echo htmlspecialchars($fournisseur['NumTel']); ?>">
                        <span class="form-hint">8 chiffres</span>
                    </div>
                </div>
                <div class="form-actions" style="margin-top:16px;">
                    <button type="submit" name="update_fournisseur" class="btn-primary">💾 Enregistrer</button>
                </div>
            </form>
        </div>

        <!-- MATIERES IN STOCK -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">📦 Matières en stock</span>
            </div>
            <?php if (empty($matieres)): ?>
            <p class="empty-state">Aucune matière en stock pour ce fournisseur.</p>
            <?php else: ?>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Matière</th>
                        <th>Unité</th>
                        <th>Quantité</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($matieres as $m): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($m['NomMat']); ?></strong></td>
                    <td><?php echo htmlspecialchars($m['typee']); ?></td>
                    <td>
                        <?php
                        $cls = $m['qte'] > 50 ? 'stock-ok' : ($m['qte'] > 0 ? 'stock-low' : 'stock-empty');
                        ?>
                        <span class="stock-badge <?php echo $cls; ?>"><?php echo $m['qte']; ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

    </div>

    <!-- DANGER ZONE -->
    <div class="danger-zone">
        <div class="danger-zone-text">
            <h4>⚠ Supprimer ce fournisseur</h4>
            <p>Cette action est irréversible.</p>
        </div>
        <form method="POST" action="details_fournisseur.php?id=<?php echo urlencode($id); ?>"
              onsubmit="return confirm('Supprimer définitivement ce fournisseur ?')">
            <button type="submit" name="delete_fournisseur" class="btn-danger">🗑 Supprimer</button>
        </form>
    </div>

</div>
</body>
</html>
