<nav class="navbar">
    <a href="index.php" class="navbar-brand">🌿 Uber-<span>Cueillette</span></a>
    <ul class="navbar-nav">
        <?php if (isLoggedIn()): ?>
            <?php if ($_SESSION['user_type'] === 'agriculteur'): ?>
                <li><a href="dashboard.php"<?php if(isset($activePage) && $activePage==='dashboard') echo ' class="active"'; ?>><i class="fa fa-home"></i> Tableau de bord</a></li>
                <li><a href="mes_offres.php"<?php if(isset($activePage) && $activePage==='mes_offres') echo ' class="active"'; ?>>Mes Offres</a></li>
                <li><a href="ajouter_offre.php"<?php if(isset($activePage) && $activePage==='ajouter_offre') echo ' class="active"'; ?>>+ Offre</a></li>
                <li><a href="profil.php"<?php if(isset($activePage) && $activePage==='profil') echo ' class="active"'; ?>>Mon Profil</a></li>
            <?php else: ?>
                <li><a href="dashboardO.php"<?php if(isset($activePage) && $activePage==='dashboard') echo ' class="active"'; ?>><i class="fa fa-home"></i> Tableau de bord</a></li>
                <li><a href="offres.php"<?php if(isset($activePage) && $activePage==='offres') echo ' class="active"'; ?>>Offres</a></li>
                <li><a href="mes_candidatures.php"<?php if(isset($activePage) && $activePage==='mes_candidatures') echo ' class="active"'; ?>>Mes Candidatures</a></li>
                <li><a href="profilO.php"<?php if(isset($activePage) && $activePage==='profil') echo ' class="active"'; ?>>Mon Profil</a></li>
            <?php endif; ?>
            <li><a href="logout.php" class="btn-logout"><i class="fa fa-sign-out-alt"></i> Déconnexion</a></li>
        <?php else: ?>
            <li><a href="login.php"<?php if(isset($activePage) && $activePage==='login') echo ' class="active"'; ?>>Connexion</a></li>
            <li><a href="register_agriculteur.php"<?php if(isset($activePage) && $activePage==='register_agriculteur') echo ' class="active"'; ?>>Je suis Agriculteur</a></li>
            <li><a href="register_ouvrier.php"<?php if(isset($activePage) && $activePage==='register_ouvrier') echo ' class="active"'; ?>>Je suis Ouvrier</a></li>
        <?php endif; ?>
    </ul>
</nav>
