<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ramo Clean — Fournisseurs</title>
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

  <div class="page-header">
    <h2>🏭 Fournisseurs</h2>
    <a href="ajoutfournisseur.php" class="btn-primary">+ Nouveau Fournisseur</a>
  </div>

  <div class="stat-row">
    <div class="stat-box">
      <h3>Total Fournisseurs</h3>
      <p><?php echo $totalFournisseurs; ?></p>
    </div>
    <div class="stat-box">
      <h3>Exporter</h3>
      <a href="export_fournisseurs.php" class="btn-spark" style="margin-top:6px;">📥 Download Excel</a>
    </div>
  </div>

  <div class="search-wrap">
    <form method="GET" class="search-form">
      <input type="text" name="searchfn" placeholder="Rechercher un fournisseur..."
        value="<?php echo htmlspecialchars(isset($_GET['searchfn']) ? $_GET['searchfn'] : ''); ?>">
      <button type="submit">Rechercher</button>
    </form>
    <a href="fournisseur.php" class="reset-btn">Réinitialiser</a>
  </div>

  <div class="data-grid">
    <?php if($resultFournisseur->num_rows > 0): while($row=$resultFournisseur->fetch_assoc()): ?>
    <div class="data-card">
      <h2><?php echo htmlspecialchars($row['NomEntreprise']); ?></h2>
      <h4>Mat: <?php echo htmlspecialchars($row['Mat']); ?></h4>
      <a href="details_fournisseur.php?id=<?php echo urlencode($row['Mat']); ?>" class="details-btn">Détails</a>
    </div>
    <?php endwhile; else: ?>
    <div class="empty-state">Aucun fournisseur trouvé.</div>
    <?php endif; ?>
  </div>

</div>
<?php $conn->close(); ?>
</body>
</html>