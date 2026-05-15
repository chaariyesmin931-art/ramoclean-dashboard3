<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ramo Clean — Factures</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .type-filter { display:flex; gap:8px; flex-wrap:wrap; }
    .type-pill {
      padding:7px 16px; border-radius:20px; font-size:13px; font-weight:600;
      border:1.5px solid var(--border); background:var(--olive-bg);
      color:var(--text-soft); text-decoration:none; transition:0.15s;
    }
    .type-pill:hover { background:var(--olive-pale); }
    .type-pill.active { background:var(--olive-mid); color:white; border-color:var(--olive-mid); }
    .type-badge {
      display:inline-block; padding:3px 10px; border-radius:999px;
      font-size:11px; font-weight:700; margin-bottom:6px;
    }
    .badge-fact  { background:#dff0c8; color:#2e5c1e; }
    .badge-devis { background:#faeeda; color:#854f0b; }
    .badge-bdl   { background:var(--blue-light); color:#1060a0; }
  </style>
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
    <li><a href="facture.php" class="active">⬡ Facture</a></li>
    <li><a href="produit.php">⬡ Produit</a></li>
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

  <?php
  /* Active type filter — default to 'fact' */
  $activeType = isset($_GET['type']) && in_array($_GET['type'],['fact','devis','bdl','all'])
                ? $_GET['type'] : 'fact';
  $typeLabels = ['fact'=>'Factures','devis'=>'Devis','bdl'=>'Bons de livraison','all'=>'Tous'];
  ?>

  <div class="page-header">
    <h2>🧾 <?php echo $typeLabels[$activeType]; ?></h2>
    <a href="ajoutfacture.php" class="btn-primary">+ Nouveau document</a>
  </div>

  <!-- STATS -->
  <div class="stat-row">
    <div class="stat-box">
      <h3>Total Factures</h3>
      <p><?php echo $totalFactures; ?></p>
    </div>
    <div class="stat-box">
      <h3>Ce mois</h3>
      <p><?php echo $facturesMonth; ?></p>
    </div>
    <div class="stat-box">
      <h3>Différence</h3>
      <p style="color:<?php echo $difference>=0?'#2e5c1e':'#a32d2d'; ?>">
        <?php echo ($difference>=0?'+':'').$difference; ?>
      </p>
    </div>
    <div class="stat-box">
      <h3>Exporter</h3>
      <a href="export_excel.php" class="btn-spark" style="margin-top:6px;">📥 Excel</a>
    </div>
  </div>

  <!-- TYPE FILTER PILLS -->
  <div class="type-filter">
    <?php foreach($typeLabels as $t => $lbl):
      $url = 'facture.php?type='.$t.(isset($_GET['searchf'])?'&searchf='.urlencode($_GET['searchf']):'');
    ?>
    <a href="<?php echo $url; ?>" class="type-pill <?php echo $activeType===$t?'active':''; ?>">
      <?php echo $lbl; ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- SEARCH -->
  <div class="search-wrap">
    <form method="GET" class="search-form">
      <input type="hidden" name="type" value="<?php echo $activeType; ?>">
      <input type="text" name="searchf" placeholder="Rechercher n° ou client..."
        value="<?php echo htmlspecialchars(isset($_GET['searchf'])?$_GET['searchf']:''); ?>">
      <select name="month">
        <option value="">Tous les mois</option>
        <?php for($m=1;$m<=12;$m++){
          $sel=(isset($_GET['month'])&&$_GET['month']==$m)?"selected":"";
          echo "<option value='$m' $sel>".date("F",mktime(0,0,0,$m,1))."</option>";
        } ?>
      </select>
      <button type="submit">Rechercher</button>
    </form>
    <a href="facture.php?type=<?php echo $activeType; ?>" class="reset-btn">Réinitialiser</a>
  </div>

  <!-- CARDS -->
  <?php
  /* Build query with type filter */
  $conditions = [];
  $searchf = isset($_GET['searchf']) ? trim($_GET['searchf']) : '';
  $month   = isset($_GET['month'])   ? intval($_GET['month']) : 0;

  if ($activeType !== 'all')
      $conditions[] = "facture.TypeFact = '" . mysqli_real_escape_string($conn, $activeType) . "'";
  if ($searchf !== '')
      $conditions[] = "(facture.NumFact LIKE '%" . mysqli_real_escape_string($conn,$searchf) . "%'
                       OR client.NomEntreprise LIKE '%" . mysqli_real_escape_string($conn,$searchf) . "%')";
  if ($month > 0)
      $conditions[] = "MONTH(facture.datefact) = $month";

  $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

  $resCards = $conn->query("
      SELECT facture.NumFact, facture.datefact, facture.payment, facture.TypeFact,
             client.NomEntreprise
      FROM facture
      LEFT JOIN client ON facture.MatFis = client.MatFis
      $where
      ORDER BY facture.datefact DESC
  ");
  ?>

  <div class="data-grid">
  <?php if($resCards && $resCards->num_rows > 0): while($row=$resCards->fetch_assoc()): ?>
    <div class="data-card">
      <?php
        $type = $row['TypeFact'];
        $badgeClass = $type==='fact' ? 'badge-fact' : ($type==='devis' ? 'badge-devis' : 'badge-bdl');
        $typeLabel  = $type==='fact' ? '🧾 Facture' : ($type==='devis' ? '📋 Devis' : '📦 BDL');
      ?>
      <span class="type-badge <?php echo $badgeClass; ?>"><?php echo $typeLabel; ?></span>
      <h2>#<?php echo str_pad($row['NumFact'],4,"0",STR_PAD_LEFT); ?></h2>
      <h4><?php echo htmlspecialchars($row['NomEntreprise']??'—'); ?></h4>
      <p><?php echo date('d/m/Y', strtotime($row['datefact'])); ?></p>
      <a href="details_facture.php?id=<?php echo $row['NumFact']; ?>" class="details-btn" style="margin-top:10px;">Détails</a>
    </div>
  <?php endwhile; else: ?>
    <div class="empty-state">Aucun document trouvé.</div>
  <?php endif; ?>
  </div>

</div>
<?php $conn->close(); ?>
</body>
</html>
