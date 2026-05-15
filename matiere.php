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
  require_once("connexion.php");

  $collection = $db->matiere;
  $searchm = isset($_GET['searchm']) ? trim($_GET['searchm']) : "";
  $filter = [];
  if ($searchm !== "") {
      $filter = ['NomMat' => new MongoDB\BSON\Regex($searchm, 'i')];
  }
  $options = ['sort' => ['NomMat' => 1]];
  $resultMatiere = $collection->find($filter, $options)->toArray();
?>

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
    <?php if(count($resultMatiere) > 0): foreach($resultMatiere as $row): ?>
    <div class="data-card">
      <h2><?php echo htmlspecialchars($row['NomMat'] ?? ''); ?></h2>
      <h4>ID: <?php echo $row['IdMatiere'] ?? ''; ?></h4>
      <a href="details_matiere.php?id=<?php echo urlencode($row['IdMatiere'] ?? ''); ?>" class="details-btn">Détails</a>
    </div>
    <?php endforeach; else: ?>
    <div class="empty-state">Aucune matière trouvée.</div>
    <?php endif; ?>
    <div class="add-card">
      <h2>Ajouter une nouvelle matière</h2>
      <a href="ajoutmatiere.php" class="btn-primary">+ Ajouter</a>
    </div>
  </div>

</div>

</body>
</html>
