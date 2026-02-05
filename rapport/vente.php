<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';

$date_debut = isset($_GET['date_debut']) ? mysqli_real_escape_string($conn, $_GET['date_debut']) : date('Y-m-01');
$date_fin = isset($_GET['date_fin']) ? mysqli_real_escape_string($conn, $_GET['date_fin']) : date('Y-m-d');

$vente_jour_res = mysqli_query($conn, "SELECT date_vente, SUM(total) AS total
                                     FROM vente
                                     GROUP BY date_vente
                                     ORDER BY date_vente DESC");

$achat_periode_res = mysqli_query($conn, "SELECT date_achat, SUM(total) AS total
                                         FROM achat
                                         WHERE date_achat BETWEEN '$date_debut' AND '$date_fin'
                                         GROUP BY date_achat
                                         ORDER BY date_achat DESC");

$benefice_res = mysqli_query($conn, "SELECT
    (SELECT IFNULL(SUM(total), 0) FROM vente WHERE date_vente BETWEEN '$date_debut' AND '$date_fin') AS total_vente,
    (SELECT IFNULL(SUM(total), 0) FROM achat WHERE date_achat BETWEEN '$date_debut' AND '$date_fin') AS total_achat");
$benefice = mysqli_fetch_assoc($benefice_res);
$profit = $benefice['total_vente'] - $benefice['total_achat'];
?>
<div class="card">
    <h1>Rapports ventes et achats</h1>

    <h2>Ventes journalières</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Date</th>
            <th>Total ventes</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($vente_jour_res)) : ?>
            <tr>
                <td><?php echo $row['date_vente']; ?></td>
                <td><?php echo number_format($row['total'], 2); ?> €</td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Achats par période</h2>
    <form method="get">
        <div class="grid-2">
            <div>
                <label>Date début</label>
                <input type="date" name="date_debut" value="<?php echo $date_debut; ?>">
            </div>
            <div>
                <label>Date fin</label>
                <input type="date" name="date_fin" value="<?php echo $date_fin; ?>">
            </div>
        </div>
        <button type="submit">Filtrer</button>
    </form>
    <table class="table">
        <thead>
        <tr>
            <th>Date</th>
            <th>Total achats</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($achat_periode_res)) : ?>
            <tr>
                <td><?php echo $row['date_achat']; ?></td>
                <td><?php echo number_format($row['total'], 2); ?> €</td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Bénéfice & perte</h2>
    <p>Total ventes: <strong><?php echo number_format($benefice['total_vente'], 2); ?> €</strong></p>
    <p>Total achats: <strong><?php echo number_format($benefice['total_achat'], 2); ?> €</strong></p>
    <p>Résultat: <span class="badge"><?php echo number_format($profit, 2); ?> €</span></p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
