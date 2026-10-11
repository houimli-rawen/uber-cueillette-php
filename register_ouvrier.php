<?php
require_once 'config.php';
require_once 'functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = '';
    $prenom = '';
    $cin = '';
    $email = '';
    $desc = '';
    $pseudo = '';
    $pass = '';
    $confirm = '';

    if (isset($_POST['nom'])) $nom = sanitize($conn, $_POST['nom']);
    if (isset($_POST['prenom'])) $prenom = sanitize($conn, $_POST['prenom']);
    if (isset($_POST['cin'])) $cin = sanitize($conn, $_POST['cin']);
    if (isset($_POST['email'])) $email = sanitize($conn, $_POST['email']);
    if (isset($_POST['description'])) $desc = sanitize($conn, $_POST['description']);
    if (isset($_POST['pseudo'])) $pseudo = sanitize($conn, $_POST['pseudo']);
    if (isset($_POST['password'])) $pass = $_POST['password'];
    if (isset($_POST['confirm_password'])) $confirm = $_POST['confirm_password'];

    if (!preg_match('/^\d{8}$/', $cin)) {
        $error = 'CIN invalide.';
    } else if (!preg_match('/^[a-zA-Z]+$/', $pseudo)) {
        $error = 'Le pseudo doit contenir uniquement des lettres.';
    } else if (!preg_match('/^[a-zA-Z0-9]{7,}[$#]$/', $pass)) {
        $error = 'Mot de passe invalide.';
    } else if ($pass !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else if (!isset($_FILES['photo']) || empty($_FILES['photo']['name'])) {
        $error = 'La photo est requise.';
    } else {
        $photoData = file_get_contents($_FILES['photo']['tmp_name']);
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $sql = "INSERT INTO ouvrier (nom, prenom, CIN, email, photo, description, pseudo, password) ";
        $sql .= "VALUES ('" . $nom . "', '" . $prenom . "', '" . $cin . "', '" . $email . "', ? , '" . $desc . "', '" . $pseudo . "', '" . $hash . "')";

        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_send_long_data($stmt, 0, $photoData);
            if (mysqli_stmt_execute($stmt)) {
                header('Location: index.php?success=registered');
                exit();
            } else {
                if (strpos(mysqli_stmt_error($stmt), 'Duplicate') !== false) {
                    $error = 'CIN, email ou pseudo deja utilise.';
                } else {
                    $error = 'Erreur d\'inscription.';
                }
            }
        } else {
            $error = 'Erreur de preparation de la requete.';
        }
    }
}

$pageTitle = 'Inscription Ouvrier — AgriLink';
$activePage = 'register_ouvrier';

include 'header.php';
include 'navbar.php';
?>

<style>
.auth-box { max-width: 560px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media(max-width:480px) { .form-row { grid-template-columns: 1fr; } }
.photo-preview { width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid var(--olive-pale); display:none; margin:0.5rem auto 0; }
</style>

<div class="auth-container" style="min-height:calc(100vh - 64px);">
    <div class="auth-box fade-in">
        <div class="auth-header">
            <div style="font-size:2.5rem;margin-bottom:0.5rem;">👷</div>
            <h2>Inscription Ouvrier</h2>
            <p style="opacity:0.8;margin-top:0.25rem;font-size:0.9rem;">Trouvez des opportunites de recolte</p>
        </div>
        <div class="auth-body">
            <div id="alert-container">
                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><i class="fa fa-times-circle"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
            </div>
            <form id="form-ouvrier" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" class="form-control" placeholder="Votre nom" value="<?php if (isset($_POST['nom'])) echo htmlspecialchars($_POST['nom']); ?>">
                        <div class="form-error" id="nom-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prenom *</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" placeholder="Votre prenom" value="<?php if (isset($_POST['prenom'])) echo htmlspecialchars($_POST['prenom']); ?>">
                        <div class="form-error" id="prenom-error"></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="cin">CIN * <small style="color:var(--text-muted)">(8 chiffres)</small></label>
                        <input type="text" id="cin" name="cin" class="form-control" placeholder="12345678" maxlength="8" value="<?php if (isset($_POST['cin'])) echo htmlspecialchars($_POST['cin']); ?>">
                        <div class="form-error" id="cin-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="exemple@mail.tn" value="<?php if (isset($_POST['email'])) echo htmlspecialchars($_POST['email']); ?>">
                        <div class="form-error" id="email-error"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="photo">Photo d'identite *</label>
                    <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
                    <div class="form-error" id="photo-error"></div>
                    <img id="photo-preview" class="photo-preview" alt="Apercu">
                </div>
                <div class="form-group">
                    <label for="description">Description <small style="color:var(--text-muted)">(experience, niveau educatif…)</small></label>
                    <textarea id="description" name="description" class="form-control" placeholder="Decrivez votre experience…" rows="3"><?php if (isset($_POST['description'])) echo htmlspecialchars($_POST['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="pseudo">Pseudo * <small style="color:var(--text-muted)">(lettres uniquement)</small></label>
                    <input type="text" id="pseudo" name="pseudo" class="form-control" placeholder="monpseudo" value="<?php if (isset($_POST['pseudo'])) echo htmlspecialchars($_POST['pseudo']); ?>">
                    <div class="form-error" id="pseudo-error"></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Mot de passe * <small style="color:var(--text-muted)">(≥8 chars, finit $ ou #)</small></label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••$">
                        <div class="form-error" id="password-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmer *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="••••••••$">
                        <div class="form-error" id="confirm_password-error"></div>
                    </div>
                </div>
                <button type="submit" class="btn btn-secondary w-full btn-lg mt-2">
                    <i class="fa fa-user-plus"></i> S'inscrire
                </button>
            </form>
        </div>
        <div class="auth-footer">
            Deja inscrit ? <a href="login.php">Se connecter</a> &nbsp;|&nbsp;
            Vous etes agriculteur ? <a href="register_agriculteur.php">Inscription Agriculteur</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
