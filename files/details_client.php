<?php require("details_client_logic.php"); ?>
<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramo Clean — Détails Client</title>
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
        <li><a href="client.php" class="active">⬡ Client</a></li>
        <li><a href="fournisseur.php">⬡ Fournisseur</a></li>
        <li><a href="employe.php">⬡ Employe</a></li>
    </ul>
    <div class="nav-logout">
    <a href="logout.php">⬡ Déconnexion</a>
</div>
</nav>

<div class="dashboard">

    <div class="topbar">
        <div class="page-title">👤 <?php echo htmlspecialchars($client['NomEntreprise']); ?></div>
        <a href="client.php" class="btn">← Retour aux clients</a>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success">✓ <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error">✗ <?php echo $error; ?></div>
    <?php endif; ?>

    <!-- AVATAR + SUMMARY -->
    <div class="avatar-row">
        <div class="avatar-circle">
            <?php
            $initials = strtoupper(
                substr($client['Nom'] ?? '', 0, 1) .
                substr($client['Prenom'] ?? '', 0, 1)
            );
            echo $initials ?: '👤';
            ?>
        </div>
        <div class="avatar-info">
            <h2><?php echo htmlspecialchars(($client['Nom'] ?? '') . ' ' . ($client['Prenom'] ?? '')); ?></h2>
            <span><?php echo htmlspecialchars($client['NomEntreprise']); ?> — <?php echo count($factures); ?> facture(s)</span>
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
                    <span class="info-box-label">Matricule Fiscale</span>
                    <span class="info-box-value"><?php echo htmlspecialchars($client['MatFis']); ?></span>
                </div>
                <div class="info-box half">
                    <span class="info-box-label">Email</span>
                    <span class="info-box-value"><?php echo htmlspecialchars($client['Email']); ?></span>
                </div>
                <div class="info-box">
                    <span class="info-box-label">Téléphone</span>
                    <span class="info-box-value"><?php echo htmlspecialchars($client['NumTel']); ?></span>
                </div>
            </div>

            <hr style="border:none;border-top:1.5px solid var(--border);margin:0 0 16px;">

            <form method="POST" action="details_client.php?id=<?php echo urlencode($id); ?>">
                <div class="edit-form-grid">
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" name="Nom" maxlength="30"
                            value="<?php echo htmlspecialchars($client['Nom'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" name="Prenom" maxlength="30"
                            value="<?php echo htmlspecialchars($client['Prenom'] ?? ''); ?>">
                    </div>
                    <div class="form-group full">
                        <label>Nom de l'entreprise *</label>
                        <input type="text" name="NomEntreprise" maxlength="50" required
                            value="<?php echo htmlspecialchars($client['NomEntreprise']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="Email" maxlength="50" required
                            value="<?php echo htmlspecialchars($client['Email']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Téléphone *</label>
                        <input type="text" name="NumTel" maxlength="8" required
                            value="<?php echo htmlspecialchars($client['NumTel']); ?>">
                        <span class="form-hint">8 chiffres</span>
                    </div>
                </div>
                <div class="form-actions" style="margin-top:16px;">
                    <button type="submit" name="update_client" class="btn-primary">💾 Enregistrer</button>
                </div>
            </form>
        </div>

        <!-- FACTURES -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">🧾 Factures</span>
                <a href="ajoutfacture.php?matfis=<?php echo urlencode($id); ?>" class="btn">+ Nouvelle</a>
            </div>
            <?php if (empty($factures)): ?>
            <p class="empty-state">Aucune facture pour ce client.</p>
            <?php else: ?>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Numéro</th>
                        <th>Date</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($factures as $f): ?>
                <tr>
                    <td><strong>#<?php echo str_pad($f['NumFact'], 4, "0", STR_PAD_LEFT); ?></strong></td>
                    <td><?php echo date('d/m/Y', strtotime($f['datefact'])); ?></td>
                    <td>
                        <?php if ($f['payment']): ?>
                        <span class="pill pill-green">Payée</span>
                        <?php else: ?>
                        <span class="pill pill-amber">Non payée</span>
                        <?php endif; ?>
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
            <h4>⚠ Supprimer ce client</h4>
            <p>Cette action est irréversible. Toutes les factures liées seront également supprimées.</p>
        </div>
        <form method="POST" action="details_client.php?id=<?php echo urlencode($id); ?>"
              onsubmit="return confirm('Supprimer définitivement ce client ?')">
            <button type="submit" name="delete_client" class="btn-danger">🗑 Supprimer</button>
        </form>
    </div>

</div>
</body>
</html>
