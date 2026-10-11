<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin('agriculteur');

$id = $_SESSION['user_id'];
$deleteMsg = '';
$deleteError = '';

if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $check = mysqli_query($conn, "SELECT COUNT(*) as c FROM candidature WHERE id_offre=" . $did);
    $rowCheck = mysqli_fetch_assoc($check);
    if ($rowCheck['c'] == 0) {
        mysqli_query($conn, "DELETE FROM offre WHERE id_offre=" . $did . " AND id_agriculteur=" . $id);
        $deleteMsg = 'Offre supprimee.';
    } else {
        $deleteError = 'Impossible de supprimer: des postulants existent.';
    }
}

$sql = "SELECT o.*, tf.libelle as fruit, g.libelle as gouv, COUNT(c.id_candidature) as nb_candidats, SUM(CASE WHEN c.decision='accepte' THEN 1 ELSE 0 END) as nb_acceptes FROM offre o JOIN type_fruit tf ON o.id_type_fruit=tf.id_type_fruit JOIN gouvernorat g ON o.id_gouvernorat=g.id_gouvernorat LEFT JOIN candidature c ON o.id_offre=c.id_offre WHERE o.id_agriculteur=" . $id . " GROUP BY o.id_offre ORDER BY o.id_offre DESC";
$offres = mysqli_query($conn, $sql);

$pageTitle = 'Mes Offres — AgriLink';
$activePage = 'mes_offres';
include 'header.php';
include 'navbar.php';
?>

<div class="page-header">
    <div class="container">
        <div class="flex flex-between items-center">
            <div>
                <h1><i class="fa fa-list"></i> Mes Offres</h1>
                <p>Gerez vos offres et candidatures</p>
            </div>
            <a href="ajouter_offre.php" class="btn btn-white"><i class="fa fa-plus"></i> Nouvelle offre</a>
        </div>
    </div>
</div>

<div class="container">
    <?php if ($deleteMsg !== ''): ?>
        <div class="alert alert-success"><i class="fa fa-check"></i> <?php echo $deleteMsg; ?></div>
    <?php endif; ?>
    <?php if ($deleteError !== ''): ?>
        <div class="alert alert-danger"><i class="fa fa-times"></i> <?php echo $deleteError; ?></div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($offres) === 0): ?>
        <div class="card text-center" style="padding:3rem;">
            <div style="font-size:4rem;margin-bottom:1rem;">📭</div>
            <h3 style="font-family:'Playfair Display',serif;margin-bottom:0.5rem;">Aucune offre</h3>
            <p class="text-muted">Commencez par publier votre premiere offre.</p>
            <a href="ajouter_offre.php" class="btn btn-primary mt-3">Publier une offre</a>
        </div>
    <?php else: ?>
        <div class="grid-2">
        <?php while ($o = mysqli_fetch_assoc($offres)):
            $expired = strtotime($o['date_limite']) < time();
            $full = $o['nb_acceptes'] >= $o['nombre_ouvriers'];
            $closed = $expired || $full;
            $duree = ceil((strtotime($o['date_fin']) - strtotime($o['date_debut'])) / 86400) + 1;
        ?>
            <div class="offer-card fade-in">
                <div class="offer-header">
                    <span class="fruit-badge">🌿 <?php echo htmlspecialchars($o['fruit']); ?></span>
                    <span class="price-badge"><?php echo number_format($o['prix_journee'], 0); ?> TND/j</span>
                </div>
                <div class="offer-body">
                    <div class="offer-meta">
                        <div class="meta-item"><i class="fa fa-map-marker-alt"></i> <?php echo htmlspecialchars($o['gouv']); ?></div>
                        <div class="meta-item"><i class="fa fa-calendar"></i> <?php echo date('d/m/Y', strtotime($o['date_debut'])); ?></div>
                        <div class="meta-item"><i class="fa fa-clock"></i> <?php echo $duree; ?> jours</div>
                        <div class="meta-item"><i class="fa fa-users"></i> <?php echo $o['nb_acceptes']; ?>/<?php echo $o['nombre_ouvriers']; ?> acceptes</div>
                    </div>
                    <p style="font-size:0.88rem;color:var(--text-muted);margin-top:0.5rem;"><i class="fa fa-map-pin"></i> <?php echo htmlspecialchars($o['adresse']); ?></p>
                    <div class="flex items-center gap-1 mt-2">
                        <span class="badge <?php echo $closed ? 'badge-danger' : 'badge-success'; ?>"><?php echo $closed ? 'Cloturee' : 'Active'; ?></span>
                        <span class="badge badge-olive"><?php echo $o['nb_candidats']; ?> postulant(s)</span>
                        <span style="font-size:0.82rem;color:var(--text-muted);">Limite: <?php echo date('d/m/Y', strtotime($o['date_limite'])); ?></span>
                    </div>
                </div>
                <div class="offer-footer">
                    <a href="postulants.php?id=<?php echo $o['id_offre']; ?>" class="btn btn-sm btn-primary"><i class="fa fa-users"></i> Postulants (<?php echo $o['nb_candidats']; ?>)</a>
                    <?php if ($o['nb_candidats'] == 0): ?>
                        <button class="btn btn-sm btn-danger btn-delete-offre" data-url="mes_offres.php?delete=<?php echo $o['id_offre']; ?>"><i class="fa fa-trash"></i> Supprimer</button>
                    <?php endif; ?>
                    <?php if ($closed && $o['nb_acceptes'] > 0): ?>
                        <a href="evaluer.php?id=<?php echo $o['id_offre']; ?>" class="btn btn-sm btn-secondary"><i class="fa fa-star"></i> Evaluer</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    <?php endif; ?>
    <div style="height:3rem;"></div>
</div>

<?php include 'footer.php'; ?>
