<?php require_once("auth.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture <?php
        require_once("connexion.php");
        $id   = isset($_GET['id']) ? intval($_GET['id']) : 0;
        echo str_pad($id, 4, '0', STR_PAD_LEFT);
    ?></title>
    <style>
        :root {
            --olive-dark:  #1a3a1a;
            --olive-mid:   #2e5c1e;
            --olive-leaf:  #6db33f;
            --olive-pale:  #c8dfa8;
            --olive-bg:    #eef4e6;
            --blue-spark:  #36a4d7;
            --border:      #b0cc90;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f4ee;
            color: #1a3a1a;
        }

        /* ---- Action bar (hidden on print) ---- */
        .action-bar {
            background: var(--olive-dark);
            padding: 13px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .action-bar-title { color: #c8dfa8; font-size: 14px; font-weight: 600; }
        .action-bar-btns  { display: flex; gap: 10px; }

        .abtn {
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-family: inherit;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: 0.15s;
        }
        .abtn-back   { background: rgba(255,255,255,0.12); color: white; border: 1.5px solid rgba(255,255,255,0.25); }
        .abtn-print  { background: var(--olive-pale); color: var(--olive-dark); }
        .abtn-pdf    { background: var(--blue-spark); color: white; }
        .abtn-back:hover  { background: rgba(255,255,255,0.22); }
        .abtn-print:hover { background: #b0d880; }
        .abtn-pdf:hover   { background: #1e8cbf; }

        /* ---- Invoice wrapper ---- */
        .invoice-wrapper {
            max-width: 860px;
            margin: 30px auto 50px;
            padding: 0 20px;
        }

        /* ---- Invoice document ---- */
        .invoice {
            background:
                linear-gradient(135deg, #b8d898 0%, #b8d898 22%, transparent 22%),
                linear-gradient(315deg, #b8d898 0%, #b8d898 22%, transparent 22%),
                white;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }

        .inv-body { position: relative; z-index: 2; padding: 36px 44px 40px; }

        /* ---- Title + Logo row ---- */
        .inv-title-row {
            position: relative;
            z-index: 3;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 28px;
        }

        .inv-big-title {
            font-size: 38px;
            font-weight: 800;
            color: var(--olive-mid);
            letter-spacing: 1px;
            text-transform: uppercase;
            line-height: 1;
        }

        .inv-big-title span {
            font-size: 38px;
            color: var(--olive-dark);
        }

        .inv-logo-block {
            text-align: right;
        }

        .inv-logo-block .brand {
            font-size: 20px;
            font-weight: 800;
            color: var(--olive-dark);
            letter-spacing: 1px;
        }

        .inv-logo-block .brand-sub {
            font-size: 11px;
            color: var(--olive-leaf);
            letter-spacing: 0.08em;
            margin-top: 2px;
        }

        .spark-dot {
            display: inline-block;
            position: relative;
            z-index: 3;
            width: 8px; height: 8px;
            background: var(--blue-spark);
            border-radius: 50%;
            margin: 0 3px;
        }

        /* ---- Info bar (date / num / emetteur) ---- */
        .inv-info-bar {
            position: relative;
            z-index: 3;
            display: grid;
            grid-template-columns: 1fr 1fr 1.4fr;
            gap: 0;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 28px;
        }

        .info-cell {
            padding: 16px 20px;
            border-right: 1.5px solid var(--border);
        }

        .info-cell:last-child { border-right: none; }

        .info-cell-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
            color: var(--olive-leaf);
            margin-bottom: 6px;
        }

        .info-cell-value {
            font-size: 15px;
            font-weight: 700;
            color: var(--olive-dark);
            line-height: 1.4;
        }

        .info-cell-value.small {
            font-size: 13px;
            font-weight: 400;
            color: #5a7a45;
            line-height: 1.7;
        }

        /* ---- Bill to section ---- */
        .inv-bill-to {
            margin-bottom: 28px;
            padding: 18px 22px;
            background: transparent;
            border-left: 4px solid var(--olive-leaf);
            border-radius: 0 10px 10px 0;
            display: inline-block;
            position: relative;
            z-index: 3;
            min-width: 300px;
        }

        .bill-to-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
            color: var(--olive-leaf);
            margin-bottom: 8px;
        }

        .bill-to-name {
            font-size: 17px;
            font-weight: 700;
            color: var(--olive-dark);
            margin-bottom: 5px;
        }

        .bill-to-detail {
            font-size: 13px;
            color: #5a7a45;
            line-height: 1.7;
        }

        .bill-to-mf {
            font-size: 13px;
            font-weight: 700;
            color: var(--olive-dark);
            margin-top: 4px;
        }

        /* ---- Products table ---- */
        .inv-table-wrap { position: relative; z-index: 3; margin-bottom: 24px; }

        .inv-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inv-table thead tr {
            background: var(--olive-mid);
        }

        .inv-table thead th {
            padding: 12px 14px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: white;
            text-align: left;
        }

        .inv-table thead th.right { text-align: right; }

        .inv-table tbody tr {
            border-bottom: 1px solid var(--olive-bg);
        }

        .inv-table tbody tr:last-child { border-bottom: none; }

        .inv-table tbody tr:nth-child(even) {
            background: #fafcf8;
        }

        .inv-table tbody td {
            padding: 13px 14px;
            font-size: 14px;
            color: #1a3a1a;
            border-left: 1px solid #e8f0e0;
        }

        .inv-table tbody td:first-child { border-left: none; }
        .inv-table tbody td.right { text-align: right; font-weight: 600; }
        .inv-table tbody td.center { text-align: center; }

        /* Outer border */
        .inv-table-outer {
            border: 1.5px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
        }

        /* ---- Totals + Signature row ---- */
        .inv-bottom-row {
            position: relative;
            z-index: 3;
            display: flex;
            justify-content: flex-end;
            margin-bottom: 28px;
        }

        .inv-totals-table {
            min-width: 280px;
            border-collapse: collapse;
        }

        .inv-totals-table td {
            padding: 6px 12px;
            font-size: 14px;
            color: #5a7a45;
        }

        .inv-totals-table td.lbl { text-align: right; }
        .inv-totals-table td.val { text-align: right; font-weight: 600; color: var(--olive-dark); min-width: 120px; }

        .inv-totals-table tr.total-line td {
            border-top: 2px solid var(--border);
            padding-top: 10px;
            font-size: 16px;
            font-weight: 700;
            color: var(--olive-dark);
        }

        /* ---- Written amount ---- */
        .inv-written {
            position: relative;
            z-index: 3;
            font-size: 13px;
            color: #5a7a45;
            font-style: italic;
            margin-bottom: 28px;
            padding: 12px 16px;
            border-left: 3px solid var(--olive-pale);
        }

        /* ---- Signature ---- */
        .inv-signature-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        .inv-signature-box {
            text-align: center;
            min-width: 180px;
        }

        .inv-signature-box .sig-line {
            width: 100%;
            height: 50px;
            border-bottom: 1.5px solid var(--border);
            margin-bottom: 8px;
        }

        .inv-signature-box p {
            font-size: 12px;
            color: #7a9a65;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        /* ---- Status badge ---- */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 6px;
        }

        .status-paid   { background: #dff0c8; color: #2e5c1e; border: 1.5px solid var(--border); }
        .status-unpaid { background: #faeeda; color: #854f0b; border: 1.5px solid #f5c842; }

        /* =============================================
           PRINT STYLES
           ============================================= */
        @media print {
            body { background: white; }
            .action-bar { display: none !important; }
            .invoice-wrapper { margin: 0; padding: 0; max-width: 100%; }
            .invoice {
                border-radius: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .inv-table thead tr { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .inv-bill-to { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<?php
if ($id <= 0) { header("Location: facture.php"); exit(); }

$factureCollection = $db->facture;
$clientCollection = $db->client;
$prodfactCollection = $db->prodfact;
$produitCollection = $db->produit;
$familleCollection = $db->famille;

/* Load facture + client */
$fact = $factureCollection->findOne(['NumFact' => $id]);
if (!$fact) { header("Location: facture.php"); exit(); }
$fact = (array) $fact;

$clientInfo = $clientCollection->findOne(['MatFis' => $fact['MatFis']]);
if ($clientInfo) {
    $fact['NomEntreprise'] = $clientInfo['NomEntreprise'];
    $fact['Nom'] = $clientInfo['Nom'];
    $fact['Prenom'] = $clientInfo['Prenom'];
    $fact['Email'] = $clientInfo['Email'];
    $fact['NumTel'] = $clientInfo['NumTel'];
    $fact['ClientMat'] = $clientInfo['MatFis'];
} else {
    $fact['NomEntreprise'] = 'Inconnu';
    $fact['Nom'] = '';
    $fact['Prenom'] = '';
    $fact['Email'] = '';
    $fact['NumTel'] = '';
    $fact['ClientMat'] = '';
}

/* Load product lines */
$lines = [];
$resLines = $prodfactCollection->find(['NumFact' => $id]);
foreach ($resLines as $l) {
    $lArray = (array) $l;
    $prodInfo = $produitCollection->findOne(['IdProduit' => $lArray['IdProduit']]);
    if ($prodInfo) {
        $lArray['NomProduit'] = $prodInfo['NomProduit'];
        $lArray['PrixUnit'] = $prodInfo['PrixUnit'];
        $lArray['poid'] = $prodInfo['poid'];
        
        $famInfo = $familleCollection->findOne(['IdFamille' => $prodInfo['IdFamille']]);
        if ($famInfo) {
            $lArray['typee'] = $famInfo['typee'];
            $lArray['tva'] = $famInfo['tva'];
        }
    }
    $lines[] = $lArray;
}


/* Totals */
$totalHT  = 0;
$totalTVA = 0;
foreach ($lines as $l) {
    $ht       = $l['PrixUnit'] * $l['qte'];
    $tva      = $ht * (($l['tva'] ?? 0) / 100);
    $totalHT  += $ht;
    $totalTVA += $tva;
}
$totalTTC   = $totalHT + $totalTVA;
$timbreFisc = 1.000; /* Standard Tunisian fiscal stamp */
$totalFinal = $totalTTC + $timbreFisc;

/* Convert number to French words (simplified for DT) */
function numberToWords($num) {
    $num = intval(round($num));
    $ones = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
             'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept',
             'dix-huit', 'dix-neuf'];
    $tens = ['', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante',
             'quatre-vingt', 'quatre-vingt'];

    if ($num === 0) return 'zéro';
    if ($num < 0)  return 'moins ' . numberToWords(-$num);

    $words = '';
    if ($num >= 1000) {
        $t = intval($num / 1000);
        $words .= ($t === 1 ? 'mille' : numberToWords($t) . ' mille');
        $num %= 1000;
        if ($num > 0) $words .= ' ';
    }
    if ($num >= 100) {
        $h = intval($num / 100);
        $words .= ($h === 1 ? 'cent' : $ones[$h] . ' cent');
        $num %= 100;
        if ($num > 0) $words .= ' ';
    }
    if ($num >= 20) {
        $t = intval($num / 10);
        $u = $num % 10;
        if ($t === 7 || $t === 9) {
            $words .= $tens[$t] . '-' . $ones[10 + $u];
        } elseif ($t === 8) {
            $words .= $tens[$t] . ($u > 0 ? '-' . $ones[$u] : 's');
        } else {
            $words .= $tens[$t] . ($u > 0 ? '-' . $ones[$u] : '');
        }
    } elseif ($num > 0) {
        $words .= $ones[$num];
    }

    return trim($words);
}

$writtenAmount = ucfirst(numberToWords(intval(round($totalFinal)))) . ' dinar' . (round($totalFinal) > 1 ? 's' : '');
$numFactStr = str_pad($id, 4, '0', STR_PAD_LEFT);

?>

<!-- ACTION BAR -->
<div class="action-bar">
    <span class="action-bar-title">
        Facture #<?php echo $numFactStr; ?> —
        <?php echo htmlspecialchars($fact['NomEntreprise'] ?? 'Client inconnu'); ?>
    </span>
    <div class="action-bar-btns">
        <a href="facture.php" class="abtn abtn-back">← Retour</a>
        <button class="abtn abtn-print" onclick="window.print()">🖨 Imprimer</button>
        <button class="abtn abtn-pdf"   onclick="savePDF()">📥 Télécharger PDF</button>
    </div>
</div>

<!-- INVOICE -->
<div class="invoice-wrapper">
<div class="invoice">
    <div class="inv-body">

        <!-- TITLE + LOGO -->
        <div class="inv-title-row">
            <div>
                <?php
                $typeLabel = $fact['TypeFact'] === 'devis' ? 'DEVIS' : ($fact['TypeFact'] === 'bdl' ? 'BON DE LIVRAISON' : 'FACTURE');
                ?>
                <div class="inv-big-title"><?php echo $typeLabel; ?> <span>#<?php echo $numFactStr; ?></span></div>
            </div>
            <div class="inv-logo-block">
                <div class="brand">RAMO CLEAN</div>
            </div>
        </div>

        <!-- INFO BAR -->
        <div class="inv-info-bar">
            <div class="info-cell">
                <div class="info-cell-label">Date</div>
                <div class="info-cell-value"><?php echo date('d/m/Y', strtotime($fact['datefact'])); ?></div>
            </div>
            <div class="info-cell">
                <div class="info-cell-label">N° de la facture</div>
                <div class="info-cell-value"><?php echo $numFactStr . '-' . date('Y', strtotime($fact['datefact'])); ?></div>
            </div>
            <div class="info-cell">
                <div class="info-cell-label">Émetteur</div>
                <div class="info-cell-value small">
                    <strong style="color:var(--olive-dark); font-size:14px;">Ramo Clean</strong><br>
                    Tunisie<br>
                    Tél. : +216 XX XXX XXX<br>
                    contact@ramoclean.tn<br>
                    MF : 1913645T
                </div>
            </div>
        </div>

        <!-- BILL TO -->
        <div class="inv-bill-to">
            <div class="bill-to-label">Facturer à</div>
            <div class="bill-to-name"><?php echo htmlspecialchars($fact['NomEntreprise'] ?? '—'); ?></div>
            <div class="bill-to-detail">
                <?php $contact = trim(($fact['Nom'] ?? '') . ' ' . ($fact['Prenom'] ?? ''));
                if ($contact) echo htmlspecialchars($contact) . '<br>'; ?>
                <?php if ($fact['NumTel']): ?>Tél. : <?php echo htmlspecialchars($fact['NumTel']); ?><br><?php endif; ?>
                <?php if ($fact['Email']): ?><?php echo htmlspecialchars($fact['Email']); ?><?php endif; ?>
            </div>
            <?php if ($fact['ClientMat']):
                /* Extract first 7 chars (digits) + slash + first letter */
                $mf     = $fact['ClientMat'];
                $parts  = explode('/', $mf);
                $mfShort = $parts[0] . (isset($parts[1]) ? '/' . strtoupper(substr($parts[1], 0, 1)) : '');
            ?>
            <div class="bill-to-mf">MF : <?php echo htmlspecialchars($mfShort); ?></div>
            <?php endif; ?>
        </div>

        <!-- PRODUCTS TABLE -->
        <div class="inv-table-wrap">
            <div class="inv-table-outer">
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Désignation</th>
                            <th>Poids</th>
                            <th class="right">Prix unitaire</th>
                            <th class="right">Quantité</th>
                            <th class="right">TVA</th>
                            <th class="right">Prix total</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($lines as $i => $l):
                        $ht = $l['PrixUnit'] * $l['qte'];
                    ?>
                    <tr>
                        <td class="center" style="color:#7a9a65; font-size:13px;"><?php echo $i + 1; ?></td>
                        <td><strong><?php echo htmlspecialchars($l['NomProduit']); ?></strong></td>
                        <td style="color:#5a7a45;"><?php echo $l['poid'] . ' ' . htmlspecialchars($l['typee'] ?? ''); ?></td>
                        <td class="right"><?php echo number_format($l['PrixUnit'], 3, '.', ' '); ?> DT</td>
                        <td class="center"><strong><?php echo $l['qte']; ?></strong></td>
                        <td class="center" style="color:#5a7a45;"><?php echo ($l['tva'] ?? 0); ?>%</td>
                        <td class="right"><?php echo number_format($ht, 3, '.', ' '); ?> DT</td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TOTALS -->
        <div class="inv-bottom-row">
            <table class="inv-totals-table">
                <tr>
                    <td class="lbl">Sous-total HT</td>
                    <td class="val"><?php echo number_format($totalHT, 3, '.', ' '); ?> DT</td>
                </tr>
                <tr>
                    <td class="lbl">TVA</td>
                    <td class="val"><?php echo number_format($totalTVA, 3, '.', ' '); ?> DT</td>
                </tr>
                <tr>
                    <td class="lbl">Timbre fiscal</td>
                    <td class="val"><?php echo number_format($timbreFisc, 3, '.', ' '); ?> DT</td>
                </tr>
                <tr class="total-line">
                    <td class="lbl">Total</td>
                    <td class="val"><?php echo number_format($totalFinal, 3, '.', ' '); ?> DT</td>
                </tr>
            </table>
        </div>

        <!-- WRITTEN AMOUNT -->
        <div class="inv-written">
            Arrêter la présente facture à la somme de <strong><?php echo $writtenAmount; ?></strong>.
        </div>

        <!-- SIGNATURE -->
        <div class="inv-signature-row">
            <div class="inv-signature-box">
                <div class="sig-line"></div>
                <p>Cachet et signature</p>
            </div>
        </div>

    </div><!-- end inv-body -->
</div><!-- end invoice -->
</div><!-- end wrapper -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
window.addEventListener('load', () => setTimeout(() => window.print(), 600));

async function savePDF() {
    const btn = document.querySelector('.abtn-pdf');
    btn.textContent = '⏳ Génération...';
    btn.disabled = true;

    try {
        const { jsPDF } = window.jspdf;
        const element   = document.querySelector('.invoice');

        /* Hide action bar during capture */
        document.querySelector('.action-bar').style.display = 'none';

        const canvas = await html2canvas(element, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
            windowWidth: element.scrollWidth,
            windowHeight: element.scrollHeight
        });

        /* Restore action bar */
        document.querySelector('.action-bar').style.display = 'flex';

        const imgData = canvas.toDataURL('image/jpeg', 0.95);
        const pdf     = new jsPDF({ unit: 'mm', format: 'a4', orientation: 'portrait' });

        const pageW  = pdf.internal.pageSize.getWidth();
        const pageH  = pdf.internal.pageSize.getHeight();
        const margin = 8;
        const imgW   = pageW - margin * 2;
        const imgH   = (canvas.height * imgW) / canvas.width;

        /* If invoice is taller than one page, split across pages */
        if (imgH <= pageH - margin * 2) {
            pdf.addImage(imgData, 'JPEG', margin, margin, imgW, imgH);
        } else {
            let yPos     = 0;
            let pageNum  = 0;
            const ratio  = canvas.width / imgW;
            const sliceH = (pageH - margin * 2) * ratio;

            while (yPos < canvas.height) {
                if (pageNum > 0) pdf.addPage();

                const sliceCanvas  = document.createElement('canvas');
                sliceCanvas.width  = canvas.width;
                sliceCanvas.height = Math.min(sliceH, canvas.height - yPos);

                const ctx = sliceCanvas.getContext('2d');
                ctx.drawImage(canvas, 0, -yPos, canvas.width, canvas.height);

                const sliceData = sliceCanvas.toDataURL('image/jpeg', 0.95);
                const sliceImgH = (sliceCanvas.height / ratio);
                pdf.addImage(sliceData, 'JPEG', margin, margin, imgW, sliceImgH);

                yPos    += sliceH;
                pageNum += 1;
            }
        }

        const filename = 'Facture_<?php echo $numFactStr; ?>_<?php echo preg_replace('/[^a-zA-Z0-9]/', '', $fact['NomEntreprise'] ?? 'RamoClean'); ?>.pdf';
        pdf.save(filename);

    } catch (err) {
        console.error('PDF error:', err);
        alert('Erreur lors de la génération du PDF. Utilisez le bouton Imprimer → Enregistrer en PDF.');
    }

    btn.textContent = '📥 Télécharger PDF';
    btn.disabled = false;
}
</script>

</body>
</html>