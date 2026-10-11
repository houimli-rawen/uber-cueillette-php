<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin('ouvrier');

$id = $_SESSION['user_id'];

$filterFruit = 0;
$filterGouv = 0;
$sortPrix = '';

if (isset($_GET['fruit'])) $filterFruit = (int)$_GET['fruit'];
if (isset($_GET['gouv'])) $filterGouv = (int)$_GET['gouv'];
if (isset($_GET['sort'])) $sortPrix = sanitize($conn, $_GET['sort']);

$where = "WHERE o.date_limite >= CURDATE()";
if ($filterFruit) $where .= " AND o.id_type_fruit=" . $filterFruit;
if ($filterGouv) $where .= " AND o.id_gouvernorat=" . $filterGouv;
$where .= " AND o.id_offre NOT IN (SELECT id_offre FROM candidature WHERE id_ouvrier=" . $id . ") AND (SELECT COUNT(*) FROM candidature c2 WHERE c2.id_offre=o.id_offre AND c2.decision='accepte') < o.nombre_ouvriers";

$orderBy = "ORDER BY o.date_limite ASC";
if ($sortPrix === 'asc') $orderBy = "ORDER BY o.prix_journee ASC";
if ($sortPrix === 'desc') $orderBy = "ORDER BY o.prix_journee DESC";

$sqlOffres = "SELECT o.*, tf.libelle as fruit, g.libelle as gouv, a.nom as ag_nom, a.prenom as ag_prenom, COUNT(c.id_candidature) as nb_candidats, SUM(CASE WHEN c.decision='accepte' THEN 1 ELSE 0 END) as nb_acceptes FROM offre o JOIN type_fruit tf ON o.id_type_fruit=tf.id_type_fruit JOIN gouvernorat g ON o.id_gouvernorat=g.id_gouvernorat JOIN agriculteur a ON o.id_agriculteur=a.id_agriculteur LEFT JOIN candidature c ON o.id_offre=c.id_offre " . $where . " GROUP BY o.id_offre " . $orderBy;
$offres = mysqli_query($conn, $sqlOffres);

$postMsg = '';
if (isset($_GET['postuler'])) {
    $oid = (int)$_GET['postuler'];
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_offre FROM offre WHERE id_offre=" . $oid . " AND date_limite >= CURDATE()"));
    $already = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_candidature FROM candidature WHERE id_offre=" . $oid . " AND id_ouvrier=" . $id));
    if ($check && !$already) {
        mysqli_query($conn, "INSERT INTO candidature (id_offre, id_ouvrier, decision) VALUES(" . $oid . ", " . $id . ", 'encours')");
        $postMsg = 'Candidature envoyee avec succes !';
    } else if ($already) {
        $postMsg = 'Vous avez deja postule a cette offre.';
    }
    $offres = mysqli_query($conn, $sqlOffres);
}

$fruits = mysqli_query($conn, "SELECT * FROM type_fruit ORDER BY libelle");
$gouvernorats = mysqli_query($conn, "SELECT * FROM gouvernorat ORDER BY libelle");

$pageTitle = 'Offres de Recolte — AgriLink';
$activePage = 'offres';

include 'header.php';
include 'navbar.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fa fa-seedling"></i> Offres de Recolte Disponibles</h1>
        <p>Parcourez les offres actives et postulez en un clic</p>
    </div>
</div>

<div class="container">
    <?php if ($postMsg !== ''): ?>
        <div class="alert alert-success fade-in"><i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($postMsg); ?></div>
    <?php endif; ?>

    <form method="GET" class="filter-bar fade-in">
        <div class="filter-group">
            <label><i class="fa fa-apple-alt"></i> Type de fruit</label>
            <select name="fruit">
                <option value="">Tous les fruits</option>
                <?php while ($f = mysqli_fetch_assoc($fruits)): ?>
                    <option value="<?php echo $f['id_type_fruit']; ?>" <?php if ($filterFruit == $f['id_type_fruit']) echo 'selected'; ?>><?php echo htmlspecialchars($f['libelle']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fa fa-map-marker-alt"></i> Gouvernorat</label>
            <select name="gouv">
                <option value="">Tous les gouvernorats</option>
                <?php while ($g = mysqli_fetch_assoc($gouvernorats)): ?>
                    <option value="<?php echo $g['id_gouvernorat']; ?>" <?php if ($filterGouv == $g['id_gouvernorat']) echo 'selected'; ?>><?php echo htmlspecialchars($g['libelle']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fa fa-sort"></i> Trier par prix</label>
            <select name="sort">
                <option value="">Par defaut</option>
                <option value="desc" <?php if ($sortPrix === 'desc') echo 'selected'; ?>>Prix decroissant</option>
                <option value="asc" <?php if ($sortPrix === 'asc') echo 'selected'; ?>>Prix croissant</option>
            </select>
        </div>
        <div style="display:flex;gap:0.5rem;align-items:flex-end;">
            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filtrer</button>
            <a href="offres.php" class="btn btn-outline">Reinitialiser</a>
        </div>
    </form>

    <div style="color:var(--text-muted);font-size:0.9rem;margin-bottom:1rem;">
        <i class="fa fa-list"></i> <strong><?php echo mysqli_num_rows($offres); ?></strong> offre(s) disponible(s)
    </div>

    <?php if (mysqli_num_rows($offres) === 0): ?>
        <div class="card text-center" style="padding:3rem;">
            <div style="font-size:4rem;margin-bottom:1rem;">🔍</div>
            <h3 style="font-family:'Playfair Display',serif;margin-bottom:0.5rem;">Aucune offre disponible</h3>
            <p class="text-muted">Essayez de modifier vos filtres ou revenez plus tard.</p>
            <a href="offres.php" class="btn btn-outline mt-3">Voir toutes les offres</a>
        </div>
    <?php else: ?>
        <div class="grid-2">
        <?php while ($o = mysqli_fetch_assoc($offres)):
            $duree = ceil((strtotime($o['date_fin']) - strtotime($o['date_debut'])) / 86400) + 1;
            $totalGain = $duree * $o['prix_journee'];
            $places = $o['nombre_ouvriers'] - ($o['nb_acceptes'] ?? 0);
        ?>
            <div class="offer-card fade-in">
                <div class="offer-header">
                    <span class="fruit-badge">🌿 <?php echo htmlspecialchars($o['fruit']); ?></span>
                    <span class="price-badge"><?php echo number_format($o['prix_journee'], 0); ?> TND/j</span>
                </div>
                <div class="offer-body">
                    <div class="offer-meta">
                        <div class="meta-item"><i class="fa fa-map-marker-alt"></i> <?php echo htmlspecialchars($o['gouv']); ?></div>
                        <div class="meta-item"><i class="fa fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($o['date_debut'])); ?></div>
                        <div class="meta-item"><i class="fa fa-clock"></i> <?php echo $duree; ?> jour(s)</div>
                        <div class="meta-item"><i class="fa fa-users"></i> <?php echo $places; ?> place(s)</div>
                    </div>
                    <p style="font-size:0.88rem;color:var(--text-muted);margin:0.5rem 0;">
                        <i class="fa fa-map-pin"></i> <?php echo htmlspecialchars($o['adresse']); ?>
                    </p>
                    <div style="background:var(--white);border-radius:var(--radius-sm);padding:0.6rem 0.9rem;margin-top:0.75rem;display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:0.85rem;color:var(--text-muted);">Gain estime:</span>
                        <span style="font-family:'Playfair Display',serif;font-size:1.2rem;font-weight:700;color:var(--olive-light);"><?php echo number_format($totalGain, 0); ?> TND</span>
                    </div>
                    <div style="margin-top:0.6rem;font-size:0.82rem;color:var(--text-muted);">
                        <i class="fa fa-user-tie"></i> <?php echo htmlspecialchars($o['ag_prenom'] . ' ' . $o['ag_nom']); ?>
                        &nbsp;|&nbsp; <i class="fa fa-hourglass-end"></i> Limite: <?php echo date('d/m/Y', strtotime($o['date_limite'])); ?>
                    </div>
                </div>
                <div class="offer-footer">
                    <a href="offres.php?postuler=<?php echo $o['id_offre']; ?><?php if ($filterFruit) echo '&fruit=' . $filterFruit; ?><?php if ($filterGouv) echo '&gouv=' . $filterGouv; ?><?php if ($sortPrix) echo '&sort=' . $sortPrix; ?>"
                       class="btn btn-primary btn-sm btn-postuler"
                       data-confirm="Postuler a cette offre de <?php echo htmlspecialchars(addslashes($o['fruit'])); ?> a <?php echo htmlspecialchars(addslashes($o['gouv'])); ?> ?">
                        <i class="fa fa-paper-plane"></i> Postuler
                    </a>
                    <span style="font-size:0.8rem;color:var(--text-muted);"><?php echo $o['nb_candidats']; ?> candidat(s)</span>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    <?php endif; ?>
    <div style="height:3rem;"></div>
</div>

<?php include 'footer.php'; ?>
