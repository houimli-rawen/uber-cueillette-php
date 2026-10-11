<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin('ouvrier');

$id = $_SESSION['user_id'];

$historique = mysqli_query($conn, "SELECT c.*, o.date_debut, o.date_fin, o.prix_journee, o.adresse as adresse_offre, tf.libelle as fruit, g.libelle as gouv, a.nom as ag_nom, a.prenom as ag_prenom FROM candidature c JOIN offre o ON c.id_offre=o.id_offre JOIN type_fruit tf ON o.id_type_fruit=tf.id_type_fruit JOIN gouvernorat g ON o.id_gouvernorat=g.id_gouvernorat JOIN agriculteur a ON o.id_agriculteur=a.id_agriculteur WHERE c.id_ouvrier=" . $id . " AND c.decision='accepte' AND o.date_fin < CURDATE() ORDER BY o.date_fin DESC");

$statsRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total_chantiers, COALESCE(SUM(c.remuneration),0) as total_gains, COALESCE(AVG(c.note),0) as moyenne_notes, COUNT(CASE WHEN c.note IS NOT NULL THEN 1 END) as nb_notes FROM candidature c JOIN offre o ON c.id_offre=o.id_offre WHERE c.id_ouvrier=" . $id . " AND c.decision='accepte' AND o.date_fin < CURDATE()"));

$pageTitle = 'Historique — AgriLink';
$activePage = '';

include 'header.php';
include 'navbar.php';
?>

<style>
.history-card { background:var(--cream); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; margin-bottom:1.25rem; border-left:5px solid var(--olive); transition:transform 0.2s; border: 1px solid var(--cream-dark); }
.history-card:hover { transform:translateX(4px); }
.history-top { display:grid; grid-template-columns:1fr auto; gap:1rem; padding:1.25rem; align-items:start; }
.history-meta { display:flex; flex-wrap:wrap; gap:0.6rem 1.5rem; padding:0 1.25rem 1rem; }
.history-meta-item { display:flex; align-items:center; gap:0.4rem; font-size:0.87rem; color:var(--text-muted); }
.history-meta-item i { color:var(--olive); }
.eval-section { margin:0 1.25rem 1.25rem; background:var(--white); border-radius:var(--radius-sm); padding:1rem 1.25rem; border: 1px solid var(--cream-dark); }
.eval-section h4 { font-size:0.88rem; font-weight:700; color:var(--text-muted); margin-bottom:0.6rem; text-transform:uppercase; letter-spacing:0.5px; }
.star-display { font-size:1.3rem; color:#f39c12; letter-spacing:2px; }
.empty-star { color:#444; }
.gain-pill { background:linear-gradient(135deg, var(--olive), var(--olive-pale)); color:white; padding:0.4rem 1rem; border-radius:20px; font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:700; white-space:nowrap; }
.no-eval { color:var(--text-muted); font-style:italic; font-size:0.88rem; }
.summary-banner { background:linear-gradient(135deg, var(--cream), var(--cream-dark)); border-radius:var(--radius); padding:1.5rem 2rem; color:var(--text); display:grid; grid-template-columns:repeat(auto-fit, minmax(150px,1fr)); gap:1rem; margin-bottom:2rem; box-shadow:var(--shadow-lg); border: 1px solid var(--cream-dark); }
.summary-item { text-align:center; }
.summary-number { font-family:'Playfair Display',serif; font-size:2rem; font-weight:900; line-height:1; color: var(--olive-light); }
.summary-label { font-size:0.85rem; color: var(--text-muted); margin-top:0.3rem; }
</style>

<div class="page-header">
    <div class="container">
        <h1><i class="fa fa-history"></i> Historique des Chantiers</h1>
        <p>Vos chantiers termines et evaluations recues</p>
    </div>
</div>

<div class="container">
    <?php if ($statsRow['total_chantiers'] > 0): ?>
    <div class="summary-banner fade-in">
        <div class="summary-item"><div class="summary-number"><?php echo $statsRow['total_chantiers']; ?></div><div class="summary-label">Chantiers termines</div></div>
        <div class="summary-item"><div class="summary-number"><?php echo number_format($statsRow['total_gains'], 0); ?></div><div class="summary-label">Gains totaux (TND)</div></div>
        <div class="summary-item"><div class="summary-number"><?php echo $statsRow['nb_notes'] > 0 ? number_format($statsRow['moyenne_notes'], 1) : '—'; ?></div><div class="summary-label">Note moyenne /10</div></div>
        <div class="summary-item"><div class="summary-number"><?php echo $statsRow['nb_notes']; ?></div><div class="summary-label">Evaluations recues</div></div>
    </div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($historique) === 0): ?>
        <div class="card text-center" style="padding:3rem;">
            <div style="font-size:4rem;margin-bottom:1rem;">📋</div>
            <h3 style="font-family:'Playfair Display',serif;margin-bottom:0.5rem;">Aucun chantier termine</h3>
            <p class="text-muted">Vos chantiers termines apparaitront ici.</p>
            <a href="offres.php" class="btn btn-primary mt-3"><i class="fa fa-search"></i> Trouver des offres</a>
        </div>
    <?php else: ?>
        <?php while ($h = mysqli_fetch_assoc($historique)):
            $duree = ceil((strtotime($h['date_fin']) - strtotime($h['date_debut'])) / 86400) + 1;
            $gainEstime = $duree * $h['prix_journee'];
            $note = $h['note'];
        ?>
        <div class="history-card fade-in">
            <div class="history-top">
                <div>
                    <h3 style="font-family:'Playfair Display',serif;font-size:1.2rem;margin-bottom:0.3rem;">🌿 <?php echo htmlspecialchars($h['fruit']); ?> — <?php echo htmlspecialchars($h['gouv']); ?></h3>
                    <div style="font-size:0.85rem;color:var(--text-muted);"><i class="fa fa-user-tie"></i> <?php echo htmlspecialchars($h['ag_prenom'] . ' ' . $h['ag_nom']); ?></div>
                </div>
                <?php if ($h['remuneration'] !== null): ?>
                    <div class="gain-pill"><?php echo number_format($h['remuneration'], 0); ?> TND</div>
                <?php else: ?>
                    <div style="text-align:right;"><div style="font-size:0.78rem;color:var(--text-muted);">Gain estime</div><div style="font-family:'Playfair Display',serif;font-size:1.1rem;color:var(--olive-light);"><?php echo number_format($gainEstime, 0); ?> TND</div></div>
                <?php endif; ?>
            </div>
            <div class="history-meta">
                <div class="history-meta-item"><i class="fa fa-calendar-check"></i> <?php echo date('d/m/Y', strtotime($h['date_debut'])); ?> → <?php echo date('d/m/Y', strtotime($h['date_fin'])); ?></div>
                <div class="history-meta-item"><i class="fa fa-clock"></i> <?php echo $duree; ?> jour(s)</div>
                <div class="history-meta-item"><i class="fa fa-money-bill-wave"></i> <?php echo number_format($h['prix_journee'], 0); ?> TND/jour</div>
                <div class="history-meta-item"><i class="fa fa-map-pin"></i> <?php echo htmlspecialchars($h['adresse_offre']); ?></div>
            </div>
            <div class="eval-section">
                <h4><i class="fa fa-star"></i> Evaluation</h4>
                <?php if ($note !== null): ?>
                    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                        <div>
                            <div class="star-display"><?php for ($s = 1; $s <= 10; $s++) echo $s <= $note ? '<span>★</span>' : '<span class="empty-star">☆</span>'; ?></div>
                            <div style="font-size:0.9rem;margin-top:0.3rem;">Note: <strong><?php echo $note; ?>/10</strong></div>
                        </div>
                        <?php if ($h['commentaire']): ?>
                        <div style="flex:1;background:var(--cream);border-radius:var(--radius-sm);padding:0.75rem 1rem;border-left:3px solid var(--olive);font-size:0.9rem;font-style:italic;color:var(--text);">"<?php echo htmlspecialchars($h['commentaire']); ?>"</div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="no-eval"><i class="fa fa-hourglass-half"></i> Pas encore evalue.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
    <div style="height:3rem;"></div>
</div>

<?php include 'footer.php'; ?>
