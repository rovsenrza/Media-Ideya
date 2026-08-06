<?php
/**
 * Media-Ideya — purge DLE caches after deploy.
 * HTTP: /scripts/mi-purge-cache.php?key=mi-purge-2026
 */
declare(strict_types=1);

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__DIR__));
define('ENGINE_DIR', ROOT_DIR . '/engine');

$key = $_GET['key'] ?? '';
if ($key !== 'mi-purge-2026') {
	http_response_code(403);
	exit('Forbidden');
}
header('Content-Type: text/plain; charset=utf-8');

require_once ENGINE_DIR . '/classes/plugins.class.php';
if (!isset($db) || !is_object($db)) {
	exit("DB connection failed\n");
}

$log = static function (string $line): void {
	echo $line . "\n";
};

$log('Media-Ideya cache purge');

$removed = 0;

foreach (glob(ENGINE_DIR . '/cache/*.php') ?: [] as $file) {
	if (@unlink($file)) {
		$removed++;
	}
}

$systemJson = [
	ENGINE_DIR . '/cache/system/category.json',
	ENGINE_DIR . '/cache/system/usergroup.json',
	ENGINE_DIR . '/cache/system/plugins.json',
];

foreach ($systemJson as $file) {
	if (is_file($file) && @unlink($file)) {
		$removed++;
		$log('  removed ' . basename(dirname($file)) . '/' . basename($file));
	}
}

$compiled = ENGINE_DIR . '/cache/templates';
if (is_dir($compiled)) {
	foreach (glob($compiled . '/*.php') ?: [] as $file) {
		if (@unlink($file)) {
			$removed++;
		}
	}
}

if (function_exists('clear_cache')) {
	clear_cache();
	$log('  clear_cache()');
}

$log("Removed {$removed} cache file(s)");
$log('Done.');
