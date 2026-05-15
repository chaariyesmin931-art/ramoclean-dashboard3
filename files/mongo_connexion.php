<?php
require_once(__DIR__ . '/vendor/autoload.php');

use MongoDB\Client;
use MongoDB\Exception\ServerException;

/* =============================================
   MONGODB ATLAS CONNECTION
   ============================================= */

$mongoUri = "mongodb+srv://chaariyesmin931_db_user:F2wq9Wxzx2UacRYn@cluster0.4tocsfx.mongodb.net/?appName=Cluster0";

try {
    $client = new Client($mongoUri);
    $db = $client->ramoclean; // Database name
    
    /* Test connection */
    $client->admin->command(['ping' => 1]);
    
} catch (ServerException $e) {
    die("MongoDB Connection failed: " . $e->getMessage());
} catch (Exception $e) {
    die("MongoDB Error: " . $e->getMessage());
}

/* =============================================
   COLLECTION REFERENCES
   ============================================= */
$clients = $db->clients;
$employes = $db->employes;
$factures = $db->factures;
$fournisseurs = $db->fournisseurs;
$matieres = $db->matieres;
$produits = $db->produits;
$familles = $db->familles;
?>
