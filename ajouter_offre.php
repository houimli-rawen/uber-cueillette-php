<?php
require_once 'config.php';
require_once 'functions.php';
requireLogin('agriculteur');

$id = $_SESSION['user_id'];
$error = '';
$success = '';

$fruits = mysqli_query($conn, "SELECT * FROM type_fruit ORDER BY libelle");
$gouvernorats = mysqli_query($conn, "SELECT * FROM gouvernorat ORDER BY libelle");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_fruit = 0;
    $nb_ouvriers = 0; $prix = 0; $date_limite = '';

    if (isset($_POST['type_fruit'])) $type_fruit = (int)$_POST['type_fruit'];
    if (isset($_POST['gouvernorat'])) $gouvernorat = (int)$_POST['gouvernorat'];
    if (isset($_POST['adresse'])) $adresse = sanitize($conn, $_POST['adresse']);
    if (isset($_POST['date_debut'])) $date_debut = sanitize($conn, $_POST['date_debut']);
    if (isset($_POST['date_fin'])) $date_fin = sanitize($conn, $_POST['date_fin']);
    if (isset($_POST['nombre_ouvriers'])) $nb_ouvriers = (int)$_POST['nombre_ouvriers'];
    if (isset($_POST['prix_journee'])) $prix = (float)$_POST['prix_journee'];
    if (isset($_POST['date_limite'])) $date_limite = sanitize($conn, $_POST['date_limite']);

    if ($type_fruit === 0 || $gouvernorat === 0 || $adresse === '' || $date_debut === '' || $date_fin === '' || $nb_ouvriers === 0 || $prix === 0 || $date_limite === '') {
        $error = 'Veuillez remplir tous les champs.';
    } else if ($date_fin <= $date_debut) {
        $error = 'La date de fin doit etre apres la date de debut.';
    } else if ($date_limite >= $date_debut) {
        $error = 'La date limite doit etre avant la date de debut.';
    } else {
        $sql = "INSERT INTO offre (id_type_fruit, id_gouvernorat, adresse, date_debut, date_fin, nombre_ouvriers, prix_journee, date_limite, id_agriculteur) VALUES (" . $type_fruit . ", " . $gouvernorat . ", '" . $adresse . "', '" . $date_debut . "', '" . $date_fin . "', " . $nb_ouvriers . ", " . $prix . ", '" . $date_limite . "', " . $id . ")";
        $result = mysqli_query($conn, $sql);
        if ($result) { $success = 'Offre publiee avec succes !'; }
        else { $error = 'Erreur lors de la publication.'; }
    }
}

$pageTitle = 'Ajouter Offre';
$activePage = 'ajouter_offre';
include 'header.php';
include 'navbar.php';
?>

<style>
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media(max-width:600px) { .form-row { grid-template-columns: 1fr; } }
</style>

<div class="page-header">
    <div class="container">
        <h1><i class="fa fa-plus-circle"></i> Publier une Offre</h1>
        <p>Renseignez les details de votre chantier</p>
    </div>
</div>

<div class="container" style="max-width:800px;">
    <div class="card fade-in">
        <div class="card-body">
            <div id="alert-container">
                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><i class="fa fa-times-circle"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success !== ''): ?>
                    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        <a href="mes_offres.php" style="margin-left:1rem;" class="btn btn-sm btn-success">Voir mes offres</a>
                    </div>
                <?php endif; ?>
            </div>

            <form id="form-offre" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="type_fruit">🍊 Type de fruit *</label>
                        <select id="type_fruit" name="type_fruit" class="form-control">
                            <option value="">-- Choisir --</option>
                            <?php mysqli_data_seek($fruits, 0); while ($f = mysqli_fetch_assoc($fruits)): ?>
                                <option value="<?php echo $f['id_type_fruit']; ?>" <?php if (isset($_POST['type_fruit']) && $_POST['type_fruit'] == $f['id_type_fruit']) echo 'selected'; ?>><?php echo htmlspecialchars($f['libelle']); ?></option>
                            <?php endwhile; ?>
                        </select>
                        <div class="form-error" id="type_fruit-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="gouvernorat">📍 Gouvernorat *</label>
                        <select id="gouvernorat" name="gouvernorat" class="form-control">
                            <option value="">-- Choisir --</option>
                            <?php mysqli_data_seek($gouvernorats, 0); while ($g = mysqli_fetch_assoc($gouvernorats)): ?>
                                <option value="<?php echo $g['id_gouvernorat']; ?>" <?php if (isset($_POST['gouvernorat']) && $_POST['gouvernorat'] == $g['id_gouvernorat']) echo 'selected'; ?>><?php echo htmlspecialchars($g['libelle']); ?></option>
                            <?php endwhile; ?>
                        </select>
                        <div class="form-error" id="gouvernorat-error"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="adresse">📌 Adresse du site *</label>
                    <input type="text" id="adresse" name="adresse" class="form-control" placeholder="Ex: Route de Sfax Km 12" value="<?php if (isset($_POST['adresse'])) echo htmlspecialchars($_POST['adresse']); ?>">
                    <div class="form-error" id="adresse-error"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="date_debut">📅 Date de debut *</label>
                        <input type="date" id="date_debut" name="date_debut" class="form-control" value="<?php if (isset($_POST['date_debut'])) echo htmlspecialchars($_POST['date_debut']); ?>">
                        <div class="form-error" id="date_debut-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="date_fin">📅 Date de fin *</label>
                        <input type="date" id="date_fin" name="date_fin" class="form-control" value="<?php if (isset($_POST['date_fin'])) echo htmlspecialchars($_POST['date_fin']); ?>">
                        <div class="form-error" id="date_fin-error"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="date_limite">⏰ Date limite de candidature *</label>
                    <input type="date" id="date_limite" name="date_limite" class="form-control" value="<?php if (isset($_POST['date_limite'])) echo htmlspecialchars($_POST['date_limite']); ?>">
                    <div class="form-error" id="date_limite-error"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre_ouvriers">👷 Nombre d'ouvriers *</label>
                        <input type="number" id="nombre_ouvriers" name="nombre_ouvriers" class="form-control" min="1" placeholder="Ex: 10" value="<?php if (isset($_POST['nombre_ouvriers'])) echo htmlspecialchars($_POST['nombre_ouvriers']); ?>">
                        <div class="form-error" id="nombre_ouvriers-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="prix_journee">💰 Prix a la journee (TND) *</label>
                        <input type="number" id="prix_journee" name="prix_journee" class="form-control" min="0" step="0.5" placeholder="Ex: 35" value="<?php if (isset($_POST['prix_journee'])) echo htmlspecialchars($_POST['prix_journee']); ?>">
                        <div class="form-error" id="prix_journee-error"></div>
                    </div>
                </div>

                <div class="flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-paper-plane"></i> Publier</button>
                    <a href="mes_offres.php" class="btn btn-outline btn-lg">Annuler</a>
                </div>
            </form>
        </div>
    </div>
    <div style="height:3rem;"></div>
</div>

<?php include 'footer.php'; ?>
