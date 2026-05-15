<?php require("ajoutclient_logic.php"); ?>
<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramo Clean — Ajouter Client</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="ajoutclient.css">
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

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="page-title">➕ Ajouter un client</div>
        <a href="client.php" class="btn">← Retour aux clients</a>
    </div>

    <!-- ALERTS -->
    <?php if ($success): ?>
    <div class="alert alert-success">✓ <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error">✗ <?php echo $error; ?></div>
    <?php endif; ?>

    <!-- FORM CARD -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">👤 Informations du client</span>
        </div>

        <form method="POST" action="ajoutclient.php">
            <div class="form-grid">

                <!-- SECTION: Entreprise -->
                <div class="form-section-title">🏢 Entreprise</div>

                <!-- Matricule Fiscale -->
                <div class="form-group full">
                    <label>Matricule Fiscale *</label>
                    <input type="text" name="MatFis" placeholder="ex: 1234567/M/A/E/001" maxlength="17" required
                        value="<?php echo (isset($_POST['MatFis']) && !$success) ? htmlspecialchars($_POST['MatFis']) : ''; ?>">
                    <div class="matfis-hint">
                        ✦ Format tunisien : <strong>7 chiffres / lettre / lettre / lettre / 3 chiffres</strong><br>
                        Exemple : <code>1234567/M/A/E/001</code> — exactement 17 caractères
                    </div>
                </div>

                <!-- Nom Entreprise -->
                <div class="form-group full">
                    <label>Nom de l'entreprise *</label>
                    <input type="text" name="NomEntreprise" placeholder="ex: Société Nettoyage Plus" maxlength="50" required
                        value="<?php echo (isset($_POST['NomEntreprise']) && !$success) ? htmlspecialchars($_POST['NomEntreprise']) : ''; ?>">
                </div>

                <!-- SECTION: Contact -->
                <div class="form-section-title">📋 Contact</div>

                <!-- Nom -->
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="Nom" placeholder="ex: Ben Ali" maxlength="30"
                        value="<?php echo (isset($_POST['Nom']) && !$success) ? htmlspecialchars($_POST['Nom']) : ''; ?>">
                    <span class="form-hint">Optionnel</span>
                </div>

                <!-- Prenom -->
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="Prenom" placeholder="ex: Mohamed" maxlength="30"
                        value="<?php echo (isset($_POST['Prenom']) && !$success) ? htmlspecialchars($_POST['Prenom']) : ''; ?>">
                    <span class="form-hint">Optionnel</span>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="Email" placeholder="ex: contact@entreprise.tn" maxlength="50" required
                        value="<?php echo (isset($_POST['Email']) && !$success) ? htmlspecialchars($_POST['Email']) : ''; ?>">
                </div>

                <!-- Téléphone -->
                <div class="form-group">
                    <label>Téléphone *</label>
                    <input type="text" name="NumTel" placeholder="ex: 23456789" maxlength="8" required
                        value="<?php echo (isset($_POST['NumTel']) && !$success) ? htmlspecialchars($_POST['NumTel']) : ''; ?>">
                    <span class="form-hint">8 chiffres, sans espaces</span>
                </div>

            </div>

            <hr class="divider" style="margin: 16px 0;">

            <div class="form-actions">
                <a href="client.php" class="btn">Annuler</a>
                <button type="submit" name="create_client" class="btn-primary">✓ Créer le client</button>
            </div>
        </form>
    </div>

</div>

</body>
</html>
