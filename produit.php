<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ramo Clean — Produits</title>
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

  <div class="page-header">
    <h2>📦 Produits</h2>
    <a href="ajoutproduit.php" class="btn-primary">+ Nouveau Produit</a>
  </div>

  <div class="produit-stats">
    <div class="stat-card">
      <h3>Produits</h3>
      <p><?php echo $totalProduits; ?></p>
    </div>
    <div class="stat-card">
      <h3>Catégories</h3>
      <p><?php echo $totalFamilles; ?></p>
      <a href="categories.php" class="btn">👁 Voir les catégories</a>
    </div>
  </div>

  <div class="search-wrap">
    <form method="GET" class="search-form">
      <input type="text" name="searchp" placeholder="Rechercher produit ou famille..."
        value="<?php echo htmlspecialchars(isset($_GET['searchp']) ? $_GET['searchp'] : ''); ?>">
      <button type="submit">Rechercher</button>
    </form>
    <a href="produit.php" class="reset-btn">Réinitialiser</a>
  </div>

  <div class="data-grid">
    <?php if($resultProduit->num_rows > 0): while($row=$resultProduit->fetch_assoc()): ?>
    <div class="data-card">
      <span class="pill pill-green"><?php echo htmlspecialchars($row['NomFamille']); ?></span>
      <h2><?php echo htmlspecialchars($row['NomProduit']); ?></h2>
      <h4>Poids: <?php echo $row['Poid'].' '.htmlspecialchars($row['typee']); ?></h4>
      <h4>Prix: <?php echo $row['PrixUnit']; ?> DT</h4>
      <h4>Quantité: <?php echo $row['total_qte']; ?></h4>
      <a href="details_produit.php?id=<?php echo $row['IdProduit']; ?>" class="details-btn">Détails</a>
    </div>
    <?php endwhile; else: ?>
    <div class="empty-state">Aucun produit trouvé.</div>
    <?php endif; ?>
    <div class="add-card">
      <h2>Ajouter un nouveau produit</h2>
      <a href="ajoutproduit.php" class="btn-primary">+ Ajouter</a>
    </div>
  </div>

</div>
<?php $conn->close(); ?>
</body>
</html>