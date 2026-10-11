<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin('agriculteur');

$id = $_SESSION['user_id'];

$sqlTotal = "SELECT COUNT(*) as c FROM offre WHERE id_agriculteur=" . $id;
$resultTotal = mysqli_query($conn, $sqlTotal);
$rowTotal = mysqli_fetch_assoc($resultTotal);
$totalOffres = $rowTotal['c'];

$sqlActives = "SELECT COUNT(*) as c FROM offre WHERE id_agriculteur=" . $id . " AND date_limite >= CURDATE()";
$resultActives = mysqli_query($conn, $sqlActives);
$rowActives = mysqli_fetch_assoc($resultActives);
$offresActives = $rowActives['c'];

$sqlCands = "SELECT COUNT(*) as c FROM candidature c JOIN offre o ON c.id_offre=o.id_offre WHERE o.id_agriculteur=" . $id;
$resultCands = mysqli_query($conn, $sqlCands);
$rowCands = mysqli_fetch_assoc($resultCands);
$totalCandidatures = $rowCands['c'];

$sqlAccept = "SELECT COUNT(*) as c FROM candidature c JOIN offre o ON c.id_offre=o.id_offre WHERE o.id_agriculteur=" . $id . " AND c.decision='accepte'";
$resultAccept = mysqli_query($conn, $sqlAccept);
$rowAccept = mysqli_fetch_assoc($resultAccept);
$acceptes = $rowAccept['c'];

$sqlOffres = "SELECT o.*, tf.libelle as fruit, g.libelle as gouv, COUNT(c.id_candidature) as nb_candidats, SUM(CASE WHEN c.decision='accepte' THEN 1 ELSE 0 END) as nb_acceptes FROM offre o JOIN type_fruit tf ON o.id_type_fruit=tf.id_type_fruit JOIN gouvernorat g ON o.id_gouvernorat=g.id_gouvernorat LEFT JOIN candidature c ON o.id_offre=c.id_offre WHERE o.id_agriculteur=" . $id . " GROUP BY o.id_offre ORDER BY o.id_offre DESC LIMIT 5";
$offres = mysqli_query($conn, $sqlOffres);

$pageTitle = 'Tableau de bord — AgriLink';
$activePage = 'dashboard';

include 'header.php';
include 'navbar.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Bonjour, <?php echo htmlspecialchars($_SESSION['user_nom']); ?> 🌾</h1>
        <p>Gerez vos offres de recolte et suivez vos chantiers</p>
    </div>
</div>

<div class="container">
    <div class="grid-3 mb-3">
        <div class="stat-card fade-in fade-in-1">
            <div class="stat-number"><?php echo $totalOffres; ?></div>
            <div class="stat-label">Total Offres publiees</div>
        </div>
        <div class="stat-card fade-in fade-in-2">
            <div class="stat-number"><?php echo $offresActives; ?></div>
            <div class="stat-label">Offres actives</div>
        </div>
        <div class="stat-card fade-in fade-in-3">
            <div class="stat-number"><?php echo $acceptes; ?></div>
            <div class="stat-label">Ouvriers acceptes</div>
        </div>
    </div>

    <div class="card mb-3 fade-in">
        <div class="card-header">⚡ Actions rapides</div>
        <div class="card-body flex gap-2" style="flex-wrap:wrap;">
            <a href="ajouter_offre.php" class="btn btn-primary"><i class="fa fa-plus"></i> Nouvelle offre</a>
            <a href="mes_offres.php" class="btn btn-outline"><i class="fa fa-list"></i> Mes offres</a>
            <a href="profil.php" class="btn btn-outline"><i class="fa fa-user-edit"></i> Mon profil</a>
        </div>
    </div>

    <div class="card fade-in">
        <div class="card-header">📋 Mes offres recentes</div>
        <div class="card-body" style="padding:0;">
            <?php if (mysqli_num_rows($offres) === 0): ?>
                <div style="padding:2rem;text-align:center;color:var(--text-muted);">
                    <div style="font-size:3rem;margin-bottom:1rem;">📭</div>
                    <p>Aucune offre publiee pour l'instant.</p>
                    <a href="ajouter_offre.php" class="btn btn-primary mt-2">Publier ma premiere offre</a>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Fruit</th><th>Gouvernorat</th><th>Periode</th>
                                <th>Prix/jour</th><th>Postulants</th><th>Statut</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($o = mysqli_fetch_assoc($offres)):
                            $expired = strtotime($o['date_limite']) < time();
                            $full = $o['nb_acceptes'] >= $o['nombre_ouvriers'];
                            $status = ($expired || $full) ? 'Cloturee' : 'Active';
                            $statusClass = ($expired || $full) ? 'badge-danger' : 'badge-success';
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($o['fruit']); ?></td>
                                <td><?php echo htmlspecialchars($o['gouv']); ?></td>
                                <td><?php echo date('d/m/y', strtotime($o['date_debut'])); ?> → <?php echo date('d/m/y', strtotime($o['date_fin'])); ?></td>
                                <td><strong><?php echo number_format($o['prix_journee'], 0); ?> TND</strong></td>
                                <td><?php echo $o['nb_candidats']; ?> / <?php echo $o['nombre_ouvriers']; ?></td>
                                <td><span class="badge <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                                <td>
                                    <a href="postulants.php?id=<?php echo $o['id_offre']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fa fa-users"></i> Postulants
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div style="padding:1rem 1.5rem;">
                    <a href="mes_offres.php" class="btn btn-outline btn-sm">Voir toutes mes offres →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div style="height:3rem;"></div>
</div>

<?php include 'footer.php'; ?>
