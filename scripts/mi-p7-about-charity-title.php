<?php
/**
 * Media-Ideya P7 — retitle the About reviews block to «Благотворительность».
 *
 * Updates xfield about_reviews_title on the `about-text` settings post
 * (category 6). Idempotent; touches nothing else.
 *
 * CLI:
 *   php scripts/mi-p7-about-charity-title.php            dry run
 *   php scripts/mi-p7-about-charity-title.php --apply
 * HTTP (production has no shell):
 *   /scripts/mi-p7-about-charity-title.php?key=mi-about-charity-2026
 *   /scripts/mi-p7-about-charity-title.php?key=mi-about-charity-2026&apply=1
 */
declare(strict_types=1);

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__DIR__));
define('ENGINE_DIR', ROOT_DIR . '/engine');

const MI_ABOUT_SLUG = 'about-text';
const MI_REVIEWS_TITLE = 'Благотворительность';

if (PHP_SAPI === 'cli') {
	$apply = in_array('--apply', $argv, true);
} else {
	if (($_GET['key'] ?? '') !== 'mi-about-charity-2026') {
		http_response_code(403);
		exit('Forbidden');
	}
	header('Content-Type: text/plain; charset=utf-8');
	$apply = ($_GET['apply'] ?? '') === '1';
}

require_once ENGINE_DIR . '/classes/plugins.class.php';
if (!isset($db) || !is_object($db)) {
	exit("DB connection failed\n");
}

$log = static function (string $line = ''): void {
	echo $line . PHP_EOL;
};

function mi_p7_xf_parse(string $raw): array {
	$fields = [];
	foreach (explode('||', $raw) as $part) {
		if ($part === '') {
			continue;
		}
		$pos = strpos($part, '|');
		if ($pos === false) {
			continue;
		}
		$fields[str_replace('&#124;', '|', substr($part, 0, $pos))] = str_replace('&#124;', '|', substr($part, $pos + 1));
	}
	return $fields;
}

function mi_p7_xf_build(array $fields): string {
	$parts = [];
	foreach ($fields as $name => $value) {
		if ($value === null || $value === '') {
			continue;
		}
		$parts[] = str_replace('|', '&#124;', (string) $name) . '|' . str_replace('|', '&#124;', (string) $value);
	}
	return implode('||', $parts);
}

$log('Media-Ideya P7 — about reviews title (' . ($apply ? 'APPLY' : 'dry run') . ')');
$log();

try {
	$slugSql = $db->safesql(MI_ABOUT_SLUG);
	$row = $db->super_query('SELECT id, title, xfields FROM ' . PREFIX . "_post WHERE alt_name='{$slugSql}'");
	if (!$row) {
		throw new RuntimeException('Post «' . MI_ABOUT_SLUG . '» not found — run mi-p4-pages-content.php first');
	}
	$fields = mi_p7_xf_parse((string) $row['xfields']);
	$current = $fields['about_reviews_title'] ?? '';
	$log("[post] #{$row['id']} «{$row['title']}»");
	$log('[current] ' . ($current === '' ? '(empty)' : $current));

	if ($current === MI_REVIEWS_TITLE) {
		$log('[ok] already «' . MI_REVIEWS_TITLE . '»');
	} else {
		$log('[set] about_reviews_title → «' . MI_REVIEWS_TITLE . '»');
		if ($apply) {
			$fields['about_reviews_title'] = MI_REVIEWS_TITLE;
			$xfSql = $db->safesql(mi_p7_xf_build($fields));
			$db->query('UPDATE ' . PREFIX . "_post SET xfields='{$xfSql}' WHERE id='" . (int) $row['id'] . "'");
			$removed = 0;
			foreach (glob(ENGINE_DIR . '/cache/*') ?: [] as $file) {
				if (is_file($file) && basename($file) !== '.htaccess' && @unlink($file)) {
					$removed++;
				}
			}
			$log("[ok] updated, cache purged ({$removed} files)");
		} else {
			$log('Dry run — nothing written.');
		}
	}
} catch (Throwable $e) {
	$log('[error] ' . $e->getMessage());
	exit(1);
}
