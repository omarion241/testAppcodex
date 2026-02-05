<?php
// Configuration de la base de données
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'gestion_stock';

$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die('Erreur de connexion : ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8');
