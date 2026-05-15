<?php require("ajoutemploye_logic.php"); ?>
<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramo Clean — Ajouter Employé</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="ajoutemploye.css">
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
        <div class="page-title">➕ Ajouter un employé</div>
        <a href="employe.php" class="btn">← Retour aux employés</a>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success">✓ <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error">✗ <?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <span class="card-title">👷 Informations de l'employé</span>
        </div>

        <form method="POST" action="ajoutemploye.php">
            <div class="form-grid">

                <!-- Live avatar preview -->
                <div class="avatar-row">
                    <div class="avatar-preview" id="avatar">👤</div>
                    <div class="avatar-info">
                        <span class="avatar-name" id="avatar-name">Nouvel employé</span>
                        <span class="avatar-sub" id="avatar-cin">CIN: —</span>
                    </div>
                </div>

                <div class="form-section-title">🪪 Identité</div>

                <!-- CIN -->
                <div class="form-group full">
                    <label>CIN *</label>
                    <input type="text" name="Cin" id="cin-input" placeholder="ex: 12345678" maxlength="17" required
                        value="<?php echo (isset($_POST['Cin']) && !$success) ? htmlspecialchars($_POST['Cin']) : ''; ?>">
                    <div class="cin-hint">
                        ✦ Carte d'identité nationale — 8 chiffres ou format spécifique selon votre système
                    </div>
                </div>

                <!-- Nom -->
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="Nom" id="nom-input" placeholder="ex: Ben Ali" maxlength="30"
                        value="<?php echo (isset($_POST['Nom']) && !$success) ? htmlspecialchars($_POST['Nom']) : ''; ?>">
                    <span class="form-hint">Optionnel</span>
                </div>

                <!-- Prenom -->
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="Prenom" id="prenom-input" placeholder="ex: Mohamed" maxlength="30"
                        value="<?php echo (isset($_POST['Prenom']) && !$success) ? htmlspecialchars($_POST['Prenom']) : ''; ?>">
                    <span class="form-hint">Optionnel</span>
                </div>

                <div class="form-section-title">📋 Contact</div>

                <!-- Email -->
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="Email" placeholder="ex: employe@ramoclean.tn" maxlength="50" required
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
                <a href="employe.php" class="btn">Annuler</a>
                <button type="submit" name="create_employe" class="btn-primary">✓ Créer l'employé</button>
            </div>
        </form>
    </div>

</div>

<script>
const nomInput    = document.getElementById('nom-input');
const prenomInput = document.getElementById('prenom-input');
const cinInput    = document.getElementById('cin-input');
const avatar      = document.getElementById('avatar');
const avatarName  = document.getElementById('avatar-name');
const avatarCin   = document.getElementById('avatar-cin');

function updateAvatar() {
    const nom    = nomInput.value.trim();
    const prenom = prenomInput.value.trim();
    const cin    = cinInput.value.trim();

    /* Build initials */
    const initials = [nom[0] || '', prenom[0] || ''].join('').toUpperCase();
    avatar.textContent   = initials || '👤';
    avatarName.textContent = (nom || prenom) ? (nom + ' ' + prenom).trim() : 'Nouvel employé';
    avatarCin.textContent  = cin ? 'CIN: ' + cin : 'CIN: —';
}

nomInput.addEventListener('input', updateAvatar);
prenomInput.addEventListener('input', updateAvatar);
cinInput.addEventListener('input', updateAvatar);

/* Init on page load in case of error reload */
updateAvatar();
</script>

</body>
</html>
