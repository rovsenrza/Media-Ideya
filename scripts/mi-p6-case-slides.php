<?php
/**
 * Media-Ideya P6 — extra slides for the case popup gallery (Swiper).
 *
 * Adds image xfields case_slide_2 … case_slide_5 (category 2) next to the
 * existing case_hero. Merges the registry only; never deletes anything.
 *
 * CLI:
 *   php scripts/mi-p6-case-slides.php            dry run
 *   php scripts/mi-p6-case-slides.php --apply
 * HTTP (production has no shell):
 *   /scripts/mi-p6-case-slides.php?key=mi-case-slides-2026
 *   /scripts/mi-p6-case-slides.php?key=mi-case-slides-2026&apply=1
 */
declare(strict_types=1);

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__DIR__));
define('ENGINE_DIR', ROOT_DIR . '/engine');

const MI_CASE_CAT_ID = '2';
const MI_CASE_SLIDES = 5;

if (PHP_SAPI === 'cli') {
	$apply = in_array('--apply', $argv, true);
} else {
	if (($_GET['key'] ?? '') !== 'mi-case-slides-2026') {
		http_response_code(403);
		exit('Forbidden');
	}
	header('Content-Type: text/plain; charset=utf-8');
	$apply = ($_GET['apply'] ?? '') === '1';
}

$log = static function (string $line = ''): void {
	echo $line . PHP_EOL;
};

$log('Media-Ideya P6 — case slides (' . ($apply ? 'APPLY' : 'dry run') . ')');
$log();

try {
	$xfieldsPath = ENGINE_DIR . '/data/xfields.json';
	if (!is_readable($xfieldsPath)) {
		throw new RuntimeException("xfields.json is not readable: {$xfieldsPath}");
	}
	$registry = json_decode((string) file_get_contents($xfieldsPath), true, 512, JSON_THROW_ON_ERROR);
	if (!isset($registry['fields']) || !is_array($registry['fields'])) {
		throw new RuntimeException('Invalid xfields registry');
	}
	if (!isset($registry['fields']['case_hero'])) {
		throw new RuntimeException('xfield «case_hero» is missing — run mi-p4-pages-content.php first');
	}

	$changed = false;
	$template = $registry['fields']['case_hero'];
	$template['category'] = MI_CASE_CAT_ID;
	$template['not_required'] = 1;

	for ($i = 2; $i <= MI_CASE_SLIDES; $i++) {
		$name = "case_slide_{$i}";
		if (isset($registry['fields'][$name])) {
			$log("[ok] xfield «{$name}» exists");
			continue;
		}
		$field = $template;
		$field['name'] = $name;
		$field['description'] = "Кейс: слайд {$i}";
		$field['hint'] = 'Дополнительный кадр галереи в попапе кейса (1443×915). Первый слайд — «большое изображение».';
		$registry['fields'][$name] = $field;
		$changed = true;
		$log("[add] xfield «{$name}» (image, category " . MI_CASE_CAT_ID . ')');
	}

	if ($changed && $apply) {
		$json = json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$tmp = $xfieldsPath . '.p6.tmp';
		if (file_put_contents($tmp, $json, LOCK_EX) === false || !rename($tmp, $xfieldsPath)) {
			@unlink($tmp);
			throw new RuntimeException('Cannot write xfields.json');
		}
		$log('[ok] xfields.json merged');

		$removed = 0;
		foreach (['system', ''] as $dir) {
			$path = ENGINE_DIR . '/cache' . ($dir !== '' ? "/{$dir}" : '');
			foreach (glob($path . '/*') ?: [] as $file) {
				if (is_file($file) && basename($file) !== '.htaccess' && @unlink($file)) {
					$removed++;
				}
			}
		}
		$log("[ok] cache purged ({$removed} files)");
	} elseif (!$changed) {
		$log('Nothing to do.');
	} else {
		$log('Dry run — nothing written.');
	}
} catch (Throwable $e) {
	$log('[error] ' . $e->getMessage());
	exit(1);
}
