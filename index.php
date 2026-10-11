<?php
require_once 'config.php';
require_once 'functions.php';

$msg = '';
$successMsg = '';

if (isset($_GET['error'])) {
    $errorCode = $_GET['error'];
    if ($errorCode === 'login_required') {
        $msg = 'Veuillez vous connecter pour acceder a cette page.';
    } else if ($errorCode === 'unauthorized') {
        $msg = 'Acces non autorise.';
    } else if ($errorCode === 'logged_out') {
        $msg = 'Vous avez ete deconnecte avec succes.';
    }
}

if (isset($_GET['success']) && $_GET['success'] === 'registered') {
    $successMsg = 'Bienvenue sur AgriLink ! Connectez-vous pour commencer.';
}

$pageTitle = 'AgriLink — Connecter la terre et les talents';
$activePage = '';
include 'header.php';
include 'navbar.php';
?>

<style>
.features { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px,1fr)); gap: 1.5rem; padding: 3rem 0; }
.feature-card { background: var(--cream); border-radius: var(--radius); padding: 2rem; box-shadow: var(--shadow); text-align: center; transition: transform 0.2s; border: 1px solid var(--cream-dark); }
.feature-card:hover { transform: translateY(-4px); }
.feature-icon { width: 64px; height: 64px; background: linear-gradient(135deg, var(--olive), var(--olive-pale)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; font-size: 1.8rem; color: #fff; }
.feature-card h3 { font-family: 'Playfair Display', serif; font-size: 1.2rem; margin-bottom: 0.75rem; color: var(--olive-light); }
.feature-card p { color: var(--text-muted); font-size: 0.92rem; line-height: 1.6; }
.cta-section { background: linear-gradient(135deg, var(--cream) 0%, var(--cream-dark) 100%); padding: 4rem 0; text-align: center; color: var(--text); border-top: 1px solid var(--cream-dark); }
.cta-section h2 { font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 1rem; }
.cta-section p { opacity: 0.8; margin-bottom: 2rem; color: var(--text-muted); }
.cta-btns { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
.fruit-icons { font-size: 1.4rem; letter-spacing: 4px; opacity: 0.7; margin-top: 0.75rem; }
</style>

<section class="hero">
    <h1>La Recolte,<br>Reinventee 🌿</h1>
    <p>AgriLink connecte agriculteurs et ouvriers agricoles pour des recoltes reussies partout en Tunisie.</p>
    <div class="fruit-icons">🫒 🍊 🍅 📅 🌾</div>
    <?php if (!isLoggedIn()): ?>
    <div class="hero-btns mt-3">
        <a href="register_agriculteur.php" class="btn btn-white btn-lg"><i class="fa fa-tractor"></i> Je recrute</a>
        <a href="register_ouvrier.php" class="btn btn-outline btn-lg" style="color:var(--olive-light);border-color:var(--olive);"><i class="fa fa-hard-hat"></i> Je travaille</a>
        <a href="login.php" class="btn btn-lg" style="background:rgba(107,142,35,0.2);color:var(--olive-light);border-color:rgba(107,142,35,0.3);">Se connecter</a>
    </div>
    <?php else: ?>
    <div class="hero-btns mt-3">
        <?php if ($_SESSION['user_type'] === 'agriculteur'): ?>
            <a href="ajouter_offre.php" class="btn btn-white btn-lg"><i class="fa fa-plus"></i> Publier une offre</a>
            <a href="dashboard.php" class="btn btn-lg" style="background:rgba(107,142,35,0.2);color:var(--olive-light);border-color:rgba(107,142,35,0.3);">Mon tableau de bord</a>
        <?php else: ?>
            <a href="offres.php" class="btn btn-white btn-lg"><i class="fa fa-search"></i> Trouver un chantier</a>
            <a href="dashboardO.php" class="btn btn-lg" style="background:rgba(107,142,35,0.2);color:var(--olive-light);border-color:rgba(107,142,35,0.3);">Mon tableau de bord</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</section>

<div class="container">
    <?php if ($msg !== ''): ?>
        <div class="alert alert-warning mt-3"><i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <?php if ($successMsg !== ''): ?>
        <div class="alert alert-success mt-3"><i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($successMsg); ?></div>
    <?php endif; ?>

    <div class="features">
        <div class="feature-card fade-in fade-in-1">
            <div class="feature-icon">🌾</div>
            <h3>Publiez vos besoins</h3>
            <p>Les agriculteurs publient facilement leurs offres de recolte avec tous les details necessaires.</p>
        </div>
        <div class="feature-card fade-in fade-in-2">
            <div class="feature-icon">🔍</div>
            <h3>Trouvez un emploi</h3>
            <p>Les ouvriers parcourent et filtrent les offres par type de recolte, region et remuneration.</p>
        </div>
        <div class="feature-card fade-in fade-in-3">
            <div class="feature-icon">⭐</div>
            <h3>Evaluez & progressez</h3>
            <p>Systeme de notation transparent. Construisez votre reputation pour decrocher les meilleurs contrats.</p>
        </div>
    </div>
</div>

<section class="cta-section">
    <div class="container">
        <h2>Prêt a rejoindre l'aventure ?</h2>
        <p>Des centaines d'agriculteurs et d'ouvriers nous font deja confiance sur AgriLink</p>
        <?php if (!isLoggedIn()): ?>
        <div class="cta-btns">
            <a href="register_agriculteur.php" class="btn btn-primary btn-lg">Je suis agriculteur</a>
            <a href="register_ouvrier.php" class="btn btn-outline btn-lg" style="color:var(--olive-light);border:2px solid var(--olive);">Je suis ouvrier agricole</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>
