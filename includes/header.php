<?php
require_once __DIR__ . '/../auth/check.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Stock</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header>
    <nav>
        <strong>Gestion Stock</strong>
        <a href="/entrepot/list.php">Entrepôts</a>
        <a href="/article/list.php">Articles</a>
        <a href="/stock/mouvement.php">Stock</a>
        <a href="/achat/add.php">Achats</a>
        <a href="/vente/add.php">Ventes</a>
        <a href="/rapport/stock.php">Rapports Stock</a>
        <a href="/rapport/vente.php">Rapports Ventes</a>
        <a href="/auth/logout.php">Déconnexion</a>
    </nav>
</header>
<main>
