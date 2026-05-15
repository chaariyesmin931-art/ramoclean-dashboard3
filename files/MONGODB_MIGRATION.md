# MongoDB Atlas Migration Guide

## What Has Been Done

✅ Created `mongo_connexion.php` - MongoDB connection file  
✅ Created `mongo_helpers.php` - Helper functions for MongoDB operations  
✅ Updated `connexion.php` - Now uses MongoDB instead of MySQLi  
✅ Converted the following logic files:
  - `ajoutclient_logic.php`
  - `ajoutemploye_logic.php`
  - `ajoutfournisseur_logic.php`
  - `ajoutmatiere_logic.php`
  - `ajoutproduit_logic.php`

## Installation Steps

### Step 1: Install MongoDB PHP Driver

Run the following in your project root directory:

```bash
composer init
composer require mongodb/mongodb
```

This will create:
- `composer.json` - Your project dependencies
- `composer.lock` - Locked versions
- `vendor/` - Directory with all libraries

### Step 2: Verify Connection

Create a test file `test_mongo.php`:

```php
<?php
require 'vendor/autoload.php';
require 'connexion.php';

echo "MongoDB connection successful!";
echo "Database: ramoclean";
?>
```

Then run: `php test_mongo.php`

## Key Changes from MySQL to MongoDB

### Before (MySQL):
```php
$conn = new mysqli("localhost", "root", "", "ramoclean");
$result = $conn->query("SELECT * FROM client WHERE MatFis='123'");
$row = $result->fetch_assoc();
```

### After (MongoDB):
```php
require_once("connexion.php");
$client = mongoFindOne($clients, ['MatFis' => '123']);
```

## Data Structure Changes

### Clients Collection
- **Before**: `client` table (separate table)
- **After**: `clients` collection (same fields)

### Products with Ingredients
- **Before**: 
  - `produit` table
  - `prodmat` table (junction)
  
- **After**: 
  - `produits` collection with embedded `ingredients` array
  
```json
{
  "_id": ObjectId(...),
  "IdProduit": 1,
  "NomProduit": "Bread",
  "ingredients": [
    {"id": 101, "qte": 2.5},
    {"id": 102, "qte": 1.0}
  ]
}
```

## Collection Reference

In `connexion.php`, these collections are available globally:
- `$clients` - Customer data
- `$employes` - Employee data  
- `$fournisseurs` - Supplier data
- `$matieres` - Materials/Ingredients
- `$produits` - Products
- `$factures` - Invoices
- `$familles` - Product families/categories
- `$stock` - Stock tracking

## Still Need to Convert

⏳ Files that still use MySQL and need conversion:
- All `details_*_logic.php` files
- All `client.php`, `produit.php`, etc. (display files)
- `facture.php` (complex invoice handling)
- `categories_logic.php`
- Export files (`export_*.php`)

## Helper Functions Available

Use these functions from `mongo_helpers.php`:

```php
mongoExists($collection, $field, $value)      // Check if exists
mongoInsert($collection, $data)                // Insert document
mongoFindOne($collection, $filter)             // Get single document
mongoFindAll($collection, $filter)             // Get multiple documents
mongoUpdate($collection, $filter, $data)       // Update document
mongoDelete($collection, $filter)              // Delete document
mongoCount($collection, $filter)               // Count documents
mongoIncrement($collection, $filter, $field)   // Increment number
mongoPushToArray($collection, $filter, $field, $value) // Add to array
```

## Notes

- MongoDB uses document IDs (`_id`) automatically
- All string comparisons are case-sensitive by default
- Timestamps are stored as MongoDB UTCDateTime objects
- Embedded documents (like ingredients) are faster than JOINs
- Migration is happening incrementally - old and new systems can coexist temporarily

## Next Steps

1. Run `composer install` in your project directory
2. Test the connection with `test_mongo.php`
3. Update remaining PHP files to use MongoDB collections
4. Test your application thoroughly
5. Delete old MySQL database once everything works

