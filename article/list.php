<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';

$notice = '';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if (mysqli_query($conn, "DELETE FROM article WHERE id = $id")) {
        $notice = '<div class="notice">Article supprimé.</div>';
    } else {
        $notice = '<div class="error">Erreur : ' . mysqli_error($conn) . '</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = (int) $_POST['edit_id'];
    $reference = mysqli_real_escape_string($conn, trim($_POST['reference']));
    $designation = mysqli_real_escape_string($conn, trim($_POST['designation']));
    $categorie = mysqli_real_escape_string($conn, trim($_POST['categorie']));
    $prix_achat = (float) $_POST['prix_achat'];
    $prix_vente = (float) $_POST['prix_vente'];
    $stock_minimum = (int) $_POST['stock_minimum'];
    $unite = mysqli_real_escape_string($conn, trim($_POST['unite']));

    $sql = "UPDATE article SET reference = '$reference', designation = '$designation', categorie = '$categorie',
            prix_achat = $prix_achat, prix_vente = $prix_vente, stock_minimum = $stock_minimum, unite = '$unite'
            WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        $notice = '<div class="notice">Article mis à jour.</div>';
    } else {
        $notice = '<div class="error">Erreur : ' . mysqli_error($conn) . '</div>';
    }
}

$result = mysqli_query($conn, 'SELECT * FROM article ORDER BY id DESC');
?>
<div class="card">
    <h1>Liste des articles</h1>
    <?php echo $notice; ?>
    <table class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Référence</th>
            <th>Désignation</th>
            <th>Catégorie</th>
            <th>Prix achat</th>
            <th>Prix vente</th>
            <th>Stock min</th>
            <th>Unité</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['reference']; ?></td>
                <td><?php echo $row['designation']; ?></td>
                <td><?php echo $row['categorie']; ?></td>
                <td><?php echo $row['prix_achat']; ?></td>
                <td><?php echo $row['prix_vente']; ?></td>
                <td><?php echo $row['stock_minimum']; ?></td>
                <td><?php echo $row['unite']; ?></td>
                <td>
                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Supprimer cet article ?')">Supprimer</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Modifier un article</h2>
    <form method="post">
        <div class="grid-2">
            <div>
                <label>ID</label>
                <input type="number" name="edit_id" required>
            </div>
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
                <input type="number" name="stock_minimum" required>
            </div>
            <div>
                <label>Unité</label>
                <input type="text" name="unite">
            </div>
        </div>
        <button type="submit" class="secondary">Mettre à jour</button>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
