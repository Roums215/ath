<?php
// tools/drop_all_mysql.php

$host = getenv('MYSQL_HOST') ?: 'ath-mysql';
$dbName = getenv('MYSQL_DATABASE') ?: 'radiotherapie_db';
$user = getenv('MYSQL_USER') ?: 'radiotherapie_user';
$pass = getenv('MYSQL_PASSWORD') ?: 'Radiotherapie2025';
$dsn = "mysql:host=$host;dbname=$dbName";

// Log de connexion
fwrite(STDOUT, "[drop_all_mysql] Connexion: $dsn, utilisateur: $user\n");
try {
    $db = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $db->exec('SET foreign_key_checks = 0;');

    // Supprimer les vues
    $views = $db->query("SELECT table_name FROM information_schema.views WHERE table_schema = '$dbName'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($views as $view) {
        $db->exec("DROP VIEW IF EXISTS `$view`;");
        fwrite(STDOUT, "[drop_all_mysql] Vue supprimée: $view\n");
    }

    // Supprimer les tables
    $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $db->exec("DROP TABLE IF EXISTS `$table`;");
        fwrite(STDOUT, "[drop_all_mysql] Table supprimée: $table\n");
    }

    $db->exec('SET foreign_key_checks = 1;');
    fwrite(STDOUT, "[drop_all_mysql] Suppression terminée.\n");
} catch (Exception $e) {
    fwrite(STDERR, "[drop_all_mysql][ERREUR] " . $e->getMessage() . "\n");
    exit(1);
}
