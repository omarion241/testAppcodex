<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';

$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = mysqli_real_escape_string($conn, trim($_POST['nom']));
    $adresse = mysqli_real_escape_string($conn, trim($_POST['adresse']));

    if ($nom === '') {
        $notice = '<div class="error">Le nom est obligatoire.</div>';
    } else {
        $sql = "INSERT INTO entrepot (nom, adresse) VALUES ('$nom', '$adresse')";
        if (mysqli_query($conn, $sql)) {
            $notice = '<div class="notice">Entrepôt ajouté avec succès.</div>';
        } else {
            $notice = '<div class="error">Erreur : ' . mysqli_error($conn) . '</div>';
        }
    }
}
?>
<div class="card">
    <h1>Ajouter un entrepôt</h1>
    <?php echo $notice; ?>
    <form method="post">
        <div class="grid-2">
            <div>
                <label>Nom</label>
                <input type="text" name="nom" required>
            </div>
            <div>
                <label>Adresse</label>
                <input type="text" name="adresse">
            </div>
        </div>
        <button type="submit">Enregistrer</button>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
