<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ramo Clean — Matières</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
  $conn = new mysqli("localhost","root","","ramoclean");
  if($conn->connect_error) die("Connection failed: ".$conn->connect_error);
?>
<?php require("insights.php"); ?>

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

  <div class="page-header">
    <h2>🧪 Matières</h2>
    <a href="ajoutmatiere.php" class="btn-primary">+ Nouvelle Matière</a>
  </div>

  <div class="search-wrap">
    <form method="GET" class="search-form">
      <input type="text" name="searchm" placeholder="Rechercher une matière..."
        value="<?php echo htmlspecialchars(isset($_GET['searchm']) ? $_GET['searchm'] : ''); ?>">
      <button type="submit">Rechercher</button>
    </form>
    <a href="matiere.php" class="reset-btn">Réinitialiser</a>
  </div>

  <div class="data-grid">
    <?php if($resultMatiere->num_rows > 0): while($row=$resultMatiere->fetch_assoc()): ?>
    <div class="data-card">
      <h2><?php echo htmlspecialchars($row['NomMat']); ?></h2>
      <h4>ID: <?php echo $row['IdMatiere']; ?></h4>
      <a href="details_matiere.php?id=<?php echo $row['IdMatiere']; ?>" class="details-btn">Détails</a>
    </div>
    <?php endwhile; else: ?>
    <div class="empty-state">Aucune matière trouvée.</div>
    <?php endif; ?>
    <div class="add-card">
      <h2>Ajouter une nouvelle matière</h2>
      <a href="ajoutmatiere.php" class="btn-primary">+ Ajouter</a>
    </div>
  </div>

</div>
<?php $conn->close(); ?>
</body>
</html>
