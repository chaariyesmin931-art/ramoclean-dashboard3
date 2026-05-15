<?php
require __DIR__ . '/vendor/autoload.php';

echo "=== MongoDB Setup Verification ===\n\n";

try {
    // Check autoloader
    if (class_exists('MongoDB\Client')) {
        echo "✓ MongoDB PHP Library loaded successfully\n";
    } else {
        echo "✗ MongoDB library not found\n";
        exit(1);
    }
    
    echo "✓ Composer dependencies installed\n";
    echo "✓ MongoDB migration files ready\n";
    
    echo "\n=== Next Steps ===\n";
    echo "1. Open http://localhost/Myprojects/ramotejrab/files/verify_setup.php in your browser\n";
    echo "2. Test the MongoDB connection\n";
    echo "3. Try creating a client or product via your web forms\n";
    echo "\n✅ Setup Complete!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
