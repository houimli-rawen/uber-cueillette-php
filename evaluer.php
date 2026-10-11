<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin('agriculteur');

$agId = $_SESSION['user_id'];
$offreId = (int)($_GET['id'] ?? 0);

$result = mysqli_query($conn, "SELECT o.*, tf.libelle as fruit FROM offre o JOIN type_fruit tf ON o.id_type_fruit=tf.id_type_fruit WHERE o.id_offre=" . $offreId . " AND o.id_agriculteur=" . $agId);
$offre = mysqli_fetch_assoc($result);
if (!$offre) { header('Location: mes_offres.php'); exit(); }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['evaluations'])) {
        $evals = $_POST['evaluations'];
        $keys = array_keys($evals);
        for ($i = 0; $i < count($keys); $i++) {
            $cid = (int)$keys[$i];
            $eval = $evals[$keys[$i]];
            $note = max(0, min(10, (int)($eval['note'] ?? 0)));
            $com = isset($eval['commentaire']) ? sanitize($conn, $eval['commentaire']) : '';
            $rem = isset($eval['remuneration']) ? (float)$eval['remuneration'] : 0;
            mysqli_query($conn, "UPDATE candidature SET note=" . $note . ", commentaire='" . $com . "', remuneration=" . $rem . " WHERE id_candidature=" . $cid . " AND id_offre=" . $offreId);
        }
        $msg = 'Evaluations enregistrees avec succes !';
    }
}

$ouvriers = mysqli_query($conn, "SELECT c.*, ou.nom, ou.prenom, ou.photo FROM candidature c JOIN ouvrier ou ON c.id_ouvrier=ou.id_ouvrier WHERE c.id_offre=" . $offreId . " AND c.decision='accepte' ORDER BY ou.nom");

$pageTitle = 'Evaluer — AgriLink';
$activePage = '';
include 'header.php';
include 'navbar.php';
?>

<style>
.eval-row { background:var(--cream); border-radius:var(--radius); padding:1.5rem; box-shadow:var(--shadow); margin-bottom:1rem; border: 1px solid var(--cream-dark); }
.eval-header { display:flex; align-items:center; gap:1rem; margin-bottom:1.25rem; }
.eval-avatar { width:56px;height:56px;border-radius:50%;object-fit:cover;border:3px solid var(--olive-pale); }
.eval-ph { width:56px;height:56px;border-radius:50%;background:var(--olive-pale);display:flex;align-items:center;justify-content:center;font-size:1.4rem; }
.eval-grid { display:grid; grid-template-columns:auto 1fr 1fr; gap:1rem; align-items:start; }
@media(max-width:600px){.eval-grid{grid-template-columns:1fr;}}
</style>

<div class="page-header">
    <div class="container">
        <a href="mes_offres.php" style="color:rgba(232,232,232,0.7);text-decoration:none;font-size:0.9rem;">← Mes offres</a>
        <h1 class="mt-1"><i class="fa fa-star"></i> Evaluer les ouvriers</h1>
        <p>Offre: <?php echo htmlspecialchars($offre['fruit']); ?> — <?php echo date('d/m/Y', strtotime($offre['date_debut'])); ?> → <?php echo date('d/m/Y', strtotime($offre['date_fin'])); ?></p>
    </div>
</div>

<div class="container" style="max-width:900px;">
    <?php if ($msg !== ''): ?>
        <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $msg; ?></div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($ouvriers) === 0): ?>
        <div class="card text-center" style="padding:3rem;">
            <p class="text-muted">Aucun ouvrier accepte pour cette offre.</p>
        </div>
    <?php else: ?>
        <form method="POST">
        <?php while ($o = mysqli_fetch_assoc($ouvriers)): ?>
            <div class="eval-row fade-in">
                <div class="eval-header">
                    <?php if ($o['photo']): ?>
                        <img src="photo_ouvrier.php?id=<?php echo $o['id_ouvrier']; ?>" class="eval-avatar" alt="">
                    <?php else: ?>
                        <div class="eval-ph">👤</div>
                    <?php endif; ?>
                    <div>
                        <h3 style="font-family:'Playfair Display',serif;"><?php echo htmlspecialchars($o['prenom'] . ' ' . $o['nom']); ?></h3>
                        <?php if ($o['note'] !== null): ?>
                            <span class="badge badge-olive">Deja evalue: <?php echo $o['note']; ?>/10</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="eval-grid">
                    <div class="form-group">
                        <label>Note (0-10) *</label>
                        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:0.5rem;">
                            <input type="number" name="evaluations[<?php echo $o['id_candidature']; ?>][note]" class="form-control" min="0" max="10" value="<?php echo $o['note'] ?? ''; ?>" style="width:80px;" placeholder="0-10" data-stars="<?php echo $o['id_candidature']; ?>">
                            <div id="stars-<?php echo $o['id_candidature']; ?>" style="font-size:1.4rem;color:#f39c12;">
                                <?php $n = $o['note'] ?? 0; for ($s = 1; $s <= 10; $s++) echo $s <= $n ? '★' : '☆'; ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Commentaire</label>
                        <input type="text" name="evaluations[<?php echo $o['id_candidature']; ?>][commentaire]" class="form-control" placeholder="Qualite du travail…" value="<?php echo htmlspecialchars($o['commentaire'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Remuneration (TND)</label>
                        <input type="number" name="evaluations[<?php echo $o['id_candidature']; ?>][remuneration]" class="form-control" min="0" step="0.5" value="<?php echo $o['remuneration'] ?? ''; ?>" placeholder="Montant paye">
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        <div class="flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-save"></i> Enregistrer</button>
            <a href="mes_offres.php" class="btn btn-outline btn-lg">Retour</a>
        </div>
        </form>
    <?php endif; ?>
    <div style="height:3rem;"></div>
</div>

<?php include 'footer.php'; ?>
