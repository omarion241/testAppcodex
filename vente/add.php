<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';

$notice = '';

$entrepots = mysqli_query($conn, 'SELECT id, nom FROM entrepot');
$articles = mysqli_query($conn, 'SELECT id, designation, prix_vente FROM article');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_nom = mysqli_real_escape_string($conn, trim($_POST['client']));
    $entrepot_id = (int) $_POST['entrepot_id'];
    $statut_paiement = mysqli_real_escape_string($conn, trim($_POST['statut_paiement']));
    $montant_paye = (float) $_POST['montant_paye'];

    $articles_ids = $_POST['article_id'] ?? [];
    $quantites = $_POST['quantite'] ?? [];
    $prix_unitaires = $_POST['prix_unitaire'] ?? [];

    if ($entrepot_id === 0) {
        $notice = '<div class="error">Entrepôt requis.</div>';
    } elseif (count($articles_ids) === 0) {
        $notice = '<div class="error">Ajoutez au moins un article.</div>';
    } else {
        $client_id = 'NULL';
        if ($client_nom !== '') {
            $client_res = mysqli_query($conn, "SELECT id FROM client WHERE nom = '$client_nom' LIMIT 1");
            $client = mysqli_fetch_assoc($client_res);
            if ($client) {
                $client_id = $client['id'];
            } else {
                mysqli_query($conn, "INSERT INTO client (nom) VALUES ('$client_nom')");
                $client_id = mysqli_insert_id($conn);
            }
        }

        $total = 0;
        foreach ($articles_ids as $index => $article_id) {
            $qte = (int) $quantites[$index];
            $prix = (float) $prix_unitaires[$index];
            $total += $qte * $prix;
        }

        $date_vente = date('Y-m-d');
        $vente_sql = "INSERT INTO vente (client_id, date_vente, total, statut_paiement)
                      VALUES ($client_id, '$date_vente', $total, '$statut_paiement')";
        if (mysqli_query($conn, $vente_sql)) {
            $vente_id = mysqli_insert_id($conn);

            foreach ($articles_ids as $index => $article_id) {
                $article_id = (int) $article_id;
                $qte = (int) $quantites[$index];
                $prix = (float) $prix_unitaires[$index];
                if ($qte <= 0) {
                    continue;
                }

                mysqli_query($conn, "INSERT INTO detail_vente (vente_id, article_id, quantite, prix_unitaire)
                                     VALUES ($vente_id, $article_id, $qte, $prix)");

                $stock_res = mysqli_query($conn, "SELECT id, quantite FROM stock WHERE entrepot_id = $entrepot_id AND article_id = $article_id");
                $stock = mysqli_fetch_assoc($stock_res);
                if ($stock) {
                    $new_qte = $stock['quantite'] - $qte;
                    mysqli_query($conn, "UPDATE stock SET quantite = $new_qte WHERE id = {$stock['id']}");
                }

                mysqli_query($conn, "INSERT INTO mouvement_stock (article_id, entrepot_source, entrepot_destination, type, quantite, date_mouvement)
                                     VALUES ($article_id, $entrepot_id, NULL, 'sortie', $qte, '$date_vente')");
            }

            $montant_du = $total - $montant_paye;
            if ($montant_paye > 0) {
                mysqli_query($conn, "INSERT INTO paiement (type, vente_id, montant, date_paiement)
                                     VALUES ('$statut_paiement', $vente_id, $montant_paye, '$date_vente')");
            }
            if ($montant_du > 0 && $client_id !== 'NULL') {
                mysqli_query($conn, "INSERT INTO dette (type, client_id, montant, date_creation, statut)
                                     VALUES ('client', $client_id, $montant_du, '$date_vente', 'ouverte')");
            }

            $notice = '<div class="notice">Vente enregistrée.</div>';
        } else {
            $notice = '<div class="error">Erreur : ' . mysqli_error($conn) . '</div>';
        }
    }
}
?>
<div class="card">
    <h1>Nouvelle vente</h1>
    <?php echo $notice; ?>
    <form method="post" id="vente-form">
        <div class="grid-2">
            <div>
                <label>Client (facultatif)</label>
                <input type="text" name="client" placeholder="Vente comptoir si vide">
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

        <h3>Détails de la vente</h3>
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

    const options = articles.map(article => `<option value="${article.id}" data-prix="${article.prix_vente}">${article.designation}</option>`).join('');

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
