<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';

$notice = '';
$entrepots = mysqli_query($conn, 'SELECT id, nom FROM entrepot');
$articles = mysqli_query($conn, 'SELECT id, designation FROM article');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $article_id = (int) $_POST['article_id'];
    $quantite = (int) $_POST['quantite'];
    $entrepot_source = (int) $_POST['entrepot_source'];
    $entrepot_destination = (int) $_POST['entrepot_destination'];
    $date_mouvement = date('Y-m-d');

    if ($article_id === 0 || $quantite <= 0) {
        $notice = '<div class="error">Article et quantité requis.</div>';
    } elseif ($type === 'transfert' && ($entrepot_source === 0 || $entrepot_destination === 0)) {
        $notice = '<div class="error">Entrepôts source et destination requis.</div>';
    } else {
        if ($type === 'entree') {
            $stock_res = mysqli_query($conn, "SELECT id, quantite FROM stock WHERE entrepot_id = $entrepot_destination AND article_id = $article_id");
            $stock = mysqli_fetch_assoc($stock_res);
            if ($stock) {
                $new_qte = $stock['quantite'] + $quantite;
                mysqli_query($conn, "UPDATE stock SET quantite = $new_qte WHERE id = {$stock['id']}");
            } else {
                mysqli_query($conn, "INSERT INTO stock (entrepot_id, article_id, quantite) VALUES ($entrepot_destination, $article_id, $quantite)");
            }
        }

        if ($type === 'sortie') {
            $stock_res = mysqli_query($conn, "SELECT id, quantite FROM stock WHERE entrepot_id = $entrepot_source AND article_id = $article_id");
            $stock = mysqli_fetch_assoc($stock_res);
            if ($stock) {
                $new_qte = $stock['quantite'] - $quantite;
                mysqli_query($conn, "UPDATE stock SET quantite = $new_qte WHERE id = {$stock['id']}");
            }
        }

        if ($type === 'transfert') {
            $source_res = mysqli_query($conn, "SELECT id, quantite FROM stock WHERE entrepot_id = $entrepot_source AND article_id = $article_id");
            $source = mysqli_fetch_assoc($source_res);
            if ($source) {
                $new_qte = $source['quantite'] - $quantite;
                mysqli_query($conn, "UPDATE stock SET quantite = $new_qte WHERE id = {$source['id']}");
            }

            $dest_res = mysqli_query($conn, "SELECT id, quantite FROM stock WHERE entrepot_id = $entrepot_destination AND article_id = $article_id");
            $dest = mysqli_fetch_assoc($dest_res);
            if ($dest) {
                $new_qte = $dest['quantite'] + $quantite;
                mysqli_query($conn, "UPDATE stock SET quantite = $new_qte WHERE id = {$dest['id']}");
            } else {
                mysqli_query($conn, "INSERT INTO stock (entrepot_id, article_id, quantite) VALUES ($entrepot_destination, $article_id, $quantite)");
            }
        }

        mysqli_query($conn, "INSERT INTO mouvement_stock (article_id, entrepot_source, entrepot_destination, type, quantite, date_mouvement)
                             VALUES ($article_id, " . ($entrepot_source ?: 'NULL') . ", " . ($entrepot_destination ?: 'NULL') . ", '$type', $quantite, '$date_mouvement')");

        $notice = '<div class="notice">Mouvement enregistré.</div>';
    }
}
?>
<div class="card">
    <h1>Mouvement de stock</h1>
    <?php echo $notice; ?>
    <form method="post">
        <div class="grid-2">
            <div>
                <label>Type</label>
                <select name="type" required>
                    <option value="entree">Entrée</option>
                    <option value="sortie">Sortie</option>
                    <option value="transfert">Transfert</option>
                </select>
            </div>
            <div>
                <label>Article</label>
                <select name="article_id" required>
                    <option value="">Sélectionner</option>
                    <?php while ($article = mysqli_fetch_assoc($articles)) : ?>
                        <option value="<?php echo $article['id']; ?>"><?php echo $article['designation']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label>Quantité</label>
                <input type="number" name="quantite" min="1" required>
            </div>
            <div>
                <label>Entrepôt source</label>
                <select name="entrepot_source">
                    <option value="">--</option>
                    <?php while ($entrepot = mysqli_fetch_assoc($entrepots)) : ?>
                        <option value="<?php echo $entrepot['id']; ?>"><?php echo $entrepot['nom']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label>Entrepôt destination</label>
                <select name="entrepot_destination">
                    <option value="">--</option>
                    <?php
                    mysqli_data_seek($entrepots, 0);
                    while ($entrepot = mysqli_fetch_assoc($entrepots)) : ?>
                        <option value="<?php echo $entrepot['id']; ?>"><?php echo $entrepot['nom']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <button type="submit">Valider</button>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
