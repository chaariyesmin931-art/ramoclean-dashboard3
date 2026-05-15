<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ramo Clean — Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <li><a href="main.php" class="active">⬡ Dashboard</a></li>
    <li><a href="facture.php">⬡ Facture</a></li>
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

  <!-- TOPBAR -->
  <div class="topbar">
    <div class="page-title">Tableau de bord</div>
    <div class="topbar-actions">
      <a href="export_excel.php" class="btn">📤 Exporter</a>
      <a href="ajoutfacture.php" class="btn-primary">+ Nouvelle Facture</a>
    </div>
  </div>

  <!-- EASY ACCESS -->
  <div class="easy-access">
    <span class="easy-title">Accès rapide</span>
    <div class="easy-buttons">
      <a href="ajoutproduit.php" class="quick-btn">➕ Ajouter Produit</a>
      <a href="ajoutfacture.php" class="quick-btn">🧾 Ajouter Facture</a>
      <a href="ajoutclient.php" class="quick-btn">👤 Ajouter Client</a>
    </div>
  </div>

  <!-- BIG STAT CARDS -->
  <div class="stat">
    <div class="stat-big-card">
      <p><?php $r=mysqli_query($conn,"SELECT COUNT(*) FROM produit"); $row=mysqli_fetch_row($r); echo $row[0]; ?></p>
      <h3>Produits</h3>
      <a href="produit.php" class="card-btn">Voir</a>
    </div>
    <div class="stat-big-card">
      <p><?php $r=mysqli_query($conn,"SELECT COUNT(*) FROM famille"); $row=mysqli_fetch_row($r); echo $row[0]; ?></p>
      <h3>Catégories</h3>
      <a href="produit.php" class="card-btn">Voir</a>
    </div>
    <div class="stat-big-card">
      <p><?php $r=mysqli_query($conn,"SELECT COUNT(*) FROM facture"); $row=mysqli_fetch_row($r); echo $row[0]; ?></p>
      <h3>Factures</h3>
      <a href="facture.php" class="card-btn">Voir</a>
    </div>
    <div class="stat-big-card">
      <p><?php $r=mysqli_query($conn,"SELECT COUNT(*) FROM client"); $row=mysqli_fetch_row($r); echo $row[0]; ?></p>
      <h3>Clients</h3>
      <a href="client.php" class="card-btn">Voir</a>
    </div>
    <div class="stat-big-card">
      <p><?php echo $totalEmployes; ?></p>
      <h3>Employés</h3>
      <a href="employe.php" class="card-btn">Voir</a>
    </div>
  </div>

  <!-- CHART -->
  <div class="chart-container">
    <canvas id="revenueChart"></canvas>
  </div>

  <!-- INSIGHTS -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">✦ Insights</span>
    </div>
    <div class="insights-section">

      <div class="insight-card">
        <h3>Clients les plus actifs</h3>
        <?php if($TopClient->num_rows > 0): while($row=$TopClient->fetch_assoc()): ?>
        <div class="list-row">
          <div>
            <div class="list-name"><?php echo htmlspecialchars($row['Nom'].' '.$row['Prenom']); ?></div>
            <div class="list-sub"><?php echo $row['total_factures']; ?> factures</div>
          </div>
          <span class="pill pill-green"><?php echo $row['total_factures']; ?></span>
        </div>
        <?php endwhile; else: ?>
        <p class="empty-state">Aucun client</p>
        <?php endif; ?>
      </div>

      <div class="insight-card">
        <h3>Factures récentes</h3>
        <?php if($FactureRecent->num_rows > 0): while($row=$FactureRecent->fetch_assoc()): ?>
        <div class="list-row">
          <div class="list-name">Facture #<?php echo str_pad($row['NumFact'],4,"0",STR_PAD_LEFT); ?></div>
        </div>
        <?php endwhile; else: ?>
        <p class="empty-state">Aucune facture</p>
        <?php endif; ?>
      </div>

      <div class="insight-card">
        <h3>Top produits</h3>
        <?php if($TopProduit->num_rows > 0): while($row=$TopProduit->fetch_assoc()): ?>
        <div class="list-row">
          <div>
            <div class="list-name"><?php echo htmlspecialchars($row['NomProduit']); ?></div>
            <div class="list-sub"><?php echo $row['total_bought']; ?> vendus</div>
          </div>
          <span class="pill pill-blue">✦</span>
        </div>
        <?php endwhile; else: ?>
        <p class="empty-state">Aucun produit</p>
        <?php endif; ?>
      </div>

      <div class="insight-card">
        <h3>Employés récents</h3>
        <?php if($RecentEmployes && $RecentEmployes->num_rows > 0): while($row=$RecentEmployes->fetch_assoc()): ?>
        <div class="list-row">
          <div>
            <div class="list-name"><?php echo htmlspecialchars($row['Nom'].' '.$row['Prenom']); ?></div>
            <div class="list-sub"><?php echo htmlspecialchars($row['NumTel']); ?></div>
          </div>
        </div>
        <?php endwhile; else: ?>
        <p class="empty-state">Aucun employé</p>
        <?php endif; ?>
      </div>

    </div>
  </div>

</div>

<?php
  $query = "
    SELECT MONTH(f.datefact) as month, SUM(p.PrixUnit * pf.qte) as revenue
    FROM facture f
    JOIN prodfact pf ON f.NumFact = pf.NumFact
    JOIN produit p ON pf.IdProduit = p.IdProduit
    WHERE f.datefact >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY MONTH(f.datefact)
    ORDER BY MONTH(f.datefact)
  ";
  $stmt = $conn->query($query);
  $months=[]; $revenues=[];
  for($i=5;$i>=0;$i--){ $months[]=date('M',strtotime("-$i month")); $revenues[]=0; }
  while($row=$stmt->fetch_assoc()){
    $idx = date('n') - $row['month'];
    if($idx>=0 && $idx<6) $revenues[5-$idx]=$row['revenue'];
  }
?>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const grad = ctx.createLinearGradient(0,0,0,260);
grad.addColorStop(0,'#4a8c28');
grad.addColorStop(1,'#c8dfa8');

new Chart(ctx,{
  type:'bar',
  data:{
    labels:<?= json_encode($months) ?>,
    datasets:[{
      label:'Revenu mensuel',
      data:<?= json_encode($revenues) ?>,
      backgroundColor: grad,
      borderRadius:10,
      borderSkipped:false,
      hoverBackgroundColor:'#36a4d7'
    }]
  },
  options:{
    responsive:true, maintainAspectRatio:false,
    animation:{duration:1000,easing:'easeOutQuart'},
    plugins:{
      legend:{display:false},
      title:{display:true,text:'Revenu — 6 derniers mois',color:'#1a3a1a',font:{size:15,weight:'bold'},padding:{bottom:16}},
      tooltip:{backgroundColor:'#1a3a1a',titleColor:'#fff',bodyColor:'#c8dfa8',borderColor:'#4a8c28',borderWidth:1}
    },
    scales:{
      y:{beginAtZero:true,grid:{color:'rgba(0,0,0,0.04)'},ticks:{color:'#5a7a45',font:{weight:'bold'}}},
      x:{grid:{display:false},ticks:{color:'#5a7a45',font:{weight:'bold'}}}
    }
  }
});
</script>

<?php $conn->close(); ?>
</body>
</html>
