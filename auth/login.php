<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);

    if ($email === '' || $password === '') {
        $message = 'Veuillez remplir tous les champs.';
    } else {
        $sql = "SELECT id, nom, mot_de_passe FROM utilisateur WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nom'];
            header('Location: /entrepot/list.php');
            exit;
        }

        $message = 'Identifiants invalides.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<main>
    <div class="card">
        <h1>Connexion</h1>
        <?php if ($message !== '') : ?>
            <div class="error"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="post">
            <div>
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div>
                <label>Mot de passe</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>
        <p>Compte demo : admin@demo.com / Admin@123</p>
    </div>
</main>
</body>
</html>
