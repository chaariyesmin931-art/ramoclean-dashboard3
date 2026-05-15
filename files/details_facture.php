<?php require("details_facture_logic.php"); ?>
<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramo Clean — Détails Document</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="details_shared.css">
    <style>
        .doc-type-badge {
            display:inline-block; padding:5px 14px; border-radius:999px;
            font-size:13px; font-weight:700; margin-bottom:4px;
        }
        .badge-fact  { background:#dff0c8; color:#2e5c1e; }
        .badge-devis { background:#faeeda; color:#854f0b; }
        .badge-bdl   { background:var(--blue-light); color:#1060a0; }

        .payment-toggle-row {
            display:flex; align-items:center; gap:14px; flex-wrap:wrap;
            padding:16px 20px; border-radius:14px;
            border:1.5px solid var(--border); background:var(--olive-bg);
        }
        .payment-toggle-row p { font-size:13px; color:var(--text-soft); flex:1; }

        .lines-view { width:100%; border-collapse:collapse; font-size:14px; }
        .lines-view th {
            text-align:left; padding:9px 12px; font-size:11px;
            text-transform:uppercase; letter-spacing:0.06em;
            color:var(--text-muted); border-bottom:1.5px solid var(--border);
            background:var(--olive-bg);
        }
        .lines-view td { padding:12px; border-bottom:1px solid var(--olive-bg); }
        .lines-view tr:last-child td { border-bottom:none; }
        .lines-view td.right { text-align:right; font-weight:600; }
    </style>
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
    $type = $fact['TypeFact'];
    $badgeClass = $type==='fact' ? 'badge-fact' : ($type==='devis' ? 'badge-devis' : 'badge-bdl');
    $typeLabel  = $type==='fact' ? '🧾 Facture' : ($type==='devis' ? '📋 Devis' : '📦 Bon de livraison');
    $numStr = str_pad($id, 4, '0', STR_PAD_LEFT);
    ?>

    <div class="topbar">
        <div class="page-title">
            <?php echo $typeLabel; ?> #<?php echo $numStr; ?>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="facture.php" class="btn">← Retour</a>
            <a href="print_facture.php?id=<?php echo $id; ?>" class="btn-primary" target="_blank">🖨 Aperçu / PDF</a>
        </div>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success">✓ <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error">✗ <?php echo $error; ?></div>
    <?php endif; ?>

    <!-- INFO CARDS -->
    <div style="display:flex;gap:14px;flex-wrap:wrap;">
        <div class="card" style="flex:1;min-width:160px;">
            <div class="info-box-label">Type</div>
            <span class="doc-type-badge <?php echo $badgeClass; ?>" style="margin-top:8px;display:inline-block;">
                <?php echo $typeLabel; ?>
            </span>
        </div>
        <div class="card" style="flex:1;min-width:160px;">
            <div class="info-box-label">Date</div>
            <div class="info-box-value" style="font-size:18px;margin-top:6px;">
                <?php echo date('d/m/Y', strtotime($fact['datefact'])); ?>
            </div>
        </div>
        <div class="card" style="flex:1;min-width:160px;">
            <div class="info-box-label">Total TTC</div>
            <div class="info-box-value" style="font-size:22px;margin-top:6px;">
                <?php echo number_format($totalFinal, 3, '.', ' '); ?> DT
            </div>
        </div>
        <?php if ($type === 'fact'): ?>
        <div class="card" style="flex:1;min-width:160px;">
            <div class="info-box-label">Paiement</div>
            <div style="margin-top:8px;">
                <?php if ($fact['payment']): ?>
                <span class="pill pill-green">✓ Payée</span>
                <?php else: ?>
                <span class="pill pill-amber">⏳ Non payée</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="two-col">

        <!-- CLIENT INFO -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">👤 Client</span>
                <a href="details_client.php?id=<?php echo urlencode($fact['MatFis']); ?>" class="card-action">Voir le client →</a>
            </div>
            <div class="info-grid">
                <div class="info-box full">
                    <span class="info-box-label">Entreprise</span>
                    <span class="info-box-value"><?php echo htmlspecialchars($fact['NomEntreprise']??'—'); ?></span>
                </div>
                <?php $contact = trim(($fact['Nom']??'').' '.($fact['Prenom']??''));
                if ($contact): ?>
                <div class="info-box">
                    <span class="info-box-label">Contact</span>
                    <span class="info-box-value"><?php echo htmlspecialchars($contact); ?></span>
                </div>
                <?php endif; ?>
                <div class="info-box">
                    <span class="info-box-label">Téléphone</span>
                    <span class="info-box-value"><?php echo htmlspecialchars($fact['NumTel']??'—'); ?></span>
                </div>
                <div class="info-box full">
                    <span class="info-box-label">Matricule Fiscale</span>
                    <span class="info-box-value"><?php echo htmlspecialchars($fact['ClientMat']??'—'); ?></span>
                </div>
            </div>
        </div>

        <!-- PAYMENT STATUS (fact only) -->
        <?php if ($type === 'fact'): ?>
        <div class="card">
            <div class="card-header">
                <span class="card-title">💳 Statut de paiement</span>
            </div>
            <p style="font-size:13px;color:var(--text-soft);margin-bottom:16px;">
                Mettez à jour le statut de paiement de cette facture.
            </p>
            <div class="payment-toggle-row">
                <?php if ($fact['payment']): ?>
                <span class="pill pill-green" style="font-size:14px;padding:8px 16px;">✓ Payée</span>
                <p>Cette facture est marquée comme payée.</p>
                <form method="POST" action="details_facture.php?id=<?php echo $id; ?>">
                    <input type="hidden" name="new_payment" value="0">
                    <button type="submit" name="toggle_payment" class="btn">✕ Marquer non payée</button>
                </form>
                <?php else: ?>
                <span class="pill pill-amber" style="font-size:14px;padding:8px 16px;">⏳ Non payée</span>
                <p>Cette facture est en attente de paiement.</p>
                <form method="POST" action="details_facture.php?id=<?php echo $id; ?>">
                    <input type="hidden" name="new_payment" value="1">
                    <button type="submit" name="toggle_payment" class="btn-primary">✓ Marquer payée</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php elseif ($type === 'devis'): ?>
        <div class="card">
            <div class="card-header"><span class="card-title">📋 Devis</span></div>
            <div style="padding:16px;background:#faeeda;border-radius:12px;font-size:13px;color:#854f0b;font-weight:500;">
                Un devis est une offre de prix — il n'est pas soumis au paiement.
                Pour le convertir en facture, créez une nouvelle facture pour ce client.
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-header"><span class="card-title">📦 Bon de livraison</span></div>
            <div style="padding:16px;background:var(--blue-light);border-radius:12px;font-size:13px;color:#1060a0;font-weight:500;">
                Un bon de livraison confirme une livraison physique.
                Le paiement est géré via la facture correspondante.
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- PRODUCT LINES -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📦 Produits</span>
        </div>
        <?php if (empty($lines)): ?>
        <p class="empty-state">Aucun produit sur ce document.</p>
        <?php else: ?>
        <table class="lines-view">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produit</th>
                    <th>Poids</th>
                    <th class="right">Prix HT</th>
                    <th class="right">Qté</th>
                    <th class="right">TVA</th>
                    <th class="right">Total HT</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lines as $i => $l):
                $ht = $l['PrixUnit'] * $l['qte'];
            ?>
            <tr>
                <td style="color:var(--text-muted);"><?php echo $i+1; ?></td>
                <td><strong><?php echo htmlspecialchars($l['NomProduit']); ?></strong></td>
                <td><?php echo $l['poid'].' '.htmlspecialchars($l['typee']??''); ?></td>
                <td class="right"><?php echo number_format($l['PrixUnit'],3,'.',' '); ?> DT</td>
                <td class="right"><?php echo $l['qte']; ?></td>
                <td class="right"><?php echo $l['tva']??0; ?>%</td>
                <td class="right"><?php echo number_format($ht,3,'.',' '); ?> DT</td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div style="display:flex;justify-content:flex-end;margin-top:16px;">
            <table style="min-width:260px;border-collapse:collapse;font-size:14px;">
                <tr><td style="text-align:right;padding:5px 12px;color:var(--text-soft);">Sous-total HT</td><td style="text-align:right;padding:5px 12px;font-weight:600;"><?php echo number_format($totalHT,3,'.',' '); ?> DT</td></tr>
                <tr><td style="text-align:right;padding:5px 12px;color:var(--text-soft);">TVA</td><td style="text-align:right;padding:5px 12px;font-weight:600;"><?php echo number_format($totalTVA,3,'.',' '); ?> DT</td></tr>
                <tr><td style="text-align:right;padding:5px 12px;color:var(--text-soft);">Timbre fiscal</td><td style="text-align:right;padding:5px 12px;font-weight:600;"><?php echo number_format($timbreFisc,3,'.',' '); ?> DT</td></tr>
                <tr style="border-top:2px solid var(--border);">
                    <td style="text-align:right;padding:10px 12px;font-size:16px;font-weight:700;color:var(--olive-dark);">Total</td>
                    <td style="text-align:right;padding:10px 12px;font-size:16px;font-weight:700;color:var(--olive-dark);"><?php echo number_format($totalFinal,3,'.',' '); ?> DT</td>
                </tr>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- DANGER ZONE -->
    <div class="danger-zone">
        <div class="danger-zone-text">
            <h4>⚠ Supprimer ce document</h4>
            <p>Cette action est irréversible.</p>
        </div>
        <form method="POST" action="details_facture.php?id=<?php echo $id; ?>"
              onsubmit="return confirm('Supprimer définitivement ce document ?')">
            <button type="submit" name="delete_facture" class="btn-danger">🗑 Supprimer</button>
        </form>
    </div>

</div>
</body>
</html>
