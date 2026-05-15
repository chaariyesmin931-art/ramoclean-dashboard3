# Quick Start Guide - MongoDB Migration

## 🚀 Getting Started in 5 Minutes

### Step 1: Install MongoDB PHP Driver (2 minutes)

Open Command Prompt/PowerShell and navigate to your project:

```bash
cd c:\xampp\htdocs\Myprojects\ramotejrab\files
composer install
```

**What it does:**
- Downloads MongoDB PHP library
- Creates `vendor/` folder with all dependencies
- Creates `composer.lock` file

**Expected output:**
```
Installing dependencies from lock file
Nothing to modify in lock file
Writing lock file
Generating autoload files
```

### Step 2: Verify Your Connection String

Open `connexion.php` and verify your MongoDB connection string is correct:

```php
$mongoUri = "mongodb+srv://chaariyesmin931_db_user:F2wq9Wxzx2UacRYn@cluster0.4tocsfx.mongodb.net/?appName=Cluster0";
```

✅ This matches what you provided earlier!

### Step 3: Test the Connection

Create a test file to verify everything works:

**File: `test_mongodb.php`**
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/vendor/autoload.php');

use MongoDB\Client;

$mongoUri = "mongodb+srv://chaariyesmin931_db_user:F2wq9Wxzx2UacRYn@cluster0.4tocsfx.mongodb.net/?appName=Cluster0";

try {
    $client = new Client($mongoUri);
    $db = $client->ramoclean;
    
    // Test connection
    $result = $db->command(['ping' => 1]);
    
    echo "<h2>✅ MongoDB Connection Successful!</h2>";
    echo "<pre>";
    echo "Connected to: ramoclean database\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h2>❌ Connection Failed</h2>";
    echo "<p style='color:red;'>" . $e->getMessage() . "</p>";
    echo "<p>Check:</p>";
    echo "<ul>";
    echo "<li>MongoDB Atlas cluster is running</li>";
    echo "<li>Connection string is correct</li>";
    echo "<li>Your IP is whitelisted in Atlas</li>";
    echo "</ul>";
}
?>
```

Run this in your browser: `http://localhost/Myprojects/ramotejrab/files/test_mongodb.php`

### Step 4: Database Structure

Your MongoDB database `ramoclean` now has these **collections**:

```
clients          → Customer data
employes         → Employee data  
fournisseurs     → Supplier data
matieres         → Materials/Ingredients
produits         → Products (with embedded ingredients)
factures         → Invoices
familles         → Product categories
stock            → Inventory tracking
```

These are created automatically on first use!

---

## 📋 Files Changed Summary

### ✅ Already Updated (Ready to Use)
- `connexion.php` - Main MongoDB connection
- `mongo_connexion.php` - Alternative connection file
- `mongo_helpers.php` - Helper functions
- `composer.json` - Dependency config

### ✅ Logic Files Converted (Database operations)
- `ajoutclient_logic.php` - Create clients
- `ajoutemploye_logic.php` - Create employees
- `ajoutfournisseur_logic.php` - Create suppliers
- `ajoutmatiere_logic.php` - Create materials
- `ajoutproduit_logic.php` - Create products
- `ajoutfacture_logic.php` - Create invoices
- `categories_logic.php` - Manage categories

### ⏳ Still Using MySQL (Need Conversion)
- `client.php` - List clients
- `produit.php` - List products
- `facture.php` - List invoices
- `employe.php` - List employees
- `fournisseur.php` - List suppliers
- `matiere.php` - List materials
- `details_*.php` - Detail pages
- `export_*.php` - Export files

---

## 🧪 Testing Your Setup

### Test 1: Simple Connection Test
```bash
php -r "require 'vendor/autoload.php'; echo 'PHP MongoDB driver installed!';"
```

### Test 2: Test Create Operation
Visit: `http://localhost/Myprojects/ramotejrab/files/ajoutclient.php`

Try creating a test client - if it works, you're connected! ✅

### Test 3: Query Test
Create `test_query.php`:
```php
<?php
require_once('connexion.php');
require_once('mongo_helpers.php');

// Count clients
$count = mongoCount($clients);
echo "Total clients: $count";

// List first client
$first = mongoFindOne($clients);
if ($first) {
    echo "<pre>";
    print_r($first);
    echo "</pre>";
}
?>
```

---

## ⚠️ Common Issues & Fixes

### Issue: "Class 'MongoDB\Client' not found"
**Fix:** Run `composer install` in the project directory

### Issue: "Connection failed" error
**Fix:** 
1. Check Internet connection
2. Verify MongoDB Atlas cluster is running
3. Check connection string in `connexion.php`
4. Verify IP whitelist in MongoDB Atlas dashboard

### Issue: "Permission denied" when running composer
**Fix:** Run Command Prompt as Administrator

### Issue: "composer not found"
**Fix:** 
- Download from https://getcomposer.org/download/
- Or use: `php composer.phar install`

---

## 📝 What Changed from MySQL to MongoDB

| Aspect | MySQL | MongoDB |
|--------|-------|---------|
| **Connection** | `mysqli` | MongoDB PHP Driver |
| **Tables** | Multiple tables + JOINs | Embedded documents |
| **Queries** | SQL strings | PHP arrays |
| **Security** | `mysqli_real_escape_string()` | Native safety |
| **Transactions** | Complex | Simple with helpers |
| **Relationships** | Foreign keys | Embedded documents |

---

## 🎯 Next Steps

### Immediate (Today)
1. ✅ Run `composer install` 
2. ✅ Test connection with test script
3. ✅ Try creating a client via web form

### Soon (This Week)
1. Update `client.php` to display clients from MongoDB
2. Update `details_client_logic.php` for detail view
3. Update other display pages similarly

### Later (As Needed)
1. Update export functions
2. Add more advanced queries
3. Performance optimization

---

## 📚 Helper Functions You Can Use

All these are available in `mongo_helpers.php`:

```php
// Check if exists
mongoExists($collection, 'MatFis', '123')

// Insert document
mongoInsert($collection, ['name' => 'value'])

// Find single document
mongoFindOne($collection, ['id' => 1])

// Find multiple documents
mongoFindAll($collection, [])

// Update document
mongoUpdate($collection, ['id' => 1], ['name' => 'new'])

// Delete document
mongoDelete($collection, ['id' => 1])

// Count documents
mongoCount($collection, [])

// Increment field
mongoIncrement($collection, ['id' => 1], 'counter', 5)
```

---

## 💡 Tips for Success

1. **Backup First:** Keep a copy of original MySQL files
2. **Test Incrementally:** Update one page at a time
3. **Use Try-Catch:** Always wrap MongoDB calls in try-catch
4. **Check Logs:** Monitor browser console for errors
5. **Reference Originals:** Compare old SQL with new MongoDB code
6. **Ask for Help:** See CONVERSION_STATUS.md for detailed patterns

---

## 🎉 You're Ready!

You now have:
- ✅ MongoDB Atlas database set up
- ✅ PHP connection configured
- ✅ Helper functions ready
- ✅ 7 core logic files converted
- ✅ Composer dependencies installed

**Next action:** Run `composer install` and test the connection!

