<?php
/**
 * Media-Ideya P4 — DLE-managed content for the four inner pages.
 *
 * Dry run (default):
 *   /Applications/MAMP/bin/php/php8.3.30/bin/php scripts/mi-p4-pages-content.php
 * Apply:
 *   /Applications/MAMP/bin/php/php8.3.30/bin/php scripts/mi-p4-pages-content.php --apply
 *
 * This migration is intentionally CLI-only. It never deletes content, merges
 * xfield definitions, and seeds defaults only on the first successful apply.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	exit("CLI only\n");
}

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__DIR__));
define('ENGINE_DIR', ROOT_DIR . '/engine');

$allowedArgs = ['', '--apply', '--help'];
foreach (array_slice($argv, 1) as $arg) {
	if (!in_array($arg, $allowedArgs, true)) {
		fwrite(STDERR, "Unknown option: {$arg}\nUse --apply or run without options for a dry run.\n");
		exit(2);
	}
}

if (in_array('--help', $argv, true)) {
	echo "Media-Ideya P4 inner-page content migration\n\n";
	echo "Dry run: php scripts/mi-p4-pages-content.php\n";
	echo "Apply:   php scripts/mi-p4-pages-content.php --apply\n";
	exit(0);
}

$apply = in_array('--apply', $argv, true);

require_once ENGINE_DIR . '/classes/plugins.class.php';
if (!isset($db) || !is_object($db)) {
	fwrite(STDERR, "DB connection failed\n");
	exit(2);
}

$log = static function (string $line = ''): void {
	echo $line . PHP_EOL;
};

function mi_p4_query(string $sql) {
	global $db;
	$result = $db->query($sql, false);
	if ($result === false) {
		$queryErrors = $db->query_errors_list;
		$lastError = end($queryErrors);
		$message = is_array($lastError) && !empty($lastError['error'])
			? (string) $lastError['error']
			: 'unknown database error';
		throw new RuntimeException('Database query failed: ' . $message);
	}
	return $result;
}

function mi_p4_query_no_throw(string $sql): void {
	global $db;
	$db->query($sql, false);
}

function mi_p4_one(string $sql): array {
	global $db;
	$result = mi_p4_query($sql);
	$row = $db->get_row($result);
	$db->free($result);
	return is_array($row) ? $row : [];
}

function mi_p4_rows(string $sql): array {
	global $db;
	$rows = [];
	$result = mi_p4_query($sql);
	while ($row = $db->get_row($result)) {
		$rows[] = $row;
	}
	$db->free($result);
	return $rows;
}

function mi_p4_xf_parse(string $raw): array {
	$fields = [];
	foreach (explode('||', $raw) as $part) {
		if ($part === '') {
			continue;
		}
		$separator = strpos($part, '|');
		if ($separator === false) {
			continue;
		}
		$name = substr($part, 0, $separator);
		$value = substr($part, $separator + 1);
		$fields[str_replace('&#124;', '|', $name)] = str_replace('&#124;', '|', $value);
	}
	return $fields;
}

function mi_p4_xf_build(array $fields): string {
	global $db;
	$parts = [];
	foreach ($fields as $name => $value) {
		if ($value === null || $value === '') {
			continue;
		}
		$parts[] = str_replace('|', '&#124;', (string) $name) . '|' . str_replace('|', '&#124;', (string) $value);
	}
	return $db->safesql(implode('||', $parts));
}

function mi_p4_category_ids(string $raw): array {
	return array_values(array_filter(array_map('intval', explode(',', $raw))));
}

function mi_p4_assert_category(int $id, string $slug, string $name, string $description, bool $create): void {
	global $db;
	$slugSql = $db->safesql($slug);
	$rows = mi_p4_rows(
		'SELECT id, alt_name FROM ' . PREFIX . "_category WHERE id='{$id}' OR alt_name='{$slugSql}' ORDER BY id"
	);

	if (count($rows) > 1) {
		throw new RuntimeException("Category collision for id {$id} / {$slug}");
	}

	if ($rows) {
		$row = $rows[0];
		if ((int) $row['id'] !== $id || $row['alt_name'] !== $slug) {
			throw new RuntimeException("Category mapping mismatch for id {$id} / {$slug}");
		}
		return;
	}

	if (!$create) {
		throw new RuntimeException("Required category is missing: {$id} / {$slug}");
	}

	$nameSql = $db->safesql($name);
	$descriptionSql = $db->safesql($description);
	mi_p4_query(
		'INSERT INTO ' . PREFIX . "_category
		(id, parentid, posi, name, alt_name, descr, keywords, metatitle, fulldescr, active, disable_main, disable_search, short_tpl, full_tpl, schema_org)
		VALUES ('{$id}', '0', '{$id}', '{$nameSql}', '{$slugSql}', '{$descriptionSql}', '{$nameSql}', '{$nameSql}', '{$descriptionSql}', '1', '1', '1', '', '', '0')"
	);
}

function mi_p4_find_post(string $slug, bool $required = false): ?array {
	global $db;
	$slugSql = $db->safesql($slug);
	$rows = mi_p4_rows(
		'SELECT id, category, title, short_story, full_story, xfields, tags FROM ' . PREFIX . "_post WHERE alt_name='{$slugSql}'"
	);
	if (count($rows) > 1) {
		throw new RuntimeException("Duplicate post slug: {$slug}");
	}
	if (!$rows) {
		if ($required) {
			throw new RuntimeException("Required post is missing: {$slug}. Run the base CMS setup first.");
		}
		return null;
	}
	return $rows[0];
}

function mi_p4_sync_post_relations(int $postId, int $categoryId, bool $repair): bool {
	$post = mi_p4_one('SELECT category FROM ' . PREFIX . "_post WHERE id='{$postId}'");
	if (!$post) {
		throw new RuntimeException("Post {$postId} disappeared during relation validation");
	}
	$postCategories = mi_p4_category_ids((string) $post['category']);
	if (!in_array($categoryId, $postCategories, true)) {
		throw new RuntimeException("Post {$postId} is not assigned to expected category {$categoryId}");
	}

	$changed = false;
	$extras = (int) (mi_p4_one(
		'SELECT COUNT(*) AS total FROM ' . PREFIX . "_post_extras WHERE news_id='{$postId}'"
	)['total'] ?? 0);
	if ($extras > 1) {
		throw new RuntimeException("Duplicate post_extras rows for post {$postId}");
	}
	if ($extras === 0) {
		$changed = true;
		if ($repair) {
			mi_p4_query(
				'INSERT INTO ' . PREFIX . "_post_extras
				(news_id, allow_rate, votes, disable_index, related_ids, access, user_id, disable_search, need_pass, allow_rss, allow_rss_dzen, allowed_country, not_allowed_country)
				VALUES ('{$postId}', '1', '0', '0', '', '', '0', '0', '0', '1', '1', '', '')"
			);
		}
	}

	$mappingRows = mi_p4_rows(
		'SELECT cat_id, COUNT(*) AS total FROM ' . PREFIX . "_post_extras_cats WHERE news_id='{$postId}' GROUP BY cat_id"
	);
	$mappedCategories = [];
	foreach ($mappingRows as $mappingRow) {
		$mappedCategory = (int) $mappingRow['cat_id'];
		if ((int) $mappingRow['total'] !== 1) {
			throw new RuntimeException("Duplicate category mapping for post {$postId}, category {$mappedCategory}");
		}
		$mappedCategories[] = $mappedCategory;
	}

	$unexpectedMappings = array_diff($mappedCategories, $postCategories);
	if ($unexpectedMappings) {
		throw new RuntimeException(
			"Post {$postId} has category mappings absent from dle_post.category: " . implode(',', $unexpectedMappings)
		);
	}
	$missingMappings = array_diff($postCategories, $mappedCategories);
	foreach ($missingMappings as $missingCategory) {
		$changed = true;
		if ($repair) {
			mi_p4_query(
				'INSERT INTO ' . PREFIX . "_post_extras_cats (news_id, cat_id) VALUES ('{$postId}', '" . (int) $missingCategory . "')"
			);
		}
	}

	return $changed;
}

function mi_p4_merge_post_fields(array $post, int $categoryId, array $defaults, bool $seedContent): void {
	global $db;
	$postId = (int) $post['id'];
	if (!in_array($categoryId, mi_p4_category_ids((string) $post['category']), true)) {
		throw new RuntimeException("Post {$postId} is not assigned to expected category {$categoryId}");
	}

	if ($seedContent) {
		$fields = mi_p4_xf_parse((string) $post['xfields']);
		$changed = false;
		foreach ($defaults as $name => $value) {
			if (!array_key_exists($name, $fields)) {
				$fields[$name] = $value;
				$changed = true;
			}
		}
		if ($changed) {
			$xfieldsSql = mi_p4_xf_build($fields);
			mi_p4_query('UPDATE ' . PREFIX . "_post SET xfields='{$xfieldsSql}' WHERE id='{$postId}'");
		}
	}

	mi_p4_sync_post_relations($postId, $categoryId, true);
}

function mi_p4_insert_post(
	string $author,
	int $categoryId,
	string $title,
	string $slug,
	string $shortStory,
	string $fullStory,
	array $xfields,
	string $date,
	string $tags = ''
): array {
	global $db;
	$titleSql = $db->safesql($title);
	$slugSql = $db->safesql($slug);
	$shortSql = $db->safesql($shortStory);
	$fullSql = $db->safesql($fullStory);
	$descriptionSql = $db->safesql(mb_substr(strip_tags($shortStory), 0, 300));
	$xfieldsSql = mi_p4_xf_build($xfields);
	$dateSql = $db->safesql($date);
	$tagsSql = $db->safesql($tags);

	mi_p4_query(
		'INSERT INTO ' . PREFIX . "_post
		(autor, date, short_story, full_story, xfields, title, descr, keywords, category, alt_name, comm_num, allow_comm, allow_main, approve, fixed, allow_br, symbol, tags, metatitle)
		VALUES ('{$author}', '{$dateSql}', '{$shortSql}', '{$fullSql}', '{$xfieldsSql}', '{$titleSql}', '{$descriptionSql}', '{$titleSql}', '{$categoryId}', '{$slugSql}', '0', '0', '0', '1', '0', '0', '', '{$tagsSql}', '{$titleSql}')"
	);
	$postId = (int) $db->insert_id();
	mi_p4_sync_post_relations($postId, $categoryId, true);

	return [
		'id' => $postId,
		'category' => (string) $categoryId,
		'title' => $title,
		'short_story' => $shortStory,
		'full_story' => $fullStory,
		'xfields' => mi_p4_xf_build($xfields),
		'tags' => $tags,
	];
}

function mi_p4_ensure_post(
	string $author,
	int $categoryId,
	string $title,
	string $slug,
	string $shortStory,
	string $fullStory,
	array $xfields,
	string $date,
	bool $seedContent,
	string $tags = ''
): ?array {
	$post = mi_p4_find_post($slug);
	if (!$post) {
		if (!$seedContent) {
			return null;
		}
		return mi_p4_insert_post(
			$author,
			$categoryId,
			$title,
			$slug,
			$shortStory,
			$fullStory,
			$xfields,
			$date,
			$tags
		);
	}
	mi_p4_merge_post_fields($post, $categoryId, $xfields, $seedContent);
	return $post;
}

function mi_p4_ensure_tag(array $post, string $tag): void {
	global $db;
	$postId = (int) $post['id'];
	$tags = array_values(array_filter(array_map('trim', explode(',', (string) $post['tags']))));
	if (!in_array($tag, $tags, true)) {
		$tags[] = $tag;
		$tagsSql = $db->safesql(implode(', ', $tags));
		mi_p4_query('UPDATE ' . PREFIX . "_post SET tags='{$tagsSql}' WHERE id='{$postId}'");
	}

	$mappingRows = mi_p4_rows(
		'SELECT tag, COUNT(*) AS total FROM ' . PREFIX . "_tags WHERE news_id='{$postId}' GROUP BY tag"
	);
	$mappedTags = [];
	foreach ($mappingRows as $mappingRow) {
		$mappedTag = trim((string) $mappingRow['tag']);
		if ((int) $mappingRow['total'] !== 1) {
			throw new RuntimeException("Duplicate tag mapping for post {$postId}: {$mappedTag}");
		}
		$mappedTags[] = $mappedTag;
	}
	$unexpectedTags = array_diff($mappedTags, $tags);
	if ($unexpectedTags) {
		throw new RuntimeException(
			"Post {$postId} has tag mappings absent from dle_post.tags: " . implode(',', $unexpectedTags)
		);
	}
	foreach (array_diff($tags, $mappedTags) as $missingTag) {
		$missingTagSql = $db->safesql($missingTag);
		mi_p4_query('INSERT INTO ' . PREFIX . "_tags (news_id, tag) VALUES ('{$postId}', '{$missingTagSql}')");
	}
}

function mi_p4_append_category_permissions(string $raw, array $ids): string {
	if (trim($raw) === 'all') {
		return 'all';
	}
	$current = array_values(array_filter(array_map('intval', explode(',', $raw))));
	foreach ($ids as $id) {
		if (!in_array($id, $current, true)) {
			$current[] = $id;
		}
	}
	sort($current, SORT_NUMERIC);
	return implode(',', $current);
}

function mi_p4_field(string $name, string $description, string $type, string $category, string $hint = ''): array {
	$field = [
		'name' => $name,
		'description' => $description,
		'hint' => $hint,
		'type' => $type,
		'category' => $category,
		'group' => '',
		'default' => '',
		'not_required' => 1,
		'allow_add_usergroups' => '',
		'use_as_links' => 0,
		'use_editor' => 0,
		'safe_mode' => $type === 'text' || $type === 'textarea' ? 1 : 0,
		'min' => '',
		'max' => '',
		'allow_multi' => 0,
		'select_separator' => '',
		'links_separator' => '',
		'files_ext' => '',
		'file_max_size' => '',
		'is_public' => 0,
		'max_files' => '',
		'max_size' => '',
		'condition' => '',
		'make_watermark' => 0,
		'make_thumb' => 0,
		'image_size' => '',
		'image_max_size' => '',
		'thumb_size' => '',
		'image_sizes' => '',
		'use_opengraph' => 0,
		'image_side' => '',
		'thumb_side' => '',
		'max_images' => '',
		'storage' => '',
	];

	if ($type === 'image') {
		$field['safe_mode'] = 0;
		$field['image_max_size'] = '8192';
		$field['image_sizes'] = '4096x4096';
		$field['storage'] = -1;
	}

	if ($type === 'yesorno') {
		$field['safe_mode'] = 0;
	}

	return $field;
}

function mi_p4_stage_file(string $directory, string $prefix, string $contents, ?array $metadata = null, int $fallbackMode = 0644): string {
	$tempPath = tempnam($directory, $prefix);
	if ($tempPath === false) {
		throw new RuntimeException("Cannot create a temporary file in {$directory}");
	}

	try {
		if (file_put_contents($tempPath, $contents, LOCK_EX) === false) {
			throw new RuntimeException("Cannot write temporary file: {$tempPath}");
		}

		if ($metadata !== null) {
			$owner = (int) ($metadata['uid'] ?? -1);
			$group = (int) ($metadata['gid'] ?? -1);
			if ($owner >= 0 && fileowner($tempPath) !== $owner && !@chown($tempPath, $owner)) {
				throw new RuntimeException("Cannot preserve file owner for {$tempPath}");
			}
			if ($group >= 0 && filegroup($tempPath) !== $group && !@chgrp($tempPath, $group)) {
				throw new RuntimeException("Cannot preserve file group for {$tempPath}");
			}
			$mode = (int) ($metadata['mode'] ?? $fallbackMode) & 0777;
		} else {
			$mode = $fallbackMode;
		}

		if (!@chmod($tempPath, $mode)) {
			throw new RuntimeException("Cannot set file permissions for {$tempPath}");
		}
	} catch (Throwable $error) {
		@unlink($tempPath);
		throw $error;
	}

	return $tempPath;
}

function mi_p4_stage_asset(string $themeRelative, string $fileName, bool $apply, array &$stagedAssets): string {
	$source = ROOT_DIR . '/templates/MediaIdeya/images/' . ltrim($themeRelative, '/');
	if (!is_file($source)) {
		throw new RuntimeException("Seed asset is missing: {$themeRelative}");
	}
	$relative = 'media-ideya/' . $fileName;
	$directory = ROOT_DIR . '/uploads/posts/media-ideya';
	$destination = $directory . '/' . $fileName;

	if (is_file($destination)) {
		if (hash_file('sha256', $source) !== hash_file('sha256', $destination)) {
			throw new RuntimeException("Existing seed asset differs from its source: {$fileName}");
		}
		return $relative;
	}
	if (!$apply) {
		return $relative;
	}
	if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
		throw new RuntimeException("Cannot create seed asset directory: {$directory}");
	}
	$tempAsset = tempnam($directory, 'mi-p4-asset-');
	if ($tempAsset === false) {
		throw new RuntimeException("Cannot stage seed asset: {$fileName}");
	}
	if (!copy($source, $tempAsset)) {
		@unlink($tempAsset);
		throw new RuntimeException("Cannot copy seed asset: {$fileName}");
	}
	if (hash_file('sha256', $source) !== hash_file('sha256', $tempAsset)) {
		@unlink($tempAsset);
		throw new RuntimeException("Seed asset checksum failed: {$fileName}");
	}
	if (!@chmod($tempAsset, 0644)) {
		@unlink($tempAsset);
		throw new RuntimeException("Cannot set seed asset permissions: {$fileName}");
	}
	$stagedAssets[] = ['temp' => $tempAsset, 'destination' => $destination];
	return $relative;
}

$categoryMap = [
	2 => ['keysy', 'Кейсы', 'Кейсы и проекты Media Ideya', false],
	4 => ['uslugi-home', 'Услуги (главная)', 'Карточки услуг на главной', false],
	6 => ['home-about', 'О компании (текст)', 'Текст блока о компании', false],
	8 => ['site-contacts', 'Контакты', 'Footer и контакты', false],
	10 => ['about-team', 'О компании — команда', 'Роли команды. Заголовок, краткий текст и изображение; порядок по дате.', true],
	11 => ['about-reviews', 'О компании — отзывы', 'До четырёх отзывов; порядок по дате.', true],
	12 => ['about-certificates', 'О компании — благодарности', 'До девяти благодарственных писем; порядок по дате.', true],
];
$staticTemplates = [
	'o-kompanii' => 'static-about',
	'uslugi' => 'static-services',
];

$xfieldsPath = ENGINE_DIR . '/data/xfields.json';
$statePath = ENGINE_DIR . '/data/mi-p4-pages-content.json';
$lockName = 'media_ideya_p4_pages_content';
$lockSql = $db->safesql($lockName);
$lockAcquired = false;
$transactionStarted = false;
$committed = false;
$xfieldsReplaced = false;
$originalXfieldsJson = '';
$originalXfieldsStat = null;
$tempXfieldsPath = '';
$tempStatePath = '';
$stagedAssets = [];
$installedAssets = [];
$editorPermissionsChanged = false;
$staticTemplatesChanged = false;

try {
	if ($apply) {
		$lock = mi_p4_one("SELECT GET_LOCK('{$lockSql}', 10) AS acquired");
		if ((int) ($lock['acquired'] ?? 0) !== 1) {
			throw new RuntimeException('Could not acquire the P4 migration lock');
		}
		$lockAcquired = true;
	}

if (!is_readable($xfieldsPath)) {
	throw new RuntimeException("XFields registry is not readable: {$xfieldsPath}");
}
$originalXfieldsStat = stat($xfieldsPath);
if ($originalXfieldsStat === false) {
	throw new RuntimeException("Cannot inspect XFields registry metadata: {$xfieldsPath}");
}
$originalXfieldsJson = (string) file_get_contents($xfieldsPath);
$registry = json_decode($originalXfieldsJson, true, 512, JSON_THROW_ON_ERROR);
if (!isset($registry['fields']) || !is_array($registry['fields'])) {
	throw new RuntimeException('Invalid xfields registry structure');
}
if (!isset($registry['groups']) || !is_array($registry['groups'])) {
	$registry['groups'] = [];
}
$hadP4Registry = isset($registry['fields']['service_detail_enabled']);

$definitions = [];
$addField = static function (string $name, string $description, string $type, string $category, string $hint = '') use (&$definitions): void {
	$definitions[$name] = mi_p4_field($name, $description, $type, $category, $hint);
};

for ($i = 1; $i <= 5; $i++) {
	$addField("about_hero_word_{$i}", "О компании: hero — слово {$i}", 'text', '6', 'Пять отдельных слов сохраняют точную анимацию заголовка.');
}
$addField('about_team_title', 'О компании: команда — заголовок', 'textarea', '6');
$addField('about_team_lead', 'О компании: команда — вводный текст', 'textarea', '6');
$addField('about_process_title', 'О компании: этапы — заголовок', 'text', '6');
for ($i = 1; $i <= 6; $i++) {
	$addField("about_process_step_{$i}", "О компании: этап {$i}", 'text', '6', 'Ровно шесть позиций; порядок связан с анимацией.');
}
$addField('about_process_caption', 'О компании: этапы — финальная фраза', 'textarea', '6');
$addField('about_reviews_title', 'О компании: отзывы — заголовок', 'textarea', '6');
$addField('about_gratitude_title', 'О компании: благодарности — заголовок', 'textarea', '6');
$addField('about_cta_title', 'О компании: CTA — заголовок', 'text', '6');
$addField('about_cta_subtitle', 'О компании: CTA — подзаголовок', 'textarea', '6');
$addField('about_cta_text', 'О компании: CTA — текст', 'textarea', '6');
$addField('about_team_image', 'Команда: изображение роли', 'image', '10', 'PNG/WebP на прозрачном фоне.');
$addField('about_team_image_alt', 'Команда: alt изображения', 'text', '10');
$addField('about_review_author', 'Отзыв: имя автора', 'text', '11');
$addField('about_review_role', 'Отзыв: должность автора', 'text', '11');
$addField('about_review_company', 'Отзыв: компания', 'text', '11');
$addField('about_review_image', 'Отзыв: фото или логотип', 'image', '11');
$addField('about_review_image_alt', 'Отзыв: alt изображения', 'text', '11');
$addField('about_certificate_image', 'Благодарность: изображение документа', 'image', '12');
$addField('about_certificate_alt', 'Благодарность: alt изображения', 'text', '12');

$addField('service_detail_enabled', 'Услуга: показывать на detail-странице', 'yesorno', '4', 'Включите только у одной услуги для /uslugi.html.');
$addField('service_detail_hero', 'Услуга: hero-изображение', 'image', '4', 'Исходный макет: 2816×1536.');
$addField('service_detail_hero_alt', 'Услуга: alt hero-изображения', 'text', '4');
$addField('service_principles_title', 'Услуга: принципы — заголовок', 'textarea', '4');
for ($i = 1; $i <= 3; $i++) {
	$addField("service_principle_{$i}_title", "Услуга: принцип {$i} — название", 'text', '4');
	$addField("service_principle_{$i}_text", "Услуга: принцип {$i} — текст", 'textarea', '4');
}
$addField('service_formats_title', 'Услуга: форматы — заголовок', 'text', '4');
for ($i = 1; $i <= 4; $i++) {
	$addField("service_format_{$i}_title", "Услуга: формат {$i} — название", 'text', '4');
	$addField("service_format_{$i}_text", "Услуга: формат {$i} — текст", 'textarea', '4');
}
$addField('service_benefits_title', 'Услуга: преимущества — заголовок', 'textarea', '4');
for ($i = 1; $i <= 6; $i++) {
	$addField("service_benefit_{$i}_title", "Услуга: преимущество {$i} — название", 'text', '4');
	$addField("service_benefit_{$i}_text", "Услуга: преимущество {$i} — текст", 'textarea', '4');
	$addField("service_benefit_{$i}_note", "Услуга: преимущество {$i} — пример", 'textarea', '4');
}
$addField('service_metrics_title', 'Услуга: метрики — строка 1', 'text', '4');
$addField('service_metrics_subtitle', 'Услуга: метрики — строка 2', 'text', '4');
for ($i = 1; $i <= 3; $i++) {
	$addField("service_metric_{$i}_title", "Услуга: метрика {$i} — название", 'text', '4');
	$addField("service_metric_{$i}_text", "Услуга: метрика {$i} — текст", 'textarea', '4');
}

$addField('case_brand', 'Кейс: бренд / партнёры', 'text', '2');
$addField('case_hero', 'Кейс: большое изображение', 'image', '2', 'Исходный макет: 1443×915.');
$addField('case_hero_alt', 'Кейс: alt большого изображения', 'text', '2');
$addField('case_flow_title', 'Кейс: путь — заголовок', 'text', '2');
for ($i = 1; $i <= 6; $i++) {
	$addField("case_flow_{$i}", "Кейс: шаг {$i}", 'textarea', '2');
}
$addField('case_participant_title', 'Кейс: участник — заголовок', 'text', '2');
$addField('case_participant_intro', 'Кейс: участник — вводный текст', 'textarea', '2');
for ($i = 1; $i <= 2; $i++) {
	$addField("case_participant_item_{$i}", "Кейс: ценность для участника {$i}", 'textarea', '2');
}
$addField('case_presentation_title', 'Кейс: подача — заголовок', 'text', '2');
for ($i = 1; $i <= 2; $i++) {
	$addField("case_presentation_p_{$i}", "Кейс: подача — абзац {$i}", 'textarea', '2');
}
$addField('case_results_title', 'Кейс: результаты — заголовок', 'text', '2');
for ($i = 1; $i <= 5; $i++) {
	$addField("case_result_{$i}_title", "Кейс: результат {$i} — название", 'text', '2');
	$addField("case_result_{$i}_text", "Кейс: результат {$i} — текст", 'textarea', '2');
}
$addField('case_cta_title', 'Кейс: CTA — заголовок', 'textarea', '2');
$addField('case_cta_text', 'Кейс: CTA — текст', 'textarea', '2');

$addField('contact_title', 'Контакты: заголовок страницы', 'text', '8');
$addField('contact_telegram_url', 'Контакты: ссылка Telegram', 'text', '8', 'Полный URL, например https://t.me/mediaideya');
$addField('contact_whatsapp_url', 'Контакты: ссылка WhatsApp', 'text', '8', 'Полный URL, например https://wa.me/79532843200');
$addField('contact_map', 'Контакты: изображение карты', 'image', '8');
$addField('contact_map_alt', 'Контакты: alt карты', 'textarea', '8');
$addField('project_request_title', 'Форма заявки: заголовок', 'textarea', '8');
$addField('project_request_text', 'Форма заявки: вводный текст', 'textarea', '8');
$addField('legal_company_name', 'Юридическое название компании', 'text', '8');

$newFieldCount = 0;
foreach ($definitions as $name => $definition) {
	if (isset($registry['fields'][$name])) {
		$existing = $registry['fields'][$name];
		if (($existing['type'] ?? '') !== $definition['type'] || ($existing['category'] ?? '') !== $definition['category']) {
			throw new RuntimeException("XField collision: {$name}");
		}
		continue;
	}
	$registry['fields'][$name] = $definition;
	$newFieldCount++;
}

$encodedRegistry = json_encode(
	$registry,
	JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
) . PHP_EOL;

$registryChanged = !hash_equals(hash('sha256', $originalXfieldsJson), hash('sha256', $encodedRegistry));
$stateExists = is_file($statePath);
if ($stateExists) {
	if (!is_readable($statePath)) {
		throw new RuntimeException("P4 state marker is not readable: {$statePath}");
	}
	$stateData = json_decode((string) file_get_contents($statePath), true, 512, JSON_THROW_ON_ERROR);
	if (!is_array($stateData) || (int) ($stateData['version'] ?? 0) !== 1) {
		throw new RuntimeException('Unsupported or invalid P4 state marker');
	}
}
$p4CategoryEvidence = (int) (mi_p4_one(
	'SELECT COUNT(*) AS total FROM ' . PREFIX . "_category WHERE (id='10' AND alt_name='about-team') OR (id='11' AND alt_name='about-reviews') OR (id='12' AND alt_name='about-certificates')"
)['total'] ?? 0);
$p4PostEvidence = (int) (mi_p4_one(
	'SELECT COUNT(*) AS total FROM ' . PREFIX . "_post WHERE alt_name IN ('about-team-designers','syrobogatov-yandex-plus')"
)['total'] ?? 0);
$recoverInstalledState = !$stateExists && $hadP4Registry && $p4CategoryEvidence === 3 && $p4PostEvidence > 0;
$seedContent = !$stateExists && !$recoverInstalledState;
$log('Media-Ideya P4 inner-page content');
$log('Mode: ' . ($apply ? 'APPLY' : 'DRY RUN'));
$log('First content seed: ' . ($seedContent ? 'yes' : 'no — existing editor values are preserved'));
$log('State marker repair: ' . ($recoverInstalledState ? 'planned' : 'no'));
$log("New xfield definitions: {$newFieldCount}");

$transactionTables = array_values(array_unique([
	PREFIX . '_category',
	PREFIX . '_post',
	PREFIX . '_post_extras',
	PREFIX . '_post_extras_cats',
	PREFIX . '_static',
	PREFIX . '_tags',
]));
$tableNamesSql = implode(', ', array_map(
	static fn(string $table): string => "'" . $db->safesql($table) . "'",
	$transactionTables
));
$engineRows = mi_p4_rows(
	"SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ({$tableNamesSql})"
);
$engines = [];
foreach ($engineRows as $engineRow) {
	$engines[(string) $engineRow['TABLE_NAME']] = strtoupper((string) $engineRow['ENGINE']);
}
foreach ($transactionTables as $table) {
	if (!isset($engines[$table])) {
		throw new RuntimeException("Required table is missing: {$table}");
	}
	if ($engines[$table] !== 'INNODB') {
		throw new RuntimeException("Table {$table} must use InnoDB; found {$engines[$table]}");
	}
}

foreach ($categoryMap as $id => [$slug, $name, $description, $create]) {
	$rows = mi_p4_rows(
		'SELECT id, alt_name FROM ' . PREFIX . "_category WHERE id='{$id}' OR alt_name='" . $db->safesql($slug) . "'"
	);
	if (count($rows) > 1) {
		throw new RuntimeException("Category collision for id {$id} / {$slug}");
	}
	if ($rows && ((int) $rows[0]['id'] !== $id || $rows[0]['alt_name'] !== $slug)) {
		throw new RuntimeException("Category mapping mismatch for id {$id} / {$slug}");
	}
	if (!$rows) {
		if (!$create) {
			throw new RuntimeException("Required category is missing: {$id} / {$slug}");
		}
		$log("Category {$id}: {$slug} — insert");
	} else {
		$log("Category {$id}: {$slug} — existing");
	}
}

foreach ($staticTemplates as $staticName => $templateName) {
	$staticNameSql = $db->safesql($staticName);
	$rows = mi_p4_rows('SELECT id, tpl FROM ' . PREFIX . "_static WHERE name='{$staticNameSql}'");
	if (count($rows) !== 1) {
		throw new RuntimeException("Expected exactly one static page: {$staticName}");
	}
	$log(
		"Static {$staticName}: "
		. ((string) $rows[0]['tpl'] === $templateName ? 'template ready' : "set template {$templateName}")
	);
}

$editorRows = mi_p4_rows('SELECT id, allow_cats, cat_add, cat_allow_addnews FROM ' . USERPREFIX . "_usergroups WHERE id='3'");
if (count($editorRows) > 1) {
	throw new RuntimeException('Duplicate editor group id 3');
}
$log('Editor group 3: ' . ($editorRows ? 'permission merge planned' : 'not present — skipped'));

$requiredPosts = [
	'about-text' => 6,
	'service-product-placement' => 4,
	'site-contacts' => 8,
];
foreach ($requiredPosts as $slug => $categoryId) {
	$post = mi_p4_find_post($slug, true);
	if (!in_array($categoryId, mi_p4_category_ids((string) $post['category']), true)) {
		throw new RuntimeException("Required post {$slug} has the wrong category");
	}
	$needsRelationRepair = mi_p4_sync_post_relations((int) $post['id'], $categoryId, false);
	$log("Post {$slug}: existing" . ($needsRelationRepair ? ' — relation repair planned' : ''));
}

$managedPostCategories = [
	'about-team-designers' => 10,
	'about-team-developers' => 10,
	'about-team-producers' => 10,
	'about-team-managers' => 10,
	'about-team-marketers' => 10,
	'about-team-specialists' => 10,
	'about-team-psychologists' => 10,
	'about-team-production' => 10,
	'about-review-1' => 11,
	'about-review-2' => 11,
	'about-review-3' => 11,
	'about-review-4' => 11,
	'about-certificate-1' => 12,
	'about-certificate-2' => 12,
	'about-certificate-3' => 12,
	'about-certificate-4' => 12,
	'about-certificate-5' => 12,
	'about-certificate-6' => 12,
	'about-certificate-7' => 12,
	'about-certificate-8' => 12,
	'about-certificate-9' => 12,
	'syrobogatov-yandex-plus' => 2,
];
$plannedInsertCount = 0;
$preservedMissingCount = 0;
foreach ($managedPostCategories as $slug => $categoryId) {
	$post = mi_p4_find_post($slug);
	if (!$post) {
		if ($seedContent) {
			$plannedInsertCount++;
		} else {
			$preservedMissingCount++;
		}
		continue;
	}
	if (!in_array($categoryId, mi_p4_category_ids((string) $post['category']), true)) {
		throw new RuntimeException("Managed post {$slug} has the wrong category");
	}
	mi_p4_sync_post_relations((int) $post['id'], $categoryId, false);
}
$log("Managed posts: {$plannedInsertCount} inserts planned, {$preservedMissingCount} editor deletions preserved");

if ($apply) {
	mi_p4_query('START TRANSACTION');
	$transactionStarted = true;
	foreach ($categoryMap as $id => [$slug, $name, $description, $create]) {
		mi_p4_assert_category($id, $slug, $name, $description, $create);
	}
	foreach ($staticTemplates as $staticName => $templateName) {
		$staticNameSql = $db->safesql($staticName);
		$templateNameSql = $db->safesql($templateName);
		$row = mi_p4_one('SELECT id, tpl FROM ' . PREFIX . "_static WHERE name='{$staticNameSql}'");
		if (!$row) {
			throw new RuntimeException("Static page disappeared during migration: {$staticName}");
		}
		if ((string) $row['tpl'] !== $templateName) {
			mi_p4_query(
				'UPDATE ' . PREFIX . "_static SET tpl='{$templateNameSql}', template_folder='MediaIdeya' WHERE id='" . (int) $row['id'] . "'"
			);
			$staticTemplatesChanged = true;
		}
	}
}

$assetPaths = [
	'contact_map' => mi_p4_stage_asset('pages/contact/map.png', 'contact-map.png', $apply, $stagedAssets),
	'service_hero' => mi_p4_stage_asset('pages/services/product-placement.png', 'service-product-placement.png', $apply, $stagedAssets),
	'case_hero' => mi_p4_stage_asset('pages/cases/syrobogatov-yandex.png', 'case-syrobogatov-yandex.png', $apply, $stagedAssets),
	'certificate' => mi_p4_stage_asset('pages/about/certificate.png', 'about-certificate.png', $apply, $stagedAssets),
];
$teamAssetFiles = ['designers', 'developers', 'producers', 'managers', 'marketers', 'specialists', 'psychologists', 'production'];
foreach ($teamAssetFiles as $asset) {
	$assetPaths['team_' . $asset] = mi_p4_stage_asset("pages/about/team/{$asset}.png", "about-team-{$asset}.png", $apply, $stagedAssets);
}

if (!$apply) {
	$log('Dry run complete. Re-run with --apply to write changes.');
	exit(0);
}

	$admin = mi_p4_one('SELECT name FROM ' . USERPREFIX . "_users WHERE user_group='1' ORDER BY user_id ASC LIMIT 1");
	$author = $db->safesql($admin['name'] ?? 'admin');

	$aboutFields = [
		'about_hero_word_1' => 'За',
		'about_hero_word_2' => 'каждым',
		'about_hero_word_3' => 'проектом',
		'about_hero_word_4' => 'стоят',
		'about_hero_word_5' => 'люди',
		'about_team_title' => 'Мы собираем команды, которые превращают идеи в работающие проекты',
		'about_team_lead' => 'Дизайнеры, разработчики, продюсеры, менеджеры, маркетологи и специалисты разных категорий — объединяем экспертизу, чтобы создавать рекламные проекты от идеи до запуска.',
		'about_process_title' => 'Один проект — много экспертиз',
		'about_process_step_1' => 'Стратегия',
		'about_process_step_2' => 'Креатив',
		'about_process_step_3' => 'Дизайн',
		'about_process_step_4' => 'Разработка',
		'about_process_step_5' => 'Продакшн',
		'about_process_step_6' => 'Запуск',
		'about_process_caption' => 'Так идея превращается в проект, который работает',
		'about_reviews_title' => 'Нас выбирают люди, с которыми хочется работать снова',
		'about_gratitude_title' => 'Нас благодарят не только словами',
		'about_cta_title' => 'Есть идея?',
		'about_cta_subtitle' => 'Давайте соберём команду под неё.',
		'about_cta_text' => 'От первой идеи до запуска — берём на себя весь продакшн.',
	];
	$aboutPost = mi_p4_find_post('about-text', true);
	mi_p4_merge_post_fields($aboutPost, 6, $aboutFields, $seedContent);

	$serviceFields = [
		'service_detail_enabled' => '1',
		'service_detail_hero' => $assetPaths['service_hero'],
		'service_detail_hero_alt' => 'Product-placement',
		'service_principles_title' => 'Формат строится на 3 ключевых принципах:',
		'service_principle_1_title' => 'Естественность',
		'service_principle_1_text' => 'Бренд не «кричит» о себе, а становится частью сцены.',
		'service_principle_2_title' => 'Релевантность',
		'service_principle_2_text' => 'Продукт логично вписывается в контекст.',
		'service_principle_3_title' => 'Эмоциональная связь',
		'service_principle_3_text' => 'Зритель запоминает бренд через сцену, диалог или персонажа.',
		'service_formats_title' => 'Основные форматы:',
		'service_format_1_title' => 'Визуальный продукт-плейсмент',
		'service_format_1_text' => 'Товар виден в кадре (Apple в «Мире Дикого Запада»)',
		'service_format_2_title' => 'Сюжетная интеграция',
		'service_format_2_text' => 'Бренд влияет на историю (Tesla в «Железном человеке»)',
		'service_format_3_title' => 'Аудио-упоминание',
		'service_format_3_text' => 'Герои обсуждают продукт («Макдоналдс» в «Криминальном чтиве»)',
		'service_format_4_title' => 'Фирменный стиль',
		'service_format_4_text' => 'Логотипы, интерьеры (Starbucks в «Секретных материалах»)',
		'service_benefits_title' => 'Преимущества нативной рекламы в кино и сериалах:',
		'service_benefit_1_title' => 'Высокий уровень доверия',
		'service_benefit_1_text' => 'Зрители воспринимают бренд как часть реального мира, а не навязанную рекламу.',
		'service_benefit_1_note' => 'Пример: Ray-Ban в «Людях в чёрном» → ассоциация с крутыми героями.',
		'service_benefit_2_title' => 'Долгосрочный эффект',
		'service_benefit_2_text' => 'Фильмы и сериалы пересматривают годами — бренд продолжает работать.',
		'service_benefit_2_note' => 'Mercedes в «Форсаже» до сих пор ассоциируется с серией.',
		'service_benefit_3_title' => 'Глобальный охват',
		'service_benefit_3_text' => 'Хит вроде «Игры престолов» или «Мстителей» охватывает сотни миллионов зрителей.',
		'service_benefit_4_title' => 'Эмоциональное вовлечение',
		'service_benefit_4_text' => 'Герой, использующий продукт, вызывает идентификацию («Я хочу такой же!»)',
		'service_benefit_4_note' => 'Пример: Aston Martin → Джеймс Бонд.',
		'service_benefit_5_title' => 'Обход блокировок рекламы',
		'service_benefit_5_text' => 'В отличие от YouTube-роликов, product placement нельзя отключить AdBlock’ом.',
		'service_benefit_6_title' => 'Поддержка digital-маркетинга',
		'service_benefit_6_text' => 'Удачные интеграции становятся мемами и вирусными обсуждениями в соцсетях.',
		'service_metrics_title' => 'Как измерить эффективность?',
		'service_metrics_subtitle' => 'Метрики успеха:',
		'service_metric_1_title' => 'Рост поисковых запросов',
		'service_metric_1_text' => '(«Какой телефон у Тони Старка?»)',
		'service_metric_2_title' => 'Всплеск продаж после выхода фильма',
		'service_metric_2_text' => '(например, Omega после «Джеймса Бонда»)',
		'service_metric_3_title' => 'Охват в соцсетях',
		'service_metric_3_text' => 'Мемы, обсуждения',
	];
	$servicePost = mi_p4_find_post('service-product-placement', true);
	mi_p4_merge_post_fields($servicePost, 4, $serviceFields, $seedContent);

	$contactFields = [
		'contact_title' => 'Контакты',
		'contact_telegram_url' => 'https://t.me/mediaideya',
		'contact_whatsapp_url' => 'https://wa.me/79532843200',
		'contact_map' => $assetPaths['contact_map'],
		'contact_map_alt' => 'Карта: офис Media Ideya, Брянск, проспект Станке Димитрова, 54А',
		'project_request_title' => 'Давайте обсудим вашу задачу',
		'project_request_text' => 'Свяжитесь с нами удобным для вас способом или оставьте заявку на консультацию. Вместе разберём вашу задачу и найдём решение.',
		'legal_company_name' => 'ООО «Медиа Идея»',
	];
	$contactPost = mi_p4_find_post('site-contacts', true);
	mi_p4_merge_post_fields($contactPost, 8, $contactFields, $seedContent);

	$teamSeeds = [
		['Дизайнеры', 'about-team-designers', 'Превращают идеи в визуальные решения и интерфейсы', 'designers'],
		['Разработчики', 'about-team-developers', 'Создают digital-продукты, которые работают так же хорошо, как выглядят', 'developers'],
		['Продюсеры', 'about-team-producers', 'Соединяют идею, людей, ресурсы и результат', 'producers'],
		['Менеджеры', 'about-team-managers', 'Организуют процесс и держат проект в движении — от первого брифа до запуска', 'managers'],
		['Маркетологи', 'about-team-marketers', 'Помогают выстроить коммуникацию так, чтобы проект решал бизнес-задачу', 'marketers'],
		['Специалисты разных категорий', 'about-team-specialists', 'Подключают нужную экспертизу под конкретный проект: от контента и аналитики до технических и специальных задач', 'specialists'],
		['Психологи когнитивисты', 'about-team-psychologists', 'Изучают поведение людей и реакцию на разные механики промо', 'psychologists'],
		['Производственники', 'about-team-production', 'Помогают превратить идею в реализуемое решение, учитывая реальные возможности, ограничения и особенности производства', 'production'],
	];
	foreach ($teamSeeds as $index => [$title, $slug, $short, $asset]) {
		mi_p4_ensure_post(
			$author,
			10,
			$title,
			$slug,
			$short,
			'',
			[
				'about_team_image' => $assetPaths['team_' . $asset],
				'about_team_image_alt' => $title,
			],
			sprintf('2026-01-10 10:%02d:00', $index),
			$seedContent
		);
	}

	for ($i = 1; $i <= 4; $i++) {
		mi_p4_ensure_post(
			$author,
			11,
			"Отзыв клиента {$i}",
			"about-review-{$i}",
			'',
			'',
			[],
			sprintf('2026-01-11 10:%02d:00', $i - 1),
			$seedContent
		);
	}

	for ($i = 1; $i <= 9; $i++) {
		mi_p4_ensure_post(
			$author,
			12,
			"Благодарственное письмо {$i}",
			"about-certificate-{$i}",
			'',
			'',
			[
				'about_certificate_image' => $assetPaths['certificate'],
				'about_certificate_alt' => "Благодарственное письмо {$i}",
			],
			sprintf('2026-01-12 10:%02d:00', $i - 1),
			$seedContent
		);
	}

	$caseFields = [
		'image' => $assetPaths['case_hero'],
		'case_brand' => 'Сыробогатов × Яндекс Плюс',
		'case_hero' => $assetPaths['case_hero'],
		'case_hero_alt' => 'Промоакция Сыробогатов и Яндекс Плюс',
		'case_flow_title' => 'Покупка становится входом в digital',
		'case_flow_1' => 'Пользователь покупает продукт',
		'case_flow_2' => 'Сканирует QR-код',
		'case_flow_3' => 'Получает промокод',
		'case_flow_4' => 'Активирует Яндекс Плюс',
		'case_flow_5' => 'Регистрируется в акции',
		'case_flow_6' => 'Получает возможность выиграть ценные призы',
		'case_participant_title' => 'Что получил участник?',
		'case_participant_intro' => 'Потребитель получает ценность уже на пути участия:',
		'case_participant_item_1' => 'гарантированный приз в виде мультиподписки Яндекс Плюс',
		'case_participant_item_2' => 'участие в розыгрыше ценных призов: регулярные призы и крупные финальные призы.',
		'case_presentation_title' => 'Это можно подать как:',
		'case_presentation_p_1' => 'Акция работает не один день — она постоянно возвращает пользователя к бренду (Не просто список призов, а визуальная демонстрация того, как поддерживалось внимание аудитории на протяжении всей акции)',
		'case_presentation_p_2' => 'У проекта предусмотрены регулярные еженедельные розыгрыши с апреля/мая по ноябрь 2026 года, а также отдельные розыгрыши крупных призов.',
		'case_results_title' => 'Результат. Партнеры получили:',
		'case_result_1_title' => 'Рост вовлечения',
		'case_result_1_text' => 'Покупатель совершает дополнительное действие после покупки.',
		'case_result_2_title' => 'Digital-контакт',
		'case_result_2_text' => 'Офлайн-продукт переводит пользователя в digital.',
		'case_result_3_title' => 'Дополнительную мотивацию к покупке',
		'case_result_3_text' => 'Призовая механика создаёт дополнительный стимул выбрать промопродукт.',
		'case_result_4_title' => 'Длительное взаимодействие',
		'case_result_4_text' => 'Регулярные розыгрыши поддерживают интерес на протяжении всей кампании.',
		'case_result_5_title' => 'Партнёрское усиление',
		'case_result_5_text' => 'Бренд получает дополнительную ценность за счёт интеграции с экосистемой Яндекс Плюса.',
		'case_cta_title' => 'А что, если ваш продукт тоже станет входом в digital?',
		'case_cta_text' => 'Мы создаём промоакции, которые объединяют бренд, продукт, digital-механику, спецпризы и пользовательский путь в единую рекламную кампанию.',
	];
	$casePost = mi_p4_ensure_post(
		$author,
		2,
		'Как превратить покупку продукта в digital-механику с вовлечением и призами?',
		'syrobogatov-yandex-plus',
		'Сыробогатов × Яндекс Плюс',
		'',
		$caseFields,
		'2026-01-13 10:00:00',
		$seedContent,
		'product-placement'
	);
	if ($casePost !== null) {
		mi_p4_ensure_tag($casePost, 'product-placement');
	}

	foreach ($stagedAssets as &$stagedAsset) {
		$tempAsset = (string) $stagedAsset['temp'];
		$destination = (string) $stagedAsset['destination'];
		if (is_file($destination)) {
			if (hash_file('sha256', $tempAsset) !== hash_file('sha256', $destination)) {
				throw new RuntimeException("Seed asset destination changed during migration: {$destination}");
			}
			@unlink($tempAsset);
			$stagedAsset['temp'] = '';
			continue;
		}
		if (!rename($tempAsset, $destination)) {
			throw new RuntimeException("Cannot install staged seed asset: {$destination}");
		}
		$stagedAsset['temp'] = '';
		$installedAssets[] = $destination;
	}
	unset($stagedAsset);

	if ($registryChanged) {
		$tempXfieldsPath = mi_p4_stage_file(
			dirname($xfieldsPath),
			'xfields-p4-',
			$encodedRegistry,
			$originalXfieldsStat
		);
		$validatedRegistry = json_decode((string) file_get_contents($tempXfieldsPath), true, 512, JSON_THROW_ON_ERROR);
		if (!isset($validatedRegistry['fields']) || count($validatedRegistry['fields']) !== count($registry['fields'])) {
			throw new RuntimeException('Staged xfields registry validation failed');
		}
	}

	if ($seedContent || $recoverInstalledState) {
		$state = json_encode(
			['version' => 1, 'applied_at' => date(DATE_ATOM)],
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
		) . PHP_EOL;
		$tempStatePath = mi_p4_stage_file(dirname($statePath), 'mi-p4-state-', $state, null, 0644);
	}

	if ($registryChanged) {
		$currentXfieldsJson = file_get_contents($xfieldsPath);
		if ($currentXfieldsJson === false || !hash_equals(hash('sha256', $originalXfieldsJson), hash('sha256', $currentXfieldsJson))) {
			throw new RuntimeException('XFields registry changed during migration; no registry update was applied');
		}
		if (!rename($tempXfieldsPath, $xfieldsPath)) {
			throw new RuntimeException('Cannot atomically replace the xfields registry');
		}
		$tempXfieldsPath = '';
		$xfieldsReplaced = true;
	}

	mi_p4_query('COMMIT');
	$transactionStarted = false;
	$committed = true;

	$editorRows = mi_p4_rows('SELECT id, allow_cats, cat_add, cat_allow_addnews FROM ' . USERPREFIX . "_usergroups WHERE id='3'");
	if (count($editorRows) > 1) {
		throw new RuntimeException('Duplicate editor group id 3 after commit');
	}
	if ($editorRows) {
		$editor = $editorRows[0];
		$allowCatsRaw = mi_p4_append_category_permissions((string) $editor['allow_cats'], [10, 11, 12]);
		$catAddRaw = mi_p4_append_category_permissions((string) $editor['cat_add'], [10, 11, 12]);
		$catAllowRaw = mi_p4_append_category_permissions((string) $editor['cat_allow_addnews'], [10, 11, 12]);
		if (
			$allowCatsRaw !== (string) $editor['allow_cats']
			|| $catAddRaw !== (string) $editor['cat_add']
			|| $catAllowRaw !== (string) $editor['cat_allow_addnews']
		) {
			$allowCats = $db->safesql($allowCatsRaw);
			$catAdd = $db->safesql($catAddRaw);
			$catAllow = $db->safesql($catAllowRaw);
			mi_p4_query(
				'UPDATE ' . USERPREFIX . "_usergroups SET allow_cats='{$allowCats}', cat_add='{$catAdd}', cat_allow_addnews='{$catAllow}' WHERE id='3'"
			);
			$editorPermissionsChanged = true;
		}
	}

	if ($tempStatePath !== '') {
		if (is_file($statePath)) {
			throw new RuntimeException('P4 state marker appeared during migration; database changes are already committed');
		}
		if (!rename($tempStatePath, $statePath)) {
			throw new RuntimeException('Database changes committed, but the P4 state marker could not be installed');
		}
		$tempStatePath = '';
	}

	if ($registryChanged || $seedContent || $recoverInstalledState || $editorPermissionsChanged || $staticTemplatesChanged) {
		@unlink(ENGINE_DIR . '/cache/system/xfields.php');
		@unlink(ENGINE_DIR . '/cache/system/category.json');
		@unlink(ENGINE_DIR . '/cache/system/usergroup.json');
		foreach (glob(ENGINE_DIR . '/cache/*.php') ?: [] as $cacheFile) {
			@unlink($cacheFile);
		}
	}

	mi_p4_query_no_throw("SELECT RELEASE_LOCK('{$lockSql}')");
	$lockAcquired = false;
	$log('Applied successfully.');
	$log('Categories: 10 team, 11 reviews, 12 certificates.');
	$log('Managed posts: about-text, service-product-placement, site-contacts, case + repeatable About items.');
	$log('Cache cleared.');
} catch (Throwable $error) {
	if ($transactionStarted) {
		mi_p4_query_no_throw('ROLLBACK');
		$transactionStarted = false;
	}
	$recoveryErrors = [];
	if (!$committed && $xfieldsReplaced && is_array($originalXfieldsStat)) {
		try {
			$restorePath = mi_p4_stage_file(
				dirname($xfieldsPath),
				'xfields-p4-restore-',
				$originalXfieldsJson,
				$originalXfieldsStat
			);
			if (!rename($restorePath, $xfieldsPath)) {
				@unlink($restorePath);
				throw new RuntimeException('atomic rename failed');
			}
		} catch (Throwable $restoreError) {
			$recoveryErrors[] = 'xfields restore failed: ' . $restoreError->getMessage();
		}
	}
	if (!$committed) {
		foreach ($stagedAssets as $stagedAsset) {
			$tempAsset = (string) ($stagedAsset['temp'] ?? '');
			if ($tempAsset !== '' && is_file($tempAsset) && !@unlink($tempAsset)) {
				$recoveryErrors[] = "staged asset cleanup failed: {$tempAsset}";
			}
		}
		foreach (array_reverse($installedAssets) as $installedAsset) {
			if (is_file($installedAsset) && !@unlink($installedAsset)) {
				$recoveryErrors[] = "asset cleanup failed: {$installedAsset}";
			}
		}
	}
	if ($tempXfieldsPath !== '' && is_file($tempXfieldsPath)) {
		@unlink($tempXfieldsPath);
	}
	if ($tempStatePath !== '' && is_file($tempStatePath)) {
		@unlink($tempStatePath);
	}
	if ($lockAcquired) {
		mi_p4_query_no_throw("SELECT RELEASE_LOCK('{$lockSql}')");
	}
	fwrite(STDERR, 'P4 failed: ' . $error->getMessage() . PHP_EOL);
	foreach ($recoveryErrors as $recoveryError) {
		fwrite(STDERR, 'Recovery warning: ' . $recoveryError . PHP_EOL);
	}
	exit(1);
}
