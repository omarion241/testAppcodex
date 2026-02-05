<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';

$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reference = mysqli_real_escape_string($conn, trim($_POST['reference']));
    $designation = mysqli_real_escape_string($conn, trim($_POST['designation']));
    $categorie = mysqli_real_escape_string($conn, trim($_POST['categorie']));
    $prix_achat = (float) $_POST['prix_achat'];
    $prix_vente = (float) $_POST['prix_vente'];
    $stock_minimum = (int) $_POST['stock_minimum'];
    $unite = mysqli_real_escape_string($conn, trim($_POST['unite']));

    if ($reference === '' || $designation === '') {
        $notice = '<div class="error">Référence et désignation sont obligatoires.</div>';
    } else {
        $sql = "INSERT INTO article (reference, designation, categorie, prix_achat, prix_vente, stock_minimum, unite)
                VALUES ('$reference', '$designation', '$categorie', $prix_achat, $prix_vente, $stock_minimum, '$unite')";
        if (mysqli_query($conn, $sql)) {
            $notice = '<div class="notice">Article ajouté.</div>';
        } else {
            $notice = '<div class="error">Erreur : ' . mysqli_error($conn) . '</div>';
        }
    }
}
?>
<div class="card">
    <h1>Ajouter un article</h1>
    <?php echo $notice; ?>
    <form method="post">
        <div class="grid-2">
            <div>
                <label>Référence</label>
                <input type="text" name="reference" required>
            </div>
            <div>
                <label>Désignation</label>
                <input type="text" name="designation" required>
            </div>
            <div>
                <label>Catégorie</label>
                <input type="text" name="categorie">
            </div>
            <div>
                <label>Prix achat</label>
                <input type="number" step="0.01" name="prix_achat" required>
            </div>
            <div>
                <label>Prix vente</label>
                <input type="number" step="0.01" name="prix_vente" required>
            </div>
            <div>
                <label>Stock minimum</label>
                <input type="number" name="stock_minimum" value="0" required>
            </div>
            <div>
                <label>Unité</label>
                <input type="text" name="unite" value="pcs">
            </div>
        </div>
        <button type="submit">Enregistrer</button>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
