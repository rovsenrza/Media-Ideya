<?php
declare(strict_types=1);

$opts = getopt('', [
	'config:',
	'url:',
	'icon:',
	'dbconfig:',
	'db-host:',
	'db-name:',
	'db-user:',
	'db-pass:',
]);

if (!empty($opts['config'])) {
	$path = $opts['config'];
	$url = rtrim($opts['url'] ?? '', '/') . '/';
	$icon = $opts['icon'] ?? ($url . 'templates/MediaIdeya/images/media-ideya-logo.png');
	$src = file_get_contents($path);
	if ($src === false) {
		fwrite(STDERR, "Cannot read {$path}\n");
		exit(1);
	}
	$keys = [
		'http_home_url' => $url,
		'site_icon' => $icon,
		'only_ssl' => '1',
		'allow_cache' => '1',
		'display_php_errors' => '0',
	];
	foreach ($keys as $key => $value) {
		$pattern = "/'" . preg_quote($key, '/') . "'\\s*=>\\s*'[^']*'/";
		$replacement = "'" . $key . "' => '" . addslashes((string) $value) . "'";
		$new = preg_replace($pattern, $replacement, $src, 1, $count);
		if (!$count || $new === null) {
			fwrite(STDERR, "Key not updated: {$key}\n");
			exit(1);
		}
		$src = $new;
	}
	file_put_contents($path, $src);
	exit(0);
}

if (!empty($opts['dbconfig'])) {
	$path = $opts['dbconfig'];
	$host = $opts['db-host'] ?? 'localhost';
	$name = $opts['db-name'] ?? '';
	$user = $opts['db-user'] ?? '';
	$pass = $opts['db-pass'] ?? '';
	if ($name === '' || $user === '') {
		fwrite(STDERR, "db-name and db-user required\n");
		exit(1);
	}
	$src = file_get_contents($path);
	if ($src === false) {
		exit(1);
	}
	$map = [
		'DBHOST' => $host,
		'DBNAME' => $name,
		'DBUSER' => $user,
		'DBPASS' => $pass,
	];
	foreach ($map as $const => $value) {
		$pattern = '/define\s*\(\s*"' . preg_quote($const, '/') . '"\s*,\s*"[^"]*"\s*\);/';
		$replacement = 'define ("' . $const . '", "' . addslashes($value) . '");';
		$new = preg_replace($pattern, $replacement, $src, 1, $count);
		if (!$count || $new === null) {
			fwrite(STDERR, "DB constant not updated: {$const}\n");
			exit(1);
		}
		$src = $new;
	}
	file_put_contents($path, $src);
	exit(0);
}

fwrite(STDERR, "Usage: mi-ftp-prepare.php --config ... | --dbconfig ...\n");
exit(1);
