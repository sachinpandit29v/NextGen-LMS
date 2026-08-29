<?php
/**
 * NextGen LMS Setup Script
 * Run this from command line: php setup/setup.php
 */

require_once __DIR__ . '/../config/database.php';

$projectName = basename(dirname(__DIR__));

echo "NextGen LMS Setup\n================\n\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to MySQL\n";

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
    echo "✓ Database created\n";

    $pdo->exec("USE `" . DB_NAME . "`");

    // Import single SQL file (keeps setup simple for sharing)
    $file = __DIR__ . "/../sql/nextgen_lms.sql";
    $sql = file_get_contents($file);
    if ($sql === false) {
        throw new RuntimeException("Unable to read SQL file at $file");
    }

    // Strip full-line `--` comments before splitting into statements.
    // Otherwise a chunk starting with a comment could accidentally skip real SQL.
    $lines = preg_split("/\\r\\n|\\r|\\n/", $sql);
    $filtered_lines = [];
    foreach ($lines as $line) {
        $trimmed = ltrim($line);
        if ($trimmed === '' || substr($trimmed, 0, 2) === '--') {
            continue;
        }
        $filtered_lines[] = $line;
    }

    $statements = explode(";", implode("\n", $filtered_lines));

    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '') {
            continue;
        }
        $pdo->exec($stmt);
    }

    echo "✓ Database schema imported\n";

    echo "✓ Setup complete!\n\nDemo accounts:\n- admin@nextgen.com / admin123\n- student@nextgen.com / student123\n- demo@nextgen.com / demo123\n\nVisit: http://localhost/$projectName/\n";
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
