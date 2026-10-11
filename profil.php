<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin('agriculteur');

$id = $_SESSION['user_id'];
$msg = '';
$error = '';

$agri = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM agriculteur WHERE id_agriculteur=" . $id));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = isset($_POST['nom']) ? sanitize($conn, $_POST['nom']) : '';
    $prenom = isset($_POST['prenom']) ? sanitize($conn, $_POST['prenom']) : '';
    $email = isset($_POST['email']) ? sanitize($conn, $_POST['email']) : '';
    $adresse = isset($_POST['adresse']) ? sanitize($conn, $_POST['adresse']) : '';
    $newpass = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($newpass !== '') {
        if (!preg_match('/^[a-zA-Z0-9]{7,}[$#]$/', $newpass)) {
            $error = 'Mot de passe invalide.';
        } else if ($newpass !== $confirm) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            $hash = password_hash($newpass, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE agriculteur SET nom='" . $nom . "', prenom='" . $prenom . "', email='" . $email . "', adresse='" . $adresse . "', password='" . $hash . "' WHERE id_agriculteur=" . $id);
            $msg = 'Profil mis a jour avec succes !';
        }
    } else {
        mysqli_query($conn, "UPDATE agriculteur SET nom='" . $nom . "', prenom='" . $prenom . "', email='" . $email . "', adresse='" . $adresse . "' WHERE id_agriculteur=" . $id);
        $msg = 'Profil mis a jour avec succes !';
    }
    if ($error === '') $agri = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM agriculteur WHERE id_agriculteur=" . $id));
}

$pageTitle = 'Mon Profil — AgriLink';
$activePage = 'profil';

include 'header.php';
include 'navbar.php';
?>

<style>
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
@media(max-width:600px){ .form-row { grid-template-columns:1fr; } }
</style>

<div class="page-header">
    <div class="container">
        <h1><i class="fa fa-user-circle"></i> Mon Profil</h1>
    </div>
</div>

<div class="container" style="max-width:700px;">
    <?php if ($msg !== ''): ?><div class="alert alert-success"><i class="fa fa-check"></i> <?php echo $msg; ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-danger"><i class="fa fa-times"></i> <?php echo $error; ?></div><?php endif; ?>

    <div class="profile-card fade-in">
        <div class="profile-header">
            <div class="profile-avatar-placeholder">🌾</div>
            <div class="profile-name"><?php echo htmlspecialchars($agri['prenom'] . ' ' . $agri['nom']); ?></div>
            <div style="opacity:0.8;font-size:0.9rem;">@<?php echo htmlspecialchars($agri['pseudo']); ?></div>
        </div>
        <div class="profile-body">
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($agri['nom']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Prenom</label>
                        <input type="text" name="prenom" class="form-control" value="<?php echo htmlspecialchars($agri['prenom']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>CIN</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($agri['CIN']); ?>" disabled style="background:var(--white);color:var(--text-muted);">
                    <small class="text-muted">Le CIN ne peut pas etre modifie.</small>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($agri['email']); ?>">
                </div>
                <div class="form-group">
                    <label>Adresse</label>
                    <input type="text" name="adresse" class="form-control" value="<?php echo htmlspecialchars($agri['adresse']); ?>">
                </div>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid var(--cream-dark);">
                <h4 style="font-family:'Playfair Display',serif;margin-bottom:1rem;color:var(--text-muted);">Changer le mot de passe (optionnel)</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Laisser vide pour ne pas changer">
                        <div class="form-error" id="new_password-error"></div>
                    </div>
                    <div class="form-group">
                        <label>Confirmer</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirmer">
                        <div class="form-error" id="confirm_password-error"></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-full"><i class="fa fa-save"></i> Enregistrer</button>
            </form>
        </div>
    </div>
    <div style="height:3rem;"></div>
</div>

<?php include 'footer.php'; ?>
