<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';

$notice = '';

$entrepots = mysqli_query($conn, 'SELECT id, nom FROM entrepot');
$articles = mysqli_query($conn, 'SELECT id, designation FROM article');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fournisseur_nom = mysqli_real_escape_string($conn, trim($_POST['fournisseur']));
    $bl = mysqli_real_escape_string($conn, trim($_POST['bl']));
    $statut_paiement = mysqli_real_escape_string($conn, trim($_POST['statut_paiement']));
    $entrepot_id = (int) $_POST['entrepot_id'];
    $montant_paye = (float) $_POST['montant_paye'];

    $articles_ids = $_POST['article_id'] ?? [];
    $quantites = $_POST['quantite'] ?? [];
    $prix_unitaires = $_POST['prix_unitaire'] ?? [];

    if ($fournisseur_nom === '' || $entrepot_id === 0) {
        $notice = '<div class="error">Fournisseur et entrepôt requis.</div>';
    } elseif (count($articles_ids) === 0) {
        $notice = '<div class="error">Ajoutez au moins un article.</div>';
    } else {
        $fournisseur_sql = "SELECT id FROM fournisseur WHERE nom = '$fournisseur_nom' LIMIT 1";
        $fournisseur_res = mysqli_query($conn, $fournisseur_sql);
        $fournisseur = mysqli_fetch_assoc($fournisseur_res);
        if ($fournisseur) {
            $fournisseur_id = $fournisseur['id'];
        } else {
            mysqli_query($conn, "INSERT INTO fournisseur (nom) VALUES ('$fournisseur_nom')");
            $fournisseur_id = mysqli_insert_id($conn);
        }

        $total = 0;
        foreach ($articles_ids as $index => $article_id) {
            $qte = (int) $quantites[$index];
            $prix = (float) $prix_unitaires[$index];
            $total += $qte * $prix;
        }

        $date_achat = date('Y-m-d');
        $achat_sql = "INSERT INTO achat (fournisseur_id, bl, date_achat, total, statut_paiement)
                      VALUES ($fournisseur_id, '$bl', '$date_achat', $total, '$statut_paiement')";
        if (mysqli_query($conn, $achat_sql)) {
            $achat_id = mysqli_insert_id($conn);

            foreach ($articles_ids as $index => $article_id) {
                $article_id = (int) $article_id;
                $qte = (int) $quantites[$index];
                $prix = (float) $prix_unitaires[$index];
                if ($qte <= 0) {
                    continue;
                }

                mysqli_query($conn, "INSERT INTO detail_achat (achat_id, article_id, quantite, prix_unitaire)
                                     VALUES ($achat_id, $article_id, $qte, $prix)");

                $stock_res = mysqli_query($conn, "SELECT id, quantite FROM stock WHERE entrepot_id = $entrepot_id AND article_id = $article_id");
                $stock = mysqli_fetch_assoc($stock_res);
                if ($stock) {
                    $new_qte = $stock['quantite'] + $qte;
                    mysqli_query($conn, "UPDATE stock SET quantite = $new_qte WHERE id = {$stock['id']}");
                } else {
                    mysqli_query($conn, "INSERT INTO stock (entrepot_id, article_id, quantite) VALUES ($entrepot_id, $article_id, $qte)");
                }

                mysqli_query($conn, "INSERT INTO mouvement_stock (article_id, entrepot_source, entrepot_destination, type, quantite, date_mouvement)
                                     VALUES ($article_id, NULL, $entrepot_id, 'entree', $qte, '$date_achat')");
            }

            $montant_du = $total - $montant_paye;
            if ($montant_paye > 0) {
                mysqli_query($conn, "INSERT INTO paiement (type, achat_id, montant, date_paiement)
                                     VALUES ('$statut_paiement', $achat_id, $montant_paye, '$date_achat')");
            }
            if ($montant_du > 0) {
                mysqli_query($conn, "INSERT INTO dette (type, fournisseur_id, montant, date_creation, statut)
                                     VALUES ('fournisseur', $fournisseur_id, $montant_du, '$date_achat', 'ouverte')");
            }

            $notice = '<div class="notice">Achat enregistré.</div>';
        } else {
            $notice = '<div class="error">Erreur : ' . mysqli_error($conn) . '</div>';
        }
    }
}
?>
<div class="card">
    <h1>Nouvel achat</h1>
    <?php echo $notice; ?>
    <form method="post" id="achat-form">
        <div class="grid-2">
            <div>
                <label>Fournisseur</label>
                <input type="text" name="fournisseur" required>
            </div>
            <div>
                <label>BL (bon de livraison)</label>
                <input type="text" name="bl">
            </div>
            <div>
                <label>Entrepôt</label>
                <select name="entrepot_id" required>
                    <option value="">Sélectionner</option>
                    <?php while ($entrepot = mysqli_fetch_assoc($entrepots)) : ?>
                        <option value="<?php echo $entrepot['id']; ?>"><?php echo $entrepot['nom']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label>Statut paiement</label>
                <select name="statut_paiement" required>
                    <option value="especes">Espèces</option>
                    <option value="virement">Virement</option>
                    <option value="cheque">Chèque</option>
                    <option value="a_terme">À terme</option>
                </select>
            </div>
            <div>
                <label>Montant payé</label>
                <input type="number" step="0.01" name="montant_paye" value="0">
            </div>
        </div>

        <h3>Détails de l'achat</h3>
        <div id="ligne-container"></div>
        <button type="button" class="secondary" onclick="ajouterLigne()">Ajouter une ligne</button>
        <button type="submit">Enregistrer</button>
    </form>
</div>

<script>
const articles = <?php
    $article_list = [];
    mysqli_data_seek($articles, 0);
    while ($article = mysqli_fetch_assoc($articles)) {
        $article_list[] = $article;
    }
    echo json_encode($article_list);
?>;

function ajouterLigne() {
    const container = document.getElementById('ligne-container');
    const wrapper = document.createElement('div');
    wrapper.className = 'grid-2';

    const options = articles.map(article => `<option value="${article.id}">${article.designation}</option>`).join('');

    wrapper.innerHTML = `
        <div>
            <label>Article</label>
            <select name="article_id[]" required>
                ${options}
            </select>
        </div>
        <div>
            <label>Quantité</label>
            <input type="number" name="quantite[]" value="1" min="1" required>
        </div>
        <div>
            <label>Prix unitaire</label>
            <input type="number" step="0.01" name="prix_unitaire[]" value="0" required>
        </div>
    `;

    container.appendChild(wrapper);
}

ajouterLigne();
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
