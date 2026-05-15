<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ramo Clean — Employés</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
  require_once("connexion.php");
  
  $collection = $db->employeur;

  if(isset($_GET['delete'])){
    $cin = trim($_GET['delete']);
    $collection->deleteOne(['Cin' => $cin]);
    header("Location: employe.php"); exit();
  }

  $search = "";
  $filter = [];
  if(isset($_GET['searche']) && $_GET['searche'] != ""){
    $search = trim($_GET['searche']);
    $filter = [
        '$or' => [
            ['Nom' => new MongoDB\BSON\Regex($search, 'i')],
            ['Prenom' => new MongoDB\BSON\Regex($search, 'i')],
            ['Cin' => new MongoDB\BSON\Regex($search, 'i')],
            ['NumTel' => new MongoDB\BSON\Regex($search, 'i')]
        ]
    ];
  }
  
  $options = ['sort' => ['Nom' => 1]];
  $resultEmployes = $collection->find($filter, $options)->toArray();

  $totalEmployes = $collection->countDocuments();
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

  <div class="page-header">
    <h2>👷 Employés</h2>
    <a href="ajoutemploye.php" class="btn-primary">+ Nouvel Employé</a>
  </div>

  <div class="stat-row">
    <div class="stat-box">
      <h3>Total Employés</h3>
      <p><?php echo $totalEmployes; ?></p>
    </div>
    <div class="stat-box">
      <h3>Exporter</h3>
      <a href="export_employes.php" class="btn-spark" style="margin-top:6px;">📥 Download Excel</a>
    </div>
  </div>

  <div class="search-wrap">
    <form method="GET" class="search-form">
      <input type="text" name="searche" placeholder="Rechercher par nom ou CIN..."
        value="<?php echo htmlspecialchars($search); ?>">
      <button type="submit">Rechercher</button>
    </form>
    <a href="employe.php" class="reset-btn">Réinitialiser</a>
  </div>

  <div class="data-grid">
    <?php if(count($resultEmployes) > 0): foreach($resultEmployes as $row): ?>
    <div class="data-card">
      <h2><?php echo htmlspecialchars(($row['Nom'] ?? '') . ' ' . ($row['Prenom'] ?? '')); ?></h2>
      <h4>CIN: <?php echo htmlspecialchars($row['Cin'] ?? ''); ?></h4>
      <h4>📞 <?php echo htmlspecialchars($row['NumTel'] ?? ''); ?></h4>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="details_employe.php?id=<?php echo urlencode($row['Cin'] ?? ''); ?>" class="details-btn">Détails</a>
        <a href="employe.php?delete=<?php echo urlencode($row['Cin'] ?? ''); ?>"
           class="delete-btn"
           onclick="return confirm('Supprimer cet employé ?')">Supprimer</a>
      </div>
    </div>
    <?php endforeach; else: ?>
    <div class="empty-state">Aucun employé trouvé.</div>
    <?php endif; ?>
  </div>

</div>

</body>
</html>
