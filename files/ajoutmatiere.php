<?php require("ajoutmatiere_logic.php"); ?>
<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramo Clean — Ajouter Matière</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="ajoutmatiere.css">
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

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="page-title">➕ Ajouter une matière</div>
        <a href="matiere.php" class="btn">← Retour aux matières</a>
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
            <span class="card-title">🧪 Informations de la matière</span>
        </div>

        <form method="POST" action="ajoutmatiere.php">
            <div class="form-grid">

                <!-- ID Matiere -->
                <div class="form-group">
                    <label>ID Matière *</label>
                    <input type="number" name="IdMatiere" placeholder="ex: 101" min="1" required
                        value="<?php echo (isset($_POST['IdMatiere']) && !$success) ? htmlspecialchars($_POST['IdMatiere']) : ''; ?>">
                    <span class="form-hint">Identifiant unique de la matière</span>
                </div>

                <!-- Nom Matiere -->
                <div class="form-group">
                    <label>Nom de la matière *</label>
                    <input type="text" name="NomMat" placeholder="ex: Soude caustique" maxlength="24" required
                        value="<?php echo (isset($_POST['NomMat']) && !$success) ? htmlspecialchars($_POST['NomMat']) : ''; ?>">
                </div>

                <!-- Type / Unité — radio buttons to match DB constraint exactly -->
                <div class="form-group full">
                    <label>Type / Unité *</label>
                    <div class="type-options">
                        <?php
                        $types = [
                            'kg' => 'Kilogramme (kg)',
                            'g'  => 'Gramme (g)',
                            'L'  => 'Litre (L)',
                            'ml' => 'Millilitre (ml)',
                            'u'  => 'Unité (u)',
                        ];
                        $selected_type = isset($_POST['typee']) ? $_POST['typee'] : 'kg';
                        foreach ($types as $val => $label):
                        ?>
                        <label class="type-option">
                            <input type="radio" name="typee" value="<?php echo $val; ?>"
                                <?php echo ($selected_type === $val) ? 'checked' : ''; ?>>
                            <span><?php echo $label; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <span class="form-hint">Unité de mesure utilisée pour le stock</span>
                </div>

                <!-- Description -->
                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="descriptionn" placeholder="Décrivez la matière première, son usage, ses propriétés…"><?php echo (isset($_POST['descriptionn']) && !$success) ? htmlspecialchars($_POST['descriptionn']) : ''; ?></textarea>
                    <span class="form-hint">Optionnel</span>
                </div>

            </div>

            <hr class="divider" style="margin: 16px 0;">

            <div class="form-actions">
                <a href="matiere.php" class="btn">Annuler</a>
                <button type="submit" name="create_matiere" class="btn-primary">✓ Créer la matière</button>
            </div>
        </form>
    </div>

</div>

</body>
</html>