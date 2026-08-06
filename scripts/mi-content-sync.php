<?php
/**
 * Media-Ideya — sync demo content: native DLE fields, uploads, xfields cleanup.
 * HTTP: /scripts/mi-content-sync.php?key=mi-content-2026
 */
declare(strict_types=1);

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__DIR__));
define('ENGINE_DIR', ROOT_DIR . '/engine');

$key = $_GET['key'] ?? '';
if ($key !== 'mi-content-2026') {
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

$log('Media-Ideya content sync');

function mi_xf_build(array $pairs): string {
	global $db;
	$parts = [];
	foreach ($pairs as $k => $v) {
		if ($v === '' || $v === null) {
			continue;
		}
		$parts[] = $k . '|' . str_replace('|', '&#124;', (string) $v);
	}
	return $db->safesql(implode('||', $parts));
}

function mi_xf_parse(string $raw): array {
	$out = [];
	foreach (explode('||', $raw) as $part) {
		if ($part === '') {
			continue;
		}
		$pos = strpos($part, '|');
		if ($pos === false) {
			continue;
		}
		$k = str_replace('&#124;', '|', substr($part, 0, $pos));
		$v = str_replace('&#124;', '|', substr($part, $pos + 1));
		$out[$k] = $v;
	}
	return $out;
}

function mi_upload_image(string $themeRelative, string $fileName): string {
	$src = ROOT_DIR . '/templates/MediaIdeya/images/' . ltrim($themeRelative, '/');
	if (!is_file($src)) {
		return '';
	}
	$month = date('Y-m');
	$dir = ROOT_DIR . '/uploads/posts/' . $month;
	if (!is_dir($dir)) {
		mkdir($dir, 0755, true);
	}
	$dest = $dir . '/' . $fileName;
	if (!copy($src, $dest)) {
		return '';
	}
	return $month . '/' . $fileName;
}

function mi_set_tags(int $newsId, string $tagsCsv): void {
	global $db;
	$tags = array_values(array_filter(array_map('trim', explode(',', $tagsCsv))));
	$postTags = $db->safesql(implode(', ', $tags));
	$db->query("UPDATE " . PREFIX . "_post SET tags='{$postTags}' WHERE id='{$newsId}'");
	$db->query("DELETE FROM " . PREFIX . "_tags WHERE news_id='{$newsId}'");
	if (!$tags) {
		return;
	}
	$rows = [];
	foreach ($tags as $tag) {
		$tagS = $db->safesql($tag);
		$rows[] = "('{$newsId}', '{$tagS}')";
	}
	$db->query('INSERT INTO ' . PREFIX . "_tags (news_id, tag) VALUES " . implode(', ', $rows));
}

function mi_update_post(int $newsId, array $opts): void {
	global $db, $log;
	$row = $db->super_query('SELECT xfields FROM ' . PREFIX . "_post WHERE id='{$newsId}' LIMIT 1");
	if (!$row) {
		return;
	}
	$xf = mi_xf_parse($row['xfields'] ?? '');
	unset($xf['lead'], $xf['service_slide'], $xf['service_bullets'], $xf['service_link']);
	if (!empty($opts['xfields'])) {
		foreach ($opts['xfields'] as $k => $v) {
			$xf[$k] = $v;
		}
	}
	$xfSql = mi_xf_build($xf);
	$sets = ["xfields='{$xfSql}'"];
	if (isset($opts['full_story'])) {
		$sets[] = "full_story='" . $db->safesql($opts['full_story']) . "'";
	}
	if (isset($opts['short_story'])) {
		$sets[] = "short_story='" . $db->safesql($opts['short_story']) . "'";
	}
	$db->query('UPDATE ' . PREFIX . '_post SET ' . implode(', ', $sets) . " WHERE id='{$newsId}'");
	if (!empty($opts['tags'])) {
		mi_set_tags($newsId, $opts['tags']);
	}
	$log('  updated id ' . $newsId);
}

$xfields = [
	'fields' => [
		'image' => [
			'name' => 'image', 'description' => 'Обложка карточки', 'hint' => '547×329, загрузите файл',
			'type' => 'image', 'category' => '1,2', 'group' => '', 'default' => '', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '', 'image_size' => '547x329', 'image_max_size' => '2048',
			'make_watermark' => 0, 'make_thumb' => 1, 'thumb_size' => '547x329', 'image_sizes' => '547x329',
			'use_opengraph' => 1, 'image_side' => 0, 'thumb_side' => 0, 'max_images' => '', 'storage' => -1,
			'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '', 'files_ext' => '',
			'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '', 'condition' => '',
		],
		'client_logo' => [
			'name' => 'client_logo', 'description' => 'Логотип партнёра', 'hint' => 'PNG/WebP на прозрачном фоне',
			'type' => 'image', 'category' => '5', 'group' => '', 'default' => '', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '', 'image_size' => '231x80', 'image_max_size' => '512',
			'make_watermark' => 0, 'make_thumb' => 0, 'thumb_size' => '', 'image_sizes' => '231x80',
			'use_opengraph' => 0, 'image_side' => 0, 'thumb_side' => 0, 'max_images' => '', 'storage' => -1,
			'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '', 'files_ext' => '',
			'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '', 'condition' => '',
		],
		'service_image' => [
			'name' => 'service_image', 'description' => 'Изображение услуги', 'hint' => '860×740, загрузите файл',
			'type' => 'image', 'category' => '4', 'group' => '', 'default' => '', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '', 'image_size' => '860x740', 'image_max_size' => '4096',
			'make_watermark' => 0, 'make_thumb' => 1, 'thumb_size' => '860x740', 'image_sizes' => '860x740',
			'use_opengraph' => 0, 'image_side' => 0, 'thumb_side' => 0, 'max_images' => '', 'storage' => -1,
			'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '', 'files_ext' => '',
			'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '', 'condition' => '',
		],
		'footer_cta_title' => [
			'name' => 'footer_cta_title', 'description' => 'Footer CTA — заголовок', 'type' => 'text', 'category' => '8',
			'group' => '', 'hint' => '', 'default' => 'Обсудим ваш проект', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '120', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
		'footer_cta_text' => [
			'name' => 'footer_cta_text', 'description' => 'Footer CTA — текст', 'type' => 'textarea', 'category' => '8',
			'group' => '', 'hint' => '', 'default' => '', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
		'footer_address' => [
			'name' => 'footer_address', 'description' => 'Адрес', 'type' => 'textarea', 'category' => '8',
			'group' => '', 'hint' => '', 'default' => '', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
		'footer_email' => [
			'name' => 'footer_email', 'description' => 'Email', 'type' => 'text', 'category' => '8',
			'group' => '', 'hint' => '', 'default' => 'info@mediaideya.ru', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '120', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
		'footer_phone' => [
			'name' => 'footer_phone', 'description' => 'Телефон (отображение)', 'type' => 'text', 'category' => '8',
			'group' => '', 'hint' => '', 'default' => '+7 (953) 284-32-00', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '40', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
		'footer_phone_link' => [
			'name' => 'footer_phone_link', 'description' => 'Телефон (tel: ссылка)', 'type' => 'text', 'category' => '8',
			'group' => '', 'hint' => '+79532843200', 'default' => '+79532843200', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '20', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
	],
	'groups' => [],
];

file_put_contents(
	ENGINE_DIR . '/data/xfields.json',
	json_encode($xfields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
	LOCK_EX
);
@unlink(ENGINE_DIR . '/cache/system/xfields.php');
$log('XFields: image, service_image, client_logo, footer');

$articleCover = mi_upload_image('articles/card.png', 'article-cover.png');
$partnerLogo = mi_upload_image('clients/logo-magnit.png', 'partner-magnit.png');
$log('Uploads: article=' . ($articleCover ?: 'fail') . ', partner=' . ($partnerLogo ?: 'fail'));

$log('Articles (tags + cover)');
$articleSeeds = [
	'product-placement-v-kino-i-serialah' => [
		'title' => 'Product-placement в кино и сериалах: что работает в 2026',
		'short' => 'Когда интеграция в контент выглядит естественно и как не превратить placement в раздражающую рекламу.',
		'tags' => 'маркетинг',
	],
];
$admin = $db->super_query("SELECT name FROM " . USERPREFIX . "_users WHERE user_group='1' LIMIT 1");
$autor = $db->safesql($admin['name'] ?? 'admin');
foreach ($articleSeeds as $alt => $seed) {
	$altS = $db->safesql($alt);
	$exists = $db->super_query("SELECT id FROM " . PREFIX . "_post WHERE alt_name='{$altS}' LIMIT 1");
	if (!empty($exists['id'])) {
		continue;
	}
	$titleS = $db->safesql($seed['title']);
	$shortS = $db->safesql($seed['short']);
	$tagsS = $db->safesql($seed['tags']);
	$descrS = $db->safesql(mb_substr(strip_tags($seed['short']), 0, 300));
	$xf = $articleCover ? mi_xf_build(['image' => $articleCover]) : $db->safesql('');
	$date = date('Y-m-d H:i:s', time() - 86400);
	$db->query("INSERT INTO " . PREFIX . "_post (autor, date, short_story, full_story, xfields, title, descr, keywords, category, alt_name, comm_num, allow_comm, allow_main, approve, fixed, allow_br, symbol, tags, metatitle) VALUES ('{$autor}', '{$date}', '{$shortS}', '{$shortS}', '{$xf}', '{$titleS}', '{$descrS}', '{$titleS}', '1', '{$altS}', '0', '1', '1', '1', '0', '1', '', '{$tagsS}', '{$titleS}')");
	$newsId = $db->insert_id();
	$db->query("INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate, votes, disable_index, related_ids, access, user_id, disable_search, need_pass, allow_rss, allow_rss_dzen, allowed_country, not_allowed_country) VALUES ('{$newsId}', '1', '0', '0', '', '', '0', '0', '0', '1', '1', '', '')");
	$db->query("INSERT INTO " . PREFIX . "_post_extras_cats (news_id, cat_id) VALUES ('{$newsId}', '1')");
	$db->query("INSERT INTO " . PREFIX . "_tags (news_id, tag) VALUES ('{$newsId}', '{$tagsS}')");
	$log('  + article ' . $alt);
}

$articles = [
	'pochemu-kross-marketing-rabotaet-luchshe' => 'маркетинг',
	'on-packing-kak-tochka-vhoda' => 'брендинг',
	'chto-izmeriat-v-cross-marketing' => 'аналитика',
	'product-placement-v-kino-i-serialah' => 'маркетинг',
];
foreach ($articles as $alt => $tag) {
	$row = $db->super_query("SELECT id FROM " . PREFIX . "_post WHERE alt_name='" . $db->safesql($alt) . "' LIMIT 1");
	if (!$row['id']) {
		continue;
	}
	mi_update_post((int) $row['id'], [
		'tags' => $tag,
		'xfields' => $articleCover ? ['image' => $articleCover] : [],
	]);
}

$log('Services (Figma copy + images)');
$services = [
	'service-on-packing' => [
		'slide' => 1,
		'short' => 'Специализация нашей компании. Изменение дизайна упаковки продукции с целью проведения стимулирующего мероприятия с&nbsp;дальнейшим розыгрышем ценных призов.',
		'full' => '<ul><li>нанесение оффера партнера с целью привлечения новой аудитории.</li><li>проведение внутренней акции производителя с&nbsp;целью повышения продаж.</li></ul>',
	],
	'service-product-placement' => [
		'slide' => 2,
		'short' => 'Органичное интегрирование товаров, услуг или брендов в сюжет фильмов, сериалов, шоу и даже видеоигр. В отличие от прямых рекламных роликов, она выглядит естественной частью повествования.',
		'full' => '',
	],
	'service-licensing' => [
		'slide' => 3,
		'short' => 'Сотрудничество между брендами и правообладателями популярного контента (кино, игр, аниме, комиксов), где компания получает официальное разрешение использовать чужие интеллектуальные свойства (персонажей, вселенные, стилистику) для продвижения своих товаров или услуг.',
		'full' => '',
	],
	'service-gamification' => [
		'slide' => 4,
		'short' => 'Внедрение игровых механик (очки, уровни, награды, соревнования) в&nbsp;неигровые процессы, чтобы повысить вовлеченность и мотивацию аудитории. В маркетинге она используется для стимулирования покупок, лояльности и взаимодействия с брендом.',
		'full' => '',
	],
	'service-qsr' => [
		'slide' => 5,
		'short' => 'Это очень популярный и эффективный канал для продвижения других брендов и продуктов. Используя рекламные инструменты крупным сетей вы привлекаете внимание данной аудитории к своему бренду, повышая узнаваемость и лояльность.',
		'full' => '',
	],
	'service-video' => [
		'slide' => 6,
		'short' => 'Это короткие или долгие видеофильмы, созданные для продвижения бренда, товара, услуги, мероприятия или идей. Они могут быть частью широкого спектра рекламных кампаний и размещаться на самых разных площадках: стриминговые сервисы, социальные сети, телевидение, сайты, потоковое видео и др.',
		'full' => '',
	],
	'service-bloggers' => [
		'slide' => 7,
		'short' => 'Это рекламный инструмент, при котором бренд сотрудничает с владельцами блогов, стримерами или активными пользователями социальных сетей, чтобы продвигать товары или услуги через их контент. Это одна из самых эффективных и гибких стратегий в современном маркетинге, особенно в социальных сетях и на тематических площадках Важно: Работа с блогерами не должна носить шаблонный характер. Сильная рекомендация от эксперта оказывается эффективнее десятков формальных рекламных объявлений.',
		'full' => '',
	],
];
foreach ($services as $alt => $data) {
	$row = $db->super_query("SELECT id FROM " . PREFIX . "_post WHERE alt_name='" . $db->safesql($alt) . "' LIMIT 1");
	if (!$row['id']) {
		continue;
	}
	$imgPath = mi_upload_image('services/slide-' . $data['slide'] . '.png', 'service-' . $alt . '.png');
	mi_update_post((int) $row['id'], [
		'short_story' => $data['short'],
		'full_story' => $data['full'],
		'xfields' => $imgPath ? ['service_image' => $imgPath] : [],
	]);
}

$log('Partners (client_logo)');
$r = $db->query("SELECT id FROM " . PREFIX . "_post WHERE category='5'");
while ($row = $db->get_row($r)) {
	if (!$partnerLogo) {
		break;
	}
	mi_update_post((int) $row['id'], [
		'xfields' => ['client_logo' => $partnerLogo],
	]);
}

$catHints = [
	1 => 'Публичные статьи. Обложка — доп. поле «Обложка». Тег — стандартное поле «Теги» в форме новости.',
	4 => 'Карточки услуг на главной. Краткое = основной текст (Figma). Полное = список только для On-packing. Изображение — доп. поле. Ссылка «Узнать подробнее» = {full-link}. Порядок — дата публикации.',
	5 => 'Логотип — доп. поле «Логотип партнёра» (загрузка файла).',
];
foreach ($catHints as $id => $hint) {
	$hintS = $db->safesql($hint);
	$db->query("UPDATE " . PREFIX . "_category SET descr='{$hintS}' WHERE id='{$id}'");
}

@unlink(ENGINE_DIR . '/cache/system/category.json');
foreach (glob(ENGINE_DIR . '/cache/*.php') ?: [] as $file) {
	@unlink($file);
}

$log('Cache cleared');
$log('Done.');
