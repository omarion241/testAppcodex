<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';

$stock_res = mysqli_query($conn, "SELECT e.nom AS entrepot, a.designation, s.quantite, a.stock_minimum
                                 FROM stock s
                                 JOIN entrepot e ON e.id = s.entrepot_id
                                 JOIN article a ON a.id = s.article_id
                                 ORDER BY e.nom, a.designation");

$alertes_res = mysqli_query($conn, "SELECT e.nom AS entrepot, a.designation, s.quantite, a.stock_minimum
                                   FROM stock s
                                   JOIN entrepot e ON e.id = s.entrepot_id
                                   JOIN article a ON a.id = s.article_id
                                   WHERE s.quantite <= a.stock_minimum
                                   ORDER BY s.quantite ASC");
?>
<div class="card">
    <h1>Rapport stock par entrepôt</h1>
    <table class="table">
        <thead>
        <tr>
            <th>Entrepôt</th>
            <th>Article</th>
            <th>Quantité</th>
            <th>Stock min</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($stock_res)) : ?>
            <tr>
                <td><?php echo $row['entrepot']; ?></td>
                <td><?php echo $row['designation']; ?></td>
                <td><?php echo $row['quantite']; ?></td>
                <td><?php echo $row['stock_minimum']; ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Alertes stock minimum</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Entrepôt</th>
            <th>Article</th>
            <th>Quantité</th>
            <th>Minimum</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($alertes_res)) : ?>
            <tr>
                <td><?php echo $row['entrepot']; ?></td>
                <td><?php echo $row['designation']; ?></td>
                <td><?php echo $row['quantite']; ?></td>
                <td><?php echo $row['stock_minimum']; ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
