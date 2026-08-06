<?php
/**
 * Media-Ideya P0 CMS bootstrap — run once via CLI or browser.
 * CLI:  php scripts/mi-p0-setup.php
 * HTTP: /scripts/mi-p0-setup.php?key=mi-p0-2026
 */
declare(strict_types=1);

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__DIR__));
define('ENGINE_DIR', ROOT_DIR . '/engine');

$isCli = PHP_SAPI === 'cli';
if (!$isCli) {
	$key = $_GET['key'] ?? '';
	if ($key !== 'mi-p0-2026') {
		http_response_code(403);
		exit('Forbidden');
	}
	header('Content-Type: text/plain; charset=utf-8');
}

require_once ENGINE_DIR . '/classes/plugins.class.php';

if (!isset($db) || !is_object($db)) {
	exit("DB connection failed\n");
}

$log = static function (string $line) use ($isCli): void {
	echo $line . ($isCli ? PHP_EOL : "\n");
};

$log('Media-Ideya P0 setup');

$categories = [
	1 => ['name' => 'Статьи', 'alt_name' => 'stat-i', 'descr' => 'Статьи и аналитика рынка'],
	2 => ['name' => 'Кейсы', 'alt_name' => 'keysy', 'descr' => 'Кейсы и проекты Media Ideya'],
];

foreach ($categories as $id => $cat) {
	$name = $db->safesql($cat['name']);
	$alt = $db->safesql($cat['alt_name']);
	$descr = $db->safesql($cat['descr']);
	$db->query("UPDATE " . PREFIX . "_category SET parentid='0', posi='{$id}', name='{$name}', alt_name='{$alt}', descr='{$descr}', keywords='{$name}', metatitle='{$name}', fulldescr='{$descr}', active='1', disable_main='0', disable_search='0' WHERE id='{$id}'");
	$log("Category {$id}: {$cat['name']} (/{$cat['alt_name']}/)");
}

$db->query("DELETE FROM " . PREFIX . "_post_extras_cats WHERE cat_id > 2");
$db->query("DELETE FROM " . PREFIX . "_category WHERE id > 2");
@unlink(ENGINE_DIR . '/cache/system/category.json');
$log('Removed demo categories (id > 2)');

$xfields = [
	'fields' => [
		'image' => [
			'name' => 'image',
			'description' => 'Обложка карточки',
			'hint' => '547×329, WebP или JPG',
			'type' => 'image',
			'category' => '',
			'group' => '',
			'default' => '',
			'not_required' => 1,
			'allow_add_usergroups' => '',
			'use_as_links' => 0,
			'use_editor' => 0,
			'safe_mode' => 0,
			'min' => '',
			'max' => '',
			'image_size' => '547x329',
			'image_max_size' => '2048',
			'make_watermark' => 0,
			'make_thumb' => 1,
			'thumb_size' => '547x329',
			'image_sizes' => '547x329',
			'use_opengraph' => 1,
			'image_side' => 0,
			'thumb_side' => 0,
			'max_images' => '',
			'storage' => -1,
			'allow_multi' => 0,
			'select_separator' => '',
			'links_separator' => '',
			'files_ext' => '',
			'file_max_size' => '',
			'is_public' => 0,
			'max_files' => '',
			'max_size' => '',
			'condition' => '',
		],
		'lead' => [
			'name' => 'lead',
			'description' => 'Тег на карточке',
			'hint' => 'Например: маркетинг',
			'type' => 'text',
			'category' => '',
			'group' => '',
			'default' => '',
			'not_required' => 1,
			'allow_add_usergroups' => '',
			'use_as_links' => 0,
			'use_editor' => 0,
			'safe_mode' => 0,
			'min' => '',
			'max' => '40',
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
		],
	],
	'groups' => [],
];

$json = json_encode($xfields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents(ENGINE_DIR . '/data/xfields.json', $json, LOCK_EX);
@chmod(ENGINE_DIR . '/data/xfields.json', 0666);
@unlink(ENGINE_DIR . '/cache/system/xfields.php');
$log('XFields: image, lead');

$existing = (int) $db->super_query("SELECT COUNT(*) AS count FROM " . PREFIX . "_post WHERE category='1'")['count'];
if ($existing < 1) {
	$admin = $db->super_query("SELECT name FROM " . USERPREFIX . "_users WHERE user_group='1' LIMIT 1");
	$autor = $db->safesql($admin['name'] ?? 'admin');
	$now = time();
	$posts = [
		[
			'title' => 'Почему кросс-маркетинг работает лучше отдельных кампаний',
			'alt' => 'pochemu-kross-marketing-rabotaet-luchshe',
			'short' => 'Разбираем, когда объединение брендов в одной кампании даёт синергию, а когда только съедает бюджет.',
			'lead' => 'маркетинг',
		],
		[
			'title' => 'On-packing как точка входа в категорию',
			'alt' => 'on-packing-kak-tochka-vhoda',
			'short' => 'Как упаковка становится медиаканалом и почему производители всё чаще вкладываются в промo на полке.',
			'lead' => 'брендинг',
		],
		[
			'title' => 'Что измерять в cross-marketing кроме охватов',
			'alt' => 'chto-izmeriat-v-cross-marketing',
			'short' => 'Практичные метрики для оценки совместных акций между производителями и ритейлом.',
			'lead' => 'аналитика',
		],
	];

	$i = 0;
	foreach ($posts as $post) {
		$date = date('Y-m-d H:i:s', $now - ($i * 86400 * 3));
		$title = $db->safesql($post['title']);
		$alt = $db->safesql($post['alt']);
		$short = $db->safesql($post['short']);
		$xf = $db->safesql('');
		$tags = $db->safesql($post['lead']);
		$descr = $db->safesql(mb_substr(strip_tags($post['short']), 0, 300));

		$db->query("INSERT INTO " . PREFIX . "_post (autor, date, short_story, full_story, xfields, title, descr, keywords, category, alt_name, comm_num, allow_comm, allow_main, approve, fixed, allow_br, symbol, tags, metatitle) VALUES ('{$autor}', '{$date}', '{$short}', '{$short}', '{$xf}', '{$title}', '{$descr}', '{$title}', '1', '{$alt}', '0', '1', '1', '1', '0', '1', '', '{$tags}', '{$title}')");
		$newsId = $db->insert_id();
		$db->query("INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate, votes, disable_index, related_ids, access, user_id, disable_search, need_pass, allow_rss, allow_rss_dzen, allowed_country, not_allowed_country) VALUES ('{$newsId}', '1', '0', '0', '', '', '0', '0', '0', '1', '1', '', '')");
		$db->query("INSERT INTO " . PREFIX . "_post_extras_cats (news_id, cat_id) VALUES ('{$newsId}', '1')");
		$log("Post: {$post['title']}");
		$i++;
	}
} else {
	$log("Posts in «Статьи»: {$existing} — skip demo insert");
}

@unlink(ENGINE_DIR . '/cache/system/category.json');
$log('Cache cleared');
$log('Done.');
