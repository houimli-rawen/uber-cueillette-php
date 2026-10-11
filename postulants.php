<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin('agriculteur');

$agId = $_SESSION['user_id'];
$offreId = (int)($_GET['id'] ?? 0);

$offre = mysqli_query($conn, "SELECT o.*, tf.libelle as fruit, g.libelle as gouv FROM offre o JOIN type_fruit tf ON o.id_type_fruit=tf.id_type_fruit JOIN gouvernorat g ON o.id_gouvernorat=g.id_gouvernorat WHERE o.id_offre=" . $offreId . " AND o.id_agriculteur=" . $agId);
$offre = mysqli_fetch_assoc($offre);
if (!$offre) { header('Location: mes_offres.php'); exit(); }

$msg = '';
if (isset($_GET['action']) && isset($_GET['cid'])) {
    $action = ($_GET['action'] === 'accepte' || $_GET['action'] === 'refuse') ? $_GET['action'] : '';
    $cid = (int)$_GET['cid'];
    if ($action === 'accepte') {
        $current = mysqli_query($conn, "SELECT COUNT(*) as c FROM candidature WHERE id_offre=" . $offreId . " AND decision='accepte'");
        $rowCur = mysqli_fetch_assoc($current);
        if ($rowCur['c'] >= $offre['nombre_ouvriers']) {
            $msg = 'Nombre maximum d\'ouvriers atteint.';
        } else {
            mysqli_query($conn, "UPDATE candidature SET decision='accepte' WHERE id_candidature=" . $cid);
            $msg = 'Candidature acceptee.';
        }
    } else if ($action === 'refuse') {
        mysqli_query($conn, "UPDATE candidature SET decision='refuse' WHERE id_candidature=" . $cid);
        $msg = 'Candidature refusee.';
    }
}

$nbAcceptes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM candidature WHERE id_offre=" . $offreId . " AND decision='accepte'"))['c'];
$closed = $nbAcceptes >= $offre['nombre_ouvriers'] || strtotime($offre['date_limite']) < time();

$postulants = mysqli_query($conn, "SELECT c.*, ou.nom, ou.prenom, ou.photo, ou.description, ou.CIN, AVG(c2.note) as moyenne_notes, GROUP_CONCAT(c2.commentaire SEPARATOR ' | ') as anciens_commentaires FROM candidature c JOIN ouvrier ou ON c.id_ouvrier=ou.id_ouvrier LEFT JOIN candidature c2 ON c2.id_ouvrier=ou.id_ouvrier AND c2.note IS NOT NULL WHERE c.id_offre=" . $offreId . " GROUP BY c.id_candidature ORDER BY c.date_candidature DESC");

$pageTitle = 'Postulants';
$activePage = '';
include 'header.php';
include 'navbar.php';
?>

<style>
.postulant-card { background:var(--white); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; margin-bottom:1rem; }
.postulant-header { display:flex; gap:1rem; align-items:center; padding:1.25rem; border-bottom:1px solid var(--cream-dark); }
.postulant-avatar { width:64px;height:64px;border-radius:50%;object-fit:cover;border:3px solid var(--olive-pale);flex-shrink:0; }
.postulant-avatar-ph { width:64px;height:64px;border-radius:50%;background:var(--olive-pale);display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0; }
.stars-display { color:#f39c12; }
</style>

<div class="page-header">
    <div class="container">
        <a href="mes_offres.php" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:0.9rem;">← Mes offres</a>
        <h1 class="mt-1"><i class="fa fa-users"></i> Postulants: <?php echo htmlspecialchars($offre['fruit']); ?></h1>
        <p>📍 <?php echo htmlspecialchars($offre['gouv']); ?> — <?php echo date('d/m/Y', strtotime($offre['date_debut'])); ?> → <?php echo date('d/m/Y', strtotime($offre['date_fin'])); ?></p>
    </div>
</div>

<div class="container">
    <?php if ($msg !== ''): ?>
        <div class="alert alert-success"><i class="fa fa-info-circle"></i> <?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body flex gap-2 items-center" style="flex-wrap:wrap;">
            <span class="badge <?php echo $closed ? 'badge-danger' : 'badge-success'; ?>"><?php echo $closed ? 'Cloturee' : 'Active'; ?></span>
            <span>Acceptes: <strong><?php echo $nbAcceptes; ?></strong> / <?php echo $offre['nombre_ouvriers']; ?></span>
            <span>Total: <strong><?php echo mysqli_num_rows($postulants); ?></strong></span>
        </div>
    </div>

    <?php if (mysqli_num_rows($postulants) === 0): ?>
        <div class="card text-center" style="padding:3rem;">
            <div style="font-size:3rem;margin-bottom:1rem;">👥</div>
            <p class="text-muted">Aucun postulant.</p>
        </div>
    <?php else: ?>
        <?php while ($p = mysqli_fetch_assoc($postulants)):
            if ($p['decision'] === 'accepte') { $decisionClass = 'badge-success'; $decisionLabel = 'Accepte'; }
            else if ($p['decision'] === 'refuse') { $decisionClass = 'badge-danger'; $decisionLabel = 'Refuse'; }
            else { $decisionClass = 'badge-warning'; $decisionLabel = 'En cours'; }
            $moy = $p['moyenne_notes'] ? round($p['moyenne_notes'], 1) : null;
        ?>
            <div class="postulant-card fade-in">
                <div class="postulant-header">
                    <?php if ($p['photo']): ?>
                        <img src="photo_ouvrier.php?id=<?php echo $p['id_ouvrier']; ?>" class="postulant-avatar" alt="Photo">
                    <?php else: ?>
                        <div class="postulant-avatar-ph">👤</div>
                    <?php endif; ?>
                    <div style="flex:1;">
                        <div class="flex flex-between items-center" style="flex-wrap:wrap;gap:0.5rem;">
                            <h3 style="font-family:'Playfair Display',serif;"><?php echo htmlspecialchars($p['prenom'] . ' ' . $p['nom']); ?></h3>
                            <span class="badge <?php echo $decisionClass; ?>"><?php echo $decisionLabel; ?></span>
                        </div>
                        <div style="font-size:0.88rem;color:var(--text-muted);margin-top:0.25rem;">
                            CIN: <?php echo htmlspecialchars($p['CIN']); ?> &nbsp;|&nbsp; Postule: <?php echo date('d/m/Y H:i', strtotime($p['date_candidature'])); ?>
                        </div>
                        <?php if ($moy !== null): ?>
                            <div class="mt-1"><span class="stars-display"><?php for ($s = 1; $s <= 10; $s++) echo $s <= $moy ? '★' : '☆'; ?></span><small style="color:var(--text-muted);"> Moy: <?php echo $moy; ?>/10</small></div>
                        <?php else: ?>
                            <small style="color:var(--text-muted);">Aucune evaluation</small>
                        <?php endif; ?>
                        <?php if ($p['description']): ?>
                            <p style="font-size:0.88rem;margin-top:0.4rem;color:var(--text);"><?php echo htmlspecialchars($p['description']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($p['decision'] === 'encours' && !$closed): ?>
                <div style="padding:1rem 1.25rem;background:var(--cream);display:flex;gap:0.75rem;">
                    <a href="?id=<?php echo $offreId; ?>&action=accepte&cid=<?php echo $p['id_candidature']; ?>" class="btn btn-sm btn-success btn-accept-cand"><i class="fa fa-check"></i> Accepter</a>
                    <a href="?id=<?php echo $offreId; ?>&action=refuse&cid=<?php echo $p['id_candidature']; ?>" class="btn btn-sm btn-danger btn-refuse-cand"><i class="fa fa-times"></i> Refuser</a>
                </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <?php if ($closed && $nbAcceptes > 0): ?>
        <div class="alert alert-info mt-3"><i class="fa fa-star"></i> <a href="evaluer.php?id=<?php echo $offreId; ?>" class="btn btn-sm btn-secondary" style="margin-left:1rem;">Evaluer les ouvriers</a></div>
    <?php endif; ?>
    <div style="height:3rem;"></div>
</div>

<?php include 'footer.php'; ?>
