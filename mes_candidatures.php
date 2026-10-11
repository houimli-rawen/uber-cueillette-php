<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin('ouvrier');

$id = $_SESSION['user_id'];

$filterDecision = '';
$where = "WHERE c.id_ouvrier=" . $id;

if (isset($_GET['decision'])) {
    $filterDecision = sanitize($conn, $_GET['decision']);
    if ($filterDecision === 'encours' || $filterDecision === 'accepte' || $filterDecision === 'refuse') {
        $where .= " AND c.decision='" . $filterDecision . "'";
    }
}

$sql = "SELECT c.*, o.date_debut, o.date_fin, o.prix_journee, o.nombre_ouvriers, o.adresse as adresse_offre, o.date_limite, tf.libelle as fruit, g.libelle as gouv, a.nom as ag_nom, a.prenom as ag_prenom FROM candidature c JOIN offre o ON c.id_offre=o.id_offre JOIN type_fruit tf ON o.id_type_fruit=tf.id_type_fruit JOIN gouvernorat g ON o.id_gouvernorat=g.id_gouvernorat JOIN agriculteur a ON o.id_agriculteur=a.id_agriculteur " . $where . " ORDER BY c.date_candidature DESC";
$candidatures = mysqli_query($conn, $sql);

$counts = mysqli_query($conn, "SELECT decision, COUNT(*) as c FROM candidature WHERE id_ouvrier=" . $id . " GROUP BY decision");
$countMap = array();
$total = 0;
while ($row = mysqli_fetch_assoc($counts)) {
    $countMap[$row['decision']] = $row['c'];
    $total += $row['c'];
}

$pageTitle = 'Mes Candidatures — AgriLink';
$activePage = 'mes_candidatures';

include 'header.php';
include 'navbar.php';
?>

<style>
.tab-bar { display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1.5rem; }
.tab-btn { padding:0.5rem 1.25rem; border-radius:20px; font-size:0.9rem; font-weight:600; border:2px solid var(--cream-dark); background:var(--cream); color:var(--text-muted); cursor:pointer; text-decoration:none; transition:all 0.2s; }
.tab-btn:hover { border-color:var(--olive); color:var(--olive-light); }
.tab-btn.active { background:var(--olive); border-color:var(--olive); color:#fff; }
.tab-btn .count { background:rgba(0,0,0,0.15); border-radius:20px; padding:0.1rem 0.5rem; font-size:0.78rem; margin-left:0.4rem; }
.tab-btn.active .count { background:rgba(255,255,255,0.25); }

.cand-card { background:var(--cream); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; margin-bottom:1rem; transition:transform 0.2s; border: 1px solid var(--cream-dark); }
.cand-card:hover { transform:translateY(-2px); }
.cand-top { display:grid; grid-template-columns:1fr auto; align-items:start; gap:1rem; padding:1.25rem; }
.cand-meta { display:flex; flex-wrap:wrap; gap:0.75rem 1.5rem; padding:0 1.25rem 1rem; }
.cand-meta-item { display:flex; align-items:center; gap:0.4rem; font-size:0.87rem; color:var(--text-muted); }
.cand-meta-item i { color:var(--olive); }
.cand-footer { padding:0.75rem 1.25rem; background:var(--cream-dark); border-top:1px solid #0f0f1a; }

.status-badge-encours { background:rgba(243,156,18,0.2); color:#f39c12; }
.status-badge-accepte { background:rgba(46,204,113,0.2); color:#2ecc71; }
.status-badge-refuse { background:rgba(231,76,60,0.2); color:#e74c3c; }

.status-indicator { display:flex; align-items:center; gap:0.5rem; }
.status-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
.dot-encours { background:#f39c12; animation:pulse 1.5s infinite; }
.dot-accepte { background:#2ecc71; }
.dot-refuse { background:#e74c3c; }
@keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.4;} }
</style>

<div class="page-header">
    <div class="container">
        <h1><i class="fa fa-file-alt"></i> Mes Candidatures</h1>
        <p>Suivez le statut de toutes vos candidatures</p>
    </div>
</div>

<div class="container">
    <div class="tab-bar">
        <a href="mes_candidatures.php" class="tab-btn <?php echo $filterDecision === '' ? 'active' : ''; ?>">Toutes <span class="count"><?php echo $total; ?></span></a>
        <a href="mes_candidatures.php?decision=encours" class="tab-btn <?php echo $filterDecision === 'encours' ? 'active' : ''; ?>">En attente <span class="count"><?php echo isset($countMap['encours']) ? $countMap['encours'] : 0; ?></span></a>
        <a href="mes_candidatures.php?decision=accepte" class="tab-btn <?php echo $filterDecision === 'accepte' ? 'active' : ''; ?>">Acceptees <span class="count"><?php echo isset($countMap['accepte']) ? $countMap['accepte'] : 0; ?></span></a>
        <a href="mes_candidatures.php?decision=refuse" class="tab-btn <?php echo $filterDecision === 'refuse' ? 'active' : ''; ?>">Refusees <span class="count"><?php echo isset($countMap['refuse']) ? $countMap['refuse'] : 0; ?></span></a>
    </div>

    <?php if (mysqli_num_rows($candidatures) === 0): ?>
        <div class="card text-center" style="padding:3rem;">
            <div style="font-size:4rem;margin-bottom:1rem;">📭</div>
            <h3 style="font-family:'Playfair Display',serif;margin-bottom:0.5rem;">Aucune candidature</h3>
            <p class="text-muted"><?php echo $filterDecision !== '' ? 'Aucune candidature dans cette categorie.' : 'Vous n\'avez pas encore postule.'; ?></p>
            <a href="offres.php" class="btn btn-primary mt-3"><i class="fa fa-search"></i> Explorer les offres</a>
        </div>
    <?php else: ?>
        <?php while ($c = mysqli_fetch_assoc($candidatures)):
            $duree = ceil((strtotime($c['date_fin']) - strtotime($c['date_debut'])) / 86400) + 1;
            $gainEstime = $duree * $c['prix_journee'];
            $expired = strtotime($c['date_limite']) < time();
            $statusLabels = array('encours' => 'En attente', 'accepte' => 'Accepte', 'refuse' => 'Refuse');
            $statusLabel = isset($statusLabels[$c['decision']]) ? $statusLabels[$c['decision']] : $c['decision'];
        ?>
        <div class="cand-card fade-in">
            <div class="cand-top">
                <div>
                    <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;margin-bottom:0.5rem;">
                        <h3 style="font-family:'Playfair Display',serif;font-size:1.15rem;">🌿 <?php echo htmlspecialchars($c['fruit']); ?> — <?php echo htmlspecialchars($c['gouv']); ?></h3>
                    </div>
                    <div style="font-size:0.85rem;color:var(--text-muted);">
                        <i class="fa fa-user-tie"></i> Agriculteur: <?php echo htmlspecialchars($c['ag_prenom'] . ' ' . $c['ag_nom']); ?>
                        &nbsp;|&nbsp; Postule: <?php echo date('d/m/Y', strtotime($c['date_candidature'])); ?>
                    </div>
                </div>
                <div class="status-indicator">
                    <div class="status-dot dot-<?php echo $c['decision']; ?>"></div>
                    <span class="badge status-badge-<?php echo $c['decision']; ?>"><?php echo $statusLabel; ?></span>
                </div>
            </div>
            <div class="cand-meta">
                <div class="cand-meta-item"><i class="fa fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($c['date_debut'])); ?> → <?php echo date('d/m/Y', strtotime($c['date_fin'])); ?></div>
                <div class="cand-meta-item"><i class="fa fa-clock"></i> <?php echo $duree; ?> jours</div>
                <div class="cand-meta-item"><i class="fa fa-money-bill-wave"></i> <?php echo number_format($c['prix_journee'], 0); ?> TND/jour</div>
                <div class="cand-meta-item"><i class="fa fa-map-pin"></i> <?php echo htmlspecialchars($c['adresse_offre']); ?></div>
                <div class="cand-meta-item"><i class="fa fa-hourglass-end"></i> Limite: <?php echo date('d/m/Y', strtotime($c['date_limite'])); ?>
                    <?php if ($expired && $c['decision'] === 'encours'): ?>
                        <span class="badge badge-danger" style="font-size:0.75rem;margin-left:0.25rem;">Expiree</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="cand-footer">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
                    <span style="font-size:0.88rem;">Gain estime: <strong style="color:var(--olive-light);font-family:'Playfair Display',serif;"><?php echo number_format($gainEstime, 0); ?> TND</strong></span>
                    <?php if ($c['decision'] === 'encours' && !$expired): ?>
                        <span style="font-size:0.82rem;color:var(--text-muted);font-style:italic;"><i class="fa fa-spinner fa-spin"></i> En attente de reponse</span>
                    <?php elseif ($c['decision'] === 'accepte'): ?>
                        <span style="font-size:0.88rem;color:var(--success);font-weight:600;"><i class="fa fa-check-circle"></i> Félicitations ! Candidature retenue.</span>
                    <?php elseif ($c['decision'] === 'refuse'): ?>
                        <span style="font-size:0.88rem;color:var(--danger);"><i class="fa fa-times-circle"></i> Candidature non retenue.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
    <div style="height:3rem;"></div>
</div>

<?php include 'footer.php'; ?>
