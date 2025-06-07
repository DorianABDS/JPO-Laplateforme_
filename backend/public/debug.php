<?php
echo "<h1>🔍 DEBUG BACKEND JPO</h1>";

// Test 1 : PHP de base
echo "<h3>1. Test PHP</h3>";
echo "✅ PHP fonctionne<br>";
echo "Version PHP: " . phpversion() . "<br>";
echo "Chemin actuel: " . __DIR__ . "<br>";

// Test 2 : Fichier .env
echo "<h3>2. Test .env</h3>";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    echo "✅ .env trouvé à: " . $envFile . "<br>";
} else {
    echo "❌ .env MANQUANT<br>";
    echo "Cherché à: " . $envFile . "<br>";
}

// Test 3 : Vendor autoload
echo "<h3>3. Test Composer</h3>";
$vendorPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorPath)) {
    echo "✅ Vendor trouvé<br>";
} else {
    echo "❌ Vendor MANQUANT - Lancer: composer install<br>";
    echo "Cherché à: " . $vendorPath . "<br>";
}

// Test 4 : Database
echo "<h3>4. Test Database</h3>";
$dbPath = __DIR__ . '/../app/Config/database.php';
if (file_exists($dbPath)) {
    echo "✅ database.php trouvé<br>";
} else {
    echo "❌ database.php MANQUANT<br>";
    echo "Cherché à: " . $dbPath . "<br>";
}

// Test 5 : Structure des dossiers
echo "<h3>5. Structure des dossiers</h3>";
$dirs = [
    'app' => __DIR__ . '/../app',
    'app/Config' => __DIR__ . '/../app/Config',
    'app/Core' => __DIR__ . '/../app/Core',
    'app/Controllers' => __DIR__ . '/../app/Controllers',
    'app/Models' => __DIR__ . '/../app/Models',
    'routes' => __DIR__ . '/../routes'
];

foreach ($dirs as $name => $path) {
    if (is_dir($path)) {
        echo "✅ Dossier {$name}<br>";
        // Lister le contenu
        $files = scandir($path);
        $files = array_diff($files, ['.', '..']);
        echo "&nbsp;&nbsp;→ Contenu: " . implode(', ', $files) . "<br>";
    } else {
        echo "❌ Dossier {$name} MANQUANT<br>";
    }
}

echo "<h3>✅ Test terminé !</h3>";
?>