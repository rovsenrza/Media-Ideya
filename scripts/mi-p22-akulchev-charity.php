<?php
/**
 * Media-Ideya P22 — new On-pack case «Акульчев × Яндекс Плюс» + real gratitude letters.
 *
 *  - Creates (or refreshes) case akulchev-yandex-plus in category 2, service On-packing. Images are
 *    screenshots of promo-akulchev.ru, staged from templates/MediaIdeya/images/cases/on-pack/akulchev-yandex-plus/
 *    into uploads/posts/media-ideya/on-pack/akulchev-yandex-plus/ (01 = hero, 02–08 = case_gallery).
 *  - Fills the first three placeholder letters of «Благотворительность» (about-certificate-1..3) with the
 *    scans from templates/MediaIdeya/images/charity/letter-N.webp. The About scroll shows the first 9 posts
 *    by date, so the placeholders are reused instead of adding posts that would fall outside the limit.
 *
 * CLI:  php scripts/mi-p22-akulchev-charity.php [dry|apply|rollback]
 * HTTP: scripts/mi-run-remote.sh scripts/mi-p22-akulchev-charity.php [dry|apply|rollback]
 */
declare(strict_types=1);

define('MI_RUNNER_FILE', __FILE__);
require __DIR__ . '/mi-runner-lib.php';

const MI_P22_CASE_SLUG = 'akulchev-yandex-plus';
const MI_P22_CASE_GROUP = 'on-pack/akulchev-yandex-plus';
const MI_P22_CASE_IMAGES = 8;
const MI_P22_SERVICE_SLUG = 'service-on-packing';

$miP22Case = [
	'title' => 'Акульчев × Яндекс Плюс',
	'date' => '2026-10-08 10:00:00',
	'tag' => 'on-pack',
	'case_brand' => 'Акульчев × Яндекс Плюс',
	'case_hero_alt' => 'Промоакция Акульчев × Яндекс Плюс',
	'case_summary' => 'С 15 сентября 2026 по 15 июля 2027 года покупатели продукции «Акульчев» активируют промокод Яндекс Плюс и участвуют в розыгрыше.',
	'case_flow_title' => 'Как участвовать',
	'case_flow_1' => 'Купить продукцию бренда «Акульчев» с акционной отметкой и сохранить упаковку',
	'case_flow_2' => 'Отсканировать QR-код на упаковке и получить промокод на Яндекс Плюс',
	'case_flow_3' => 'Активировать промокод и получить до 30 дней мультиподписки Яндекс Плюс',
	'case_flow_4' => 'Заполнить данные: зарегистрироваться или войти в Яндекс ID',
	'case_flow_5' => 'Участвовать в розыгрыше Яндекс Станций, товаров RED SOLUTION и 100 000 баллов Плюса',
	'case_prizes' => "Розыгрыш\nПромокод на 100 000 баллов Плюса — 1 шт.\nНаушники AirPods Pro 2 — 2 шт.\nЯндекс Станция Лайт — 6 шт.\nУмный робот RED SOLUTION LOONA — 1 шт.\nГриль-духовка RED SOLUTION G850P — 2 шт.\nУмный очиститель воздуха RED SOLUTION UMI — 3 шт.\nУмный робот-пылесос RED SOLUTION RV-RL6000S Wi-Fi — 4 шт.\nРучной отпариватель RED SOLUTION HS700 — 4 шт.\nЧайник RED SOLUTION COLORSENSE AM120D — 4 шт.",
	'case_cta_title' => 'Хотите запустить промоакцию?',
	'case_cta_text' => 'Разрабатываем механику, сайт акции и коммуникацию с участниками под задачи бренда.',
];

$miP22Letters = [
	'about-certificate-1' => ['title' => 'Российский детский фонд, Брянское отделение — 2025', 'file' => 'letter-1.webp'],
	'about-certificate-2' => ['title' => 'АНО «Поколения» — студия инклюзивного танца «Равный танец»', 'file' => 'letter-2.webp'],
	'about-certificate-3' => ['title' => 'Российский детский фонд, Брянское отделение — 2026', 'file' => 'letter-3.webp'],
];

/** Copies a theme image into uploads/posts/ (atomic); returns the uploads-relative path. */
function mi_p22_stage(string $themeRelative, string $postRelative, bool $write): string {
	$source = ROOT_DIR . '/templates/MediaIdeya/images/' . $themeRelative;
	$target = ROOT_DIR . '/uploads/posts/' . $postRelative;
	if (!is_file($source)) {
		throw new RuntimeException("Missing asset: images/{$themeRelative}");
	}
	if ($write && (!is_file($target) || hash_file('sha256', $source) !== hash_file('sha256', $target))) {
		$dir = dirname($target);
		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
			throw new RuntimeException("Cannot create {$dir}");
		}
		$tmp = $target . '.p22.tmp';
		if (!copy($source, $tmp) || !rename($tmp, $target)) {
			@unlink($tmp);
			throw new RuntimeException("Cannot stage {$postRelative}");
		}
		@chmod($target, 0644);
	}
	return $postRelative;
}

try {
	mi_log('Media-Ideya P22 — Акульчев case + gratitude letters · mode: ' . $miMode);
	mi_log();

	if ($miMode === 'rollback') {
		$backup = mi_backup_read('p22');
		foreach ($backup['letters'] as $id => $row) {
			mi_q('UPDATE ' . PREFIX . "_post SET title='" . mi_esc($row['title']) . "', xfields='" . mi_esc($row['xfields']) . "' WHERE id='" . (int) $id . "'");
		}
		if (!empty($backup['case'])) {
			$id = (int) $backup['case']['id'];
			mi_q('UPDATE ' . PREFIX . "_post SET title='" . mi_esc($backup['case']['title']) . "', xfields='" . mi_esc($backup['case']['xfields']) . "' WHERE id='{$id}'");
			mi_log("Case #{$id} restored.");
		} elseif (!empty($backup['created_case_id'])) {
			$id = (int) $backup['created_case_id'];
			foreach (['_post' => 'id', '_post_extras' => 'news_id', '_post_extras_cats' => 'news_id', '_tags' => 'news_id'] as $table => $column) {
				mi_q('DELETE FROM ' . PREFIX . "{$table} WHERE {$column}='{$id}'");
			}
			mi_log("Case #{$id} removed.");
		}
		mi_purge_cache();
		@rename(mi_backup_path('p22'), mi_backup_path('p22') . '.rolled-back-' . date('YmdHis'));
		mi_log(count($backup['letters']) . ' letters restored. Rollback done.');
		exit;
	}
	if ($miMode !== 'dry' && $miMode !== 'apply') {
		exit("Mode {$miMode} is not used by P22\n");
	}
	$write = $miMode === 'apply';

	$fields = mi_registry_load()['fields'];
	foreach (['case_gallery', 'case_service', 'case_prizes', 'case_flow_5', 'charity_certificate'] as $name) {
		if (!isset($fields[$name])) {
			throw new RuntimeException("Field {$name} is missing from the registry");
		}
	}
	$service = mi_one('SELECT id FROM ' . PREFIX . "_post WHERE alt_name='" . MI_P22_SERVICE_SLUG . "' AND FIND_IN_SET('4', category)");
	if (!$service) {
		throw new RuntimeException('Service ' . MI_P22_SERVICE_SLUG . ' not found');
	}
	$serviceValue = 's' . (int) $service['id'] . 's';
	if (strpos((string) $fields['case_service']['default'], $serviceValue . '|') === false) {
		throw new RuntimeException("Option {$serviceValue} is not in case_service");
	}

	/* ——— Case ——— */
	$values = [];
	$gallery = [];
	for ($n = 1; $n <= MI_P22_CASE_IMAGES; $n++) {
		$file = sprintf('%02d.webp', $n);
		$path = mi_p22_stage('cases/' . MI_P22_CASE_GROUP . '/' . $file, 'media-ideya/' . MI_P22_CASE_GROUP . '/' . $file, $write);
		if ($n === 1) {
			$values['image'] = $path;
		} else {
			$gallery[] = $path;
		}
	}
	foreach ($miP22Case as $key => $value) {
		if (str_starts_with($key, 'case_')) {
			$values[$key] = $value;
		}
	}
	$values['case_hero'] = $values['image'];
	$values['case_gallery'] = implode(',', $gallery);
	$values['case_service'] = $serviceValue;

	$existing = mi_one('SELECT id, title, xfields FROM ' . PREFIX . "_post WHERE alt_name='" . MI_P22_CASE_SLUG . "'");
	mi_log($existing ? "Case: refresh #{$existing['id']}" : 'Case: create «' . $miP22Case['title'] . '»');
	mi_log('  service ' . $serviceValue . ', hero + ' . count($gallery) . ' gallery images');

	/* ——— Letters ——— */
	$letters = [];
	foreach ($miP22Letters as $slug => $letter) {
		$row = mi_one('SELECT id, title, xfields FROM ' . PREFIX . "_post WHERE alt_name='" . mi_esc($slug) . "' AND FIND_IN_SET('13', category)");
		if (!$row) {
			throw new RuntimeException("Charity post {$slug} not found");
		}
		$xf = mi_xf_parse((string) $row['xfields']);
		$xf['charity_certificate'] = mi_p22_stage('charity/' . $letter['file'], 'media-ideya/charity/' . $letter['file'], $write);
		$letters[(int) $row['id']] = ['row' => $row, 'title' => $letter['title'], 'xfields' => mi_xf_build($xf)];
		mi_log(sprintf('Letter #%d «%s» → «%s», %s', $row['id'], $row['title'], $letter['title'], $xf['charity_certificate']));
	}
	mi_log();

	if (!$write) {
		mi_log('Dry run only. Re-run with apply to write.');
		exit;
	}

	$backup = ['created' => date('c'), 'letters' => [], 'case' => $existing ?: null, 'created_case_id' => null];
	foreach ($letters as $id => $letter) {
		$backup['letters'][$id] = ['title' => $letter['row']['title'], 'xfields' => $letter['row']['xfields']];
	}

	$xfields = mi_esc(mi_xf_build($values));
	$title = mi_esc($miP22Case['title']);
	$summary = mi_esc($miP22Case['case_summary']);
	$brand = mi_esc($miP22Case['case_brand']);
	$tag = mi_esc($miP22Case['tag']);
	if ($existing) {
		$id = (int) $existing['id'];
		mi_q('UPDATE ' . PREFIX . "_post SET date='" . mi_esc($miP22Case['date']) . "', short_story='{$summary}', xfields='{$xfields}', title='{$title}', descr='{$summary}', keywords='{$brand}', metatitle='{$title}' WHERE id='{$id}'");
	} else {
		$admin = mi_one('SELECT name FROM ' . USERPREFIX . "_users WHERE user_group='1' ORDER BY user_id ASC LIMIT 1");
		$author = mi_esc($admin['name'] ?? 'admin');
		$date = mi_esc($miP22Case['date']);
		mi_q('INSERT INTO ' . PREFIX . "_post (autor, date, short_story, full_story, xfields, title, descr, keywords, category, alt_name, comm_num, allow_comm, allow_main, approve, fixed, allow_br, symbol, tags, metatitle) VALUES ('{$author}', '{$date}', '{$summary}', '', '{$xfields}', '{$title}', '{$summary}', '{$brand}', '2', '" . MI_P22_CASE_SLUG . "', '0', '0', '0', '1', '0', '0', '', '{$tag}', '{$title}')");
		$id = (int) $db->insert_id();
		$backup['created_case_id'] = $id;
		mi_q('INSERT INTO ' . PREFIX . "_post_extras (news_id, allow_rate, votes, disable_index, related_ids, access, user_id, disable_search, need_pass, allow_rss, allow_rss_dzen, allowed_country, not_allowed_country) VALUES ('{$id}', '1', '0', '0', '', '', '0', '0', '0', '1', '1', '', '')");
		mi_q('INSERT INTO ' . PREFIX . "_post_extras_cats (news_id, cat_id) VALUES ('{$id}', '2')");
		mi_q('INSERT INTO ' . PREFIX . "_tags (tag, news_id) VALUES ('{$tag}', '{$id}')");
	}
	$wrote = mi_backup_write('p22', $backup);
	mi_log($wrote ? 'Backup: engine/data/mi-p22-backup.json' : 'Backup already exists — kept the original one.');

	foreach ($letters as $letterId => $letter) {
		mi_q('UPDATE ' . PREFIX . "_post SET title='" . mi_esc($letter['title']) . "', xfields='" . mi_esc($letter['xfields']) . "' WHERE id='{$letterId}'");
	}
	mi_purge_cache();
	mi_log("Case #{$id} written, " . count($letters) . ' letters updated, cache purged. Done.');
} catch (Throwable $error) {
	mi_log('[error] ' . $error->getMessage());
	exit(1);
}
