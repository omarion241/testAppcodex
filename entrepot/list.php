<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';

$notice = '';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $sql = "DELETE FROM entrepot WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        $notice = '<div class="notice">Entrepôt supprimé.</div>';
    } else {
        $notice = '<div class="error">Erreur : ' . mysqli_error($conn) . '</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = (int) $_POST['edit_id'];
    $nom = mysqli_real_escape_string($conn, trim($_POST['nom']));
    $adresse = mysqli_real_escape_string($conn, trim($_POST['adresse']));

    if ($nom === '') {
        $notice = '<div class="error">Le nom est obligatoire.</div>';
    } else {
        $sql = "UPDATE entrepot SET nom = '$nom', adresse = '$adresse' WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            $notice = '<div class="notice">Entrepôt mis à jour.</div>';
        } else {
            $notice = '<div class="error">Erreur : ' . mysqli_error($conn) . '</div>';
        }
    }
}

$result = mysqli_query($conn, 'SELECT * FROM entrepot ORDER BY id DESC');
?>
<div class="card">
    <h1>Liste des entrepôts</h1>
    <?php echo $notice; ?>
    <table class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Adresse</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['nom']; ?></td>
                <td><?php echo $row['adresse']; ?></td>
                <td>
                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Supprimer cet entrepôt ?')">Supprimer</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Modifier un entrepôt</h2>
    <form method="post">
        <div class="grid-2">
            <div>
                <label>ID</label>
                <input type="number" name="edit_id" required>
            </div>
            <div>
                <label>Nom</label>
                <input type="text" name="nom" required>
            </div>
            <div>
                <label>Adresse</label>
                <input type="text" name="adresse">
            </div>
        </div>
        <button type="submit" class="secondary">Mettre à jour</button>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
