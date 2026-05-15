<?php require("ajoutfacture_logic.php"); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ramo Clean — Nouveau Document</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="ajoutfacture.css">
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
</nav>

<div class="dashboard">

    <div class="topbar">
        <div class="page-title" id="page-title">🧾 Nouvelle Facture <span style="font-size:14px;color:var(--text-muted);font-weight:400;">#<?php echo str_pad($nextNum,4,'0',STR_PAD_LEFT); ?></span></div>
        <a href="facture.php" class="btn">← Retour</a>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error">✗ <?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($warning && !empty($shortages)): ?>
    <div style="background:#faeeda;border:1.5px solid #f5c842;border-radius:14px;padding:20px 24px;">
        <div style="font-size:15px;font-weight:700;color:#854f0b;margin-bottom:12px;">
            ⚠ Stock insuffisant pour certains produits
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:13px;margin-bottom:16px;">
            <thead>
                <tr style="border-bottom:1.5px solid #f5c842;">
                    <th style="text-align:left;padding:6px 10px;color:#854f0b;">Produit</th>
                    <th style="text-align:right;padding:6px 10px;color:#854f0b;">Commandé</th>
                    <th style="text-align:right;padding:6px 10px;color:#854f0b;">Disponible</th>
                    <th style="text-align:right;padding:6px 10px;color:#854f0b;">Manque</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($shortages as $s): ?>
            <tr style="border-bottom:1px solid rgba(245,200,66,0.3);">
                <td style="padding:8px 10px;font-weight:600;color:#1a3a1a;"><?php echo htmlspecialchars($s['nom']); ?></td>
                <td style="text-align:right;padding:8px 10px;color:#1a3a1a;"><?php echo $s['needed']; ?></td>
                <td style="text-align:right;padding:8px 10px;color:<?php echo $s['dispo'] > 0 ? '#2e5c1e' : '#a32d2d'; ?>;"><?php echo $s['dispo']; ?></td>
                <td style="text-align:right;padding:8px 10px;font-weight:700;color:#a32d2d;">-<?php echo $s['manque']; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p style="font-size:13px;color:#854f0b;margin-bottom:16px;">
            Voulez-vous continuer quand même ? Le stock sera déduit jusqu'à 0 pour les produits manquants.
        </p>
        <!-- Force form — carries all original POST values -->
        <form method="POST" action="ajoutfacture.php">
            <input type="hidden" name="MatFis"    value="<?php echo htmlspecialchars($_POST['MatFis']   ?? ''); ?>">
            <input type="hidden" name="datefact"  value="<?php echo htmlspecialchars($_POST['datefact'] ?? ''); ?>">
            <input type="hidden" name="TypeFact"  value="<?php echo htmlspecialchars($_POST['TypeFact'] ?? 'fact'); ?>">
            <input type="hidden" name="force_create" value="1">
            <?php
            $pids  = $_POST['prod_id']  ?? [];
            $pqtes = $_POST['prod_qte'] ?? [];
            for ($i = 0; $i < count($pids); $i++):
                if (intval($pids[$i]) > 0 && intval($pqtes[$i]) > 0):
            ?>
            <input type="hidden" name="prod_id[]"  value="<?php echo intval($pids[$i]); ?>">
            <input type="hidden" name="prod_qte[]" value="<?php echo intval($pqtes[$i]); ?>">
            <?php endif; endfor; ?>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="ajoutfacture.php" class="btn">✕ Annuler</a>
                <button type="submit" name="create_facture" class="btn-primary"
                    style="background:#854f0b;border-color:#854f0b;">
                    ⚠ Continuer quand même
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <form method="POST" action="ajoutfacture.php" id="facture-form">

        <!-- TYPE SELECTOR -->
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header">
                <span class="card-title">📄 Type de document</span>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap; padding-top:4px;">

                <label class="type-doc-option active" id="opt-fact" onclick="setType('fact')">
                    <input type="radio" name="TypeFact" value="fact" checked style="display:none;">
                    <div class="type-doc-icon">🧾</div>
                    <div class="type-doc-label">Facture</div>
                    <div class="type-doc-desc">Document de vente avec paiement</div>
                </label>

                <label class="type-doc-option" id="opt-devis" onclick="setType('devis')">
                    <input type="radio" name="TypeFact" value="devis" style="display:none;">
                    <div class="type-doc-icon">📋</div>
                    <div class="type-doc-label">Devis</div>
                    <div class="type-doc-desc">Offre de prix, non payable</div>
                </label>

                <label class="type-doc-option" id="opt-bdl" onclick="setType('bdl')">
                    <input type="radio" name="TypeFact" value="bdl" style="display:none;">
                    <div class="type-doc-icon">📦</div>
                    <div class="type-doc-label">Bon de livraison</div>
                    <div class="type-doc-desc">Confirmation de livraison</div>
                </label>

            </div>
        </div>

        <!-- CLIENT + DATE -->
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header">
                <span class="card-title">👤 Client & Date</span>
            </div>
            <div class="form-grid">

                <div class="form-group full">
                    <label>Client *</label>
                    <select name="MatFis" id="client-select" required onchange="previewClient(this.value)">
                        <option value="">— Choisir un client —</option>
                        <?php foreach ($allClients as $c): ?>
                        <option value="<?php echo htmlspecialchars($c['MatFis']); ?>"
                            data-nom="<?php echo htmlspecialchars(($c['Nom']??'').' '.($c['Prenom']??'')); ?>"
                            data-tel="<?php echo htmlspecialchars($c['NumTel']); ?>"
                            data-mat="<?php echo htmlspecialchars($c['MatFis']); ?>"
                            <?php echo (isset($_POST['MatFis'])&&$_POST['MatFis']===$c['MatFis'])?'selected':''; ?>>
                            <?php echo htmlspecialchars($c['NomEntreprise']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <a href="ajoutclient.php" style="font-size:12px;color:var(--blue-spark);margin-top:4px;font-weight:600;">+ Créer un nouveau client →</a>
                </div>

                <div class="form-group full">
                    <div class="client-preview" id="client-preview">
                        <div class="cp-field"><span class="cp-label">Entreprise</span><span class="cp-value" id="cp-entreprise">—</span></div>
                        <div class="cp-field"><span class="cp-label">Contact</span><span class="cp-value" id="cp-contact">—</span></div>
                        <div class="cp-field"><span class="cp-label">Matricule</span><span class="cp-value" id="cp-mat">—</span></div>
                        <div class="cp-field"><span class="cp-label">Téléphone</span><span class="cp-value" id="cp-tel">—</span></div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Date *</label>
                    <input type="datetime-local" name="datefact" required
                        value="<?php echo isset($_POST['datefact']) ? htmlspecialchars($_POST['datefact']) : date('Y-m-d\TH:i'); ?>">
                </div>

                <!-- Payment note (dynamic based on type) -->
                <div class="form-group">
                    <label>Paiement</label>
                    <div id="payment-note" class="payment-note fact-note">
                        💡 Le statut de paiement se gère dans les détails après création.
                    </div>
                </div>

            </div>
        </div>

        <!-- PRODUCT LINES -->
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header">
                <span class="card-title">📦 Lignes de produits</span>
                <button type="button" class="btn" onclick="addLine()">+ Ajouter ligne</button>
            </div>
            <div style="overflow-x:auto;">
                <table class="lines-table" id="lines-table">
                    <thead>
                        <tr>
                            <th style="width:28%;">Produit</th>
                            <th style="width:10%;">Stock</th>
                            <th style="width:10%;">Prix HT</th>
                            <th style="width:7%;">TVA</th>
                            <th style="width:13%;">Poids (kg/lit)</th>
                            <th style="width:12%;">Quantité</th>
                            <th style="width:10%;">Total HT</th>
                            <th style="width:5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="lines-body"></tbody>
                </table>
            </div>
            <div class="add-line-row">
                <button type="button" class="btn" onclick="addLine()" style="font-size:13px;">+ Ajouter une ligne</button>
            </div>
            <div style="display:flex;justify-content:flex-end;margin-top:16px;padding:0 12px;">
                <div class="totals-panel">
                    <div class="total-row ht"><span>Sous-total HT</span><span id="total-ht">0.000 DT</span></div>
                    <div class="total-row tva"><span>TVA (estimée)</span><span id="total-tva">0.000 DT</span></div>
                    <div class="total-row ttc"><span>Total TTC</span><span id="total-ttc">0.000 DT</span></div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;">
            <a href="facture.php" class="btn">Annuler</a>
            <button type="submit" name="create_facture" class="btn-primary" id="submit-btn">
                🧾 Créer & Aperçu
            </button>
        </div>

    </form>
</div>

<div id="produits-data" style="display:none;"><?php echo json_encode($allProduits); ?></div>
<div id="clients-data" style="display:none;"><?php echo json_encode($allClients); ?></div>

<style>
.type-doc-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 16px 24px;
    border: 2px solid var(--border);
    border-radius: 14px;
    background: var(--olive-bg);
    cursor: pointer;
    transition: all 0.15s;
    min-width: 160px;
    text-align: center;
    user-select: none;
}
.type-doc-option:hover { background: var(--olive-pale); border-color: var(--olive-bright); }
.type-doc-option.active { border-color: var(--olive-mid); background: #dff0c8; }
.type-doc-icon  { font-size: 26px; }
.type-doc-label { font-size: 14px; font-weight: 700; color: var(--olive-dark); }
.type-doc-desc  { font-size: 11px; color: var(--text-muted); }
.payment-note {
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.5;
}
.fact-note  { background: #eef4e6; color: var(--text-soft); border: 1.5px solid var(--border); }
.devis-note { background: #faeeda; color: #854f0b; border: 1.5px solid #f5c842; }
.bdl-note   { background: var(--blue-light); color: #1060a0; border: 1.5px solid #8fd0f0; }
</style>

<script>
const produits = JSON.parse(document.getElementById('produits-data').textContent);
let lineCount  = 0;

const typeConfig = {
    fact:  { title: '🧾 Nouvelle Facture', btn: '🧾 Créer & Aperçu', noteClass: 'fact-note',  note: '💡 Le statut de paiement se gère dans les détails après création.' },
    devis: { title: '📋 Nouveau Devis',    btn: '📋 Créer le devis',  noteClass: 'devis-note', note: '📋 Un devis est toujours non payé — c\'est une offre de prix.' },
    bdl:   { title: '📦 Bon de livraison', btn: '📦 Créer le BDL',   noteClass: 'bdl-note',   note: '📦 Le bon de livraison confirme une livraison, sans paiement direct.' }
};

function setType(type) {
    ['fact','devis','bdl'].forEach(t => {
        document.getElementById('opt-' + t).classList.toggle('active', t === type);
        document.querySelector(`input[value="${t}"]`).checked = (t === type);
    });
    const cfg = typeConfig[type];
    document.getElementById('page-title').innerHTML = cfg.title + ' <span style="font-size:14px;color:var(--text-muted);font-weight:400;">#<?php echo str_pad($nextNum,4,"0",STR_PAD_LEFT); ?></span>';
    document.getElementById('submit-btn').textContent = cfg.btn;
    const note = document.getElementById('payment-note');
    note.className = 'payment-note ' + cfg.noteClass;
    note.textContent = cfg.note;
}

function prodOptions(selectedId) {
    let html = '<option value="">— Choisir un produit —</option>';
    produits.forEach(p => {
        const sel = p.IdProduit == selectedId ? 'selected' : '';
        html += `<option value="${p.IdProduit}" ${sel} data-prix="${p.PrixUnit}" data-tva="${p.tva??0}" data-stock="${p.stock}" data-poid="${p.poid}" data-typee="${p.typee??''}">${p.NomProduit} — ${p.PrixUnit} DT</option>`;
    });
    return html;
}

function addLine(prodId, qte) {
    const tbody = document.getElementById('lines-body');
    const id    = 'line-' + lineCount++;
    const tr    = document.createElement('tr');
    tr.id = id;
    tr.innerHTML = `
        <td><select name="prod_id[]" onchange="onProdChange('${id}')" required>${prodOptions(prodId||null)}</select></td>
        <td><span class="line-stock" style="font-size:13px;color:var(--text-muted);">—</span></td>
        <td><span class="line-prix" style="font-size:13px;font-weight:600;color:var(--olive-dark);">—</span></td>
        <td><span class="line-tva" style="font-size:13px;color:var(--text-muted);">—</span></td>
        <td>
            <input type="number" class="line-weight" step="0.001" min="0.001"
                placeholder="—" title="Entrez le poids total pour calculer la quantité"
                oninput="calcQteFromWeight('${id}')" style="width:90px;">
            <span class="line-unit" style="font-size:11px;color:var(--text-muted);margin-left:3px;">—</span>
        </td>
        <td><input type="number" name="prod_qte[]" class="line-qte" min="1" value="${qte||1}" oninput="calcWeightFromQte('${id}')" onchange="calcWeightFromQte('${id}')" required style="width:70px;"></td>
        <td><span class="line-total">—</span></td>
        <td><button type="button" class="remove-line-btn" onclick="removeLine('${id}')">✕</button></td>
    `;
    tbody.appendChild(tr);
    if (prodId) onProdChange(id);
    updateRemoveButtons();
    recalcTotals();
}

function onProdChange(rowId) {
    const row   = document.getElementById(rowId);
    const sel   = row.querySelector('select[name="prod_id[]"]');
    const opt   = sel.options[sel.selectedIndex];
    const prix  = parseFloat(opt.dataset.prix)  || 0;
    const tva   = parseFloat(opt.dataset.tva)   || 0;
    const stock = opt.dataset.stock ?? '—';
    const poid  = parseFloat(opt.dataset.poid)  || 0;
    const typee = opt.dataset.typee || '';

    row.querySelector('.line-stock').textContent = stock > 0 ? `${stock} en stock` : '⚠ Rupture';
    row.querySelector('.line-stock').style.color = stock > 0 ? 'var(--text-muted)' : '#a32d2d';
    row.querySelector('.line-prix').textContent  = prix > 0 ? prix.toFixed(3)+' DT' : '—';
    row.querySelector('.line-tva').textContent   = tva+'%';
    row.querySelector('.line-unit').textContent  = typee || '—';

    /* Clear weight input when product changes */
    const weightInput = row.querySelector('.line-weight');
    if (weightInput) weightInput.value = '';

    recalcTotals();
}

function calcQteFromWeight(rowId) {
    const row       = document.getElementById(rowId);
    const sel       = row.querySelector('select[name="prod_id[]"]');
    const opt       = sel.options[sel.selectedIndex];
    const poid      = parseFloat(opt.dataset.poid) || 0;
    const weightVal = parseFloat(row.querySelector('.line-weight').value) || 0;
    const qteInput  = row.querySelector('.line-qte');

    if (poid > 0 && weightVal > 0) {
        const calculatedQte = Math.round(weightVal / poid);
        qteInput.value = calculatedQte > 0 ? calculatedQte : 1;
    }
    recalcTotals();
}

function calcWeightFromQte(rowId) {
    const row       = document.getElementById(rowId);
    const sel       = row.querySelector('select[name="prod_id[]"]');
    const opt       = sel.options[sel.selectedIndex];
    const poid      = parseFloat(opt.dataset.poid) || 0;
    const qteVal    = parseFloat(row.querySelector('.line-qte').value) || 0;
    const weightInput = row.querySelector('.line-weight');

    if (poid > 0 && qteVal > 0) {
        weightInput.value = (qteVal * poid).toFixed(3);
    }
    recalcTotals();
}

function recalcTotals() {
    let ht = 0, tvaTotal = 0;
    document.querySelectorAll('#lines-body tr').forEach(row => {
        const sel = row.querySelector('select[name="prod_id[]"]');
        const qte = parseFloat(row.querySelector('input[name="prod_qte[]"]')?.value)||0;
        if (!sel||!sel.value) return;
        const opt  = sel.options[sel.selectedIndex];
        const prix = parseFloat(opt.dataset.prix)||0;
        const tva  = parseFloat(opt.dataset.tva) ||0;
        const lineHt = prix * qte;
        ht += lineHt;
        tvaTotal += lineHt * (tva/100);
        const tc = row.querySelector('.line-total');
        if (tc) tc.textContent = lineHt.toFixed(3)+' DT';
    });
    document.getElementById('total-ht').textContent  = ht.toFixed(3)+' DT';
    document.getElementById('total-tva').textContent = tvaTotal.toFixed(3)+' DT';
    document.getElementById('total-ttc').textContent = (ht+tvaTotal).toFixed(3)+' DT';
}

function removeLine(id) {
    document.getElementById(id)?.remove();
    updateRemoveButtons(); recalcTotals();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('#lines-body tr');
    rows.forEach(r => {
        const b = r.querySelector('.remove-line-btn');
        if (b) b.style.visibility = rows.length > 1 ? 'visible' : 'hidden';
    });
}

function previewClient(mat) {
    const preview = document.getElementById('client-preview');
    if (!mat) { preview.classList.remove('visible'); return; }
    const sel = document.getElementById('client-select');
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('cp-entreprise').textContent = opt.text;
    document.getElementById('cp-contact').textContent    = opt.dataset.nom?.trim()||'—';
    document.getElementById('cp-mat').textContent        = opt.dataset.mat||'—';
    document.getElementById('cp-tel').textContent        = opt.dataset.tel||'—';
    preview.classList.add('visible');
}

document.addEventListener('DOMContentLoaded', () => {
    addLine();
    const sel = document.getElementById('client-select');
    if (sel.value) previewClient(sel.value);
});
</script>

</body>
</html>