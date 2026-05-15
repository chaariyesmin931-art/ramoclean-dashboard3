<?php
require_once __DIR__ . '/vendor/autoload.php';

$uri = "mongodb+srv://chaariyesmin931_db_user:F2wq9Wxzx2UacRYn@cluster0.4tocsfx.mongodb.net/?appName=Cluster0";

try {
    $client = new MongoDB\Client($uri);
    $db = $client->selectDatabase('ramoclean');
} catch (Exception $e) {
    die("Connection to MongoDB failed: " . $e->getMessage());
}
?>