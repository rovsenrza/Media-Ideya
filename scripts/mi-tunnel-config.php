<?php
/**
 * Media-Ideya — tunnel config helper (internal).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	exit("CLI only\n");
}

$action = $argv[1] ?? '';
$key = $argv[2] ?? '';
$value = $argv[3] ?? '';

$configPath = dirname(__DIR__) . '/engine/data/config.php';
if (!is_readable($configPath) || !is_writable($configPath)) {
	fwrite(STDERR, "config.php not writable\n");
	exit(1);
}

$src = file_get_contents($configPath);
if ($src === false) {
	fwrite(STDERR, "config read failed\n");
	exit(1);
}

if ($action === 'get') {
	if (!preg_match("/'" . preg_quote($key, '/') . "'\\s*=>\\s*'([^']*)'/", $src, $m)) {
		exit(1);
	}
	echo $m[1];
	exit(0);
}

if ($action === 'set' && $key !== '') {
	$pattern = "/'" . preg_quote($key, '/') . "'\\s*=>\\s*'[^']*'/";
	$replacement = "'" . $key . "' => '" . addslashes($value) . "'";
	$new = preg_replace($pattern, $replacement, $src, 1, $count);
	if (!$count || $new === null) {
		fwrite(STDERR, "config key not updated: {$key}\n");
		exit(1);
	}
	file_put_contents($configPath, $new);
	exit(0);
}

fwrite(STDERR, "Usage: php mi-tunnel-config.php get|set <key> [value]\n");
exit(1);
