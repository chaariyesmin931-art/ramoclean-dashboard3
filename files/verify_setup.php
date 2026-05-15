<?php
/**
 * MongoDB Migration Verification Script
 * 
 * This script verifies that your MongoDB migration is correctly set up.
 * Run this in your browser: http://localhost/Myprojects/ramotejrab/files/verify_setup.php
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$checks = [
    'vendor_autoload' => false,
    'mongodb_driver' => false,
    'mongodb_connection' => false,
    'collections_exist' => false,
    'helper_functions' => false,
];

$messages = [];

// Color scheme
$success_color = '#28a745';
$error_color = '#dc3545';
$warning_color = '#ffc107';

?>
<!DOCTYPE html>
<html>
<head>
    <title>MongoDB Migration Verification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #ddd;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check-item.success {
            background-color: #f0f9f6;
            border-left-color: <?php echo $success_color; ?>;
        }
        .check-item.error {
            background-color: #fff5f5;
            border-left-color: <?php echo $error_color; ?>;
        }
        .check-item.warning {
            background-color: #fffbf0;
            border-left-color: <?php echo $warning_color; ?>;
        }
        .status {
            padding: 5px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
        .status.success {
            background-color: <?php echo $success_color; ?>;
            color: white;
        }
        .status.error {
            background-color: <?php echo $error_color; ?>;
            color: white;
        }
        .status.warning {
            background-color: <?php echo $warning_color; ?>;
            color: #333;
        }
        .summary {
            margin-top: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }
        .details {
            margin-top: 10px;
            font-size: 12px;
            color: #666;
        }
        .code {
            background-color: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            margin: 5px 0;
            font-size: 11px;
        }
        .action-items {
            margin-top: 30px;
            padding: 20px;
            background-color: #e7f3ff;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }
        .action-items h3 {
            margin-top: 0;
            color: #007bff;
        }
        .action-items ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .action-items li {
            margin: 8px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>🔧 MongoDB Migration Verification</h1>

<?php

// Check 1: Vendor autoload
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    $checks['vendor_autoload'] = true;
    $messages['vendor_autoload'] = 'Composer dependencies installed';
} else {
    $messages['vendor_autoload'] = 'Composer not initialized - Run: composer install';
}

// If vendor exists, check for MongoDB driver
if ($checks['vendor_autoload']) {
    try {
        require_once(__DIR__ . '/vendor/autoload.php');
        if (class_exists('MongoDB\Client')) {
            $checks['mongodb_driver'] = true;
            $messages['mongodb_driver'] = 'MongoDB PHP driver loaded successfully';
        }
    } catch (Exception $e) {
        $messages['mongodb_driver'] = 'Error loading MongoDB driver: ' . $e->getMessage();
    }
}

// Check 2: MongoDB Connection
if ($checks['mongodb_driver']) {
    try {
        $mongoUri = "mongodb+srv://chaariyesmin931_db_user:F2wq9Wxzx2UacRYn@cluster0.4tocsfx.mongodb.net/?appName=Cluster0";
        $client = new MongoDB\Client($mongoUri);
        $db = $client->ramoclean;
        
        // Ping database
        $result = $db->command(['ping' => 1]);
        
        $checks['mongodb_connection'] = true;
        $messages['mongodb_connection'] = 'Connected to MongoDB Atlas - Database: ramoclean';
        
        // Check collections
        $collections = $db->listCollections();
        $collectionNames = [];
        foreach ($collections as $col) {
            $collectionNames[] = $col['name'];
        }
        
        $expectedCollections = ['clients', 'employes', 'produits', 'factures', 'matieres', 'fournisseurs', 'familles'];
        $found = count(array_intersect($expectedCollections, $collectionNames));
        
        if ($found > 0) {
            $checks['collections_exist'] = true;
            $messages['collections_exist'] = "Found $found expected collections: " . implode(', ', array_intersect($expectedCollections, $collectionNames));
        } else {
            $messages['collections_exist'] = "No collections found yet (created on first use)";
        }
        
    } catch (Exception $e) {
        $checks['mongodb_connection'] = false;
        $messages['mongodb_connection'] = 'Connection failed: ' . $e->getMessage();
    }
}

// Check 3: Helper functions
if (file_exists(__DIR__ . '/mongo_helpers.php')) {
    try {
        require_once(__DIR__ . '/mongo_helpers.php');
        if (function_exists('mongoFindOne')) {
            $checks['helper_functions'] = true;
            $messages['helper_functions'] = 'All helper functions available';
        }
    } catch (Exception $e) {
        $messages['helper_functions'] = 'Error loading helpers: ' . $e->getMessage();
    }
}

// Display checks
$check_names = [
    'vendor_autoload' => '1. Composer Dependencies',
    'mongodb_driver' => '2. MongoDB PHP Driver',
    'mongodb_connection' => '3. MongoDB Atlas Connection',
    'collections_exist' => '4. Database Collections',
    'helper_functions' => '5. Helper Functions',
];

foreach ($check_names as $key => $name) {
    $status = $checks[$key] ? 'success' : 'error';
    $status_text = $checks[$key] ? '✓ OK' : '✗ FAILED';
    $icon = $checks[$key] ? '✓' : '✗';
    
    ?>
    <div class="check-item <?php echo $status; ?>">
        <div>
            <strong><?php echo $name; ?></strong>
            <div class="details"><?php echo $messages[$key]; ?></div>
        </div>
        <div class="status <?php echo $status; ?>"><?php echo $status_text; ?></div>
    </div>
    <?php
}

// Summary
$all_passed = array_reduce($checks, function($carry, $item) {
    return $carry && $item;
}, true);

?>

    <div class="summary">
        <h3><?php echo $all_passed ? '✓ All Checks Passed!' : '⚠ Some Checks Failed'; ?></h3>
        <?php if ($all_passed): ?>
            <p style="color: <?php echo $success_color; ?>;">
                Your MongoDB migration is ready! You can now use MongoDB in your application.
            </p>
            <p><strong>Next steps:</strong></p>
            <ol>
                <li>Review the converted logic files (ajoutclient_logic.php, ajoutemploye_logic.php, etc.)</li>
                <li>Start using MongoDB for create/update operations</li>
                <li>Convert display pages (client.php, produit.php, etc.)</li>
                <li>See CONVERSION_STATUS.md for detailed patterns</li>
            </ol>
        <?php else: ?>
            <p style="color: <?php echo $error_color; ?>;">
                Please fix the failed checks above before proceeding.
            </p>
            <div class="code">
                <strong>To install Composer dependencies, run:</strong><br>
                cd c:\xampp\htdocs\Myprojects\ramotejrab\files<br>
                composer install
            </div>
        <?php endif; ?>
    </div>

    <?php if ($all_passed): ?>
    <div class="action-items">
        <h3>📋 Quick Action Items</h3>
        <ol>
            <li><strong>Read QUICKSTART.md</strong> - 5-minute setup guide</li>
            <li><strong>Review CONVERSION_STATUS.md</strong> - Complete migration details</li>
            <li><strong>Test create operations</strong> - Try creating a client via web form</li>
            <li><strong>Convert display pages</strong> - Update client.php, produit.php, etc.</li>
            <li><strong>Update export functions</strong> - Modify export_*.php files</li>
        </ol>
    </div>
    <?php else: ?>
    <div class="action-items">
        <h3>⚠ Required Actions</h3>
        <ol>
            <li><strong>Install Composer</strong> - Visit https://getcomposer.org/download/</li>
            <li><strong>Run: composer install</strong> - Install MongoDB PHP driver</li>
            <li><strong>Verify Connection String</strong> - Check connexion.php</li>
            <li><strong>Refresh this page</strong> - Verify all checks pass</li>
        </ol>
    </div>
    <?php endif; ?>

    <div class="summary" style="margin-top: 30px; background-color: #f0f0f0; border-left-color: #666;">
        <h3>📁 Files Involved in This Migration</h3>
        <p><strong>Conversion Files Created:</strong></p>
        <div class="code">
            mongo_connexion.php - MongoDB connection file<br>
            mongo_helpers.php - Helper functions<br>
            connexion.php - Updated main connection<br>
            composer.json - Dependency manager<br>
        </div>
        <p><strong>Logic Files Converted:</strong></p>
        <div class="code">
            ajoutclient_logic.php<br>
            ajoutemploye_logic.php<br>
            ajoutfournisseur_logic.php<br>
            ajoutmatiere_logic.php<br>
            ajoutproduit_logic.php<br>
            ajoutfacture_logic.php<br>
            categories_logic.php<br>
        </div>
        <p><strong>Documentation Created:</strong></p>
        <div class="code">
            QUICKSTART.md - Quick start guide<br>
            CONVERSION_STATUS.md - Detailed status and patterns<br>
            MONGODB_MIGRATION.md - Initial migration guide<br>
            verify_setup.php - This verification script<br>
        </div>
    </div>

</div>

</body>
</html>
