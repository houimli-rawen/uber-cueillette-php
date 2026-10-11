<?php
require_once 'config.php';
require_once 'functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = '';
    $password = '';
    $type = '';

    if (isset($_POST['pseudo'])) $pseudo = sanitize($conn, $_POST['pseudo']);
    if (isset($_POST['password'])) $password = $_POST['password'];
    if (isset($_POST['user_type'])) $type = $_POST['user_type'];

    if ($pseudo !== '' && $password !== '' && ($type === 'agriculteur' || $type === 'ouvrier')) {
        $table = ($type === 'agriculteur') ? 'agriculteur' : 'ouvrier';
        $idCol = 'id_' . $type;

        $sql = "SELECT " . $idCol . ", nom, prenom, password FROM " . $table . " WHERE pseudo = '" . $pseudo . "'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row[$idCol];
                $_SESSION['user_type'] = $type;
                $_SESSION['user_nom'] = $row['prenom'] . ' ' . $row['nom'];

                if ($type === 'agriculteur') {
                    header('Location: dashboard.php');
                } else {
                    header('Location: dashboardO.php');
                }
                exit();
            }
        }
        $error = 'Pseudo ou mot de passe incorrect.';
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}

$pageTitle = 'Connexion — AgriLink';
$activePage = 'login';

include 'header.php';
?>

<div class="auth-container">
    <div class="auth-box fade-in">
        <div class="auth-header">
            <div style="font-size:2.5rem;margin-bottom:0.5rem;">🌿</div>
            <h2>Connexion</h2>
            <p style="opacity:0.8;margin-top:0.25rem;font-size:0.9rem;">Bienvenue sur AgriLink</p>
        </div>
        <div class="auth-body">
            <div id="alert-container">
                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><i class="fa fa-times-circle"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
            </div>
            <form id="form-login" method="POST">
                <div class="form-group">
                    <label>Type de compte</label>
                    <select name="user_type" id="user_type" class="form-control" required>
                        <option value="">-- Choisir --</option>
                        <option value="agriculteur" <?php if (isset($_POST['user_type']) && $_POST['user_type'] === 'agriculteur') echo 'selected'; ?>>🌾 Agriculteur</option>
                        <option value="ouvrier" <?php if (isset($_POST['user_type']) && $_POST['user_type'] === 'ouvrier') echo 'selected'; ?>>👷 Ouvrier agricole</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pseudo"><i class="fa fa-user"></i> Pseudo</label>
                    <input type="text" id="pseudo" name="pseudo" class="form-control" placeholder="Votre pseudo" value="<?php if (isset($_POST['pseudo'])) echo htmlspecialchars($_POST['pseudo']); ?>">
                    <div class="form-error" id="pseudo-error"></div>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fa fa-lock"></i> Mot de passe</label>
                    <div style="position:relative;">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Votre mot de passe" style="padding-right:3rem;">
                        <button type="button" id="toggle-password-btn" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);">
                            <i class="fa fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                    <div class="form-error" id="password-error"></div>
                </div>
                <button type="submit" class="btn btn-primary w-full btn-lg mt-2">
                    <i class="fa fa-sign-in-alt"></i> Se connecter
                </button>
            </form>
        </div>
        <div class="auth-footer">
            Pas encore inscrit ? <a href="register_agriculteur.php">Agriculteur</a> ou <a href="register_ouvrier.php">Ouvrier</a>
            <br><a href="index.php" style="color:var(--text-muted);font-size:0.85rem;"><i class="fa fa-arrow-left"></i> Retour a l'accueil</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
