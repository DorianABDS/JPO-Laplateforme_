<?php
// Page de test backend JPO

// Test 1 : Fonctionnement de PHP
echo "<h3>1. PHP</h3>";
echo "PHP OK<br>";
echo "Version : " . phpversion() . "<br>";
echo "Chemin actuel : " . __DIR__ . "<br>";

// Test 2 : Fichier .env
echo "<h3>2. Fichier .env</h3>";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    echo ".env trouvé à : $envFile<br>";
} else {
    echo ".env manquant<br>";
}

// Test 3 : Autoloader Composer
echo "<h3>3. Autoload Composer</h3>";
$vendorPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorPath)) {
    echo "Autoload Composer présent<br>";
} else {
    echo "Autoload manquant - exécuter : composer install<br>";
}

// Test 4 : Fichier database.php
echo "<h3>4. Fichier database.php</h3>";
$dbPath = __DIR__ . '/../app/Config/database.php';
if (file_exists($dbPath)) {
    echo "Fichier database.php trouvé<br>";
} else {
    echo "Fichier database.php manquant<br>";
}

// Test 5 : Structure des dossiers
echo "<h3>5. Structure</h3>";
$dirs = [
    'app' => __DIR__ . '/../app',
    'Config' => __DIR__ . '/../Config',
    'Core' => __DIR__ . '/../Core',
    'app/Controllers' => __DIR__ . '/../app/Controllers',
    'app/Models' => __DIR__ . '/../app/Models',
    'routes' => __DIR__ . '/../routes'
];

foreach ($dirs as $name => $path) {
    if (is_dir($path)) {
        echo "Dossier '$name' présent<br>";
        $files = array_diff(scandir($path), ['.', '..']);
        echo "&nbsp;&nbsp;&nbsp;&nbsp;→ Contenu : " . implode(', ', $files) . "<br>";
    } else {
        echo "Dossier '$name' manquant<br>";
    }
}

echo "<h3>Tests terminés.</h3>";
?>
