<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin('ouvrier');

$id = $_SESSION['user_id'];

$sqlTotal = "SELECT COUNT(*) as c FROM candidature WHERE id_ouvrier=" . $id;
$resultTotal = mysqli_query($conn, $sqlTotal);
$rowTotal = mysqli_fetch_assoc($resultTotal);
$totalCandidatures = $rowTotal['c'];

$sqlAccept = "SELECT COUNT(*) as c FROM candidature WHERE id_ouvrier=" . $id . " AND decision='accepte'";
$resultAccept = mysqli_query($conn, $sqlAccept);
$rowAccept = mysqli_fetch_assoc($resultAccept);
$acceptes = $rowAccept['c'];

$sqlEncours = "SELECT COUNT(*) as c FROM candidature WHERE id_ouvrier=" . $id . " AND decision='encours'";
$resultEncours = mysqli_query($conn, $sqlEncours);
$rowEncours = mysqli_fetch_assoc($resultEncours);
$encours = $rowEncours['c'];

$sqlGains = "SELECT COALESCE(SUM(remuneration),0) as g FROM candidature WHERE id_ouvrier=" . $id;
$resultGains = mysqli_query($conn, $sqlGains);
$rowGains = mysqli_fetch_assoc($resultGains);
$totalGains = $rowGains['g'];

$sqlCands = "SELECT c.*, o.date_debut, o.date_fin, o.prix_journee, tf.libelle as fruit, g.libelle as gouv FROM candidature c JOIN offre o ON c.id_offre=o.id_offre JOIN type_fruit tf ON o.id_type_fruit=tf.id_type_fruit JOIN gouvernorat g ON o.id_gouvernorat=g.id_gouvernorat WHERE c.id_ouvrier=" . $id . " ORDER BY c.date_candidature DESC LIMIT 5";
$cands = mysqli_query($conn, $sqlCands);

$pageTitle = 'Tableau de bord — AgriLink';
$activePage = 'dashboard';

include 'header.php';
include 'navbar.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Bonjour, <?php echo htmlspecialchars($_SESSION['user_nom']); ?> 👷</h1>
        <p>Trouvez des opportunites de recolte et suivez vos chantiers</p>
    </div>
</div>

<div class="container">
    <div class="grid-3 mb-3" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">
        <div class="stat-card fade-in fade-in-1" style="border-color:var(--earth);">
            <div class="stat-number" style="color:var(--earth-light);"><?php echo $totalCandidatures; ?></div>
            <div class="stat-label">Candidatures deposees</div>
        </div>
        <div class="stat-card fade-in fade-in-2">
            <div class="stat-number"><?php echo $acceptes; ?></div>
            <div class="stat-label">Candidatures acceptees</div>
        </div>
        <div class="stat-card fade-in fade-in-3" style="border-color:var(--warning);">
            <div class="stat-number" style="color:var(--warning);"><?php echo number_format($totalGains, 0); ?></div>
            <div class="stat-label">Gains totaux (TND)</div>
        </div>
    </div>

    <div class="card mb-3 fade-in">
        <div class="card-header">⚡ Actions rapides</div>
        <div class="card-body flex gap-2" style="flex-wrap:wrap;">
            <a href="offres.php" class="btn btn-primary"><i class="fa fa-search"></i> Trouver des offres</a>
            <a href="mes_candidatures.php" class="btn btn-outline"><i class="fa fa-list"></i> Mes candidatures</a>
            <a href="historique.php" class="btn btn-outline"><i class="fa fa-history"></i> Historique</a>
        </div>
    </div>

    <div class="card fade-in">
        <div class="card-header">📋 Mes candidatures recentes</div>
        <div class="card-body" style="padding:0;">
            <?php if (mysqli_num_rows($cands) === 0): ?>
                <div style="padding:2rem;text-align:center;color:var(--text-muted);">
                    <div style="font-size:3rem;margin-bottom:1rem;">🌾</div>
                    <p>Vous n'avez pas encore postule a des offres.</p>
                    <a href="offres.php" class="btn btn-primary mt-2">Voir les offres disponibles</a>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Fruit</th><th>Gouvernorat</th><th>Periode</th><th>Prix/j</th><th>Statut</th></tr></thead>
                        <tbody>
                        <?php while ($c = mysqli_fetch_assoc($cands)):
                            $cls = $c['decision'] === 'accepte' ? 'badge-success' : ($c['decision'] === 'refuse' ? 'badge-danger' : 'badge-warning');
                            $lbl = $c['decision'] === 'accepte' ? 'Accepte' : ($c['decision'] === 'refuse' ? 'Refuse' : 'En attente');
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($c['fruit']); ?></td>
                                <td><?php echo htmlspecialchars($c['gouv']); ?></td>
                                <td><?php echo date('d/m/y', strtotime($c['date_debut'])); ?> → <?php echo date('d/m/y', strtotime($c['date_fin'])); ?></td>
                                <td><?php echo number_format($c['prix_journee'], 0); ?> TND</td>
                                <td><span class="badge <?php echo $cls; ?>"><?php echo $lbl; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div style="padding:1rem 1.5rem;">
                    <a href="mes_candidatures.php" class="btn btn-outline btn-sm">Voir toutes mes candidatures →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div style="height:3rem;"></div>
</div>

<?php include 'footer.php'; ?>
