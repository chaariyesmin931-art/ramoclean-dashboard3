<?php
require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/mongo_helpers.php');

use MongoDB\Client;
use MongoDB\Exception\ServerException;

$mongoUri = "mongodb+srv://chaariyesmin931_db_user:F2wq9Wxzx2UacRYn@cluster0.4tocsfx.mongodb.net/?appName=Cluster0";

try {
    $client = new Client($mongoUri);
    $db = $client->ramoclean;
    $client->admin->command(['ping' => 1]);
    
    /* Collection references */
    $clients = $db->clients;
    $employes = $db->employes;
    $factures = $db->factures;
    $fournisseurs = $db->fournisseurs;
    $matieres = $db->matieres;
    $produits = $db->produits;
    $familles = $db->familles;
    $stock = $db->stock;
    
} catch (ServerException $e) {
    die("MongoDB Connection failed: " . $e->getMessage());
} catch (Exception $e) {
    die("MongoDB Error: " . $e->getMessage());
}
?>