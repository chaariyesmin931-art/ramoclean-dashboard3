<?php require("details_employe_logic.php"); ?>
<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramo Clean — Détails Employé</title>
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
        <li><a href="fournisseur.php">⬡ Fournisseur</a></li>
        <li><a href="employe.php" class="active">⬡ Employe</a></li>
    </ul>
    <div class="nav-logout">
    <a href="logout.php">⬡ Déconnexion</a>
</div>
</nav>

<div class="dashboard">

    <div class="topbar">
        <div class="page-title">👷 <?php echo htmlspecialchars(($employe['Nom'] ?? '') . ' ' . ($employe['Prenom'] ?? '')); ?></div>
        <a href="employe.php" class="btn">← Retour aux employés</a>
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
                substr($employe['Nom'] ?? '', 0, 1) .
                substr($employe['Prenom'] ?? '', 0, 1)
            );
            echo $initials ?: '👷';
            ?>
        </div>
        <div class="avatar-info">
            <h2><?php echo htmlspecialchars(($employe['Nom'] ?? '') . ' ' . ($employe['Prenom'] ?? '')); ?></h2>
            <span>CIN : <?php echo htmlspecialchars($employe['Cin']); ?></span>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">📋 Informations & Modification</span>
        </div>

        <div class="info-grid" style="margin-bottom:20px;">
            <div class="info-box full">
                <span class="info-box-label">CIN</span>
                <span class="info-box-value"><?php echo htmlspecialchars($employe['Cin']); ?></span>
            </div>
            <div class="info-box half">
                <span class="info-box-label">Email</span>
                <span class="info-box-value"><?php echo htmlspecialchars($employe['Email']); ?></span>
            </div>
            <div class="info-box">
                <span class="info-box-label">Téléphone</span>
                <span class="info-box-value"><?php echo htmlspecialchars($employe['NumTel']); ?></span>
            </div>
        </div>

        <hr style="border:none;border-top:1.5px solid var(--border);margin:0 0 16px;">

        <form method="POST" action="details_employe.php?id=<?php echo urlencode($id); ?>">
            <div class="edit-form-grid">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="Nom" maxlength="30"
                        value="<?php echo htmlspecialchars($employe['Nom'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="Prenom" maxlength="30"
                        value="<?php echo htmlspecialchars($employe['Prenom'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="Email" maxlength="50" required
                        value="<?php echo htmlspecialchars($employe['Email']); ?>">
                </div>
                <div class="form-group">
                    <label>Téléphone *</label>
                    <input type="text" name="NumTel" maxlength="8" required
                        value="<?php echo htmlspecialchars($employe['NumTel']); ?>">
                    <span class="form-hint">8 chiffres</span>
                </div>
            </div>
            <div class="form-actions" style="margin-top:16px;">
                <button type="submit" name="update_employe" class="btn-primary">💾 Enregistrer</button>
            </div>
        </form>
    </div>

    <!-- DANGER ZONE -->
    <div class="danger-zone">
        <div class="danger-zone-text">
            <h4>⚠ Supprimer cet employé</h4>
            <p>Cette action est irréversible.</p>
        </div>
        <form method="POST" action="details_employe.php?id=<?php echo urlencode($id); ?>"
              onsubmit="return confirm('Supprimer définitivement cet employé ?')">
            <button type="submit" name="delete_employe" class="btn-danger">🗑 Supprimer</button>
        </form>
    </div>

</div>
</body>
</html>
